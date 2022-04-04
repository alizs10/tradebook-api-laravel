<?php

use App\Http\Controllers\api\Admin\PlansController;
use App\Http\Controllers\api\Admin\UsersController;
use App\Http\Controllers\api\app\AccountsController;
use App\Http\Controllers\api\app\HomeController;
use App\Http\Controllers\api\app\NotesController;
use App\Http\Controllers\api\app\PairsController;
use App\Http\Controllers\api\app\TradesController;
use App\Http\Controllers\api\app\UserController;
use App\Http\Controllers\api\auth\AuthController;
use App\Http\Middleware\idAdmin;
use App\Http\Middleware\isActive;
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

// Authentications

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);


// admin routes

Route::prefix('admin')->middleware(['auth:sanctum', idAdmin::class])->group(function () {

    //plans
    Route::prefix('plans')->group(function () {
        Route::get('/', [PlansController::class, 'index']);
        Route::post('/store', [PlansController::class, 'store']);
        Route::put('{plan}/update', [PlansController::class, 'update']);
        Route::get('{plan}/destroy', [PlansController::class, 'destroy']);
    });


    //users
    Route::prefix('users')->group(function () {
        Route::get('/', [UsersController::class, 'index']);
        Route::put('{user}/update', [UsersController::class, 'update']);
        Route::get('{user}/destroy', [UsersController::class, 'destroy']);
    });

});


// app private routes

Route::prefix('panel')->middleware(['auth:sanctum', isActive::class])->group(function () {

    //home
    Route::prefix('home')->group(function () {
        Route::get('/', [HomeController::class, 'index']);
    });


    //profile
    Route::prefix('profile')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::put('/update', [UserController::class, 'update']);
        Route::get('/send-verification-code', [UserController::class, 'sendVerificationCode'])->middleware("throttle:1, 2");
        Route::post('/check-verification-code', [UserController::class, 'checkVerificationCode']);
        Route::post('/reset-password', [UserController::class, 'resetPassword']);
        Route::get('/forgot-password', [UserController::class, 'forgotPassword'])->middleware("throttle:1, 2");
    });

    //accounts
    Route::prefix('accounts')->group(function () {
        Route::get('/', [AccountsController::class, 'index']);
        Route::post('/store', [AccountsController::class, 'store']);
        Route::get('{account}/show', [AccountsController::class, 'show']);
        Route::put('{account}/update', [AccountsController::class, 'update']);
        Route::get('{account}/destroy', [AccountsController::class, 'destroy']);


        //trades
        Route::get('{account}/trades', [TradesController::class, 'index']);
        Route::post('{account}/trades/store', [TradesController::class, 'store']);
        Route::put('{account}/trades/{trade}/update', [TradesController::class, 'update']);
        Route::get('{account}/trades/{trade}/destroy', [TradesController::class, 'destroy']);
        Route::put('{account}/trades/update-open-trades-price', [TradesController::class, 'updateOpenTradesPrice']);
    });

    //notes
    Route::prefix('notes')->group(function () {

        Route::get('/', [NotesController::class, 'index']);
        Route::post('/store', [NotesController::class, 'store']);
        Route::put('/{note}/update', [NotesController::class, 'update']);
        Route::get('/{note}/destroy', [NotesController::class, 'destroy']);
    });
});


//app public routes

Route::prefix('panel')->group(function () {

    //pairs
    Route::prefix('pairs')->group(function () {
        Route::get('/crypto', [PairsController::class, 'cryptoPairs']);
        Route::get('/forex', [PairsController::class, 'forexPairs']);
    });
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return response([
        'status' => true,
        'user' => $request->user()
    ], 200);
});