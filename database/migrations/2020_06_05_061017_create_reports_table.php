<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->string('title_ja')->nullable();
            $table->dateTime('date_time');
            $table->string('author');
            $table->string('issue')->nullable();
            $table->string('attend_person')->nullable();
            $table->string('attend_other_person')->nullable();
            $table->string('language', 2);
            $table->boolean('translatable')->nullable();
            $table->string('type');
            $table->longText('content');
            $table->longText('content_ja')->nullable();
            $table->string('seen');
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
        Schema::dropIfExists('reports');
    }
}
