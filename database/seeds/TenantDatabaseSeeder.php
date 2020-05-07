<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Class TenantDatabaseSeeder
 * This seeder is to create a 'mock' setup of the full application ready for use.
 * In this mock setup the help desk is that of a software house where their main product is a invoicing system.
 * It is assumed the original instance (before the seeder is ran with the correct $databaseName
 * was created through the API).
 */
class TenantDatabaseSeeder extends Seeder
{
  /**
   * The name of the database connection to add.
   * @var string
   */
  private $databaseName = "test";

  /**
   * Seed the roles.
   *
   * @param array $roles
   */
  private function createRoles(array $roles)
  {
    foreach ($roles as $role) {
      $roleModel = new \App\TenantRole();
      $roleModel->name = $role['name'];
      $roleModel->display_name = $role['display_name'];
      $roleModel->protected_role = 0;
      $roleModel->created_at = $roleModel->freshTimestampString();
      $roleModel->updated_at = $roleModel->freshTimestampString();
      $roleModel->save();
    }
  }

  /**
   * Seed the permissions for the role.
   *
   * @param \App\TenantRole $role
   * @param array $permissionActions
   */
  private function createPermissions(\App\TenantRole $role, array $permissionActions)
  {
    foreach ($permissionActions as $permissionAction) {
      $permissionModel = new \App\TenantPermission();
      $permissionModel->role_id = $role->id;
      $permissionModel->permission_action_id = $permissionAction->id;
      $permissionModel->created_at = $permissionModel->freshTimestampString();
      $permissionModel->updated_at = $permissionModel->freshTimestampString();
      $permissionModel->save();
    }
  }

  /**
   * Seed the users.
   *
   * @param array $users
   */
  private function createUsers(array $users)
  {
    foreach ($users as $user) {
      $userModel = new \App\TenantUser();
      $userModel->role_id = $user['role_id'];
      $userModel->first_name = $user['first_name'];
      $userModel->second_name = $user['second_name'];
      $userModel->email_address = $user['email_address'];
      $userModel->password = $user['password'];
      $userModel->created_at = $userModel->freshTimestampString();
      $userModel->updated_at = $userModel->freshTimestampString();
      $userModel->save();
    }
  }

  /**
   * Seed the calls.
   *
   * @param array $calls
   */
  private function createCalls(array $calls)
  {
    foreach ($calls as $call) {
      $callModel = new \App\TenantCall();
      $callModel->receiver_id = $call['receiver_id'];
      $callModel->current_analyst_id = 0;
      $callModel->client_id = $call['client_id'];
      $callModel->caller_name = $call['caller_name'];
      $callModel->name = $call['name'];
      $callModel->details = $call['details'];
      $callModel->tags = $call['tags'];
      $callModel->resolved = 0;
      $callModel->save();
    }
  }

  /**
   * Create the clients.
   *
   * @param array $clients
   */
  private function createClients(array $clients)
  {
    foreach ($clients as $client) {
      $clientModel = new \App\TenantClient();
      $clientModel->created_by = $client['created_by'];
      $clientModel->name = $client['name'];
      $clientModel->email_address = $client['email_address'];
      $clientModel->phone_number = $client['phone_number'];
      $clientModel->created_at = $clientModel->freshTimestampString();
      $clientModel->updated_at = $clientModel->freshTimestampString();
      $clientModel->save();
    }
  }

  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    // Attempting to find a record in the global company database
    $databaseRecord = \App\GlobalCompanyDatabase::where('company_url_subdirectory', $this->databaseName)
      ->first();

    if (empty($databaseRecord)) {
      echo "Company subdirectory '" . $this->databaseName . "' not found.\n";
      echo "\e[1;37;41mSEEDER FAILED! CHECK THE GLOBAL DATABASE RECORD EXISTS.\e[0m\n";
      return;
    }

    // Adding Tenant Connection
    addConnectionByName($databaseRecord->company_database_name);

