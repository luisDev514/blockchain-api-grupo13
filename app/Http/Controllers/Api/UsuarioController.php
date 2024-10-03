<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\Modulo;
use App\Models\Rol_Acceso;

class UsuarioController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $usuarios = Usuario::with('roles.accesos')->get();
        return $usuarios;
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
        $usuario = new Usuario();
        $request->validate([
            'persona_id',
            'rol_id',
            'habilitado',
        ]);
        $usuario->persona_id = $request->persona_id;
        $usuario->rol_id = $request->rol_id;
        $usuario->habilitado = $request->habilitado;
        $usuario->save();
        return response()->json([
            "success" => true,
            "message" => "Usuario registrado exitosamente.",
            "data" => $usuario
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
        $usuario = Usuario::with('persona')->with('roles')->find($id);
        // $accesos = Rol_Acceso::where('rol_id', $usuario->rol_id)->get();
        // $modulos_ids = [];
        // $modulos = [];
        // if (!empty($accesos)) {
        //     $modulos_ids = $accesos->map(function ($acceso, $key) {
        //         return $acceso->modulo;
        //     });
        //     $modulos = Modulo::whereIn('modulo_id', $modulos_ids)->get();
        // }
        // $usuario->accesos = $accesos;
        // $usuario->modulos = $modulos;
        return $usuario;
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
        $usuario = Usuario::findOrFail($request->id);
        $request->validate([
            'persona_id',
            'rol_id',
            'habilitado',
        ]);
        $usuario->persona_id = $request->persona_id;
        $usuario->rol_id = $request->rol_id;
        $usuario->habilitado = $request->habilitado;
        $usuario->save();
        return response()->json([
            "success" => true,
            "message" => "Usuario registrado exitosamente.",
            "data" => $usuario
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
        $usuario = Usuario::find($id);
        $usuario->habilitado = 0;
        $usuario->save();
        return $usuario;
    }
}
