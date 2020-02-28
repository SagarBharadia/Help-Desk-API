<?php /** @noinspection PhpComposerExtensionStubsInspection */

use Illuminate\Support\Facades\DB;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class TenantCallsTest extends TestCase
{
  private $token;
  private $api_url;

  public function __construct($name = null, array $data = [], $dataName = '')
  {
    parent::__construct($name, $data, $dataName);
    $this->api_url = '/'.env('TEST_COMPANY_SUBDIR').'/';
  }

  public function setUp(): void
  {
    parent::setUp();
    $credentials = [
      'email_address' => env('TENANT_MASTER_ACC_EMAIL_ADDRESS'),
      'password' => env('TENANT_MASTER_ACC_PASSWORD')
    ];
    $response = $this->call('POST', $this->api_url.'api/login', $credentials);
    $data = json_decode($response->getContent());
    $this->token = $data->token;
    \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'TenantCallsReset']);
    \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'TenantClientSeeder']);
  }

  private function getHeaders() {
    return [
      'Authorization' => 'Bearer '.$this->token
    ];
  }

  public function testShouldCreateCall()
  {
    $client = DB::connection('tenant')->table('clients')->where('id', '=', 1)->first();
    if(!$client) $this->fail('Seeder did not work.');
    $parameters = [
      'client_id' => $client->id,
      'caller_name' => 'Emma',
      'name' => 'Can\'t login to the online booking system',
      'details' => 'Attempted to login and received error D405',
      'tags' => 'login, d405'
    ];
    $headers = $this->getHeaders();
    $response = $this->call('POST', $this->api_url.'api/calls/create', $parameters, $headers);
    $this->assertEquals(204, $response->status());
  }

}
