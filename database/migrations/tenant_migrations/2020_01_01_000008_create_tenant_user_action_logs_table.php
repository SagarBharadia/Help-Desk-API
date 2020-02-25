<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantUserActionLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('tenant')->create('user_action_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('log_action_id');
            $table->text('details')->nullable();
            $table->timestamps();


        });

        Schema::connection('tenant')->table('user_action_logs', function(Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('log_action_id')->references('id')->on('log_actions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('tenant')->dropIfExists('user_action_logs');
    }
}
