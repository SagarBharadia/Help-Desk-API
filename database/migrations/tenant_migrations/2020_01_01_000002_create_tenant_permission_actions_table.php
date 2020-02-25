<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantPermissionActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('tenant')->create('permission_actions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('action')->unique();
            $table->timestamps();
        });

        DB::connection('tenant')->table('permission_actions')->insert([
          [
            'name' => 'Create Users',
            'action' => 'create-users'
          ],
          [
            'name' => 'Read Users',
            'action' => 'read-users'
          ],
          [
            'name' => 'Update Users',
            'action' => 'update-users'
          ],
          [
            'name' => 'Toggle Active State For Users',
            'action' => 'toggleActive-for-users'
          ],
          [
            'name' => 'Create Roles',
            'action' => 'create-roles'
          ],
          [
            'name' => 'Read Roles',
            'action' => 'read-roles'
          ],
          [
            'name' => 'Update Roles',
            'action' => 'update-roles'
          ],
          [
            'name' => 'Delete Roles',
            'action' => 'delete-roles'
          ],
          [
            'name' => 'Assign Permissions to Role',
            'action' => 'assign-permissions-to-role'
          ],
          [
            'name' => 'Read Permissions for Role',
            'action' => 'read-permissions-for-role'
          ],
          [
            'name' => 'Update Permissions for Role',
            'action' => 'update-permissions-for-role'
          ],
          [
            'name' => 'Create Call',
            'action' => 'create-call'
          ],
          [
            'name' => 'Read Call',
            'action' => 'read-call'
          ],
          [
            'name' => 'Update Call',
            'action' => 'update-call'
          ],
          [
            'name' => 'Delete Call',
            'action' => 'delete-call'
          ],
          [
            'name' => 'Create Report',
            'action' => 'create-report'
          ],
          [
            'name' => 'Read Report',
            'action' => 'read-report'
          ],
          [
            'name' => 'Delete Report',
            'action' => 'delete-report'
          ],
          [
            'name' => 'Read User Logs',
            'action' => 'read-user-logs'
          ],
          [
            'name' => 'Create Client',
            'action' => 'create-client'
          ],
          [
            'name' => 'Read Client',
            'action' => 'read-client'
          ],
          [
            'name' => 'Update Client',
            'action' => 'update-client'
          ],
          [
            'name' => 'Delete Client',
            'action' => 'delete-client'
          ],
          [
            'name' => 'Search Previous Solved Logs',
            'action' => 'search-previous-solved-logs'
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
        Schema::connection('tenant')->dropIfExists('permission_actions');
    }
}
