<?php


use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientIRSProfileController;
use App\Http\Controllers\HelpController;
use App\Http\Controllers\IRSController;
use App\Http\Controllers\PDController;
use App\Http\Controllers\QuestionController;
use Illuminate\Support\Facades\Route;


Route::get('test', [HelpController::class, 'test']);
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout']);


Route::group(['middleware' => 'auth:api',], function () {

    // ************************************* Help Routes ******************************
    Route::group(['prefix' => 'help'], function () {
        Route::get('fetch-predefined', [HelpController::class, 'fetchPredefined']);
        Route::post('upload-attachments', [HelpController::class, 'uploadAttachments']);
        Route::get('delete-attachments', [HelpController::class, 'deleteAttachments']);
    });

    // ************************************* Client Routes ******************************
    Route::group(['prefix' => 'clients'], function () {
        Route::get('cif/{cif}', [ClientController::class, 'showByCif']);
    });
    Route::resource('clients', ClientController::class);

    // ************************************* IRS Routes ******************************

    Route::resource('irs/client-profile', ClientIRSProfileController::class)->except('index','update');

    Route::resource('irs', IRSController::class)->except('update');

    // ************************************* Question Routes ******************************
    Route::resource('questions', QuestionController::class);

    // ************************************* PD Routes ******************************
    Route::group(['prefix' => 'pd'], function () {
        Route::get('class-type-years', [PDController::class, 'classTypeYears']);
    });
    Route::resource('pd', PDController::class);

});



