<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

$app->get('/', function() use ($app) {
    return $app->welcome();
});

$app->get('api/v1/get_current_user', 'App\Http\Controllers\AccountController@getCurrentUser');
$app->post('api/v1/oauth', 'App\Http\Controllers\AccountController@oauth');
$app->post('api/v1/logout', 'App\Http\Controllers\AccountController@logout');


$app->post('api/v1/db/insert', ['uses'=>'App\Http\Controllers\DataBaseController@insert', 'middleware' => 'rights']);
$app->post('api/v1/db/update', ['uses'=>'App\Http\Controllers\DataBaseController@update', 'middleware' => 'rights']);
$app->post('api/v1/db/delete', ['uses'=>'App\Http\Controllers\DataBaseController@delete', 'middleware' => 'rights']);
$app->get('api/v1/db/select', ['uses'=>'App\Http\Controllers\DataBaseController@select', 'middleware' => 'rights']);
