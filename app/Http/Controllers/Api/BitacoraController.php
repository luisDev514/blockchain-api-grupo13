<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bitacora;
use Exception;

class BitacoraController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $bitacoras = Bitacora::leftJoin('users', 'id', 'user_id')
                ->leftJoin('persona', 'users.persona_id', '=', 'persona.persona_id')
                ->select('bitacora.bitacora_id', 'persona.nombre_completo', 'bitacora.operacion', 'bitacora.tabla', 'bitacora.tabla_identificador', 'bitacora.fecha', 'bitacora.codigo_app')
                ->get();
            return response()->json([
                "success" => true,
                "message" => "Registro de actividades obtenido exitosamente",
                "data" => $bitacoras
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
            $bitacora = new Bitacora();
            $request->validate([
                'usuario_id',
                'operacion',
                'tabla',
                'tabla_identificador',
                'fecha'
            ]);
            $bitacora->usuario_id = $request->usuario_id;
            $bitacora->operacion = $request->operacion;
            $bitacora->tabla = $request->tabla;
            $bitacora->tabla_identificador = $request->tabla_identificador;
            $bitacora->fecha = $request->fecha;
            $bitacora->save();
            return response()->json([
                "success" => true,
                "message" => "Bitacora registrado exitosamente.",
                "data" => $bitacora
            ]);
        } catch (Exception $e) {
            $message = $e->getMessage();

            return response()->json([
                "success" => false,
                "message" => "No se pudo registrar la categoria",
                "data" => $message
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
            $bitacora = Bitacora::find($id);
            return $bitacora;
        } catch (Exception $e) {
            $message = $e->getMessage();

            return response()->json([
                "success" => false,
                "message" => "No se pudo registrar la categoria",
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
            $bitacora = Bitacora::findOrFail($request->id);
            $request->validate([
                'usuario',
                'operacion_id',
                'tabla',
                'tabla_identificador',
                'fecha'
            ]);
            $bitacora->usuario = $request->usuario;
            $bitacora->operacion_id = $request->operacion_id;
            $bitacora->tabla = $request->tabla;
            $bitacora->tabla_identificador = $request->tabla_identificador;
            $bitacora->fecha = $request->fecha;
            $bitacora->save();
            return response()->json([
                "success" => true,
                "message" => "Bitacora registrado exitosamente.",
                "data" => $bitacora
            ]);
        } catch (Exception $e) {
            $message = $e->getMessage();

            return response()->json([
                "success" => false,
                "message" => "No se pudo registrar la categoria",
                "data" => $message
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
}
