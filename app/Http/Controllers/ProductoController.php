<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Responses\ApiResponse;
use App\Models\Producto;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProductoController extends Controller
{
    public function index()
    {
        try {
            $productos = Producto::all();
            return ApiResponse::success('Lista de Productos', 200, $productos);
        } catch (Exception $e) {
            return $this->handleException($e, 'Ocurrió un error al obtener la lista de productos');
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'nombre' => 'required|unique:productos',
                'precio' => 'required|numeric|between:0,999999.99',
                'cantidad_disponible' => 'required|integer',
                'categoria_id' => 'required|exists:categorias,id',
                'marca_id' => 'required|exists:marcas,id',
                'categoria_origen' => 'required|in:nacional,extranjera',
            ]);

            $producto = Producto::create($request->all());

            return ApiResponse::success('Producto creado exitosamente', 201, $producto);
        } catch (ValidationException $e) {
            $formattedErrors = $this->formatValidationErrors($e->errors());
            $formattedErrors = $this->renameValidationErrors($formattedErrors);

            return ApiResponse::error('Errores de validación:', 422, $formattedErrors);
        } catch (Exception $e) {
            return $this->handleException($e, 'Error al crear el producto');
        }
    }

    public function show($id)
    {
        try {
            $producto = Producto::with('marca', 'categoria')->findOrFail($id);
            return ApiResponse::success('Producto obtenido exitosamente', 200, $producto);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Producto no encontrado', 404);
        } catch (Exception $e) {
            return $this->handleException($e, 'Error al obtener el producto');
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $producto = Producto::findOrFail($id);

            $request->validate([
                'nombre' => ['required', Rule::unique('productos')->ignore($producto)],
                'precio' => 'required|numeric|between:0,999999.99',
                'cantidad_disponible' => 'required|integer',
                'categoria_id' => 'required|exists:categorias,id',
                'marca_id' => 'required|exists:marcas,id',
                'categoria_origen' => 'required|in:nacional,extranjera',
            ]);

            $producto->update($request->all());

            return ApiResponse::success('Producto actualizado exitosamente', 200, $producto);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Producto no encontrado', 404);
        } catch (ValidationException $e) {
            $formattedErrors = $this->formatValidationErrors($e->errors());
            $formattedErrors = $this->renameValidationErrors($formattedErrors);

            return ApiResponse::error('Errores de validación:', 422, $formattedErrors);
        } catch (Exception $e) {
            return $this->handleException($e, 'Error al actualizar el producto');
        }
    }

    public function destroy($id)
    {
        try {
            $producto = Producto::findOrFail($id);
            $producto->delete();

            return ApiResponse::success('Producto eliminado exitosamente', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Producto no encontrado', 404);
        } catch (Exception $e) {
            return $this->handleException($e, 'Error al eliminar el producto');
        }
    }

    private function formatValidationErrors($errors)
    {
        $formattedErrors = [];

        foreach ($errors as $field => $fieldErrors) {
            $formattedErrors[$field] = $fieldErrors[0];
        }

        return $formattedErrors;
    }

    private function renameValidationErrors($errors)
    {
        $fieldMappings = ['categoria_id' => 'categoria', 'marca_id' => 'marca'];

        foreach ($fieldMappings as $oldKey => $newKey) {
            if (isset($errors[$oldKey])) {
                $errors[$newKey] = $errors[$oldKey];
                unset($errors[$oldKey]);
            }
        }

        return $errors;
    }

    private function handleException(Exception $e, $errorMessage)
    {
        return ApiResponse::error($errorMessage . ': ' . $e->getMessage(), 500);
    }
}
