<?php

use App\Http\Controllers\PatientController;
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

Route::controller(PatientController::class)
    ->prefix('v1/auth/patient')
    ->middleware('auth:api')
    ->group(function(){
        Route::get('/dashboard', 'dashboard');
        Route::get('/profile', 'userProfile');
        Route::get('/validate', 'validatePatient');
        Route::post('/onboard-step1', 'register');
        Route::post('/personal-details', 'personalDetails');
        Route::patch('/change-password', 'updatePassword');
        Route::patch('/update-profile', 'updateProfile');
        Route::post('/update-profile-picture', 'uploadProfilePicture');
        Route::post('/onboard-dependents', 'onboardDependents');
        Route::post('/other-details', 'otherDetails');




        Route::controller(PatientController::class)
                ->prefix('/records')
                ->middleware('auth:api')
                ->group(function(){

                Route::post('/add-medical-record', 'addMedicalRecord');
                Route::post('/add-medical-records', 'addMedicalRecords');
                Route::get('/all-medical-records', 'getAllMedicalRecords');
                Route::get('/single-medical-record/{id}', 'getSingleMedicalRecord');
                Route::delete('/delete-medical-record/{id}', 'deleteSingleMedicalRecord');

                Route::get('/other-medical-record/view/{id}', 'getSingleOtherMedicalRecord');
                Route::get('/other-medical-record/all', 'allOtherMedicalRecords');
                Route::post('/other-medical-record/add', 'addOtherMedicalRecord');
                Route::patch('/other-medical-record/edit/{id}', 'updateOtherMedicalRecord');
                Route::delete('/other-medical-record/delete/{id}', 'deleteOtherMedicalRecords');
        });

        Route::controller(PatientController::class)
                ->prefix('/dependents')
                ->middleware('auth:api')
                ->group(function(){

                Route::get('/all-dependents', 'AllDependents');
                Route::get('/single-dependent/{id}', 'SingleDependent');
                Route::post('/add-dependent', 'addDependent');
                Route::patch('/edit-dependent/{id}', 'updateDependent');
                Route::delete('/delete-dependent/{id}', 'deleteSingleDependent');
        });

        Route::controller(PatientController::class)
                ->prefix('/account')
                ->middleware('auth:api')
                ->group(function(){

                Route::get('/view', 'getAccountDetails');
                Route::post('/add', 'setAccountDetails');
                Route::patch('/edit', 'editAccountDetails');
        });

        //createReminder

        Route::controller(PatientController::class)
                ->prefix('/reminders')
                ->middleware('auth:api')
                ->group(function(){

                Route::get('/all', 'getReminders');
                Route::get('/single/{id}', 'singleReminder');
                Route::post('/add', 'createReminder');
                Route::delete('/delete/{id}', 'deleteReminder');
        });

        Route::controller(PatientController::class)
                ->prefix('/referral')
                ->middleware('auth:api')
                ->group(function(){

                Route::get('/code', 'getReferralCode');
                Route::get('/all', 'getAllReferrals');
        });

        Route::controller(PatientController::class)
                ->prefix('/prescriptions')
                ->middleware('auth:api')
                ->group(function(){

                Route::get('/all', 'getAllPrescriptions');
                Route::get('/single/{id}', 'singlePrescription');
                Route::post('/add', 'createPrescription');
        });

        Route::controller(PatientController::class)
                ->prefix('/reviews')
                ->middleware('auth:api')
                ->group(function(){

                Route::get('/all', 'allReviews');
                Route::post('/add', 'createReview');
        });

        //coupons

        Route::controller(PatientController::class)
                ->prefix('/coupons')
                ->middleware('auth:api')
                ->group(function(){

                Route::get('/all', 'allCoupons');
                Route::post('/add', 'createCoupon');
                Route::delete('/delete/{id}', 'deleteCoupon');
        });

        //favorites

        Route::controller(PatientController::class)
                ->prefix('/favorites')
                ->middleware('auth:api')
                ->group(function(){

                Route::get('/all', 'allDoctors');
                Route::get('/view/{id}', 'singleDoctor');
        });

        Route::controller(PatientController::class)
                ->prefix('/vitals')
                ->middleware('auth:api')
                ->group(function(){

                Route::post('/add-blood-pressure', 'bloodPressure');
                Route::post('/add-blood-sugar', 'bloodSugar');
                Route::post('/add-cholesterol', 'cholesterol');
                Route::post('/add-weight', 'weight');
        });

        Route::post('/logout', 'logout');

        
});

