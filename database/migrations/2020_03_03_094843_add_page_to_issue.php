<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPageToIssue extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql_dtp')->table('issues', function (Blueprint $table) {
            $table->smallInteger('page')->nullable()->after('status');
        });

        Schema::connection('mysql_path')->table('issues', function (Blueprint $table) {
            $table->smallInteger('page')->nullable()->after('status');
        });

        Schema::connection('mysql_web')->table('issues', function (Blueprint $table) {
            $table->smallInteger('page')->nullable()->after('status');
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
            $table->dropColumn('page');
        });

        Schema::connection('mysql_path')->table('issues', function (Blueprint $table) {
            $table->dropColumn('page');
        });

        Schema::connection('mysql_web')->table('issues', function (Blueprint $table) {
            $table->dropColumn('page');
        });
    }
}
