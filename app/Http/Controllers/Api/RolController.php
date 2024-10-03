<?php

namespace App\Http\Controllers\Api;

use App\enums\OperacionesBitacora;
use App\enums\TablasBitacora;
use App\Http\Controllers\Controller;
use App\Models\Bitacora;
use Illuminate\Http\Request;
use App\Models\Rol;
use App\Models\Rol_Asignacion;
use Carbon\Carbon;
use Exception;

class RolController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $habilitado = $request->input('habilitado');
        $aplicacion_id = $request->input('aplicacionId');

        $roles = Rol::with('users')
            ->with('aplicacion')
            ->when($aplicacion_id, function ($query, $aplicacion_id) {
                $query->where('rol.aplicacion_id', '=', $aplicacion_id);
            })
            ->when($habilitado, function ($query, $habilitado) {
                $query->where('rol.habilitado', '=', $habilitado);
            })
            ->get();
        $roles_count = $roles->count();
        error_log("rol_usuario: $roles");
        return response()->json([
            "success" => true,
            "message" => "($roles_count) Roles obtenidos exitosamente.",
            "data" => $roles
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $rol = new Rol();
            $request->validate([
                'aplicacion_id',
                'codigo_app',
                'habilitado',
                'nombre',
                'user',
            ]);
            $rol->nombre = $request->nombre;
            $rol->habilitado = $request->habilitado;
            $rol->aplicacion_id = $request->aplicacion_id;
            $rol->save();

            $log = new Bitacora();
            $log->user_id = $request->user;
            $log->operacion = $rol->habilitado === OperacionesBitacora::Registrar;
            $log->tabla = TablasBitacora::Rol;
            $log->tabla_identificador = $rol->rol_id;
            $log->fecha = Carbon::now();
            $log->codigo_app = $rol->codigo_app;
            $log->save();

            return response()->json([
                "success" => true,
                "message" => "Rol registrado exitosamente.",
                "data" => $rol
            ]);
        } catch (Exception $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "data" => null
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        // $rol = Rol::with('users')->with('aplicacion')->with('modulos')->find($id);
        $rol = Rol::when($request->input('withUsers'), function ($query) {
            $query->with('users');
        })
            ->with('aplicacion')->with('modulos')->find($id);
        return $rol;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $rol = Rol::find($id);
            if (!$rol) {
                return response()->json([
                    "success" => false,
                    "message" => "No se econtró el Rol con id: $id.",
                    "data" => null
                ]);
            }

            $request->validate([
                'codigo_app',
                'habilitado',
                'nombre',
                'rol_id',
                'user',
            ]);

            $rol->nombre = $request->nombre;
            $rol->habilitado = $request->habilitado === true ? 1 : 0;
            $rol->save();

            $log = new Bitacora();
            $log->user_id = $request->user;
            $log->operacion = OperacionesBitacora::Modificar;
            $log->tabla = TablasBitacora::Rol;
            $log->tabla_identificador = $rol->rol_id;
            $log->fecha = Carbon::now();
            $log->codigo_app = $request->codigo_app;
            $log->save();

            return response()->json([
                "success" => true,
                "message" => "Rol modificado exitosamente.",
                "data" => $rol
            ]);
        } catch (Exception $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "data" => null
            ]);
        }
    }

    public function addAccesos(Request $request)
    {
        try {
            $request->validate([
                'accesos',
                'codigo_app',
                'rol_id',
                'user',
            ]);
            $rol = Rol::find($request->rol_id);
            if (!$rol) {
                return response()->json([
                    "success" => false,
                    "message" => "No se econtró el Rol con id: $request->rol_id.",
                    "data" => null
                ]);
            }

            $rol->modulos()->sync($request->accesos);

            $log = new Bitacora();
            $log->user_id = $request->user;
            $log->operacion = OperacionesBitacora::Modificar;
            $log->tabla = TablasBitacora::Rol_Acceso;
            $log->tabla_identificador = $rol->rol_id;
            $log->fecha = Carbon::now();
            $log->codigo_app = $request->codigo_app;
            $log->save();

            return response()->json([
                "success" => true,
                "message" => "Rol modificado exitosamente.",
                "data" => $rol
            ]);
        } catch (Exception $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "data" => null
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function habilitar(Request $request, $id)
    {
        try {
            $request->validate([
                'user',
                'codigo_app',
            ]);
            $rol = Rol::find($id);
            if (!$rol) {
                return response()->json([
                    "success" => false,
                    "message" => "No existe el Rol con id: $id",
                    "data" => $rol
                ]);
            }

            $rol->habilitado = $rol->habilitado === 0 ? 1 : 0;
            $rol->save();

            $log = new Bitacora();
            $log->user_id = $request->user;
            $log->operacion = $rol->habilitado === 1 ? OperacionesBitacora::Habilitar : OperacionesBitacora::Deshabilitar;
            $log->tabla = TablasBitacora::Rol;
            $log->tabla_identificador = $rol->rol_id;
            $log->fecha = Carbon::now();
            $log->codigo_app = $request->codigo_app;
            $log->save();

            return response()->json([
                "success" => true,
                "message" => "Estado actualizado correctamente",
                "data" => $rol
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