    // Dropping the tables and only leaving the data that was inserted by creating the company instance.
    \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'DropTablesForSeeding']);

    // Create Roles
    $roles = [
      ['name' => "admin", 'display_name' => 'Admin'],
      ['name' => "receiver", 'display_name' => "Receiver"],
      ['name' => "support-analyst", 'display_name' => "Analyst"],
      ['name' => 'analytics', 'display_name' => 'Analytics'],
      ['name' => 'first-seeded-role', 'display_name' => 'First Role'],
      ['name' => 'second-seeded-role', 'display_name' => 'Second Role'],
      ['name' => 'third-seeded-role', 'display_name' => 'Third Role']
    ];
    $this->createRoles($roles);


    // Create Permissions for Roles
    $adminRole = \App\TenantRole::getByName('admin');
    $adminPermissionActions = [];
    foreach (DB::connection('tenant')->table('permission_actions')->get() as $pa) {
      array_push($adminPermissionActions, \App\TenantPermissionAction::getByAction($pa->action));
    }
    if (!$adminRole) {
      echo "\e[1;31mCould not find admin role in table, permissions not being assigned.\e[0m\n";
    } else {
      $this->createPermissions($adminRole, $adminPermissionActions);
    }

    $receiverRole = \App\TenantRole::getByName('receiver');
    $receiverPermissionActions = [
      \App\TenantPermissionAction::getByAction('create-call')
    ];
    if (!$receiverRole) {
      echo "\e[1;31mCould not find receiver role in table, permissions not being assigned.\e[0m\n";
    } else {
      $this->createPermissions($receiverRole, $receiverPermissionActions);
    }

    $supportAnalystRole = \App\TenantRole::getByName('support-analyst');
    $supportAnalystPermissionActions = [
      // Call Permissions
      \App\TenantPermissionAction::getByAction('create-call'),
      \App\TenantPermissionAction::getByAction('update-call'),
      \App\TenantPermissionAction::getByAction('read-call'),
      \App\TenantPermissionAction::getByAction('delete-call'),
      // Client Permissions
      \App\TenantPermissionAction::getByAction('create-client'),
      \App\TenantPermissionAction::getByAction('update-client'),
      \App\TenantPermissionAction::getByAction('read-client'),
      \App\TenantPermissionAction::getByAction('delete-client'),
      // Searching permissions
      \App\TenantPermissionAction::getByAction('search-previous-solved-logs')
    ];
    if (!$supportAnalystRole) {
      echo "\e[1;31mCould not find support analyst role in table, permissions not being assigned.\e[0m\n";
    } else {
      $this->createPermissions($supportAnalystRole, $supportAnalystPermissionActions);
    }

    $analyticsRole = \App\TenantRole::getByName('analytics');
    $analyticsPermissionActions = [
      \App\TenantPermissionAction::getByAction('create-report'),
      \App\TenantPermissionAction::getByAction('read-report'),
      \App\TenantPermissionAction::getByAction('delete-report')
    ];
    if (!$supportAnalystRole) {
      echo "\e[1;31mCould not find analytics role in table, permissions not being assigned.\e[0m\n";
    } else {
      $this->createPermissions($analyticsRole, $analyticsPermissionActions);
    }

    // Creating Users
    $usersToSeed = [
      [
        'role_id' => $adminRole->id,
        'first_name' => 'Admin',
        'second_name' => 'Role',
        'email_address' => 'admin@mycompany.com',
        'password' => Hash::make('1234567890')
      ],
      [
        'role_id' => $receiverRole->id,
        'first_name' => 'Receiver',
        'second_name' => 'Role',
        'email_address' => 'receiver@mycompany.com',
        'password' => Hash::make('1234567890')
      ],
      [
        'role_id' => $supportAnalystRole->id,
        'first_name' => 'Support Analyst',
        'second_name' => 'Role',
        'email_address' => 'supportanalyst@mycompany.com',
        'password' => Hash::make('1234567890')
      ],
      [
        'role_id' => $analyticsRole->id,
        'first_name' => "Analytics",
        'second_name' => "Role",
        'email_address' => 'analytics@mycompany.com',
        'password' => Hash::make('1234567890')
      ],
      [
        'first_name' => 'First Name Seeded 1',
        'second_name' => 'Second Name Seeded 1',
        'email_address' => 'firstuserseeded@gmail.com',
        'password' => Hash::make('1234567890'),
        'role_id' => $analyticsRole->id
      ],
      [
        'first_name' => 'First Name Seeded 2',
        'second_name' => 'Second Name Seeded 2',
        'email_address' => 'seconduserseeded@gmail.com',
        'password' => Hash::make('1234567890'),
        'role_id' => $analyticsRole->id
      ]
    ];
    $this->createUsers($usersToSeed);

    $adminUser = \App\TenantUser::where('email_address', '=', 'admin@mycompany.com')->first();
    $receiverUser = \App\TenantUser::where('email_address', '=', 'receiver@mycompany.com')->first();
    $supportAnalystUser = \App\TenantUser::where('email_address', '=', 'supportAnalyst@mycompany.com')->first();
    $analyticsUser = \App\TenantUser::where('email_address', '=', 'analytics@mycompany.com')->first();

    // Creating Clients
    $clients = [
      [
        'created_by' => $adminUser->id,
        'name' => "Ste's Accounting",
        'email_address' => "ste@stesaccounting.com",
        'phone_number' => '04567891234'
      ],
      [
        'created_by' => $adminUser->id,
        'name' => "Emma's & Co Solicitors",
        'email_address' => "emma@emmaandcosolicitor.com",
        'phone_number' => '04567291234'
      ],
      [
        'created_by' => $adminUser->id,
        'name' => "Ralph's Printers",
        'email_address' => "ralph@ralphsprinters.com",
        'phone_number' => '04167891234'
      ],
      [
        'created_by' => $adminUser->id,
        'name' => "Raj's Graphics",
        'email_address' => "raj@rajsgraphics.com",
        'phone_number' => '04567821234'
      ],
      [
        'created_by' => $adminUser->id,
        'name' => 'Kajal\'s hair salon',
        'email_address' => 'kajal@kajalhair.com',
        'phone_number' => '07345475677'
      ],
      [
        'created_by' => $adminUser->id,
        'name' => 'Emma\'s hair salon',
        'email_address' => 'emma@emmasalon.com',
        'phone_number' => '01235479867'
      ],
      [
        'created_by' => $adminUser->id,
        'name' => 'Stacey\'s hair salon',
        'email_address' => 'stacey@staceyssalon.com',
        'phone_number' => '07345479867'
      ]
    ];
    $this->createClients($clients);

    // Creating Calls
    $calls = [
      [
        'receiver_id' => $receiverUser->id,
        'client_id' => \App\TenantClient::where('email_address', '=', 'ste@stesaccounting.com')->first()->id,
        'caller_name' => 'Joseph',
        'name' => 'Adding custom field to invoice not working',
        'details' => 'Unable to add a custom field to the invoice. User was met with error A506.',
        'tags' => "custom field | invoice | A506"
      ],
      [
        'receiver_id' => $receiverUser->id,
        'client_id' => \App\TenantClient::where('email_address', '=', 'emma@emmaandcosolicitor.com')->first()->id,
        'caller_name' => 'Katie',
        'name' => 'Unable to download pdf of invoice',
        'details' => 'Caller says she is unable to download a invoice she has generated. The error D500 pops up to screen.',
        'tags' => 'download invoice | D500'
      ],
      [
        'receiver_id' => $receiverUser->id,
        'client_id' => \App\TenantClient::where('email_address', '=', 'emma@emmaandcosolicitor.com')->first()->id,
        'caller_name' => 'Ellie',
        'name' => 'Unable to save invoice draft',
        'details' => 'Caller has created a draft for a invoice, but is unable to save the draft for her supervisor to check it off',
        'tags' => 'unable to save draft invoice | draft invoice'
      ],
      [
        'receiver_id' => $receiverUser->id,
        'client_id' => \App\TenantClient::where('email_address', '=', 'ralph@ralphsprinters.com')->first()->id,
        'caller_name' => 'Joe',
        'name' => "Can't use email feature to send invoice to a client",
        'details' => 'User tried to use the email feature to send a email to their client. The email was unable to send and showed the error : Connection refused E201',
        'tags' => 'email invoice | E201 | connection refused'
      ]
    ];
    $this->createCalls($calls);

  }
}
