<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booth_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->json('name');
            $table->json('description')->nullable();
            $table->string('sku')->nullable();
            $table->unsignedBigInteger('price'); // به تومان
            $table->unsignedInteger('stock')->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active')->index();
            $table->timestamps();

            $table->index(['booth_id', 'status']);
            $table->index(['category_id', 'status']);
        });

        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('products');
    }
};
