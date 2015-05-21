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

$app->post('api/v1/db/push', 'App\Http\Controllers\DataBaseController@push');
$app->post('api/v1/db/set', 'App\Http\Controllers\DataBaseController@set');
$app->post('api/v1/db/remove', 'App\Http\Controllers\DataBaseController@remove');
$app->post('api/v1/db/select', 'App\Http\Controllers\DataBaseController@select');
