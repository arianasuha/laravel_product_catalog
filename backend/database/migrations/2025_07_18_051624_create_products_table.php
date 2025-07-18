<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Product name
            $table->text('description')->nullable(); // Product description, can be null
            $table->decimal('price', 8, 2); // Price with 8 digits total, 2 after decimal
            $table->integer('stock')->default(0); // Stock quantity, default to 0
            $table->string('image')->nullable(); // Path to product image, can be null
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
