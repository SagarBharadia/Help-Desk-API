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
            $table->string('caller_name');
            $table->string('name');
            $table->bigInteger('receiver_id');
            $table->bigInteger('current_analyst_id');
            $table->text('details');
            $table->text('tags');
            $table->boolean('resolved');
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
        Schema::connection('tenant')->dropIfExists('calls');
    }
}
