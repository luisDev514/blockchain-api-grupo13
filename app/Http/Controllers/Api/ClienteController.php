<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Exception;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $habilitado = $request->input('habilitado');

        $clientes = Cliente::when($habilitado, function ($query, $habilitado) {
            $query->where('clientes.habilitado', '=', $habilitado);
        })
            ->get();
        $clientes_count = $clientes->count();
        return response()->json([
            "success" => true,
            "message" => "($clientes_count) Clientes obtenidos exitosamente.",
            "data" => $clientes
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
            $cliente = new Cliente();
            $request->validate([
                'name' => 'required',
                'email' => 'required',
                'phone' => 'required',
            ]);
            $cliente->name = $request->name;
            $cliente->email = $request->email;
            $cliente->phone = $request->phone;
            $cliente->save();

            return response()->json([
                "success" => true,
                "message" => "Cliente registrado exitosamente.",
                "data" => $cliente
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
            $cliente = Cliente::find($id);

            if (!$cliente) {
                return response()->json([
                    "success" => false,
                    "message" => "No se encontró el Cliente con id = $id.",
                    "data" => null
                ]);
            }
            return response()->json([
                "success" => true,
                "message" => "Cliente obtenido exitosamente.",
                "data" => $cliente
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
                'name' => 'required',
                'email' => 'required',
                'phone' => 'required',
            ]);
            $cliente = Cliente::find($id);
            if (!$cliente) {
                return response()->json([
                    "success" => false,
                    "message" => "No se econtró el Cliente con id: $id.",
                    "data" => null
                ]);
            }

            $cliente->name = $request->name;
            $cliente->email = $request->email;
            $cliente->phone = $request->phone;
            $cliente->habilitado = $request->habilitado === true ? 1 : 0;
            $cliente->save();

            return response()->json([
                "success" => true,
                "message" => "Cliente modificado exitosamente.",
                "data" => $cliente
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

    public function habilitar($id)
    {
        try {
            $cliente = Cliente::find($id);
            if (!$cliente) {
                return response()->json([
                    "success" => false,
                    "message" => "No existe el Cliente con id: $id",
                    "data" => $cliente
                ]);
            }

            $cliente->habilitado = $cliente->habilitado === 0 ? 1 : 0;
            $cliente->save();

            return response()->json([
                "success" => true,
                "message" => "Estado actualizado correctamente",
                "data" => $cliente
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
