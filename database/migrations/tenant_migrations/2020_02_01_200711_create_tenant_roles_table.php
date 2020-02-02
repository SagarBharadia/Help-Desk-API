<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('tenant')->create('roles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('display_name');
            $table->timestamps();
        });
        DB::table('roles')->insert([
          [
            'id' => 1,
            'name' => 'master',
            'display_name' => 'Master'
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
        Schema::connection('tenant')->dropIfExists('roles');
    }
}
