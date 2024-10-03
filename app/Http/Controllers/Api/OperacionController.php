<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Operacion;
use Exception;

class OperacionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $operaciones = Operacion::all();
            return $operaciones;
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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {}

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $operacion = new Operacion();
            $request->validate([
                'nombre',
            ]);
            $operacion->nombre = $request->nombre;
            $operacion->save();
            return response()->json([
                "success" => true,
                "message" => "Administrador registrado exitosamente.",
                "data" => $operacion
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
            $operacion = Operacion::find($id);
            return $operacion;
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
            $operacion = Operacion::findOrFail($request->id);
            $request->validate([
                'nombre',
            ]);
            $operacion->nombre = $request->nombre;
            $operacion->save();
            return response()->json([
                "success" => true,
                "message" => "Administrador registrado exitosamente.",
                "data" => $operacion
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
