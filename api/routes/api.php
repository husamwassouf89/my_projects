<?php


use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientIRSProfileController;
use App\Http\Controllers\ClientStagingProfileController;
use App\Http\Controllers\HelpController;
use App\Http\Controllers\IRSController;
use App\Http\Controllers\LimitController;
use App\Http\Controllers\PDController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StagingController;
use Illuminate\Support\Facades\Route;


Route::get('test', [HelpController::class, 'test']);
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout']);
Route::get('cc', [HelpController::class, 'clearCache']);


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
        Route::post('change-financial-status', [ClientController::class, 'changeFinancialStatus']);
        Route::post('set-grade/{id}/{gradeId}', [ClientController::class, 'setGrade']);
        Route::post('set-stage/{id}/{gradeId}', [ClientController::class, 'setStage']);
        Route::get('get-class-type-grades/{id}', [ClientController::class, 'getClassTypeGrades']);
        Route::post('add-attachments/{id}', [ClientController::class, 'addAttachments']);
        Route::get('irs', [ClientController::class, 'indexIRS']);
        Route::get('stage', [ClientController::class, 'indexStage']);
    });
    Route::resource('clients', ClientController::class);

    // ************************************* IRS Routes ******************************
    Route::group(['prefix' => 'irs'], function () {
        Route::get('class-type-percentage', [IRSController::class, 'classTypePercentage']);
        Route::get('show', [IRSController::class, 'show']);
        Route::get('client-profile/all/{id}', [ClientIRSProfileController::class, 'index']);
    });
    Route::resource('irs/client-profile', ClientIRSProfileController::class)->except('update');


    Route::resource('irs', IRSController::class)->except('update', 'show');

    // ************************************* IRS Question Routes ******************************
    Route::resource('irs/questions', QuestionController::class);

    // ************************************* Staging Routes ******************************

    Route::group(['prefix' => 'staging'], function () {
        Route::group(['prefix' => 'questions'], function () {
            Route::post('', [StagingController::class, 'store']);
            Route::put('{id}', [StagingController::class, 'update']);
            Route::get('{id}', [StagingController::class, 'show']);
            Route::delete('{id}', [StagingController::class, 'destroy']);
        });
        Route::group(['prefix' => 'client-profile'], function () {
            Route::get('all/{id}', [ClientStagingProfileController::class, 'index']);
            Route::post('', [ClientStagingProfileController::class, 'store']);
            Route::get('{id}', [ClientStagingProfileController::class, 'show']);
            Route::delete('{id}', [ClientStagingProfileController::class, 'destroy']);
        });
        Route::get('{id}', [StagingController::class, 'index']);
    });

    // ************************************* PD Routes ******************************
    Route::group(['prefix' => 'pd'], function () {
        Route::get('class-type-years', [PDController::class, 'classTypeYears']);
        Route::get('inserted-years', [PDController::class, 'insertedYears']);
        Route::get('show-raw/{id}', [PDController::class, 'showRaw']);
    });
    Route::resource('pd', PDController::class);

    // ************************************* Limits Routes ******************************

    Route::group(['prefix' => 'limits'], function () {
        Route::post('import', [LimitController::class, 'import']);;
        Route::get('show', [LimitController::class, 'show']);;
    });


    // ************************************* Limits Routes ******************************

    Route::group(['prefix' => 'settings'], function () {
        Route::get('show-predefined', [SettingController::class, 'showPredefined']);
        Route::get('show-guarantee-lgd', [SettingController::class, 'showGuaranteeLGD']);
        Route::post('update-predefined/{id}', [SettingController::class, 'updatePredefined']);
        Route::post('update-guarantee-lgd/{id}', [SettingController::class, 'updateGuaranteeLGD']);
        Route::get('show-document-types', [SettingController::class, 'showDocumentTypes']);
        Route::post('update-document-type/{id}', [SettingController::class, 'updateDocumentType']);
        Route::post('toggle-period-lock/{quarter}/{year}', [SettingController::class, 'togglePeriodLock']);
        Route::get('get-locked', [SettingController::class, 'getLocked']);
        Route::post('update-value/{id}/{value}', [SettingController::class, 'updateValue']);
        Route::get('get-values', [SettingController::class, 'getValues']);
    });


});

// ************************************* Report Routes ******************************
Route::group(['prefix' => 'reports'], function () {
    Route::get('cif', [ReportController::class, 'cif']);
    Route::get('cif-guarantee', [ReportController::class, 'cifGuarantee']);
    Route::get('disclosure', [ReportController::class, 'disclosure']);
    Route::get('ead-guarantee', [ReportController::class, 'eadGuarantee']);
    Route::get('ecl', [ReportController::class, 'ecl']);
    Route::get('facility-disclosure', [ReportController::class, 'facilityDisclosure']);
});
