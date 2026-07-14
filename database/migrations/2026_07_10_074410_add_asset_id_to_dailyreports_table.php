<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('dailyreports', function (Blueprint $table) {
            $table->foreignId('itassets_id')
                ->nullable()
                ->after('work_category_id')
                ->constrained('itassets')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dailyreports', function (Blueprint $table) {
             $table->dropConstrainedForeignId('itassets_id');
        });
    }
};
