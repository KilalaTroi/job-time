<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql_dtp')->create('projects', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('dept_id')->unsigned();
            $table->string('name', 100);
            $table->string('name_vi', 100)->nullable();
            $table->string('name_ja', 100)->nullable();
            $table->integer('type_id')->unsigned();
            $table->timestamps();
        });

        Schema::connection('mysql_path')->create('projects', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('dept_id')->unsigned();
            $table->string('name', 100);
            $table->string('name_vi', 100)->nullable();
            $table->string('name_ja', 100)->nullable();
            $table->integer('type_id')->unsigned();
            $table->timestamps();
        });

        Schema::connection('mysql_web')->create('projects', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('dept_id')->unsigned();
            $table->string('name', 100);
            $table->string('name_vi', 100)->nullable();
            $table->string('name_ja', 100)->nullable();
            $table->integer('type_id')->unsigned();
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
        Schema::connection('mysql_dtp')->dropIfExists('projects');
        Schema::connection('mysql_path')->dropIfExists('projects');
        Schema::connection('mysql_web')->dropIfExists('projects');
    }
}
