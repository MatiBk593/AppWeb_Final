<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;

// --- RUTAS PÚBLICAS ---
// Autenticación [cite: 153]
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Catálogo Público [cite: 157]
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::get('/comments/{product_id}', [ProductController::class, 'getComments']); // Ver comentarios

// --- RUTAS PROTEGIDAS (Necesitan Login) ---
Route::middleware('auth:sanctum')->group(function () {
    // Cerrar sesión [cite: 156]
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Gestión de Productos (Admin) [cite: 160, 162]
    Route::post('/products', [ProductController::class, 'store']); // Crear con imagen Firebase
    Route::delete('/products/{id}', [ProductController::class, 'destroy']); // Eliminar

    // Favoritos [cite: 165]
    Route::post('/favorites', [ProductController::class, 'addFavorite']);
    Route::get('/favorites', [ProductController::class, 'getFavorites']);
    Route::delete('/favorites/{product_id}', [ProductController::class, 'removeFavorite']);

    // Comentarios [cite: 169]
    Route::post('/comments', [ProductController::class, 'addComment']);
});