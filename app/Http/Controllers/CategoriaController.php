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
            return response()->json(['message' => 'Lista de categorías', 'data' => $categorias], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al obtener la lista de categorías', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'nombre' => 'required|unique:categorias',
            ]);

            $categoria = Categoria::create($request->all());
            return response()->json(['message' => 'Categoría creada exitosamente', 'data' => $categoria], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Error de validación', 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al crear la categoría', 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $categoria = Categoria::findOrFail($id);
            return response()->json(['message' => 'Categoría obtenida exitosamente', 'data' => $categoria], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Categoría no encontrada', 'error' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al obtener la categoría', 'error' => $e->getMessage()], 500);
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

            return response()->json(['message' => 'Categoría actualizada exitosamente', 'data' => $categoria], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Categoría no encontrada', 'error' => $e->getMessage()], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Error de validación', 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al actualizar la categoría', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $categoria = Categoria::findOrFail($id);
            $categoria->delete();
            return response()->json(['message' => 'Categoría eliminada exitosamente'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Categoría no encontrada', 'error' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al eliminar la categoría', 'error' => $e->getMessage()], 500);
        }
    }

    public function productosPorCategoria($id): JsonResponse
    {
        try {
            $categoria = Categoria::with('productos')->findOrFail($id);
            return response()->json(['message' => 'Categoría y lista de productos', 'data' => $categoria], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Categoría no encontrada', 'error' => $e->getMessage()], 404);
        }
    }
}
