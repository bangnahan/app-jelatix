<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jersey_sizes', function (Blueprint $table) {
            $table->boolean('is_unlimited')->default(true)->after('body_length_cm');
            $table->integer('stock')->nullable()->change();
        });

        // Set all existing default jersey sizes to unlimited
        DB::table('jersey_sizes')->update([
            'is_unlimited' => true,
        ]);
    }

    public function down(): void
    {
        Schema::table('jersey_sizes', function (Blueprint $table) {
            $table->dropColumn('is_unlimited');
            $table->integer('stock')->default(50)->change();
        });
    }
};
