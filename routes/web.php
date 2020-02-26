<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

// Unauthenticated /super/api/ routes
$router->group([
  'prefix' => 'super/api'
], function () use ($router) {
    // Matches /super/api/login
    $router->post('login', 'GlobalAuthController@login');
    // Matches /super/api/register
    $router->post('register', 'GlobalAuthController@register');
});

// Authenticated /super/api/ routes
$router->group([
  'prefix' => 'super/api',
  'middleware' => [
    'auth:global_api',
    'role:super'
  ]
], function () use ($router) {
    // Matches /super/api/create/tenant
    $router->post('create/tenant', 'GlobalCompanyController@create');
});

// Unauthenticated tenant routes
$router->group([
  'prefix' => '{company_subdirectory}/api',
  'middleware' => [
    'addTenantConnection',
  ]
], function () use ($router) {
    $router->post('login', 'TenantAuthController@login');
});

// Authenticated tenant routes
$router->group([
  'prefix' => '{company_subdirectory}/api',
  'middleware' => [
    'addTenantConnection',
    'auth:tenant_api'
  ]
], function () use ($router) {
  // User Related Routes
  $router->post('users/create', ['middleware' => 'perm:create-users', 'uses' => 'TenantUserController@create']);
  $router->post('users/update', ['middleware' => 'perm:update-users', 'uses' => 'TenantUserController@update']);
  $router->post('users/toggleActive', ['middleware' => 'perm:toggleActive-for-users', 'uses' => 'TenantUserController@toggleActive']);
  $router->get('users/get/all', ['middleware' => 'perm:read-users', 'uses' => 'TenantUserController@getAll']);
//  $router->get('users/get/{user_id}', ['middleware' => 'perm:read-users', 'uses' => 'TenantUserController@getUser']);

  // THEN USER RELATED (RESET PASSWORD, EMAIL CONFIRMATION)

  // THEN CREATING NEW COMPANIES

  // THEN CREATING NEW CALLS

  // THEN CREATING CALL UPDATES

  // THEN CREATING REPORTS

  // Calls related routes
  $router->post('calls/create', ['middleware' => 'perm:create-call', 'uses' => 'TenantCallController@create']);
});