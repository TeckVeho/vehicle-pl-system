<?php

use App\Http\Controllers\SpaController;
use App\Http\Controllers\ViewFileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AutoTestController;

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

// Route::view('/{any}', 'home')
//     ->where('any', '.*');

// Google Auth Settings
Route::prefix('google-auth')->name('google-auth.')->group(function () {
    Route::get('/settings', [App\Http\Controllers\GoogleAuthSettingController::class, 'index'])->name('settings');
    Route::get('/authenticate', [App\Http\Controllers\GoogleAuthSettingController::class, 'authenticate'])->name('authenticate');
    Route::get('/callback', [App\Http\Controllers\GoogleAuthSettingController::class, 'callback'])->name('callback');
    Route::post('/logout', [App\Http\Controllers\GoogleAuthSettingController::class, 'logout'])->name('logout');
    Route::post('/test-connection', [App\Http\Controllers\GoogleAuthSettingController::class, 'testConnection'])->name('test-connection');
});

Route::get('/autotest',[AutoTestController::class, 'index']);
Route::get('/view-file/{id}/{filename}', [ViewFileController::class, 'index']);
Route::view('/{any}', 'spa')->where('any', '.*');
