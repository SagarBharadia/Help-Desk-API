<?php /** @noinspection PhpComposerExtensionStubsInspection */

use Illuminate\Support\Facades\DB;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class TenantUserTest extends TestCase
{
  private $token;
  private $api_url;
  protected static $alreadySetup = false;

  public function __construct($name = null, array $data = [], $dataName = '')
  {
    parent::__construct($name, $data, $dataName);
    $this->api_url = '/' . env('TEST_COMPANY_SUBDIR') . '/';
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
      \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'DropTablesForTest']);
      \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'TenantUserSeeder']);
      static::$alreadySetup = true;
    }
  }

  private function getHeaders()
  {
    return [
      'Authorization' => 'Bearer ' . $this->token
    ];
  }

  private function getUserByEmail($email)
  {
    return DB::connection('tenant')->table('users')->where('email_address', '=', $email)->first();
  }

  public function testShouldCreateUser()
  {
    $parameters = [
      'role_id' => 2,
      'first_name' => 'Test First',
      'second_name' => 'Test Second',
      'email_address' => \Illuminate\Support\Str::random(16) . "@gmail.com",
      'password' => 'N8gDs6H#gc9^-Y(F',
      'password_confirmation' => 'N8gDs6H#gc9^-Y(F'
    ];
    $headers = $this->getHeaders();
    $response = $this->call('POST', $this->api_url . 'api/users/create', $parameters, $headers);
    $this->assertEquals(201, $response->status());
  }

  public function testShouldUpdateUser()
  {
    $user = $this->getUserByEmail("firstuserseeded@gmail.com");
    if (!$user) $this->fail('User seeder didn\'t work.');
    $parameters = [
      'user_id' => $user->id,
      'first_name' => 'Test Updated First Name',
      'second_name' => 'Test Update Second Name'
    ];
    $headers = $this->getHeaders();
    $response = $this->call('POST', $this->api_url . 'api/users/update', $parameters, $headers);
    $this->assertEquals(204, $response->status());
  }

  public function testShouldToggleActiveStateOfUser()
  {
    $user = $this->getUserByEmail("firstuserseeded@gmail.com");
    if (!$user) $this->fail('User seeder didn\'t work.');
    $parameters = [
      'user_id' => $user->id
    ];
    $headers = $this->getHeaders();
    $response = $this->call('POST', $this->api_url . 'api/users/toggleActive', $parameters, $headers);
    $this->assertEquals(204, $response->status());
  }

  public function testShouldGetAllUsers()
  {
    $headers = $this->getHeaders();
    $response = $this->call('GET', $this->api_url . 'api/users/get/all', [], $headers);
    $this->assertEquals(200, $response->status());
  }

  public function testShouldGetUser()
  {
    $user = $this->getUserByEmail("seconduserseeded@gmail.com");
    if (!$user) $this->fail('User seeder didn\'t work.');
    $headers = $this->getHeaders();
    $response = $this->call('GET', $this->api_url . 'api/users/get/' . $user->id, [], $headers);
    $this->assertEquals(200, $response->status());
  }
}
