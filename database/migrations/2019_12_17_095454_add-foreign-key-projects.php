<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeyProjects extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql_dtp')->table('projects', function (Blueprint $table) {
            $table->foreign('dept_id')
                ->references('id')->on('departments')
                ->onDelete('cascade');
            $table->foreign('type_id')
                ->references('id')->on('types')
                ->onDelete('cascade');
        });

        Schema::connection('mysql_path')->table('projects', function (Blueprint $table) {
            $table->foreign('dept_id')
                ->references('id')->on('departments')
                ->onDelete('cascade');
            $table->foreign('type_id')
                ->references('id')->on('types')
                ->onDelete('cascade');
        });

        Schema::connection('mysql_web')->table('projects', function (Blueprint $table) {
            $table->foreign('dept_id')
                ->references('id')->on('departments')
                ->onDelete('cascade');
            $table->foreign('type_id')
                ->references('id')->on('types')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql_dtp')->table('projects', function (Blueprint $table) {
            $table->dropForeign(['dept_id', 'type_id']);
        });

        Schema::connection('mysql_path')->table('projects', function (Blueprint $table) {
            $table->dropForeign(['dept_id', 'type_id']);
        });

        Schema::connection('mysql_web')->table('projects', function (Blueprint $table) {
            $table->dropForeign(['dept_id', 'type_id']);
        });
    }
}
