<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Responses\ApiResponse;
use App\Models\Marca;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\Rule;

class MarcaController extends Controller
{
    const NACIONAL = 'nacional';
    const EXTRANJERA = 'extranjera';

    protected $apiResponse;

    public function __construct(ApiResponse $apiResponse)
    {
        $this->apiResponse = $apiResponse;
    }

    public function index()
    {
        try {
            $marcas = Marca::all();
            return $this->apiResponse->success('Lista de marcas obtenida exitosamente', 200, $marcas);
        } catch (Exception $e) {
            return $this->handleException($e, 'Ocurrió un error al obtener la lista de marcas');
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'nombre' => 'required|unique:marcas',
                'origen' => 'required|in:' . self::NACIONAL . ',' . self::EXTRANJERA,
            ]);

            $marca = Marca::create($request->all());
            return $this->apiResponse->success('Marca creada exitosamente', 201, $marca);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->apiResponse->error('Error de validación: ' . $e->getMessage(), 422);
        } catch (Exception $e) {
            return $this->handleException($e, 'Error al crear la marca');
        }
    }

    public function show($id)
    {
        try {
            $marca = Marca::findOrFail($id);
            return $this->apiResponse->success('Marca obtenida exitosamente', 200, $marca);
        } catch (ModelNotFoundException $e) {
            return $this->apiResponse->error('Marca no encontrada: ' . $e->getMessage(), 404);
        } catch (Exception $e) {
            return $this->handleException($e, 'Error al obtener la marca');
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $marca = Marca::findOrFail($id);
            $request->validate([
                'nombre' => ['required', Rule::unique('marcas')->ignore($marca)],
                'origen' => 'required|in:' . self::NACIONAL . ',' . self::EXTRANJERA,
            ]);

            $marca->update($request->all());
            return $this->apiResponse->success('Marca actualizada exitosamente', 200, $marca);
        } catch (ModelNotFoundException $e) {
            return $this->apiResponse->error('Marca no encontrada: ' . $e->getMessage(), 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->apiResponse->error('Error de validación: ' . $e->getMessage(), 422);
        } catch (Exception $e) {
            return $this->handleException($e, 'Error al actualizar la marca');
        }
    }

    public function destroy($id)
    {
        try {
            $marca = Marca::findOrFail($id);
            $marca->delete();
            return $this->apiResponse->success('Marca eliminada exitosamente', 200);
        } catch (ModelNotFoundException $e) {
            return $this->apiResponse->error('Marca no encontrada', 404);
        } catch (Exception $e) {
            return $this->handleException($e, 'Error al eliminar la marca');
        }
    }

    public function productosPorMarca($id)
    {
        try {
            $marca = Marca::with('productos')->findOrFail($id);
            return $this->apiResponse->success('Marca y lista de productos obtenidos exitosamente', 200, $marca);
        } catch (ModelNotFoundException $e) {
            return $this->apiResponse->error('Marca no encontrada: ' . $e->getMessage(), 404);
        } catch (Exception $e) {
            return $this->handleException($e, 'Error al obtener la marca y la lista de productos');
        }
    }

    private function handleException(Exception $e, $errorMessage)
    {
        return $this->apiResponse->error($errorMessage . ': ' . $e->getMessage(), 500);
    }
}

