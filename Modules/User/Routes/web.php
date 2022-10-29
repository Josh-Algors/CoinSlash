<?php

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

use App\Http\Controllers\SsoController;
use Modules\User\Http\Controllers\UserController;

Route::prefix('user')->group(function() {
    Route::get('/', 'UserController@index');
});

Route::get('login', [UserController::class, 'failedLogin'])->name('login');


Route::get('auth/{provider}/callback', [SsoController::class, 'handleSsoCallback']);
Route::get('auth/{provider}', [SsoController::class, 'redirectToSSO']);
