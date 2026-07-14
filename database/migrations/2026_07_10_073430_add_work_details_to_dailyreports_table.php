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
            $table->string('priority')->default('normal')->after('work_category_id');
            $table->string('location')->nullable()->after('title');
            $table->time('start_time')->nullable()->after('location');
            $table->time('end_time')->nullable()->after('start_time');
            $table->unsignedInteger('duration_minutes')->nullable()->after('end_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dailyreports', function (Blueprint $table) {
            $table->dropColumn([
                'priority',
                'location',
                'start_time',
                'end_time',
                'duration_minutes',
            ]);
        });
    }
};
