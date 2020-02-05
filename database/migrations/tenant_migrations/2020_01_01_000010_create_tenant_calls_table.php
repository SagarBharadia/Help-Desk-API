<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantCallsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('tenant')->create('calls', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('receiver_id');
            $table->unsignedBigInteger('current_analyst_id');
            $table->unsignedBigInteger('company_id');
            $table->string('caller_name');
            $table->string('name');
            $table->text('details');
            $table->text('tags');
            $table->boolean('resolved');
            $table->timestamps();



        });

        Schema::connection('tenant')->table('calls', function(Blueprint $table) {
            $table->foreign('receiver_id')->references('id')->on('users');
            $table->foreign('current_analyst_id')->references('id')->on('users');
            $table->foreign('company_id')->references('id')->on('companies');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('tenant')->dropIfExists('calls');
    }
}
