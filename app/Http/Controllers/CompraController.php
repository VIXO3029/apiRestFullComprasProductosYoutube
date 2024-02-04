<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponse;
use App\Models\Compra;
use App\Models\Producto;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CompraController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = $request->query('perPage', 10); // Número de elementos por página
            $compras = Compra::orderBy('created_at', 'desc')->paginate($perPage);

            return ApiResponse::success('Lista de compras', 200, $compras);
        } catch (Exception $e) {
            return ApiResponse::error('Error al obtener la lista de compras: ' . $e->getMessage(), 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $productos = $request->input('productos');

            // Validar los productos
            if (empty($productos)) {
                return ApiResponse::error('No se proporcionaron productos', 400);
            }

            // Validar la lista de productos
            $validator = Validator::make($request->all(), [
                'productos' => 'required|array',
                'productos.*.producto_id' => 'required|integer|exists:productos,id',
                'productos.*.cantidad' => 'required|integer|min:1',
            ]);

            if ($validator->fails()) {
                return ApiResponse::error('Datos inválidos en la lista de productos', 400, $validator->errors());
            }

            // Validar productos duplicados
            $productoIds = array_column($productos, 'producto_id');
            if (count($productoIds) !== count(array_unique($productoIds))) {
                return ApiResponse::error('No se permiten productos duplicados para la compra', 400);
            }

            $totalPagar = 0;
            $subtotal = 0;
            $compraItems = [];

            // Utilizamos transacción para garantizar la consistencia de la base de datos
            DB::beginTransaction();

            try {
                // Iteración de los productos para calcular el total a pagar
                foreach ($productos as $producto) {
                    $productoB = Producto::find($producto['producto_id']);
                    if (!$productoB) {
                        throw new Exception('Producto no encontrado');
                    }

                    // Validar la cantidad disponible de los productos
                    if ($productoB->cantidad_disponible < $producto['cantidad']) {
                        throw new Exception('El producto no tiene suficiente cantidad disponible');
                    }

                    // Actualización de la cantidad disponible de cada producto
                    $productoB->cantidad_disponible -= $producto['cantidad'];
                    $productoB->save();

                    // Cálculo de los importes
                    $subtotal = $productoB->precio * $producto['cantidad'];
                    $totalPagar += $subtotal;

                    // Items de la compra
                    $compraItems[] = [
                        'producto_id' => $productoB->id,
                        'precio' => $productoB->precio,
                        'cantidad' => $producto['cantidad'],
                        'subtotal' => $subtotal,
                    ];
                }

                // Registro en la tabla compra
                $compra = Compra::create([
                    'subtotal' => $totalPagar,
                    'total' => $totalPagar,
                ]);

                // Asociar los productos a la compra con sus cantidades y sus subtotales
                $compra->productos()->attach($compraItems);

                // Confirmar la transacción si todo fue exitoso
                DB::commit();

                return ApiResponse::success('Compra realizada exitosamente', 201, $compra);
            } catch (Exception $e) {
                // Revertir la transacción en caso de error
                DB::rollBack();

                return ApiResponse::error('Error inesperado: ' . $e->getMessage(), 500);
            }

        } catch (QueryException $e) {
            // Error de consulta en la base de datos
            return ApiResponse::error('Error en la consulta de datos', 500);
        } catch (Exception $e) {
            return ApiResponse::error('Error inesperado', 500);
        }
    }

    public function show($id)
    {
        try {
            // Obtener la compra por su ID junto con los productos asociados
            $compra = Compra::with('productos')->findOrFail($id);

            return ApiResponse::success('Detalles de la compra', 200, $compra);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Compra no encontrada', 404);
        } catch (Exception $e) {
            return ApiResponse::error('Error al obtener los detalles de la compra: ' . $e->getMessage(), 500);
        }
    }
}
