<?php

use App\Http\Controllers\api\Admin\AdminHomeController;
use App\Http\Controllers\api\Admin\DiscountsController;
use App\Http\Controllers\api\Admin\OrdersController;
use App\Http\Controllers\api\Admin\PairsController as AdminPairsController;
use App\Http\Controllers\api\Admin\PaymentsController;
use App\Http\Controllers\api\Admin\PlansController;
use App\Http\Controllers\api\Admin\TicketsController as AdminTicketsController;
use App\Http\Controllers\api\Admin\UsersController;
use App\Http\Controllers\api\app\AccountsController;
use App\Http\Controllers\api\app\HomeController;
use App\Http\Controllers\api\app\NotesController;
use App\Http\Controllers\api\app\NotificationsController;
use App\Http\Controllers\api\app\PairsController;
use App\Http\Controllers\api\app\TicketsController;
use App\Http\Controllers\api\app\TradesController;
use App\Http\Controllers\api\app\UserController;
use App\Http\Controllers\api\auth\AuthController;
use App\Http\Middleware\idAdmin;
use App\Http\Middleware\isActive;
use App\Models\Account;
use App\Services\StopLossAndTakeProfitCalculator;
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


    //home
    Route::get('home', [AdminHomeController::class, 'index']);

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

    //payments
    Route::prefix('payments')->group(function () {
        Route::get('/', [PaymentsController::class, 'index']);
        Route::post('/store', [PaymentsController::class, 'store']);
        Route::put('{payment}/update', [PaymentsController::class, 'update']);
        Route::get('{payment}/destroy', [PaymentsController::class, 'destroy']);
    });

    //orders
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrdersController::class, 'index']);
        Route::post('/store', [OrdersController::class, 'store']);
        Route::put('{order}/update', [OrdersController::class, 'update']);
        Route::get('{order}/destroy', [OrdersController::class, 'destroy']);
    });

    //pairs
    Route::prefix('pairs')->group(function () {
        Route::get('/', [AdminPairsController::class, 'index']);
        Route::post('/store', [AdminPairsController::class, 'store']);
        Route::put('{pair}/update', [AdminPairsController::class, 'update']);
        Route::get('{pair}/destroy', [AdminPairsController::class, 'destroy']);
    });

    //discounts
    Route::prefix('discounts')->group(function () {
        Route::get('/', [DiscountsController::class, 'index']);
        Route::post('/store', [DiscountsController::class, 'store']);
        Route::put('{discount}/update', [DiscountsController::class, 'update']);
        Route::get('{discount}/destroy', [DiscountsController::class, 'destroy']);
    });

    //tickets
    Route::prefix('tickets')->group(function () {
        Route::get('/', [AdminTicketsController::class, 'index']);
        Route::post('{ticket}/answer', [AdminTicketsController::class, 'answer']);
        Route::get('{ticket}/show', [AdminTicketsController::class, 'show']);
        Route::put('{ticket}/update', [AdminTicketsController::class, 'update']);
        Route::get('{ticket}/destroy', [AdminTicketsController::class, 'destroy']);
        Route::get('{ticket}/change-status', [AdminTicketsController::class, 'changeStatus']);
    });
});


// app private routes

Route::prefix('panel')->middleware(['auth:sanctum', isActive::class])->group(function () {

    //home
    Route::prefix('home')->group(function () {
        Route::get('/', [HomeController::class, 'index']);
    });


    //notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationsController::class, 'index']);
        Route::get('{notification}/seen', [NotificationsController::class, 'seen']);
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
        Route::get('{account}/update-stop-loss-and-take-profit', [AccountsController::class, 'UpdateStopLossAndTakeProfit']);


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

    //tickets
    Route::prefix('tickets')->group(function () {
        Route::get('/', [TicketsController::class, 'index']);
        Route::post('/store', [TicketsController::class, 'store']);
        Route::post('{ticket}/answer', [TicketsController::class, 'answer']);
        Route::get('{ticket}/show', [TicketsController::class, 'show']);
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

// Route::get('/{account}/calculate-sl-tp', function (Request $request, Account $account) {

//     $calculator = new StopLossAndTakeProfitCalculator($account);
//     $data = $calculator->calculate();

//     return response([
//         'data' => $data
//     ], 200);
// });
