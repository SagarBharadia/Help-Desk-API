<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGlobalCompanyDatabasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('global')->create('company_databases', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('global_user_id');
            $table->string('company_name');
            $table->string('company_database_name')->unique();
            $table->string('company_url_subdirectory')->unique();
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
        Schema::connection('global')->dropIfExists('company_databases');
    }
}
