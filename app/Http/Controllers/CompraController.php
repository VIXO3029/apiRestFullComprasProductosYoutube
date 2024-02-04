<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponse;
use App\Models\Compra;
use App\Models\Producto;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CompraController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = $request->query('perPage', 10);
            $compras = Compra::orderBy('created_at', 'desc')->paginate($perPage);

            return ApiResponse::success('Lista de compras', 200, $compras);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al obtener la lista de compras: ' . $e->getMessage(), 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $productos = $request->input('productos');

            if (empty($productos)) {
                return ApiResponse::error('No se proporcionaron productos', 400);
            }

            $validator = Validator::make($request->all(), [
                'productos' => 'required|array',
                'productos.*.producto_id' => 'required|integer|exists:productos,id',
                'productos.*.cantidad' => 'required|integer|min:1',
            ]);

            if ($validator->fails()) {
                return ApiResponse::error('Datos inválidos en la lista de productos', 400, $validator->errors());
            }

            $productoIds = array_column($productos, 'producto_id');
            if (count($productoIds) !== count(array_unique($productoIds))) {
                return ApiResponse::error('No se permiten productos duplicados para la compra', 400);
            }

            $totalPagar = 0;
            $compraItems = [];

            DB::beginTransaction();

            try {
                foreach ($productos as $producto) {
                    $productoB = Producto::findOrFail($producto['producto_id']);

                    if ($productoB->cantidad_disponible < $producto['cantidad']) {
                        throw new \Exception('El producto no tiene suficiente cantidad disponible');
                    }

                    $productoB->cantidad_disponible -= $producto['cantidad'];
                    $productoB->save();

                    $subtotal = $productoB->precio * $producto['cantidad'];
                    $totalPagar += $subtotal;

                    $compraItems[] = [
                        'producto_id' => $productoB->id,
                        'precio' => $productoB->precio,
                        'cantidad' => $producto['cantidad'],
                        'subtotal' => $subtotal,
                    ];
                }

                $compra = Compra::create([
                    'subtotal' => $totalPagar,
                    'total' => $totalPagar,
                ]);

                $compra->productos()->attach($compraItems);

                DB::commit();

                return ApiResponse::success('Compra realizada exitosamente', 201, $compra);
            } catch (\Exception $e) {
                DB::rollBack();

                return ApiResponse::error('Error inesperado: ' . $e->getMessage(), 500);
            }

        } catch (QueryException $e) {
            return ApiResponse::error('Error en la consulta de datos', 500);
        } catch (\Exception $e) {
            return ApiResponse::error('Error inesperado', 500);
        }
    }

    public function show($id)
    {
        try {
            $compra = Compra::with('productos')->find($id);

            if ($compra) {
                return ApiResponse::success('Detalles de la compra', 200, $compra);
            } else {
                return ApiResponse::error('Compra no encontrada', 404);
            }
        } catch (\Exception $e) {
            return ApiResponse::error('Error al obtener los detalles de la compra: ' . $e->getMessage(), 500);
        }
    }
}
