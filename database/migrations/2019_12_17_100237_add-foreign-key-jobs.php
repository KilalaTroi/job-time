<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeyJobs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql_dtp')->table('jobs', function (Blueprint $table) {
            $table->foreign('issue_id')
                ->references('id')->on('issues')
                ->onDelete('cascade');
        });

        Schema::connection('mysql_path')->table('jobs', function (Blueprint $table) {
            $table->foreign('issue_id')
                ->references('id')->on('issues')
                ->onDelete('cascade');
        });

        Schema::connection('mysql_web')->table('jobs', function (Blueprint $table) {
            $table->foreign('issue_id')
                ->references('id')->on('issues')
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
        Schema::connection('mysql_dtp')->table('jobs', function (Blueprint $table) {
            $table->dropForeign(['issue_id']);
        });

        Schema::connection('mysql_path')->table('jobs', function (Blueprint $table) {
            $table->dropForeign(['issue_id']);
        });

        Schema::connection('mysql_web')->table('jobs', function (Blueprint $table) {
            $table->dropForeign(['issue_id']);
        });
    }
}
