<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql_dtp')->create('types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug', 100);
            $table->string('slug_vi', 100)->nullable();
            $table->string('slug_ja', 100)->nullable();
            $table->string('value', 50);
            $table->timestamps();
        });

        Schema::connection('mysql_path')->create('types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug', 100);
            $table->string('slug_vi', 100)->nullable();
            $table->string('slug_ja', 100)->nullable();
            $table->string('value', 50);
            $table->timestamps();
        });

        Schema::connection('mysql_web')->create('types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug', 100);
            $table->string('slug_vi', 100)->nullable();
            $table->string('slug_ja', 100)->nullable();
            $table->string('value', 50);
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
        Schema::connection('mysql_dtp')->dropIfExists('types');
        Schema::connection('mysql_path')->dropIfExists('types');
        Schema::connection('mysql_web')->dropIfExists('types');
    }
}
