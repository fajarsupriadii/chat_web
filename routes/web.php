<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', 'DashboardController@index');
Route::post('/dashboard/create-chat-contact', 'DashboardController@createChatContact');
Route::get('/dashboard/create-chat-room', 'DashboardController@createChatRoom');
Route::post('/dashboard/send-message', 'DashboardController@sendMessage');
Route::get('/dashboard/get-chat-history', 'DashboardController@getChatHistory');
Route::post('/dashboard/close-room', 'DashboardController@closeRoom');
Route::post('/dashboard/upload-file-chat', 'DashboardController@uploadFileChat');
