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
    'addTenantSecret'
  ]
], function () use ($router) {
  $router->post('login', 'TenantAuthController@login');
});

// Authenticated tenant routes
$router->group([
  'prefix' => '{company_subdirectory}/api',
  'middleware' => [
    'addTenantConnection',
    'addTenantSecret',
    'auth:tenant_api'
  ]
], function () use ($router) {
  // Check if token relates to this company
  $router->get('check-token', 'TenantUserController@checkToken');

  // Platform Level User Routes
  $router->post('users/create', ['middleware' => 'perm:create-users', 'uses' => 'TenantUserController@create']);
  $router->post('users/update', ['middleware' => 'perm:update-users', 'uses' => 'TenantUserController@update']);
  $router->post('users/toggleActive', ['middleware' => 'perm:toggleActive-for-users', 'uses' => 'TenantUserController@toggleActive']);
  $router->get('users/get/all', ['middleware' => 'perm:read-users', 'uses' => 'TenantUserController@getAll']);
  $router->get('users/get/{user_id}', ['middleware' => 'perm:read-users', 'uses' => 'TenantUserController@get']);

  // TODO: Need to create routes to load user action logs with appropriate permission to read users
  // Not to be confused with "self" routes which have not been created yet

  // TODO: Need to create self routes with self permission or middleware to check they are editting themselves

  // Tenant Level Role Routes
  $router->post('roles/create', ['middleware' => 'perm:create-roles', 'uses' => 'TenantRoleController@create']);
  $router->post('roles/update', ['middleware' => 'perm:update-roles', 'uses' => 'TenantRoleController@update']);
  $router->post('roles/delete', ['middleware' => 'perm:delete-roles', 'uses' => 'TenantRoleController@delete']);
  $router->get('roles/get/all', ['middleware' => 'perm:read-roles', 'uses' => 'TenantRoleController@getAll']);
  $router->get('roles/get/{role_id}', ['middleware' => 'perm:read-roles', 'uses' => 'TenantRoleController@get']);

  // Tenant Level Permissions Routes
  $router->get('permissions/get/all', ['middleware' => 'perm:create-roles', 'uses' => 'TenantPermissionController@getAll']);

  // Tenant Level Client Routes
  $router->post('clients/create', ['middleware' => 'perm:create-client', 'uses' => 'TenantClientController@create']);
  $router->post('clients/update', ['middleware' => 'perm:update-client', 'uses' => 'TenantClientController@update']);
  $router->post('clients/delete', ['middleware' => 'perm:delete-client', 'uses' => 'TenantClientController@delete']);
  $router->get('clients/get/all', ['middleware' => 'perm:read-client', 'uses' => 'TenantClientController@getAll']);
  $router->get('clients/get/{client_id}', ['middleware' => 'perm:read-client', 'uses' => 'TenantClientController@get']);

  // Tenant Level Call Routes
  $router->post('calls/create', ['middleware' => 'perm:create-call', 'uses' => 'TenantCallController@create']);
  $router->post('calls/update', ['middleware' => 'perm:update-call', 'uses' => 'TenantCallController@update']);
  $router->post('calls/delete', ['middleware' => 'perm:delete-call', 'uses' => 'TenantCallController@delete']);
  $router->get('calls/get/all', ['middleware' => 'perm:read-call', 'uses' => 'TenantCallController@getAll']);
  $router->get('calls/get/{call_id}', ['middleware' => 'perm:read-call', 'uses' => 'TenantCallController@get']);
  $router->get('calls/search', ['middleware' => 'perm:search-previous-solved-logs', 'uses' => 'TenantCallController@search']);

  // THEN CREATING REPORTS

});