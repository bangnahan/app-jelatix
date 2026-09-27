<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('order_code')->unique(); // JLTX-YYYYMM-XXXX
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone');
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('discount_amount', 14, 2)->default(0);
            $table->decimal('platform_fee', 14, 2)->default(0);
            $table->decimal('payment_gateway_fee', 14, 2)->default(0);
            $table->decimal('grand_total', 14, 2)->default(0);
            $table->string('status')->default('pending'); // pending, paid, expired, failed, refunded

            // Tripay details
            $table->string('tripay_reference')->nullable()->index();
            $table->string('tripay_payment_method')->nullable(); // QRIS, BCAVA, BRIVA, dll
            $table->string('tripay_pay_code')->nullable(); // Virtual Account number / kode bayar
            $table->text('tripay_qr_url')->nullable(); // QRIS image url
            $table->text('tripay_checkout_url')->nullable();

            $table->dateTime('paid_at')->nullable();
            $table->dateTime('expired_at')->nullable(); // Batas waktu bayar (15-30 menit)
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('event_category_id')->constrained('event_categories')->cascadeOnDelete();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total_price', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
