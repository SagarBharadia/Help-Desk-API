<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGlobalRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('global')->create('roles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('display_name');
            $table->timestamps();
        });

        DB::connection('global')->table('roles')->insert([
            [
              'id' => 1,
              'name' => 'super',
              'display_name' => 'Super Admin'
            ],
            [
              'id' => 2,
              'name' => 'user',
              'display_name' => 'User'
            ]

        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('global')->dropIfExists('roles');
    }
}
