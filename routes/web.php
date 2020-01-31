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
    'auth:global_api'
  ]
], function () use ($router) {
    // Matches /super/api/create/tenant
    $router->post('create/tenant', 'GlobalCompanyController@create');
});

