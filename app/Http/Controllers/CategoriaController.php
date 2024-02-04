<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Categoria;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;

class CategoriaController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $categorias = Categoria::all();
            return $this->successResponse('Lista de categorías obtenida exitosamente', $categorias);
        } catch (Exception $e) {
            return $this->errorResponse('Error al obtener la lista de categorías', $e, 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'nombre' => 'required|unique:categorias',
            ]);

            $categoria = Categoria::create($request->all());
            return $this->successResponse('Categoría creada exitosamente', $categoria, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorResponse('Error de validación', $e, 422);
        } catch (Exception $e) {
            return $this->errorResponse('Error al crear la categoría', $e, 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $categoria = Categoria::findOrFail($id);
            return $this->successResponse('Categoría obtenida exitosamente', $categoria);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Categoría no encontrada', $e, 404);
        } catch (Exception $e) {
            return $this->errorResponse('Error al obtener la categoría', $e, 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $categoria = Categoria::findOrFail($id);
            $request->validate([
                'nombre' => ['required', Rule::unique('categorias')->ignore($categoria)],
            ]);

            $categoria->update($request->all());
            return $this->successResponse('Categoría actualizada exitosamente', $categoria);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Categoría no encontrada', $e, 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorResponse('Error de validación', $e, 422);
        } catch (Exception $e) {
            return $this->errorResponse('Error al actualizar la categoría', $e, 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $categoria = Categoria::findOrFail($id);
            $categoria->delete();
            return $this->successResponse('Categoría eliminada exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Categoría no encontrada', $e, 404);
        } catch (Exception $e) {
            return $this->errorResponse('Error al eliminar la categoría', $e, 500);
        }
    }

    public function productosPorCategoria($id): JsonResponse
    {
        try {
            $categoria = Categoria::with('productos')->findOrFail($id);
            return $this->successResponse('Categoría y lista de productos obtenidos exitosamente', $categoria);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Categoría no encontrada', $e, 404);
        } catch (Exception $e) {
            return $this->errorResponse('Error al obtener la categoría y la lista de productos', $e, 500);
        }
    }

    private function successResponse($message, $data = null, $statusCode = 200): JsonResponse
    {
        return response()->json(['message' => $message, 'data' => $data], $statusCode);
    }

    private function errorResponse($message, Exception $exception, $statusCode): JsonResponse
    {
        return response()->json(['message' => $message, 'error' => $exception->getMessage()], $statusCode);
    }
}
