<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantCallUpdatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('tenant')->create('call_updates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('call_id');
            $table->unsignedBigInteger('user_id');
            $table->text('details');
            $table->timestamps();


        });

        Schema::connection('tenant')->table('call_updates', function(Blueprint $table) {
            $table->foreign('call_id')->references('id')->on('calls');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('tenant')->dropIfExists('call_updates');
    }
}
