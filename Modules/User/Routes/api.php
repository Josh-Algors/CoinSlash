<?php

use App\Http\Controllers\SsoController;
use Illuminate\Http\Request;
use Modules\User\Http\Controllers\PermissionController;
// use Illuminate\Routing\Route;
use Modules\User\Http\Controllers\UserController;
use Spatie\Permission\Traits\HasRoles;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::prefix('user')->group(function () {
    
    Route::post('register', [UserController::class, 'register']);
    Route::post('email/verify', [UserController::class, 'verifyEmail']);
    Route::post('phone/verify', [UserController::class, 'verifyEmail']);
    Route::post('login', [UserController::class, 'login']);
    
    Route::post('otp/resend', [UserController::class, 'resendOTP']);


    Route::middleware('auth:api')->group(function(){
        Route::post('add_category', [UserController::class, 'addUserCategory'])->name('add_category');
        Route::get('profile', [UserController::class, 'userProfile']);
    });
    
    Route::prefix('permissions')->group(function () {
    Route::get('/', [PermissionController::class, 'getAllPermissions'])->middleware('auth:api');
    Route::get('self', [PermissionController::class, 'getMyPermissions'])->middleware('auth:api');
    Route::post('{action}', [PermissionController::class, 'assignRevokePermissions'])->middleware('auth:api');
    
});
});


Route::get('auth/{provider}/callback', [SsoController::class, 'handleSsoCallback']);
Route::get('auth/{provider}', [SsoController::class, 'redirectToSSO']);


// Route::middleware('auth:api')->group(function(){
    Route::get('run/{cmd}', [UserController::class, 'runCommand']);
// });
