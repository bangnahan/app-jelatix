<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organizer_id')->constrained('organizers')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('short_description')->nullable();
            $table->longText('full_description')->nullable();
            $table->string('banner_path')->nullable();
            $table->string('race_location_name');
            $table->text('race_location_address')->nullable();
            $table->string('race_location_map_url')->nullable();
            $table->dateTime('event_start_date');
            $table->dateTime('event_end_date')->nullable();
            $table->dateTime('registration_open_date');
            $table->dateTime('registration_close_date');
            $table->dateTime('rpc_start_date')->nullable();
            $table->dateTime('rpc_end_date')->nullable();
            $table->text('rpc_location')->nullable();
            $table->longText('terms_and_conditions')->nullable();
            $table->longText('waiver_content')->nullable(); // Pelepasan tanggung jawab hukum & medis
            $table->boolean('auto_assign_bib')->default(true);
            $table->string('status')->default('draft'); // draft, published, registration_closed, completed, cancelled
            $table->timestamps();
        });

        Schema::create('event_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->string('name'); // 5K Fun Run, 10K Open, 21K Half Marathon
            $table->decimal('distance_km', 6, 2)->default(5.00);
            $table->decimal('normal_price', 12, 2);
            $table->decimal('early_bird_price', 12, 2)->nullable();
            $table->dateTime('early_bird_end_date')->nullable();
            $table->integer('quota')->default(100);
            $table->integer('slots_taken')->default(0);
            $table->string('bib_prefix', 10)->default('5K');
            $table->integer('bib_start_number')->default(1001);
            $table->json('reserved_bib_numbers')->nullable(); // Nomor yang dilewati auto-assign
            $table->integer('cut_off_time_minutes')->nullable(); // COT in minutes
            $table->integer('min_age')->nullable()->default(12);
            $table->integer('max_age')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_categories');
        Schema::dropIfExists('events');
    }
};
