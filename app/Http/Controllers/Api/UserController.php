<?php

namespace App\Http\Controllers\Api;

use App\enums\OperacionesBitacora;
use App\enums\TablasBitacora;
use App\Http\Controllers\Controller;
use App\Models\Aplicacion;
use App\Models\Bitacora;
use App\Models\Rol;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        try {
            $habilitado = $request->input('habilitado');
            $users = User::when($habilitado, function ($query, $habilitado) {
                $query->where('users.habilitado', '=', 1);
            })
                ->with('persona')
                ->with('roles')
                ->get();
            // $users_count = $users->count();
            $users_count = $users->count();
            return response()->json([
                "success" => true,
                "message" => "($users_count) Usuarios obtenidos exitosamente.",
                "data" => $users
            ]);
        } catch (Exception $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "data" => null
            ]);
        }
    }

    public function show($id)
    {
        try {
            $users = User::with('roles')->find($id);
            return response()->json([
                "success" => true,
                "message" => "Usuario obtenido exitosamente.",
                "data" => $users
            ]);
        } catch (Exception $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "data" => null
            ]);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'codigo_app',
                'email',
                'name',
                'persona_id',
                'user',
            ]);
            // Solo puede existir 1 Usuario por Persona
            $usuarioExistente = User::with('persona')->where('persona_id', '=', $request->persona_id)->get()->first();
            error_log("usuarioExistente: $usuarioExistente");
            if ($usuarioExistente) {
                $personaEncontrada = $usuarioExistente->persona->nombre_completo;
                return response()->json([
                    "success" => false,
                    "message" => "Ya existe un Usuario registrado para la Persona: $personaEncontrada",
                    "data" => $usuarioExistente
                ]);
            }
            $user = new User();

            $user->persona_id = $request->persona_id;
            $user->email = $request->email;
            $user->name = $request->name;
            $user->habilitado = $request->habilitado ? 1 : 0;
            $user->password = env('USER_DEFAULT_PASSWORD');
            $user->save();

            $log = new Bitacora();
            $log->user_id = $request->user;
            $log->operacion = OperacionesBitacora::Registrar;
            $log->tabla = TablasBitacora::Users;
            $log->tabla_identificador = $user->id;
            $log->fecha = Carbon::now();
            $log->codigo_app = $request->codigo_app;
            $log->save();

            return response()->json([
                "success" => true,
                "message" => "Usuario registrado exitosamente.",
                "data" => $user
            ]);
        } catch (Exception $e) {
            $message = $e->getMessage();

            return response()->json([
                "success" => false,
                "message" => $message,
                "data" => null
            ]);
        }
    }

    public function update(Request $request)
    {
        try {
            $request->validate([
                'codigo_app',
                'email',
                'name',
                'persona_id',
                'user',
                'user_id',
            ]);
            $user = User::find($request->user_id);
            error_log("user: $user");
            if (!$user) {
                return response()->json([
                    "success" => false,
                    "message" => "No se encontró el Usuario con Username: $request->name",
                    "data" => null
                ]);
            }

            $user->persona_id = $request->persona_id;
            if ($user->email != $request->email) $user->email = $request->email;
            $user->name = $request->name;
            $user->habilitado = $request->habilitado ? 1 : 0;
            $user->save();

            $log = new Bitacora();
            $log->user_id = $request->user;
            $log->operacion = OperacionesBitacora::Modificar;
            $log->tabla = TablasBitacora::Users;
            $log->tabla_identificador = $user->id;
            $log->fecha = Carbon::now();
            $log->codigo_app = $request->codigo_app;
            $log->save();

            return response()->json([
                "success" => true,
                "message" => "Usuario modificado exitosamente.",
                "data" => $user
            ]);
        } catch (Exception $e) {
            $message = $e->getMessage();

            return response()->json([
                "success" => false,
                "message" => $message,
                "data" => null
            ]);
        }
    }

    public function habilitar(Request $request, $id)
    {
        try {
            $request->validate([
                'user',
                'codigo_app',
            ]);
            $user = User::find($id);
            if (!$user) {
                return response()->json([
                    "success" => false,
                    "message" => "No existe el Usuario con id: $id",
                    "data" => $user
                ]);
            }

            $user->habilitado = $user->habilitado === 0 ? 1 : 0;
            $user->save();

            $log = new Bitacora();
            $log->user_id = $request->user;
            $log->operacion = $user->habilitado === 1 ? OperacionesBitacora::Habilitar : OperacionesBitacora::Deshabilitar;
            $log->tabla = TablasBitacora::Users;
            $log->tabla_identificador = $user->id;
            $log->fecha = Carbon::now();
            $log->codigo_app = $request->codigo_app;
            $log->save();

            return response()->json([
                "success" => true,
                "message" => "Estado actualizado correctamente",
                "data" => $user
            ]);
        } catch (Exception $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "data" => null
            ]);
        }
    }

    public function usersByModulo(Request $request)
    {
        try {
            $modulo_nombre = $request->modulo_nombre;
            if (empty($modulo_nombre)) {
                return response()->json([
                    "success" => false,
                    "message" => "No se encontraron Roles con Acceso al Módulo: $modulo_nombre",
                    "data" => null
                ]);
            }
            $usuario_roles = Rol::join('rol_acceso', 'rol_acceso.rol_id', 'rol.rol_id')
                ->join('modulo', 'modulo.modulo_id', 'rol_acceso.modulo')
                ->join('usuario_rol', 'usuario_rol.rol_id', 'rol.rol_id')
                ->where('modulo.nombre', '=', $request->modulo_nombre)
                ->select('usuario_rol.rol_id')
                ->distinct()
                ->get();
            error_log("usuario_roles $usuario_roles");

            $users = User::join('usuario_rol', 'usuario_rol.user_id', 'users.id')
                ->whereIn('usuario_rol.rol_id', $usuario_roles)->get();
            $users_count = $users->count();
            return response()->json([
                "success" => true,
                "message" => "$users_count Usuarios obtenidos exitosamente.",
                "data" => $users
            ]);
        } catch (Exception $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "data" => null
            ]);
        }
    }

    public function usersByComponente(Request $request)
    {
        try {
            $request->validate([
                'nombre_componente',
                'codigo_app',
            ]);

            $aplicacion = Aplicacion::where('codigo', '=', $request->codigo_app)->get()->first();
            if (empty($aplicacion)) {
                return response()->json([
                    "success" => false,
                    "message" => "No se encontraron Aplicaciones con código: $$request->codigo_app",
                    "data" => null
                ]);
            }
            $usuario_roles = Rol::join('rol_asignacion', 'rol_asignacion.rol_id', 'rol.rol_id')
                ->join('componente', 'componente.componente_id', 'rol_asignacion.componente_id')
                ->join('usuario_rol', 'usuario_rol.rol_id', 'rol.rol_id')
                ->where('componente.nombre', '=', $request->nombre_componente)
                ->where('rol_asignacion.aplicacion_id', '=', $aplicacion->aplicacion_id)
                ->select('usuario_rol.rol_id')
                ->distinct()
                ->get();

            $users = User::join('usuario_rol', 'usuario_rol.user_id', 'users.id')
                ->whereIn('usuario_rol.rol_id', $usuario_roles)->with('persona')->get();
            $users_count = $users->count();
            return response()->json([
                "success" => true,
                "message" => "$users_count Usuarios obtenidos exitosamente.",
                "data" => $users
            ]);
        } catch (Exception $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "data" => null
            ]);
        }
    }

    public function usersByUnidadNegocio($unidad_negocio_id)
    {
        try {
            $users = User::join('persona', 'persona.persona_id', 'users.persona_id')
                ->join('unidad_negocio', 'unidad_negocio.codigo', 'persona.unidad_negocio_id')
                ->where('unidad_negocio.codigo', '=', $unidad_negocio_id)
                ->with('persona')->get();
            $users_count = $users->count();
            return response()->json([
                "success" => true,
                "message" => "$users_count Usuarios obtenidos exitosamente.",
                "data" => $users
            ]);
        } catch (Exception $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "data" => null
            ]);
        }
    }

    public function usersByApp(Request $request)
    {
        try {
            $codigo_app = $request->input('codigoApp');
            $users = User::join('usuario_rol', 'usuario_rol.user_id', 'users.id')
                ->join('rol', 'usuario_rol.rol_id', 'rol.rol_id')
                ->join('aplicacion', 'rol.aplicacion_id', 'aplicacion.aplicacion_id')
                ->where('aplicacion.codigo', 'like', "'%$codigo_app%'")
                ->get([
                    'users.name',
                    'usuario_rol.rol_id',
                    'usuario_rol.user_id',
                    'rol.nombre',
                    'rol.aplicacion_id',
                    'aplicacion.nombre'
                ]);
            $users_count = $users->count();
            return response()->json([
                "success" => true,
                "message" => "($users_count) Usuarios obtenidos exitosamente.",
                "data" => $users
            ]);
        } catch (Exception $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "data" => null
            ]);
        }
    }
}
