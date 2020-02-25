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
            $table->boolean('protected_role');
            $table->timestamps();
        });
        DB::connection('tenant')->table('roles')->insert([
          [
            'id' => 1,
            'name' => 'master',
            'display_name' => 'Master',
            'protected_role' => true
          ],
          [
            'id' => 2,
            'name' => 'user',
            'display_name' => 'User',
            'protected_role' => true
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
