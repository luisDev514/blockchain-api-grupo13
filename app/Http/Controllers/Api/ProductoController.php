<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use Exception;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $habilitado = $request->input('habilitado');

        $productos = Producto::when($habilitado, function ($query, $habilitado) {
            $query->where('productos.habilitado', '=', $habilitado);
        })
            ->get();
        $productos_count = $productos->count();
        return response()->json([
            "success" => true,
            "message" => "($productos_count) Productos obtenidos exitosamente.",
            "data" => $productos
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
            $producto = new Producto();
            $request->validate([
                'name',
                'price',
                'stock',
            ]);
            $producto->name = $request->name;
            $producto->price = $request->price;
            $producto->stock = $request->stock;
            $producto->save();

            return response()->json([
                "success" => true,
                "message" => "Producto registrado exitosamente.",
                "data" => $producto
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
        try {
            $producto = Producto::find($id);

            if (!$producto) {
                return response()->json([
                    "success" => false,
                    "message" => "No se encontró el Producto con id = $id.",
                    "data" => null
                ]);
            }
            return response()->json([
                "success" => true,
                "message" => "Producto registrado exitosamente.",
                "data" => $producto
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
                'name',
                'price',
                'stock',
            ]);
            $producto = Producto::find($id);
            if (!$producto) {
                return response()->json([
                    "success" => false,
                    "message" => "No se econtró el Producto con id: $id.",
                    "data" => null
                ]);
            }

            $producto->name = $request->name;
            $producto->price = $request->price;
            $producto->stock = $request->stock;
            $producto->habilitado = $request->habilitado === true ? 1 : 0;
            $producto->save();

            return response()->json([
                "success" => true,
                "message" => "Producto modificado exitosamente.",
                "data" => $producto
            ]);
        } catch (Exception $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "data" => null
            ]);
        }
    }

    public function habilitar($id)
    {
        try {
            $producto = Producto::find($id);
            if (!$producto) {
                return response()->json([
                    "success" => false,
                    "message" => "No existe el Producto con id: $id",
                    "data" => $producto
                ]);
            }

            $producto->habilitado = $producto->habilitado === 0 ? 1 : 0;
            $producto->save();

            return response()->json([
                "success" => true,
                "message" => "Estado actualizado correctamente",
                "data" => $producto
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
