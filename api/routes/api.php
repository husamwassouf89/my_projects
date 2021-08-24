<?php


use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\HelpController;
use App\Http\Controllers\IRSController;
use App\Http\Controllers\PDController;
use App\Http\Controllers\QuestionController;
use Illuminate\Support\Facades\Route;


Route::get('test', [HelpController::class, 'test']);
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout']);


Route::group(['middleware' => 'auth:api',], function () {

    // ************************************* Users Routes ******************************
//    Route::group(['prefix' => 'users'], function () {
//        Route::get('', [UserController::class, 'index']);
//        Route::post('store', [UserController::class, 'store']);
//        Route::get('show', [UserController::class, 'show']);
//        Route::get('edit', [UserController::class, 'edit']);
//        Route::post('update', [UserController::class, 'update']);
//        Route::delete('delete', [UserController::class, 'delete']);
//        Route::get('fetch-user-info', [UserController::class, 'fetchUserInfo']);
//        Route::post('update-user-info', [UserController::class, 'updateUserInfo']);
//    });
//
//
//    // ************************************* Roles Routes ******************************
//    Route::group(['prefix' => 'roles'], function () {
//        Route::get('', [RoleController::class, 'index']);
//        Route::post('store', [RoleController::class, 'store']);
//        Route::get('show', [RoleController::class, 'show']);
//        Route::post('update', [RoleController::class, 'update']);
//        Route::delete('delete', [RoleController::class, 'delete']);
//        Route::get('permissions', [RoleController::class, 'permissions']);
//    });

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
    Route::resource('irs', IRSController::class)->except('update');

    // ************************************* Question Routes ******************************
    Route::resource('questions', QuestionController::class);


    // ************************************* PD Routes ******************************
    Route::group(['prefix' => 'pd'], function () {
        Route::get('class-type-years', [PDController::class, 'classTypeYears']);
    });
    Route::resource('pd', PDController::class);


});



