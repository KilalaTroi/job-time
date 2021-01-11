<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->integer('dept_id')->unsigned()->nullable()->after('issue');
            $table->integer('project_id')->unsigned()->nullable()->after('issue');
            $table->integer('issue_year')->unsigned()->nullable()->after('issue');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn('dept_id');
            $table->dropColumn('project_id');
            $table->dropColumn('issue_year');
        });
    }
}
