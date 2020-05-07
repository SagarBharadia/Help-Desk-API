<?php /** @noinspection PhpComposerExtensionStubsInspection */

use Illuminate\Support\Facades\DB;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class TenantClientTest extends TestCase
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

    if(!static::$alreadySetup) {
      \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'DropTablesForSeeding']);
      \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'TenantDatabaseSeeder']);
      static::$alreadySetup = true;
    }
  }

  private function getClientByEmail($email)
  {
    return DB::connection('tenant')->table('clients')->where('email_address', '=', $email)->first();
  }

  private function getHeaders()
  {
    return [
      'Authorization' => 'Bearer ' . $this->token
    ];
  }

  public function testShouldCreateClient()
  {
    $parameters = [
      'name' => 'Client Name',
      'email_address' => 'client@clienturl.com',
      'phone_number' => '01234567894'
    ];
    $headers = $this->getHeaders();
    $response = $this->call('POST', $this->api_url . 'api/clients/create', $parameters, $headers);
    $this->assertEquals(201, $response->status());
  }

  public function testShouldUpdateClient()
  {
    $client = $this->getClientByEmail("emma@emmasalon.com");
    if (!$client) $this->fail('Client seeder did not work.');
    $parameters = [
      'client_id' => $client->id,
      'name' => 'Client Update Name',
      'email_address' => 'updateclientemail@clienturl.com',
      'phone_number' => '09876543210'
    ];
    $headers = $this->getHeaders();
    $response = $this->call('POST', $this->api_url . 'api/clients/update', $parameters, $headers);
    $this->assertEquals(200, $response->status());
  }

  public function testShouldGetAllClients()
  {
    $headers = $this->getHeaders();
    $response = $this->call('GET', $this->api_url . 'api/clients/get/all', [], $headers);
    $this->assertEquals(200, $response->status());
  }

  public function testShouldGetClient()
  {
    $client = $this->getClientByEmail("stacey@staceyssalon.com");
    if(!$client) $this->fail("Client seeder did not work.");
    $headers = $this->getHeaders();
    $response = $this->call('GET', $this->api_url . 'api/clients/get/'.$client->id, [], $headers);
    $this->assertEquals(200, $response->status());
  }

  public function testShouldDeleteClient()
  {
    $client = $this->getClientByEmail("kajal@kajalhair.com");
    if (!$client) $this->fail('Client seeder did not work.');
    $parameters = [
      'client_id' => $client->id
    ];
    $headers = $this->getHeaders();
    $response = $this->call('POST', $this->api_url . 'api/clients/delete', $parameters, $headers);
    $this->assertEquals(200, $response->status());
  }
}
