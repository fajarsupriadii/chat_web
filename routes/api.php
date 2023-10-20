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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['cors'])->group(function () {
    Route::get('rocketchat-routing', 'Api\RocketChatController@rocketchatRouting');

    Route::group(['prefix' => 'chatbot'], function () {
        Route::get('check-cid', 'Api\ChatbotController@checkCid');
        Route::get('current-billing', 'Api\ChatbotController@currentBilling');
        Route::get('billing-history', 'Api\ChatbotController@billingHistory');
        Route::get('technical-status', 'Api\ChatbotController@technicalStatus');
        Route::post('create-so', 'Api\ChatbotController@createSo');
        Route::get('package-category', 'Api\ChatbotController@packageCategory');
        Route::get('package-speed', 'Api\ChatbotController@packageSpeed');
        Route::post('upgrade-request', 'Api\ChatbotController@upgradeRequest');
        Route::get('package', 'Api\ChatbotController@package');
        Route::post('new-connect', 'Api\ChatbotController@newConnect');
    });
});