<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('tenant')->create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('role_id')->default(2);
            $table->string('first_name');
            $table->string('second_name');
            $table->string('email_address')->unique();
            $table->string('password');
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
        Schema::connection('tenant')->dropIfExists('users');
    }
}
