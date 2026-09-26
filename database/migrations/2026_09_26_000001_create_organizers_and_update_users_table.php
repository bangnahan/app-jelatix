<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('logo_path')->nullable();
            $table->text('description')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_account_holder')->nullable();
            $table->decimal('commission_rate', 5, 2)->default(5.00); // persentase fee platform
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('organizer_id')->nullable()->constrained('organizers')->nullOnDelete()->after('id');
            $table->string('phone')->nullable()->after('email');
            $table->string('role')->default('customer')->after('phone'); // superadmin, organizer_owner, organizer_staff, scanner_crew, customer
            $table->boolean('is_active')->default(true)->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['organizer_id']);
            $table->dropColumn(['organizer_id', 'phone', 'role', 'is_active']);
        });

        Schema::dropIfExists('organizers');
    }
};
