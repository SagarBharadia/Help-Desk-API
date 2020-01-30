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

$router->group(['prefix' => 'super/api'], function () use ($router) {
    // Matches /super/api/register
    $router->post('register', 'GlobalAuthController@register');
    // Matches /super/api/login
    $router->post('login', 'GlobalAuthController@login');
    // Matches /super/api/create/tenant
    $router->post('create/tenant', 'GlobalTenantController@create');
});

