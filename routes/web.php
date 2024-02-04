<?php

use App\Http\Controllers\NombreController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\CompraController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
});

Route::resource('nombres', NombreController::class);

Route::resource('marcas', MarcaController::class);
Route::resource('categorias', CategoriaController::class);
Route::resource('productos', ProductoController::class);
Route::resource('compras', CompraController::class);

Route::get('categorias/{categoria}/productos', [CategoriaController::class, 'productosPorCategoria']);
Route::get('marcas/{marca}/productos', [MarcaController::class, 'productosPorMarca']);
