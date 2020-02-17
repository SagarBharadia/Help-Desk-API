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

        DB::table('permission_actions')->insert([
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
            'name' => 'Deactivate Users',
            'action' => 'deactivate-users'
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
            'name' => 'Assign Permissions to Roles',
            'action' => 'assign-permissions-to-roles'
          ],
          [
            'name' => 'Read Permissions for Roles',
            'action' => 'read-permissions-for-roles'
          ],
          [
            'name' => 'Update Permissions for Roles',
            'action' => 'update-permissions-for-roles'
          ],
          [
            'name' => 'Create Calls',
            'action' => 'create-calls'
          ],
          [
            'name' => 'Read Calls',
            'action' => 'read-calls'
          ],
          [
            'name' => 'Update Calls',
            'action' => 'update-calls'
          ],
          [
            'name' => 'Delete Calls',
            'action' => 'delete-calls'
          ],
          [
            'name' => 'Create Reports',
            'action' => 'create-reports'
          ],
          [
            'name' => 'Read Reports',
            'action' => 'read-reports'
          ],
          [
            'name' => 'Delete Reports',
            'action' => 'delete-reports'
          ],
          [
            'name' => 'Read User Logs',
            'action' => 'read-user-logs'
          ],
          [
            'name' => 'Create Clients',
            'action' => 'create-clients'
          ],
          [
            'name' => 'Read Clients',
            'action' => 'read-clients'
          ],
          [
            'name' => 'Update Clients',
            'action' => 'update-clients'
          ],
          [
            'name' => 'Delete Clients',
            'action' => 'delete-clients'
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
