<?php

namespace App\Http\Controllers\Api;

use App\enums\DefaultModulos;
use App\enums\OperacionesBitacora;
use App\enums\TablasBitacora;
use \Exception;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Aplicacion;
use App\Models\Bitacora;
use App\Models\Modulo;
use Carbon\Carbon;

class AplicacionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $habilitado = $request->query('habilitado');
            $roles = $request->query('roles');
            if ($habilitado != null) {
                $aplicaciones = $roles ? Aplicacion::where('habilitado', '=', $habilitado)->with('roles')->get() : Aplicacion::where('habilitado', '=', $habilitado)->get();
                $aplicaciones_count = $aplicaciones->count();
                return response()->json([
                    "success" => true,
                    "message" => "$aplicaciones_count Aplicaciones habilitadas listadas correctamente",
                    "data" => $aplicaciones
                ]);
            }
            $aplicaciones = $roles ? Aplicacion::with('roles')->get() : Aplicacion::get();
            $aplicaciones_count = $aplicaciones->count();

            return response()->json([
                "success" => true,
                "message" => "$aplicaciones_count Aplicaciones listadas correctamente",
                "data" => $aplicaciones
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
                'codigo',
                'codigo_app',
                'nombre',
                'version',
                'habilitado',
                'user'
            ]);
            error_log("request: $request");

            $aplicacion = new Aplicacion();
            $aplicacion->codigo = $request->codigo;
            $aplicacion->nombre = $request->nombre;
            $aplicacion->titulo = $request->titulo;
            $aplicacion->icono = $request->icono;
            $aplicacion->url = $request->url;
            $aplicacion->descripcion = $request->descripcion;
            $aplicacion->area = $request->area;
            $aplicacion->base_datos = $request->base_datos;
            $aplicacion->ip_servidor = $request->ip_servidor;
            $aplicacion->version = $request->version;
            $habilitado = $request->habilitado == true ? 1 : 0;
            $aplicacion->habilitado = $habilitado;
            $aplicacion->save();

            $modulo = new Modulo();
            $modulo->aplicacion_id = $aplicacion->aplicacion_id;
            $modulo->url = DefaultModulos::Url;
            $modulo->nombre = DefaultModulos::Nombre;
            $modulo->icono = DefaultModulos::Icono;
            $modulo->menu = 1;
            $modulo->habilitado = 1;
            $modulo->titulo = DefaultModulos::Titulo;
            $modulo->save();

            $log = new Bitacora();

            $log->user_id = $request->user;
            $log->operacion = OperacionesBitacora::Registrar;
            $log->tabla = TablasBitacora::Aplicacion;
            $log->tabla_identificador = $aplicacion->aplicacion_id;
            $log->fecha = Carbon::now();
            $log->codigo_app = $request->codigo_app;
            $log->save();

            return response()->json([
                "success" => true,
                "message" => "Aplicación registrada exitosamente.",
                "data" => $aplicacion
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
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $aplicacion = Aplicacion::with('roles')->with('modulos')->find($id);
            return $aplicacion;
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
            $aplicacion = Aplicacion::findOrFail($id);
            $request->validate([
                'user',
                'codigo',
                'codigo_app',
                'nombre',
                'version',
            ]);
            $aplicacion->codigo = $request->codigo;
            $aplicacion->nombre = $request->nombre;
            $aplicacion->titulo = $request->titulo;
            $aplicacion->icono = $request->icono;
            $aplicacion->url = $request->url;
            $aplicacion->descripcion = $request->descripcion;
            $aplicacion->area = $request->area;
            $aplicacion->base_datos = $request->base_datos;
            $aplicacion->ip_servidor = $request->ip_servidor;
            $aplicacion->version = $request->version;
            $aplicacion->habilitado = $request->habilitado;
            $aplicacion->save();

            $log = new Bitacora();
            $log->user_id = $request->user;
            $log->operacion = OperacionesBitacora::Modificar;
            $log->tabla = TablasBitacora::Aplicacion;
            $log->tabla_identificador = $aplicacion->aplicacion_id;
            $log->fecha = Carbon::now();
            $log->codigo_app = $request->codigo_app;
            $log->save();

            return response()->json([
                "success" => true,
                "message" => "Aplicación actualizada correctamente",
                "data" => $aplicacion
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
            $request->validate([
                'user',
                'codigo_app',
            ]);
            $aplicacion = Aplicacion::find($id);
            if (!$aplicacion) {
                return response()->json([
                    "success" => false,
                    "message" => "No existe la Aplicación con id: $id",
                    "data" => $aplicacion
                ]);
            }

            $aplicacion->habilitado = $aplicacion->habilitado === 0 ? 1 : 0;
            $aplicacion->save();

            $log = new Bitacora();
            $log->user_id = $request->user;
            $log->operacion = $aplicacion->habilitado === 1 ? OperacionesBitacora::Habilitar : OperacionesBitacora::Deshabilitar;
            $log->tabla = TablasBitacora::Aplicacion;
            $log->tabla_identificador = $aplicacion->aplicacion_id;
            $log->fecha = Carbon::now();
            $log->codigo_app = $request->codigo_app;
            $log->save();

            return response()->json([
                "success" => true,
                "message" => "Estado actualizado correctamente",
                "data" => $aplicacion
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
        $aplicacion = Aplicacion::find($id);
        $aplicacion->habilitado = 0;
        $aplicacion->save();
        return $aplicacion;
    }
}
