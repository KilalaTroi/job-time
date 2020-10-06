<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDepartmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql_dtp')->create('departments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('name_vi')->nullable();
            $table->string('name_ja')->nullable();
            $table->timestamps();
        });

        Schema::connection('mysql_path')->create('departments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('name_vi')->nullable();
            $table->string('name_ja')->nullable();
            $table->timestamps();
        });

        Schema::connection('mysql_web')->create('departments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('name_vi')->nullable();
            $table->string('name_ja')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql_dtp')->dropIfExists('departments');
        Schema::connection('mysql_path')->dropIfExists('departments');
        Schema::connection('mysql_web')->dropIfExists('departments');
    }
}
