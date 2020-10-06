<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMemoToSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql_dtp')->table('schedules', function (Blueprint $table) {
            $table->string('memo', 255)->nullable()->after('date');
        });

        Schema::connection('mysql_path')->table('schedules', function (Blueprint $table) {
            $table->string('memo', 255)->nullable()->after('date');
        });

        Schema::connection('mysql_web')->table('schedules', function (Blueprint $table) {
            $table->string('memo', 255)->nullable()->after('date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql_dtp')->table('schedules', function (Blueprint $table) {
            $table->dropColumn('memo');
        });

        Schema::connection('mysql_path')->table('schedules', function (Blueprint $table) {
            $table->dropColumn('memo');
        });

        Schema::connection('mysql_web')->table('schedules', function (Blueprint $table) {
            $table->dropColumn('memo');
        });
    }
}
