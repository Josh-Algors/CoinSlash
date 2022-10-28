<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterCtrl;
use App\Http\Controllers\LoginController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

$router->get('/', function () use ($router) {
    //return $router->app->version();
    return redirect('https://google.com');
});

$router->group([
    'prefix' => 'api/v1',
    'middleware' => [
        'cors',
        'throttle',
    ],
    'namespace' => 'App\Http\Controllers',
], function () use ($router) {

        //Invoice
        $router->post('/signup', "RegisterCtrl@signup");
        $router->post('/login', "LoginController@login");
        $router->post('/forgot-password', "LoginController@forgotPassword");
        $router->post('/logout', "LoginController@logout");


});