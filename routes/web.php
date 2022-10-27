<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterCtrl;
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
        $router->get('/test', "RegisterCtrl@test");


});