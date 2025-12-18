<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;

// Auth
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Rutas Públicas
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::get('/comments/{id}', [ProductController::class, 'getComments']);

// Rutas Protegidas (Necesitan Token)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    
    // Productos (Crear, Editar, Borrar)
    Route::post('/products', [ProductController::class, 'store']);
    Route::post('/products/{id}', [ProductController::class, 'update']); 
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);
    
    // Favoritos y Comentarios
    Route::post('/favorites', [ProductController::class, 'addFavorite']);
    Route::get('/favorites', [ProductController::class, 'getFavorites']);
    Route::delete('/favorites/{id}', [ProductController::class, 'removeFavorite']);
    Route::post('/comments', [ProductController::class, 'addComment']);
});