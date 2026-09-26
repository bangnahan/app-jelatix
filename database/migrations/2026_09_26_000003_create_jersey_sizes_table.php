<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jersey_sizes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->string('size_name', 10); // XS, S, M, L, XL, XXL, 3XL
            $table->string('gender_type', 10)->default('unisex'); // unisex, male, female
            $table->integer('chest_width_cm')->nullable(); // Lebar dada
            $table->integer('body_length_cm')->nullable(); // Panjang baju
            $table->integer('stock')->default(50);
            $table->integer('allocated_stock')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jersey_sizes');
    }
};
