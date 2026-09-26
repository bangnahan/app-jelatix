<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizer_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organizer_id')->constrained('organizers')->cascadeOnDelete();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->string('milestone_phase'); // phase_1_closed_reg, phase_2_post_race
            $table->decimal('requested_amount', 14, 2);
            $table->decimal('platform_fee_deducted', 14, 2)->default(0);
            $table->decimal('net_payout_amount', 14, 2);
            $table->string('bank_name');
            $table->string('bank_account_number');
            $table->string('bank_account_holder');
            $table->string('status')->default('requested'); // requested, approved, transferred, rejected
            $table->string('transfer_proof_path')->nullable();
            $table->dateTime('transferred_at')->nullable();
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizer_payouts');
    }
};
