<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantLogActionsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::connection('tenant')->create('log_actions', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->string('name');
      $table->string('action')->unique();
      $table->timestamps();
    });

    DB::connection('tenant')->table('log_actions')->insert([
      [
        'name' => 'Created User',
        'action' => 'created-user'
      ],
      [
        'name' => 'Accessed User',
        'action' => 'accessed-user'
      ],
      [
        'name' => 'Updated User',
        'action' => 'updated-user'
      ],
      [
        'name' => 'Toggle Active State for User',
        'action' => 'toggledActive-for-user'
      ],
      [
        'name' => 'Created Role',
        'action' => 'created-role'
      ],
      [
        'name' => 'Accessed Role',
        'action' => 'accessed-role'
      ],
      [
        'name' => 'Updated Role',
        'action' => 'updated-role'
      ],
      [
        'name' => 'Deleted Role',
        'action' => 'deleted-role'
      ],
      [
        'name' => 'Assigned Permissions to Role',
        'action' => 'assigned-permissions-to-role'
      ],
      [
        'name' => 'Accessed Permissions for Role',
        'action' => "accessed-permissions-for-role"
      ],
      [
        'name' => 'Updated Permissions for Role',
        'action' => 'updated-permissions-for-role'
      ],
      [
        'name' => 'Created Call',
        'action' => 'created-call'
      ],
      [
        'name' => 'Accessed Call',
        'action' => 'accessed-call'
      ],
      [
        'name' => 'Updated Call',
        'action' => 'updated-call'
      ],
      [
        'name' => 'Deleted Call',
        'action' => 'deleted-call',
      ],
      [
        'name' => 'Created Report',
        'action' => 'created-report'
      ],
      [
        'name' => 'Accessed Report',
        'action' => 'accessed-report'
      ],
      [
        'name' => 'Deleted Report',
        'action' => 'deleted-report'
      ],
      [
        'name' => 'Accessed User Logs',
        'action' => 'accessed-user-logs'
      ],
      [
        'name' => 'Created Client',
        'action' => 'created-client'
      ],
      [
        'name' => 'Accessed Client',
        'action' => 'accessed-client'
      ],
      [
        'name' => 'Updated Client',
        'action' => 'updated-client'
      ],
      [
        'name' => 'Deleted Client',
        'action' => 'deleted-client'
      ],
      [
        'name' => 'Searched Previous Solved Logs',
        'action' => 'searched-previous-solved-logs'
      ],
      [
        'name' => 'User logged in',
        'action' => 'user-logged-in'
      ],
      [
        'name' => 'User attempted to login',
        'action' => 'user-attempted-to-login'
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
    Schema::connection('tenant')->dropIfExists('log_actions');
  }
}
