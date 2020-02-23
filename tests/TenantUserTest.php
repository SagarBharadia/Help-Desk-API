<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class TenantUserTest extends TestCase
{
  private $token;
  private $test_company_subdir;

  public function __construct($name = null, array $data = [], $dataName = '')
  {
    parent::__construct($name, $data, $dataName);
    $this->test_company_subdir = env('TEST_COMPANY_SUBDIR');
  }

  public function setUp(): void
  {
    parent::setUp();
    $credentials = [
      'email_address' => env('TENANT_MASTER_ACC_EMAIL_ADDRESS'),
      'password' => env('TENANT_MASTER_ACC_PASSWORD')
    ];
    $response = $this->call('POST', '/'.$this->test_company_subdir.'/api/login', $credentials);
    $data = json_decode($response->getContent());
    $this->token = $data->token;
  }

  public function testShouldCreateUser()
  {
    $parameters = [
      'role_id' => 2,
      'first_name' => 'Test First',
      'second_name' => 'Test Second',
      'email_address' => 'testemail@gmail.com',
      'password' => '123456789',
      'password_confirmation' => '123456789'
    ];
    $headers = ['Authorization' => 'Bearer '.$this->token];
    $response = $this->call('POST', '/'.$this->test_company_subdir.'/api/users/create', $parameters, $headers);
    $this->assertEquals(201, $response->status());
  }

//  public function testShouldUpdateUser()
//  {
//
//  }
//
//  public function testShouldDeactivateUser()
//  {
//
//  }
//
//  public function testShouldGetAllUsers()
//  {
//
//  }
//
//  public function testShouldGetUser()
//  {
//
//  }
}
