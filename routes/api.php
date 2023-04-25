<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use Laravel\Passport\Passport;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:api')->group(function () {
    Route::get('/getuser/{id}',[UserController::class, 'getUser']);
    Route::get('/list',[ProductController::class, 'list']);
    
});


Route::post('/register',[UserController::class,'register']);
Route::post('/login',[UserController::class,'login']);


Route::delete('/delete/{id}',[ProductController::class, 'delete']);
Route::get('/product/{id}',[ProductController::class, 'getProduct']);
/**
 * put is used to update all the columsn of a row
 * patch is used to update single column of a row
 */
Route::put('/updateproduct/{id}',[ProductController::class, 'updateProduct']);
Route::get('/getusers',[UserController::class,'getAllusers']);
Route::delete('/deleteUser/{id}',[UserController::class, 'deleteUser']);
Route::patch('change-password/{id}',[UserController::class, 'changeUserPassword']);