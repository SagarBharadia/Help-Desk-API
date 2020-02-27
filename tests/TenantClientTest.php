<?php /** @noinspection PhpComposerExtensionStubsInspection */

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class TenantClientTest extends TestCase
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
  }

  private function getHeaders() {
    return [
      'Authorization' => 'Bearer '.$this->token
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
    $response = $this->call('POST', $this->api_url.'api/clients/create', $parameters, $headers);
    $this->assertEquals(204, $response->status());
  }

  public function testShouldUpdateClient()
  {
    $parameters = [
      'client_id' => 1,
      'name' => 'Client Update Name',
      'email_address' => 'updateclientemail@clienturl.com',
      'phone_number' => '09876543210'
    ];
    $headers = $this->getHeaders();
    $response = $this->call('POST', $this->api_url.'api/clients/update', $parameters, $headers);
    $this->assertEquals(204, $response->status());
  }

  public function testShouldGetAllClients()
  {
    $headers = $this->getHeaders();
    $response = $this->call('GET', $this->api_url.'api/clients/get/all', [], $headers);
    $this->assertEquals(200, $response->status());
  }

  public function testShouldGetClient()
  {
    $headers = $this->getHeaders();
    $response = $this->call('GET', $this->api_url.'api/clients/get/1', [], $headers);
    $this->assertEquals(200, $response->status());
  }

  public function testShouldDeleteClient()
  {
    $parameters = [
      'client_id' => 1
    ];
    $headers = $this->getHeaders();
    $response = $this->call('POST', $this->api_url.'api/clients/delete', $parameters, $headers);
    $this->assertEquals(204, $response->status());
  }
}
