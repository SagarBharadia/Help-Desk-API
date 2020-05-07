<?php /** @noinspection PhpComposerExtensionStubsInspection */

use Illuminate\Support\Facades\DB;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class TenantRoleTest extends TestCase
{
  private $token;
  private $api_url;
  protected static $alreadySetup = false;

  public function __construct($name = null, array $data = [], $dataName = '')
  {
    parent::__construct($name, $data, $dataName);
    $this->api_url = '/' . env('TEST_COMPANY_SUBDIR') . '/';
  }

  public function getRoleByName(string $name)
  {
    return DB::connection('tenant')->table('roles')->where('name', '=', $name)->first();
  }

  public function setUp(): void
  {
    parent::setUp();
    $credentials = [
      'email_address' => env('TENANT_MASTER_ACC_EMAIL_ADDRESS'),
      'password' => env('TENANT_MASTER_ACC_PASSWORD')
    ];
    $response = $this->call('POST', $this->api_url . 'api/login', $credentials);
    $data = json_decode($response->getContent());
    $this->token = $data->token;

    if (!static::$alreadySetup) {
      \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'DropTablesForSeeding']);
      \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'TenantDatabaseSeeder']);
      static::$alreadySetup = true;
    }
  }

  private function getHeaders()
  {
    return [
      'Authorization' => 'Bearer ' . $this->token
    ];
  }

  public function testShouldCreateRole()
  {
    $parameters = [
      'name' => 'first-created-role',
      'display_name' => 'First Created Role',
    ];
    $headers = $this->getHeaders();
    $response = $this->call('POST', $this->api_url . 'api/roles/create', $parameters, $headers);
    $this->assertEquals(200, $response->status());
  }

  public function testShouldUpdateRole()
  {
    $role = $this->getRoleByName('first-seeded-role');
    if (!$role) $this->fail('Role seeder didn\'t work.');
    $parameters = [
      'role_id' => $role->id,
      'name' => 'first-updated-role',
      'display_name' => 'First Updated Role',
      'appliedPermissions' => []
    ];
    $headers = $this->getHeaders();
    $response = $this->call('POST', $this->api_url . 'api/roles/update', $parameters, $headers);
    $this->assertEquals(200, $response->status());
  }

  public function testShouldDeleteRole()
  {
    $role = $this->getRoleByName('second-seeded-role');
    if (!$role) $this->fail('Role seeder didn\'t work.');
    $parameters = [
      'role_id' => $role->id
    ];
    $headers = $this->getHeaders();
    $response = $this->call('POST', $this->api_url . 'api/roles/delete', $parameters, $headers);
    $this->assertEquals(200, $response->status());
  }

  public function testShouldGetAllRoles()
  {
    $headers = $this->getHeaders();
    $response = $this->call('GET', $this->api_url.'api/roles/get/all', [], $headers);
    $this->assertEquals(200, $response->status());
  }

  public function testShouldGetRole() {
    $role = $this->getRoleByName('third-seeded-role');
    if(!$role) $this->fail("Role seeder did not work.");
    $headers = $this->getHeaders();
    $response = $this->call('GET', $this->api_url . 'api/roles/get/'.$role->id, [], $headers);
    $this->assertEquals(200, $response->status());
  }

}
