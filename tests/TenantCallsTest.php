<?php /** @noinspection PhpComposerExtensionStubsInspection */

use Illuminate\Support\Facades\DB;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class TenantCallsTest extends TestCase
{
  private $token;
  private $api_url;
  protected static $alreadySetup = false;

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

    if(!static::$alreadySetup) {
      \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'DropTablesForSeeding']);
      \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'TenantDatabaseSeeder']);
      static::$alreadySetup = true;
    }
  }

  private function getHeaders() {
    return [
      'Authorization' => 'Bearer '.$this->token
    ];
  }

  private function getClient() {
    return DB::connection('tenant')->table('clients')->select()->first();
  }

  private function getCall() {
    return DB::connection('tenant')->table('calls')->select()->first();
  }

  public function testShouldCreateCall()
  {
    $client = $this->getClient();
    if(!$client) $this->fail('Client seeder did not work.');
    $parameters = [
      'client_id' => $client->id,
      'caller_name' => 'Emma',
      'name' => 'Can\'t login to the online booking system',
      'details' => 'Attempted to login and received error D405',
      'tags' => ['login', 'd405']
    ];
    $headers = $this->getHeaders();
    $response = $this->call('POST', $this->api_url.'api/calls/create', $parameters, $headers);
    $this->assertEquals(200, $response->status());
  }

  public function testShouldUpdateCall()
  {
    $call = $this->getCall();
    if(!$call) $this->fail('Call seeder failed.');
    $parameters = [
      'call_id' => $call->id,
      'details' => 'Currently looking into the issue. The client has said that the online payment was via stripe.',
      'tags' => explode(" | ",$call->tags),
      'current_analyst_id' => $call->current_analyst_id
    ];
    $headers = $this->getHeaders();
    $response = $this->call('POST', $this->api_url.'api/calls/update', $parameters, $headers);
    $this->assertEquals(200, $response->status());
  }

  public function testShouldDeleteCall()
  {
    $call = $this->getCall();
    if(!$call) $this->fail('Call seeder failed.');
    DB::connection('tenant')->table('call_updates')->where('call_id', '=', $call->id)->delete();
    $parameters = [
      'call_id' => $call->id
    ];
    $headers = $this->getHeaders();
    $response = $this->call('POST', $this->api_url.'api/calls/delete', $parameters, $headers);
    $this->assertEquals(200, $response->status());
  }

  public function testShouldGetAllCalls()
  {
    $headers = $this->getHeaders();
    $response = $this->call('GET', $this->api_url.'api/calls/get/all', [], $headers);
    $this->assertEquals(200, $response->status());
  }

  public function testShouldGetCall() {
    $headers = $this->getHeaders();
    $call = $this->getCall();
    $response = $this->call('GET', $this->api_url.'api/calls/get/'.$call->id, [], $headers);
    $this->assertEquals(200, $response->status());
  }

}
