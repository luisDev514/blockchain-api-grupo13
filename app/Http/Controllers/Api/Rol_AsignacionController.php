<?php

namespace App\Http\Controllers\Api;

use App\enums\OperacionesBitacora;
use App\enums\TablasBitacora;
use App\Http\Controllers\Controller;
use App\Models\Aplicacion;
use App\Models\Bitacora;
use App\Models\Componente;
use Illuminate\Http\Request;
use App\Models\Rol_Asignacion;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class Rol_AsignacionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            // DB::enableQueryLog();
            $rol_id = $request->query('rol-id');
            $aplicacion_id = $request->query('aplicacion-id');
            $rol_asignaciones = Rol_Asignacion::join('rol', 'rol.rol_id', 'rol_asignacion.rol_id')
                ->join('aplicacion', 'aplicacion.aplicacion_id', 'rol_asignacion.aplicacion_id')
                ->join('componente', 'componente.componente_id', 'rol_asignacion.componente_id')
                ->when($aplicacion_id, function ($query, $aplicacion_id) {
                    $query->where('rol_asignacion.aplicacion_id', '=', $aplicacion_id);
                })
                ->when($rol_id, function ($query, $rol_id) {
                    $query->where('rol_asignacion.rol_id', '=', $rol_id);
                })
                ->get([
                    'rol_asignacion.rol_asignacion_id', 'componente.componente_id', 'componente.nombre as nombre_componente', 'rol_asignacion.aplicacion_id', 'rol_asignacion.nombre', 'rol.rol_id', 'rol.nombre as rol', 'rol_asignacion.visible', 'rol_asignacion.editable', 'rol_asignacion.habilitado', 'aplicacion.codigo as codigo_app'
                ]);

            // $queryLog = DB::getQueryLog();
            $rol_asignaciones_count = $rol_asignaciones->count();

            return response()->json([
                "success" => true,
                "message" => "($rol_asignaciones_count) Rol asignaciones obtenidos exitosamente",
                "data" => $rol_asignaciones
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

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
            $request->validate([
                'aplicacion_id',
                'codigo_app',
                'componente_id',
                'editable',
                'habilitado',
                'nombre',
                'rol_id',
                'user',
                'visible',
            ]);
            $componente = Componente::find($request->componente_id);
            if (!$componente) {
                return response()->json([
                    "success" => false,
                    "message" => "No se econtró el Componente con id: $request->componente_id.",
                    "data" => null
                ]);
            }

            $data = new Rol_Asignacion();
            $data->rol_id = $request->rol_id;
            $data->componente_id = $request->componente_id;
            $data->aplicacion_id = $request->aplicacion_id;
            $data->nombre = $request->nombre;
            $data->visible = $request->visible ? 1 : 0;
            $data->habilitado = $request->habilitado ? 1 : 0;
            $data->editable = $request->editable ? 1 : 0;
            $data->save();

            $log = new Bitacora();
            $log->user_id = $request->user;
            $log->operacion = OperacionesBitacora::Modificar;
            $log->tabla = TablasBitacora::Rol_Asignacion;
            $log->tabla_identificador = $componente->componente_id;
            $log->fecha = Carbon::now();
            $log->codigo_app = $request->codigo_app;
            $log->save();

            return response()->json([
                "success" => true,
                "message" => "Asignación realizada exitosamente.",
                "data" => $componente
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
    public function show($id)
    {
        try {
            $rol_asignacion = Rol_Asignacion::join('rol', 'rol.rol_id', 'rol_asignacion.rol_id')
                ->join('aplicacion', 'aplicacion.aplicacion_id', 'rol_asignacion.aplicacion_id')
                ->select('rol_asignacion.rol_asignacion_id', '.componente_id', 'rol_asignacion.aplicacion_id', 'rol_asignacion.nombre', 'rol.rol_id', 'rol.nombre as rol', 'rol_asignacion.visible', 'rol_asignacion.editable', 'rol_asignacion.habilitado')
                ->where('rol_asignacion.rol_asignacion_id', '=', $id)->get()->first();
            if (!$rol_asignacion) {
                return response()->json([
                    "success" => false,
                    "message" => "No se encontró la Asignación con id: $id.",
                    "data" => null
                ]);
            }
            return response()->json([
                "success" => true,
                "message" => "Asignacion obtenida exitosamente.",
                "data" => $rol_asignacion
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
            $request->validate([
                'codigo_app',
                'editable',
                'habilitado',
                'user',
                'visible',
            ]);
            $rol_asignacion = Rol_Asignacion::find($id);
            if (!$rol_asignacion) {
                return response()->json([
                    "success" => false,
                    "message" => "No se econtró la Asignación con id: $id.",
                    "data" => null
                ]);
            }

            $rol_asignacion->visible = $request->visible ? 1 : 0;
            $rol_asignacion->habilitado = $request->habilitado ? 1 : 0;
            $rol_asignacion->editable = $request->editable ? 1 : 0;

            $rol_asignacion->rol_id = $request->rol_id;
            $rol_asignacion->componente_id = $request->componente_id;
            $rol_asignacion->aplicacion_id = $request->aplicacion_id;
            $rol_asignacion->save();

            $log = new Bitacora();
            $log->user_id = $request->user;
            $log->operacion = OperacionesBitacora::Modificar;
            $log->tabla = TablasBitacora::Rol_Asignacion;
            $log->tabla_identificador = $rol_asignacion->rol_asignacion_id;
            $log->fecha = Carbon::now();
            $log->codigo_app = $request->codigo_app;
            $log->save();

            return response()->json([
                "success" => true,
                "message" => "Asignación modificada exitosamente.",
                "data" => $rol_asignacion
            ]);
        } catch (Exception $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "data" => null
            ]);
        }
    }

    public function habilitar(Request $request, $id)
    {
        try {
            $rol_asignacion = Rol_Asignacion::find($id);

            if (!$rol_asignacion) {
                return response()->json([
                    "success" => false,
                    "message" => "No se encontró la Asignación con Id:$id.",
                    "data" => $rol_asignacion
                ]);
            }

            $request->validate([
                'habilitado',
                'codigo_app',
                'habilitado',
                'user'
            ]);
            $rol_asignacion->habilitado = $rol_asignacion->habilitado === 0 ? 1 : 0;
            $rol_asignacion->save();

            $log = new Bitacora();

            $log->user_id = $request->user;
            $log->operacion = OperacionesBitacora::Modificar->value;
            $log->tabla = TablasBitacora::Rol_Asignacion;
            $log->tabla_identificador = $rol_asignacion->rol_asignacion_id;
            $log->fecha = Carbon::now();
            $log->codigo_app = $request->codigo_app;
            $log->save();

            return response()->json([
                "success" => true,
                "message" => "Asignación modificada exitosamente.",
                "data" => $rol_asignacion
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
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        try {
            $rol_asignacion = Rol_Asignacion::find($id);

            if (!$rol_asignacion) {
                return response()->json([
                    "success" => false,
                    "message" => "No se encontró la Asignación con Id:$id.",
                    "data" => $rol_asignacion
                ]);
            }

            $request->validate([
                'habilitado',
                'codigo_app',
                'habilitado',
                'user'
            ]);
            $rol_asignacion->delete();

            $log = new Bitacora();

            $log->user_id = $request->user;
            $log->operacion = OperacionesBitacora::Eliminar->value;
            $log->tabla = TablasBitacora::Rol_Asignacion;
            $log->tabla_identificador = $id;
            $log->fecha = Carbon::now();
            $log->codigo_app = $request->codigo_app;
            $log->save();

            return response()->json([
                "success" => true,
                "message" => "Asignación eliminada exitosamente.",
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
}
