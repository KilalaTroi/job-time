<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeyIssues extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql_dtp')->table('issues', function (Blueprint $table) {
            $table->foreign('project_id')
                ->references('id')->on('projects')
                ->onDelete('cascade');
        });

        Schema::connection('mysql_path')->table('issues', function (Blueprint $table) {
            $table->foreign('project_id')
                ->references('id')->on('projects')
                ->onDelete('cascade');
        });

        Schema::connection('mysql_web')->table('issues', function (Blueprint $table) {
            $table->foreign('project_id')
                ->references('id')->on('projects')
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
        Schema::connection('mysql_dtp')->table('issues', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
        });

        Schema::connection('mysql_path')->table('issues', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
        });

        Schema::connection('mysql_web')->table('issues', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
        });
    }
}
