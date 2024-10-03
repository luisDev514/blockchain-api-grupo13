<?php

namespace App\Http\Controllers\Api;

use App\enums\OperacionesBitacora;
use App\enums\TablasBitacora;
use App\enums\TiposAcceso;
use App\Http\Controllers\Controller;
use App\Models\Aplicacion;
use App\Models\Bitacora;
use Illuminate\Http\Request;
use App\Models\Modulo;
use Carbon\Carbon;
use Exception;

class ModuloController extends Controller
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
            if ($habilitado != null) {
                error_log("Habilitado $habilitado");
                $modulos = Modulo::with('aplicacion')->where('habilitado', '=', $habilitado)->get();
            } else {
                error_log("Habilitado $habilitado");
                $modulos = Modulo::with('aplicacion')->get();
            }

            return response()->json([
                "success" => true,
                "message" => "Modulos obtenidos correctamente",
                "data" => $modulos
            ]);
        } catch (Exception $e) {
            $message = $e->getMessage();
            error_log("message $message");
            return response()->json([
                "success" => false,
                "message" => $message,
                "data" => null
            ]);
        }
    }

    public function getOperacion($tipo_acceso)
    {
        switch ($tipo_acceso) {
            case (TiposAcceso::Insert):
                return OperacionesBitacora::Registrar->value;
            case (TiposAcceso::Update):
                return OperacionesBitacora::Modificar->value;
            case (TiposAcceso::Delete):
                return OperacionesBitacora::Eliminar->value;
            default:
                return '';
        }
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
            $modulo = new Modulo();
            $request->validate([
                'aplicacion_id',
                'codigo_app',
                'icono',
                'menu',
                'modulo_padre',
                'nombre',
                'url',
                'user',
                'titulo',
                'habilitado',
            ]);

            // Modulo padre
            if ($request->menu) {
                $modulo->aplicacion_id = $request->aplicacion_id;
                $modulo->url = '/' . $request->url;
                $modulo->nombre = $request->nombre;
                $modulo->titulo = $request->titulo;
                $modulo->icono = $request->icono;
                $modulo->menu = $request->menu;
                $modulo->habilitado = $request->habilitado ? 1 : 0;
                $modulo->save();

                $log = new Bitacora();
                $log->user_id = $request->user;
                $log->operacion = OperacionesBitacora::Registrar->value . ' - ' . $request->titulo;
                $log->tabla = TablasBitacora::Modulo;
                $log->tabla_identificador = $modulo->modulo_id;
                $log->fecha = Carbon::now();
                $log->codigo_app = $request->codigo_app;
                $log->save();

                return response()->json([
                    "success" => true,
                    "message" => "Modulo registrado exitosamente.",
                    "data" => $modulo
                ]);
            }

            // Insertar los demas modulos: insert, update, delete
            foreach (TiposAcceso::cases() as $tipo) {
                $op = '';
                switch ($tipo->value) {
                    case (TiposAcceso::Insert->value):
                        $op = OperacionesBitacora::Registrar->value;
                        break;
                    case (TiposAcceso::Update->value):
                        $op = OperacionesBitacora::Modificar->value;
                        break;
                    case (TiposAcceso::Delete->value):
                        $op = OperacionesBitacora::Eliminar->value;
                        break;
                    default:
                        $op = '';
                }

                $requestUrl = $tipo->value === TiposAcceso::Index->value ? str_replace('_', '-', $request->url) : $request->url;
                $modulo_hijo = new Modulo();
                $modulo_hijo->aplicacion_id = $request->aplicacion_id;
                $modulo_hijo->modulo_padre = $request->modulo_padre;
                $modulo_hijo->url = $tipo->value === TiposAcceso::Index->value ? '/' . $requestUrl : '/' . $requestUrl . $tipo->value;
                $modulo_hijo->nombre = $request->nombre . $tipo->value;
                $modulo_hijo->titulo = empty($op) ? $request->titulo : $op . ' ' . $request->titulo;
                error_log("modulo_hijo->titulo: $modulo_hijo->titulo");
                $modulo_hijo->icono = $request->icono;
                $modulo_hijo->menu = 0;
                $modulo_hijo->habilitado = $request->habilitado ? 1 : 0;
                $modulo_hijo->save();

                $log = new Bitacora();
                $log->user_id = $request->user;
                $log->operacion = OperacionesBitacora::Registrar->value . ' - ' . $modulo_hijo->titulo;
                $log->tabla = TablasBitacora::Modulo;
                $log->tabla_identificador = $modulo_hijo->modulo_id;
                $log->fecha = Carbon::now();
                $log->codigo_app = $request->codigo_app;
                $log->save();
            }

            return response()->json([
                "success" => true,
                "message" => "Modulos registrados exitosamente.",
                "data" => []
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
            // $modulo = Modulo::find($id);
            $modulo = Modulo::with('acceso')->find($id);
            return $modulo;
        } catch (Exception $e) {
            $message = $e->getMessage();

            return response()->json([
                "success" => false,
                "message" => "No se pudo obtener los detalles del modulo con id: " . $id,
                "data" => $message
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
            $modulo = Modulo::find($id);
            if (!$modulo) {
                return response()->json([
                    "success" => false,
                    "message" => "No se econtró el Modulo con id: $id.",
                    "data" => null
                ]);
            }
            $request->validate([
                'aplicacion_id',
                'icono',
                'modulo_padre',
                'nombre',
                'titulo',
                'url',
            ]);
            // $modulo->aplicacion_id = $request->aplicacion_id;
            if ($request->modulo_padre) $modulo->modulo_padre = $request->modulo_padre;
            $modulo->icono = $request->icono;
            $modulo->menu = $request->menu;
            $modulo->nombre = $request->nombre;
            $modulo->titulo = $request->titulo;
            $modulo->url = $request->url;
            $modulo->save();

            $log = new Bitacora();
            $log->user_id = $request->user;
            $log->operacion = $modulo->habilitado === OperacionesBitacora::Modificar;
            $log->tabla = TablasBitacora::Modulo;
            $log->tabla_identificador = $modulo->modulo_id;
            $log->fecha = Carbon::now();
            $log->codigo_app = $request->codigo_app;
            $log->save();

            return response()->json([
                "success" => true,
                "message" => "Modulo modificado exitosamente.",
                "data" => $modulo
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
            $modulo = Modulo::find($id);
            if (!$modulo) {
                return response()->json([
                    "success" => false,
                    "message" => "No existe el Modulo con id: $id",
                    "data" => $modulo
                ]);
            }

            $modulo->habilitado = $modulo->habilitado === 0 ? 1 : 0;
            $modulo->save();

            $log = new Bitacora();
            $log->user_id = $request->user;
            $log->operacion = $modulo->habilitado === 1 ? OperacionesBitacora::Habilitar : OperacionesBitacora::Deshabilitar;
            $log->tabla = TablasBitacora::Modulo;
            $log->tabla_identificador = $modulo->modulo_id;
            $log->fecha = Carbon::now();
            $log->codigo_app = $request->codigo_app;
            $log->save();

            return response()->json([
                "success" => true,
                "message" => "Estado actualizado correctamente",
                "data" => $modulo
            ]);
        } catch (Exception $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "data" => null
            ]);
        }
    }

    public function showByApp($codigo_app)
    {
        try {
            $app = Aplicacion::where('codigo', '=', $codigo_app)->get()->first();
            if (!$app) {
                return response()->json([
                    "success" => false,
                    "message" => "No se encontró la aplicación con código: $codigo_app",
                    "data" => null
                ]);
            }

            $modulos = Modulo::where('aplicacion_id', '=', $app->aplicacion_id)->with('aplicacion')->get();
            return response()->json([
                "success" => true,
                "message" => "Modulos obtenidos correctamente",
                "data" => $modulos
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
