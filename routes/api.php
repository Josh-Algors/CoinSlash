<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::controller(DashboardController::class)
    ->prefix('v1/auth/user')
    ->middleware('auth:api')
    ->group(function(){
        
        Route::get('/profile', 'profile');
        Route::get('/referrals', 'allReferrals');
        Route::post('/account', 'getAccount');
        Route::get('/bank-codes', 'getBnkCodes');
        Route::post('/set-account', 'setAccount');
        Route::get('/logout', 'logout');
        Route::get('/personal-account', 'getPersonalAcc');
        Route::post('/testt', 'testp');


        //payments group
        Route::group(['prefix' => 'payments'], function(){
            Route::get('/create-sub-account', 'createPaystackAccount');
        });

        //updateProfile
        Route::group(['prefix' => 'settings'], function(){
            Route::patch('/update-profile', 'updateProfile');
        });

        Route::group(['prefix' => 'refer'], function(){
            Route::post('/initialize', 'referAndEarn');
            Route::post('/verify', 'verifyPayment');
        });


});

