<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // SCORM SCO Data
        Schema::create('scorm_sco_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scorm_sco_id')->constrained('scorm_scoes')->onDelete('cascade');
            $table->string('name');
            $table->text('value')->nullable();
            $table->timestamps();

            $table->index(['scorm_sco_id', 'name']);
        });

        // SCORM Attempts
        Schema::create('scorm_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scorm_id')->constrained('scorms')->onDelete('cascade');
            $table->integer('attempt')->default(1);
            $table->string('session_id')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['scorm_id', 'attempt']);
        });

        // SCORM SCO Values (tracking data)
        Schema::create('scorm_sco_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scorm_attempt_id')->constrained('scorm_attempts')->onDelete('cascade');
            $table->foreignId('scorm_sco_id')->constrained('scorm_scoes')->onDelete('cascade');
            $table->string('element');
            $table->text('value')->nullable();
            $table->timestamps();

            $table->index(['scorm_attempt_id', 'scorm_sco_id', 'element']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('scorm_sco_values');
        Schema::dropIfExists('scorm_attempts');
        Schema::dropIfExists('scorm_sco_data');
    }
};
