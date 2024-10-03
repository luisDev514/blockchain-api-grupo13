<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Modulo;
use Illuminate\Http\Request;
use App\Models\Rol_Acceso;
use Exception;

class Rol_AccesoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            // $modulos = Modulo::where('aplicacion_id', '', $aplicacion_id)->get();
            $accesos = Rol_Acceso::with('modulo:modulo_id,nombre')->with('rol:rol_id,nombre')->get();
            return response()->json([
                "success" => true,
                "message" => "Accesos obtenidos correctamente",
                "data" => $accesos
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
        $rol_acceso = new Rol_Acceso();
        $request->validate([
            'rol_id',
            'modulo',
        ]);
        $rol_acceso->rol_id = $request->rol_id;
        $rol_acceso->modulo = $request->modulo;
        $rol_acceso->save();
        return response()->json([
            "success" => true,
            "message" => "Administrador registrado exitosamente.",
            "data" => $rol_acceso
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $rol_acceso = Rol_Acceso::find($id);
        return $rol_acceso;
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
        $rol_acceso = Rol_Acceso::findOrFail($request->id);
        $request->validate([
            'rol_id',
            'modulo',
        ]);
        $rol_acceso->rol_id = $request->rol_id;
        $rol_acceso->modulo = $request->modulo;
        $rol_acceso->save();
        return response()->json([
            "success" => true,
            "message" => "Acceso actualizado exitosamente.",
            "data" => $rol_acceso
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $rol_acceso = Rol_Acceso::destroy($id);
            return $rol_acceso;
        } catch (Exception $e) {
            $message = $e->getMessage();

            return response()->json([
                "success" => false,
                "message" => "No se pudo registrar la categoria",
                "data" => $message
            ]);
        }
    }

    // public function showByRol($rol_id)
    // {
    //     $rol_acceso = Rol_Acceso::join('rol', 'rol_acceso.rol_id', '', 'rol.rol_id')
    //         ->join('modulo', 'rol_acceso.modulo_id', '=', 'modulo.modulo')
    //         ->where('rol_acceso.rol_id', '', $rol_id)
    //         ->where('rol_acceso.rol_id', '', $rol_id)

    //         ->get(['rol_acceso.acceso_id', 'rol.rol_id', 'rol.nombre', 'modulo.modulo', 'modulo.modulo_padre',  'modulo.menu', 'modulo.titulo', 'modulo.nombre', 'modulo.url']);
    //     return $rol_acceso;
    // }
}
