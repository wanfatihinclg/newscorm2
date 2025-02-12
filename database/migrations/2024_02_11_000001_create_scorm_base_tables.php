<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Main SCORM table
        Schema::create('scorms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('scorm_type')->default('local');
            $table->string('reference');
            $table->string('version', 9);
            $table->float('max_grade')->default(0);
            $table->integer('max_attempt')->default(1);
            $table->string('sha1_hash', 40)->nullable();
            $table->string('md5_hash', 32);
            $table->timestamps();
        });

        // SCORM SCOs (Sharable Content Objects)
        Schema::create('scorm_scoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scorm_id')->constrained('scorms')->onDelete('cascade');
            $table->string('manifest')->default('imsmanifest.xml');
            $table->string('organization')->nullable();
            $table->string('parent')->nullable();
            $table->string('identifier');
            $table->string('launch')->nullable();
            $table->string('scorm_type')->default('asset');
            $table->string('title');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['scorm_id', 'identifier']);
            $table->index(['parent', 'sort_order']);
        });

        // SCORM Elements (for tracking)
        Schema::create('scorm_elements', function (Blueprint $table) {
            $table->id();
            $table->string('element');
            $table->string('scorm_type');
            $table->timestamps();

            $table->unique(['element', 'scorm_type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('scorm_elements');
        Schema::dropIfExists('scorm_scoes');
        Schema::dropIfExists('scorms');
    }
};
