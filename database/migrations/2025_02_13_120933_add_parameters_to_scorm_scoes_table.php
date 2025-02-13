<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddParametersToScormScoesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('scorm_scoes', function (Blueprint $table) {
            $table->text('parameters')->nullable()->after('launch');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('scorm_scoes', function (Blueprint $table) {
            $table->dropColumn('parameters');
        });
    }
}
