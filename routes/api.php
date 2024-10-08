<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\ProductCategoryController;
use App\Http\Controllers\API\TransactionController;
use App\Http\Controllers\API\UserController;

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


Route::get('/products', [ProductController::class,'all']);
Route::get('/categories', [ProductCategoryController::class,'all']);
Route::post('/register',[UserController::class,'register']);
Route::post('/login',[UserController::class,'login']);

Route::middleware('auth:sanctum')->group(function(){
    Route::get('/user',[UserController::class,'fetch']);
    Route::post('/update',[UserController::class,'updateProfil']);
    Route::post('/logout',[UserController::class,'logout']);
    Route::get('/transactions',[TransactionController::class,'all']);
    Route::get('/checkout',[TransactionController::class,'checkout']);
});