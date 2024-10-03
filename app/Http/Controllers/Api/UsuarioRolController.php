<?php

namespace App\Http\Controllers\Api;

use App\enums\OperacionesBitacora;
use App\enums\TablasBitacora;
use App\Http\Controllers\Controller;
use App\Models\Aplicacion;
use App\Models\Bitacora;
use App\Models\Rol;
use App\Models\User;
use App\Models\Usuario_Rol;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

class UsuarioRolController extends Controller
{
    public function index()
    {
        try {
            $users = Usuario_Rol::with('usuario')->with('rol')->get();
            return response()->json([
                "success" => true,
                "message" => "Usuario Rol obtenidos exitosamente.",
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
            $users = Usuario_Rol::with('usuario')->with('rol')->find($id);
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
                'rol_id',
                'user_id',
                'user',
            ]);
            // Solo puede existir 1 Usuario_Rol por Usuario
            $usuarioExistente = Usuario_Rol::where('rol_id', '=', $request->rol_id)->where('user_id', '=', $request->user_id)->get()->first();
            error_log("usuarioExistente: $usuarioExistente");
            if ($usuarioExistente) {
                $user = User::where('id', '=', $request->user_id)->get()->first();
                return response()->json([
                    "success" => false,
                    "message" => "Ya existe un Rol asignado al Usuario: $user->name",
                    "data" => null
                ]);
            }
            $usuario_rol = new Usuario_Rol();

            $usuario_rol->user_id = $request->user_id;
            $usuario_rol->rol_id = $request->rol_id;
            $usuario_rol->save();

            $log = new Bitacora();
            $log->user_id = $request->user;
            $log->operacion = OperacionesBitacora::Registrar;
            $log->tabla = TablasBitacora::Usuario_Rol;
            $log->tabla_identificador = $usuario_rol->usuario_rol_id;
            $log->fecha = Carbon::now();
            $log->codigo_app = $request->codigo_app;
            $log->save();

            return response()->json([
                "success" => true,
                "message" => "Usuario Rol registrado exitosamente.",
                "data" => $usuario_rol
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

    public function destroy(Request $request)
    {
        try {
            $request->validate([
                'codigo_app',
                'rol_id',
                'user_id',
            ]);

            $usuario_rol = Usuario_Rol::where('rol_id', '=', $request->rol_id)->where('user_id', '=', $request->user_id)->get()->first();
            if (!$usuario_rol) {
                return response()->json([
                    "success" => false,
                    "message" => "No existe un Rol asignado al Usuario: $request->user_id",
                    "data" => null
                ]);
            }
            $usuario_rol_id = $usuario_rol->usuario_rol_id;
            $usuario_rol->delete();

            $log = new Bitacora();
            $log->user_id = $request->user_id;
            $log->operacion = OperacionesBitacora::Eliminar;
            $log->tabla = TablasBitacora::Usuario_Rol;
            $log->tabla_identificador = $usuario_rol_id;
            $log->fecha = Carbon::now();
            $log->codigo_app = $request->codigo_app;
            $log->save();

            return response()->json([
                "success" => true,
                "message" => "Usuario Rol eliminado exitosamente.",
                "data" => null
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

    public function setRolBase(Request $request)
    {
        try {
            $request->validate([
                'codigo',
                'codigo_app',
                'rol_id',
                'user',
            ]);

            // Buscar el rol
            $rol = Rol::find($request->rol_id);
            error_log("rol $rol");
            if (!$rol) { // No existe el rol con id = $request->rol_id
                return response()->json([
                    "success" => false,
                    "message" => "No se encontró el Rol con id: $request->rol_id",
                    "data" => null
                ]);
            }

            // Buscar la aplicacion
            $aplicacion = Aplicacion::where('codigo', '=', $request->codigo_app)->get()->first();
            error_log("aplicacion $aplicacion");
            if (!$aplicacion) { // No existe la Aplicacion con codigo = $request->codigo_app
                return response()->json([
                    "success" => false,
                    "message" => "No se encontró la Aplicacion con codigo: $request->codigo_app",
                    "data" => null
                ]);
            }

            if ($rol->aplicacion_id != $aplicacion->aplicacion_id) { // No coincide el rol con la aplicacion
                return response()->json([
                    "success" => false,
                    "message" => "El Rol con id: $request->rol_id no pertenece a la Aplicación con id: $aplicacion->aplicacion_id",
                    "data" => null
                ]);
            }

            $user_in_app = Usuario_Rol::join('rol', 'rol.rol_id', 'usuario_rol.rol_id')
                ->join('aplicacion', 'aplicacion.aplicacion_id', 'rol.aplicacion_id')
                ->where('aplicacion.aplicacion_id', '=', $aplicacion->aplicacion_id)
                ->select('usuario_rol.user_id')
                ->get();

            $users = User::whereNotIn('users.id', $user_in_app)->get();
            error_log("users $users");
            if ($users->count() == 0) { //empty($users)
                return response()->json([
                    "success" => false,
                    "message" => "No se encontraron usuarios sin rol asignado",
                    "data" => null
                ]);
            }

            $users->map(function ($user) use ($request) {
                $usuario_rol = new Usuario_Rol();
                $usuario_rol->user_id = $user->id;
                $usuario_rol->rol_id = $request->rol_id;
                $usuario_rol->save();
            });

            $log = new Bitacora();
            $log->user_id = $request->user;
            $log->operacion = OperacionesBitacora::Registrar_en_bloque;
            $log->tabla = TablasBitacora::Usuario_Rol;
            $log->tabla_identificador = $rol->rol_id;
            $log->fecha = Carbon::now();
            $log->codigo_app = $request->codigo;
            $log->save();

            return response()->json([
                "success" => true,
                "message" => "Usuario Rol registrados exitosamente.",
                "data" => $users
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
}
