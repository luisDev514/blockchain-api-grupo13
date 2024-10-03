<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DetalleVenta;
use App\Models\Producto;
use App\Models\Venta;
use Exception;
use Illuminate\Http\Request;
use stdClass;

class VentaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $habilitado = $request->input('habilitado');

        $ventas = Venta::join('clientes', 'ventas.cliente_id', 'clientes.id')
            ->when($habilitado, function ($query, $habilitado) {
                $query->where('ventas.habilitado', '=', $habilitado);
            })
            ->get(
                [
                    'ventas.*',
                    'clientes.name as cliente_name'
                ]
            );
        $ventas_count = $ventas->count();
        return response()->json([
            "success" => true,
            "message" => "($ventas_count) Ventas obtenidos exitosamente.",
            "data" => $ventas
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
            $venta = new Venta();
            $request->validate([
                'cliente_id' => 'required',
                'total' => 'required',
                'detalleVentas' => 'required',
            ]);
            $venta->cliente_id = $request->cliente_id;
            $venta->total = $request->total;
            $venta->save();

            error_log("Request: $request");
            // print_r($request->detalleVentas);
            foreach ($request->detalleVentas as $item_por_insertar) {
                // print_r($item_por_insertar);
                $producto = Producto::find($item_por_insertar['id']);
                $producto->stock = $producto->stock - $item_por_insertar['quantity'];
                $producto->save();
                $item = new DetalleVenta([
                    'venta_id' => $venta->id,
                    'producto_id' => $producto->id,
                    'quantity' => $item_por_insertar['quantity'],
                    'price' => $producto->price,
                    'total' => $item_por_insertar['total'],
                ]);
                $item->save();
            }
            $resultado = Venta::where('ventas.id', $venta->id)->with('detalle')->first();

            return response()->json([
                "success" => true,
                "message" => "Venta registrada exitosamente.",
                "data" => $resultado
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
            // $venta = Venta::where('ventas.id', $id)->with('detalle')->first();
            $venta = Venta::find($id);

            if (!$venta) {
                return response()->json([
                    "success" => false,
                    "message" => "No se encontró la Venta con id = $id.",
                    "data" => null
                ]);
            }
            $detalle = DetalleVenta::join('productos', 'detalle_ventas.producto_id', 'productos.id')
                ->where('detalle_ventas.venta_id', $venta->id)->get([
                    'detalle_ventas.*',
                    'productos.name as product_name'
                ]);
            $data = new stdClass();
            $data = $venta;
            $data->detalle = $detalle;
            return response()->json([
                "success" => true,
                "message" => "Venta obtenida exitosamente.",
                "data" => $data
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
            $venta = Venta::find($id);
            if (!$venta) {
                return response()->json([
                    "success" => false,
                    "message" => "No se econtró el Venta con id: $id.",
                    "data" => null
                ]);
            }

            $venta->name = $request->name;
            $venta->price = $request->price;
            $venta->stock = $request->stock;
            $venta->habilitado = $request->habilitado === true ? 1 : 0;
            $venta->save();

            return response()->json([
                "success" => true,
                "message" => "Venta modificado exitosamente.",
                "data" => $venta
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
            $venta = Venta::find($id);
            if (!$venta) {
                return response()->json([
                    "success" => false,
                    "message" => "No existe la Venta con id: $id",
                    "data" => $venta
                ]);
            }

            $venta->habilitado = $venta->habilitado === 0 ? 1 : 0;
            $venta->save();

            return response()->json([
                "success" => true,
                "message" => "Estado actualizado correctamente",
                "data" => $venta
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
