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
        Schema::table('scorm_attempts', function (Blueprint $table) {
            // Add columns for storing SCORM tracking data
            $table->string('last_location')->nullable()->after('completed_at');
            $table->text('suspend_data')->nullable()->after('last_location');
            $table->string('total_time')->default('0000:00:00')->after('suspend_data');
            $table->string('session_time')->default('0000:00:00')->after('total_time');
            $table->decimal('score', 8, 2)->nullable()->after('session_time');
            $table->string('status')->default('not attempted')->after('score');
            $table->json('objectives')->nullable()->after('status');
            $table->json('interactions')->nullable()->after('objectives');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scorm_attempts', function (Blueprint $table) {
            $table->dropColumn([
                'last_location',
                'suspend_data',
                'total_time',
                'session_time',
                'score',
                'status',
                'objectives',
                'interactions'
            ]);
        });
    }
};
