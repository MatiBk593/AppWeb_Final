<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CrearTablasDelSistema extends Migration
{
    public function up()
    {
        // 1. Tabla de Productos
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');              // Nombre
            $table->text('description');         // Descripción
            $table->decimal('price', 8, 2);      // Precio
            $table->text('image_url');
            $table->timestamps();
        });

        // 2. Tabla de Favoritos
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            // Relación con Usuario y Producto
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        // 3. Tabla de Comentarios
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            // Relación con Usuario y Producto
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('content', 200);      // Comentario (Máx 200 letras)
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('comments');
        Schema::dropIfExists('favorites');
        Schema::dropIfExists('products');
    }
}