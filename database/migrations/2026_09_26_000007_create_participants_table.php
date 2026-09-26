<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained('orders')->cascadeOnDelete();
            $table->foreignId('event_category_id')->constrained('event_categories')->cascadeOnDelete();
            $table->foreignId('jersey_size_id')->nullable()->constrained('jersey_sizes')->nullOnDelete();
            
            // BIB Management
            $table->string('bib_number')->nullable()->index();
            $table->boolean('is_vip')->default(false);
            $table->boolean('is_custom_bib')->default(false); // Terkunci dari auto / bulk remapping
            $table->string('custom_bib_reason')->nullable(); // Pejabat, Sponsor Utama, Atlet Elite
            
            // Identitas Pelari
            $table->string('full_name');
            $table->string('bib_name', 50)->nullable(); // Nama yang dicetak di nomor dada
            $table->string('id_type', 20)->default('KTP'); // KTP, SIM, Passport, KIA
            $table->string('id_number');
            $table->string('gender', 10); // male, female
            $table->date('birth_date');
            $table->string('blood_type', 10)->default('Unknown'); // A, B, AB, O, Unknown
            $table->string('phone');
            $table->string('email');
            
            // Kontak Darurat & Medis
            $table->string('emergency_contact_name');
            $table->string('emergency_contact_phone');
            $table->string('emergency_contact_relation');
            $table->text('medical_conditions')->nullable(); // Riwayat asma, jantung, dll
            
            // Wave & Target Pace
            $table->string('wave_group', 20)->nullable(); // Wave A, Wave B, Wave C
            $table->string('estimated_finish_time', 50)->nullable(); // e.g. 00:45:00
            
            // Dynamic Custom Form Data
            $table->json('custom_fields_data')->nullable();
            
            // Digital Waiver & Legal Liability
            $table->boolean('waiver_accepted')->default(false);
            $table->dateTime('waiver_accepted_at')->nullable();
            $table->string('waiver_ip_address', 45)->nullable();
            
            // Race Pack Collection (RPC) & QR E-Ticket
            $table->string('qr_token', 64)->unique();
            $table->boolean('is_rpc_claimed')->default(false);
            $table->dateTime('rpc_claimed_at')->nullable();
            $table->foreignId('rpc_claimed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            
            // Proxy Pickup (Surat Kuasa / Pengambilan Diwakilkan)
            $table->boolean('is_proxy_claimed')->default(false);
            $table->string('proxy_collector_name')->nullable();
            $table->string('proxy_collector_id_number')->nullable();
            $table->string('proxy_document_path')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participants');
    }
};
