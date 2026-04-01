<?php

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

Route::group([
    'namespace' => 'App\Http\Controllers\Api',
//    'middleware' => ['logs3.crud']
], function () {

    Route::get('sendNondeliveryDataToTimeSheet', 'CourseController@sendNondeliveryDataToTimeSheet');
    Route::post('register-account', 'AuthController@RegisterAccount');
    Route::post('register-password', 'AuthController@RegisterPassword');
    Route::post('/send-unit', 'DataController@orcAiUnit');
    Route::post('/sync/vehicle-maintenance-cost', 'VehicleMaintenanceCostController@syncVehicleMaintenanceCost');
    Route::post('/vehicle-maintenance-cost/upload-file', 'VehicleMaintenanceCostController@uploadFile');
    Route::group(['prefix' => 'auth'], function () {
        Route::post('login', 'AuthController@login');
        Route::post('mobile/login', 'AuthController@mobileLogin');
        Route::post('register', 'AuthController@register');
    });

    Route::get('/pl-service/time_sheet_index', 'PLServiceController@getDataTimeSheetIndex');
    Route::get('/pl-service/welfare_expenses_for_cloud', 'PLServiceController@totalWelfareExpenses');
    Route::get('/pl-service/vehicle-mahoujin-data', "PLServiceController@getMahoujin");
    Route::get('/pl-service/maintenance-cost', "PLServiceController@getMaintenanceCost");
    Route::get('/pl-service/vehicle-for-pl', "PLServiceController@getVehicleForPL");
    Route::get('/pl-service/vehicle-itp-for-pl', "PLServiceController@getVehicleItpForCloud");

    Route::get('driver-recorder/download/{id}', "DriverRecorderController@download");
    Route::post('/data_connection/update-status-connection', 'DataConnectionController@updateStatusConnection');
    Route::get('/data_connection/exec-queue-by-pl', 'DataConnectionController@execQueueByPl');
    Route::get('/pl-service/pl-pca', 'PLServiceController@getDataPCAForPl');
    Route::get('department/list-all', 'DepartmentController@listAll');
    // Issue #810: 例外通知者マスタ（for-notification は izumi-maintenance から呼ぶため認証なし）
    Route::get('inspection-notification-recipients/for-notification', 'InspectionNotificationRecipientController@forNotification');
    Route::get('line-works-list-pic', 'UserController@userPicLw');
    Route::put('driver-play-list/update-position-for-user', "DriverPlayListController@updatePositionForUser");
    Route::get('movies/noti', 'MoviesController@updateSendNoti');
    Route::post('/lineworks/webhook', 'LineWorksController@webhook');

    // driver recode deface
    Route::post('driver-recorder/save-process', "DriverRecorderController@handleSaveProcess");
    Route::post('driver-recorder/save-file-deface/{id}', "DriverRecorderController@handleSaveDeface");

    Route::group(['middleware' => 'auth:api'], function () {
        Route::group(['prefix' => 'auth'], function () {
            Route::post('refresh', 'AuthController@refresh');
        });
        Route::get('profile', 'AuthController@getProfile');

        // Issue #810: 例外通知者マスタ（認証あり）
        Route::get('inspection-notification-recipients/candidates', 'InspectionNotificationRecipientController@candidates');
        Route::get('inspection-notification-recipients', 'InspectionNotificationRecipientController@index');
        Route::put('inspection-notification-recipients', 'InspectionNotificationRecipientController@store');

        Route::group(['prefix' => 'user'], function () {
            Route::post('language', 'UserController@updateLanguage');
        });
        //        Route::put('Update', 'UserController@updateuser');
        //        Route::put('change', 'UserController@changePass');
        Route::get('send-mail-set-up-password', 'UserController@sendMailSetUpPassword');
        Route::get('user-interview-pic', 'UserController@userInterviewPic');
        Route::get('profile-informations', 'UserController@getProfileInfor');
        Route::post('profile-informations', 'UserController@storeProfileInfor');
        Route::get('user/department/{id}', 'UserController@getUserWithDepartment');
        Route::apiResource('user', 'UserController');
        Route::apiResource('role', 'RoleController');
        Route::get('data_connection/exec-queue/{id}', 'DataConnectionController@execQueue');
        Route::apiResource('data_connection', 'DataConnectionController');

        Route::get('system', 'SystemController@index');
        Route::post('system', 'SystemController@store');
        Route::get('data/get-list-data-import', 'DataController@getListDataImport');
        Route::apiResource('data', "DataController");
        Route::post('upload', "UploadDataController@store");
        Route::get('download-file', "UploadDataController@download");

        //Route(Course) master data
        Route::get('store/list-all', 'StoreController@listAll');
        Route::post('update-store-for-web/{id}', 'StoreController@updateStore');
        Route::apiResource('store', "StoreController");
        Route::get('customer/list-all', 'CustomerController@listAll');
        Route::apiResource('customer', "CustomerController");
        Route::get('course/schedule', 'CourseController@index');
        Route::get('course/{id}', 'CourseController@show');
        Route::post('course', 'CourseController@store');
        Route::put('course/{id}', 'CourseController@update');
        Route::get('course/selected/list-all', 'CourseController@listAll');
        Route::delete('course/{id}', 'CourseController@destroy');
        Route::post('course/import-course-route', "CourseController@importCourseRoute");
        Route::post('route/update-many', "RouteController@updateMany");
        Route::post('route/import', "RouteController@import");
        Route::apiResource('route', "RouteController");
//        Route::get('department/list-all', 'DepartmentController@listAll');
        Route::post('department/change-order', 'DepartmentController@changeOrder');
        Route::get('department/export', 'DepartmentController@exportCsv');
        Route::apiResource('department', 'DepartmentController');
        Route::post('sync-course', 'CourseController@sendCourseDataToTimeSheet');
        //employee
        Route::post('employee/upload-employee-pdf', "EmployeeController@uploadEmployeePdf");
        Route::delete('employee/delete-employee-pdf/{id}', "EmployeeController@deleteEmployeePdf");
        Route::get('employee/get-employee-by-department-id/{department_id}', "EmployeeController@getEmployeeByDepartmentId");
        Route::get('employee/all', "EmployeeController@all");
        Route::post('employee/upload-file', "EmployeeController@uploadFile");
        Route::post('employee/driver-license', "EmployeeController@addDriverLicense");
        Route::post('employee/driving-record-certificate', "EmployeeController@addDrivingRecordCertificate");
        Route::post('employee/aptitude-assessment-form', "EmployeeController@addAptitudeAssessmentForm");
        Route::post('employee/health-examination-results', "EmployeeController@addHealthExaminationResults");
        Route::delete('employee/delete-health-examination-file-history/{id}', "EmployeeController@deleteHealthExaminationFileHistory");
        Route::delete('employee/delete-aptitude-assessment-form/{id}', "EmployeeController@deleteAptitudeAssessmentForm");
        Route::delete('employee/delete-driving-record-certificate/{id}', "EmployeeController@deleteEmployeeDrivingRecordCertificates");
        Route::delete('employee/delete-driver-license/{id}', "EmployeeController@deleteDriverLicense");
        Route::get('employee/dp-working', "EmployeeController@departmentWorking");
        Route::get('employee/dp-working/list-course/{department_working_id}', "EmployeeController@listCourse");
        Route::get('employee/export-all', "EmployeeController@exportAll");
        Route::post('employee/import-detail', "EmployeeController@importDetail");
        Route::apiResource('employee', "EmployeeController");
        Route::put('employee/contents/{id}', "EmployeeController@contentEmployee");
        Route::apiResource('insurance-rate', 'InsuranceRateController');
        Route::get('insurance-rate/list/history', 'InsuranceRateController@listHistory');

        Route::get('vehicle/dashboard', 'VehicleController@dashboardVehicle');
        Route::post('vehicle/add-vehicle-style-show', 'VehicleController@addVehicleStyleShow');
        Route::get('vehicle/vehicle-style-show', 'VehicleController@getVehicleStyleShow');
        Route::get('vehicle/download', 'VehicleController@downloadVehicle');
        Route::get('vehicle/division', 'VehicleController@getDepartmentDivision');
        Route::apiResource("vehicle", "VehicleController");

        Route::group(['prefix' => 'mobile'], function () {
            Route::get('store/{id}', "StoreController@storeDetailFromMobileApp");
            Route::post('store/{id}', "StoreController@storeEditFromMobileApp");
            Route::get('store/image/{image_type}/{id}', "StoreController@storeImage");
            Route::get('store/list/{course}', 'StoreController@listStoreInCourse');
            Route::get('course/list-course-by-department', 'CourseController@listAllCourse');
            Route::get('store-with-department/list', 'StoreController@listStoreFromDepartment');
            Route::get('driver-recorder', "DriverRecorderController@indexMobile");
        });
        //driver recorder
        Route::put('driver-play-list/update-position', "DriverPlayListController@updatePosition");
        Route::get('driver-recorder/video-deface', "DriverRecorderController@getAllVideoDeface");
        Route::delete('driver-recorder/video-deface/{id}', "DriverRecorderController@deleteDefaceVideo");
        Route::get('driver-recorder/driver-play-list/{id}', "DriverRecorderController@getDriverPlayList");
        Route::post('driver-recorder/upload-file', "DriverRecorderController@uploadFile");
        Route::post('driver-recorder/add-or-update-play-list/{id}', "DriverRecorderController@addOrUpdatePlayList");
        Route::post('driver-recorder/upload-file-deface', "DriverRecorderController@handleDeface");
        Route::get('driver-recorder/video-deface/{id}', "DriverRecorderController@getDefaceVideo");
        Route::apiResource('driver-recorder', "DriverRecorderController");
        // Route::delete('driver-recorder/delete/{id}', "DriverRecorderController@destroy");
        Route::apiResource('driver-play-list', "DriverPlayListController");

        //movies
        Route::get('movies/all-watching-movie-list', "MoviesController@getAllWatchingMovieList");
        Route::get('movies/download-all-watching-movie', "MoviesController@downloadAllWatchingMovie");
        Route::get('movies/dowload-user-watching', "MoviesController@downloadUserWatchingMovie");
        Route::get('movies/show-user-watch-movie', "MoviesController@showUserWatchMovie");
        Route::get('movies/mobile/show-like', "MoviesController@showMovieLike");
        Route::get('movies/mobile/{id}', "MoviesController@showMovieMobileDetail");
        Route::put('movies/mobile/update-like', "MoviesController@updateMovieLike");
        Route::post('movies/mobile/like', "MoviesController@storeMovieLike");
        Route::post('movies/mobile/watching', "MoviesController@createMovieWatching");
        Route::get('movies/mobile/watching/{id}', "MoviesController@showUserWatchingMovieMobile");
        Route::get('movies/mobile', "MoviesController@showMovieMobile");
        Route::get('movies/schedule', "MoviesController@showMovieSchedules");
        Route::post('movies/upload-file', "MoviesController@uploadFile");
        Route::post('movies/store-movie-schedule', "MoviesController@storeMovieSchedules");
        Route::put('movies/delete-schedule', "MoviesController@deleteMovieSchedules");
        Route::put('movies/{id}/loop-enabled', "MoviesController@updateLoopEnabled");
        Route::put('movies/update-position', "MoviesController@updatePosition");

        Route::apiResource('movies', "MoviesController");
        Route::post('pocket-book/change-order', "PocketBooksController@changeOrder");
        Route::post('pocket-book/upload-file', "PocketBooksController@uploadFile");
        Route::get('pocket-book/option', "PocketBooksController@option");
        Route::apiResource('pocket-book', "PocketBooksController");
        Route::get('user-contacts/check-update-user-contact', "UserContactsController@checkUpdateUserContact");
        Route::get('user-contacts/user-contacts-profile', "UserContactsController@getUserContactsProfile");
        Route::get('user-contacts/download', "UserContactsController@download");
        Route::apiResource('user-contacts', "UserContactsController");

        // hotlines route
        Route::post('setting/channel-id', "HotlineController@storeChannelId");
        Route::get('hotline/channel-id', "HotlineController@getChanel");
        Route::apiResource('hotline', "HotlineController");

        // linework bot message
        Route::post('linework-bot-message/import', "LineworkBotMessageController@import");
        Route::apiResource('linework-bot-message', "LineworkBotMessageController");

        // employee pdf storage
        Route::post('employee-pdf-storage/driver-license', "EmployeePdfStorageController@addDriverLicense");
        Route::post('employee-pdf-storage/driving-record-certificate', "EmployeePdfStorageController@addDrivingRecordCertificate");
        Route::post('employee-pdf-storage/aptitude-assessment-form', "EmployeePdfStorageController@addAptitudeAssessmentForm");
        Route::post('employee-pdf-storage/health-examination-results', "EmployeePdfStorageController@addHealthExaminationResults");
        Route::apiResource('employee-pdf-storage', "EmployeePdfStorageController");

        // news letter
        Route::get('news-letter-mobile', "NewsLetterController@indexMobile");
        Route::post('news-letter/update-position', "NewsLetterController@updatePosition");
        Route::post('news-letter/upload-file', "NewsLetterController@uploadFile");
        Route::apiResource('news-letter', "NewsLetterController");
    });

    Route::group(['middleware' => 'auth.other'], function () {
        Route::get('shakensho-email', "ShakenshoEmailController@index");
        Route::post('shakensho-email', "ShakenshoEmailController@store");
    });

    Route::post('receive-vehicle-inspection-cert', 'UploadDataController@receiveVehicleInspectionCert');
    Route::post('receive-data-jinzi-bugyo', "UploadDataController@receiveDataJinziBugyo");
    Route::post('receive-data-mahojin', "UploadDataController@receiveDataMahojin");
    Route::post('receive-data-kyuyo-bugyo', "UploadDataController@receiveDataKyuyoBugyo");
    Route::post('receive-data-pca', "UploadDataController@receiveDataPCA");
    Route::post('receive-import-store', "UploadDataController@importStore");

    Route::apiResource('roles', "RoleController");
    //Route::get('permissions', 'RoleController@listPermission')->middleware(['auth:user']);
    Route::post('remind-passwords', 'AuthController@remindPassword');
    Route::put('change_pass/{emp_code}', 'AuthController@changePassword');
    //Route::post('store', 'UserController@store');
    //viewer driver-recorder

    Route::get('driver-recorder-viewer', "DriverRecorderController@indexViewer");
    Route::get('driver-recorder-viewer/{driver_recorder}', "DriverRecorderController@showViewer");

    Route::get('driver-recorder-play-list-viewer', "DriverPlayListController@indexViewer");
    Route::get('driver-recorder-play-list-viewer/{id}', "DriverPlayListController@showViewer");
    // Quotation Route AI Calculation
    Route::prefix('quotation')->group(function () {
        Route::post('routes/calculate', 'QuotationRouteController@calculate');
        Route::get('routes', 'QuotationRouteController@index');
        Route::get('routes/{id}', 'QuotationRouteController@show');
        Route::get('routes/{id}/ai-response', 'QuotationRouteController@downloadAIResponse');
    });

    Route::apiResource('quotations', "QuotationController");
    Route::get('quotation-master-data', "QuotationMasterDataController@index");
    Route::get('quotation-master-data/{tonnage}', "QuotationMasterDataController@show");
    Route::post('quotation-master-data/update', "QuotationMasterDataController@update");
    Route::apiResource('quotation-staff', "QuotationStaffController");
    Route::post('linework-bot-message/update', "LineworkBotMessageController@updateFromGoogleSheet");
});
