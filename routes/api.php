<?php

use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/register', [UserController::class, 'register']);
Route::get('/getUser/{id}',[UserController::class,'getUser']);
Route::middleware(['auth:api'])->group(function () {

    Route::middleware(['role:admin'])->group(function () {
        Route::get('/profile/{id}',[UserController::class,'getProfile']);
    });
});
