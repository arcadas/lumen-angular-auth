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

$app->post('/auth/login', 'Auth\AuthController@login');
$app->post('/auth/register', 'Auth\AuthController@register');
$app->get('/auth/verify', 'Auth\AuthController@verify');

$app->group(['middleware' => 'jwt.auth'], function ($app) {

    $app->get('/auth/me', function () use ($app) {
        return [
            'success' => [
                'user' => JWTAuth::parseToken()->authenticate(),
            ],
        ];
    });

    $app->get('/auth/logout', 'App\Http\Controllers\Auth\AuthController@logout');
});
