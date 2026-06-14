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
            $table->foreignId('category_id')
                  ->constrained()
                  ->restrictOnDelete();
            $table->string('name', 200);
            $table->string('slug', 200)->unique();
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();
            $table->unsignedBigInteger('price');        // Lưu VNĐ, đơn vị: đồng
            $table->unsignedBigInteger('compare_price') // Giá gốc để hiện "đã giảm"
                  ->nullable();
            $table->unsignedInteger('stock')->default(0);
            $table->string('image')->nullable();
            $table->json('images')->nullable();         // Nhiều ảnh
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('low_stock_threshold')->default(5);
            $table->unsignedBigInteger('sold_count')->default(0);
            $table->timestamps();
            $table->softDeletes();                      // Xóa mềm
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};