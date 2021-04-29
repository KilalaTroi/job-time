<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHrProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hr_profiles', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->nullable();
            $table->integer('team_id')->nullable();
            $table->tinyInteger('type');
            $table->string('code',60)->nullable();
            $table->string('name',255);
            $table->string('email',255)->nullable();
            $table->string('tel',255)->nullable();
            $table->string('address',255)->nullable();
            $table->string('position',255)->nullable();
            $table->string('avatar',255)->nullable();
            $table->string('description',255)->nullable();
            $table->tinyInteger('status')->default(1);
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
        Schema::dropIfExists('hr_profiles');
    }
}
