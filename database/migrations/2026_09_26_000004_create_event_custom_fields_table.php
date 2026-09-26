<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_custom_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('event_category_id')->nullable()->constrained('event_categories')->nullOnDelete();
            $table->string('field_key'); // e.g., shuttle_bus_point, itra_link, club_name
            $table->string('label'); // Teks label di form
            $table->string('field_type')->default('text'); // text, number, textarea, select, radio, checkbox, file
            $table->json('options')->nullable(); // Opsi untuk dropdown/radio
            $table->boolean('is_required')->default(false);
            $table->string('placeholder')->nullable();
            $table->string('help_text')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_custom_fields');
    }
};
