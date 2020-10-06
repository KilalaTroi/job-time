<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDescriptionToJobType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql_dtp')->table('types', function (Blueprint $table) {
            $table->string('description_vi')->nullable()->after('slug_vi');
            $table->string('description_ja')->nullable()->after('slug_ja');
        });

        Schema::connection('mysql_path')->table('types', function (Blueprint $table) {
            $table->string('description_vi')->nullable()->after('slug_vi');
            $table->string('description_ja')->nullable()->after('slug_ja');
        });

        Schema::connection('mysql_web')->table('types', function (Blueprint $table) {
            $table->string('description_vi')->nullable()->after('slug_vi');
            $table->string('description_ja')->nullable()->after('slug_ja');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql_dtp')->table('types', function (Blueprint $table) {
            $table->dropColumn('description_vi');
            $table->dropColumn('description_ja');
        });

        Schema::connection('mysql_path')->table('types', function (Blueprint $table) {
            $table->dropColumn('description_vi');
            $table->dropColumn('description_ja');
        });

        Schema::connection('mysql_web')->table('types', function (Blueprint $table) {
            $table->dropColumn('description_vi');
            $table->dropColumn('description_ja');
        });
    }
}
