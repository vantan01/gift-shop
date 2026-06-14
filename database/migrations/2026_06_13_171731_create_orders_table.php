<?php

use App\Enums\OrderStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->nullable()           // Cho phép guest order sau này
                  ->constrained()
                  ->nullOnDelete();
            $table->string('order_number', 20)->unique();
            $table->string('status')->default(OrderStatus::PENDING->value);

            // Snapshot thông tin người nhận tại thời điểm đặt hàng
            $table->string('recipient_name', 100);
            $table->string('recipient_phone', 20);
            $table->string('shipping_address');
            $table->string('shipping_city', 100);

            // Gift options
            $table->string('gift_message', 300)->nullable();
            $table->date('scheduled_delivery_date')->nullable();

            // Tiền — tất cả tự tính ở backend
            $table->unsignedBigInteger('subtotal');
            $table->unsignedBigInteger('shipping_fee')->default(0);
            $table->unsignedBigInteger('discount_amount')->default(0);
            $table->unsignedBigInteger('total');

            // Coupon snapshot
            $table->string('coupon_code', 50)->nullable();

            // Idempotency — tránh tạo đơn trùng
            $table->string('idempotency_key', 64)->unique()->nullable();

            // Notes
            $table->text('customer_note')->nullable();
            $table->text('internal_note')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};