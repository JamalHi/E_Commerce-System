<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Routing\ResponseFactory;
use App\Http\Controllers\company_controller;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\others_controller;
use App\Http\Controllers\market_controller;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Middleware;
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

Route::get('/', function () {
    return view('welcome');
});

Route::post('add_credit',[AdminController::class,'admin_add_credit_view']);
Route::view('add_credit','add_credit');


