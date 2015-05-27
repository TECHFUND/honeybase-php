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
$app->post('api/v1/signup', 'App\Http\Controllers\AccountController@signup');
$app->post('api/v1/login', 'App\Http\Controllers\AccountController@login');
$app->post('api/v1/logout', 'App\Http\Controllers\AccountController@logout');
$app->post('api/v1/anonymous', 'App\Http\Controllers\AccountController@anonymousLogin');


$app->post('api/v1/db/insert', 'App\Http\Controllers\DataBaseController@insert');
$app->post('api/v1/db/update', 'App\Http\Controllers\DataBaseController@update');
$app->post('api/v1/db/delete', 'App\Http\Controllers\DataBaseController@delete');
$app->get('api/v1/db/select', 'App\Http\Controllers\DataBaseController@select');
