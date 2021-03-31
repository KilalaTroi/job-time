<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTimeTableDays extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('time_table_days', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('time_table_id');
            $table->string('check_in',6);
            $table->string('check_out',6);
            $table->string('check_in_start',6);
            $table->string('check_in_end',6);
            $table->string('check_out_start',6);
            $table->string('check_out_end',6);
            $table->string('day',25);
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
        Schema::dropIfExists('time_table_days');
    }
}
