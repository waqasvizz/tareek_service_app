<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\BidController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\StorageAssetsController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SupportController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\FCM_TokenController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\WeekDayController;
use App\Http\Controllers\Api\UserWeekDayController;
use App\Http\Controllers\Api\UserController;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


// Route::post('authorize', [RegisterController::class, 'authorizeUser']);
Route::post('login', [RegisterController::class, 'loginUser']);
Route::post('register', [RegisterController::class, 'registerUser']);
Route::post('forgot_password', [RegisterController::class, 'forgotPassword']);

Route::middleware('auth:api')->group( function () {
    
    Route::get('get_profile', [RegisterController::class, 'getProfile']);
    Route::post('logout', [RegisterController::class, 'logoutUser']);

    // Route::post('bids/{id}', [BidController::class, 'update']);
    // Route::post('posts/{id}', [PostController::class, 'update']);
    Route::post('supports/{id}', [SupportController::class, 'update']);
    Route::post('services/{id}', [ServiceController::class, 'update']);
    Route::post('categories/{id}', [CategoryController::class, 'update']);
    Route::post('products/{id}', [ProductController::class, 'update']);

    //resouce routes
    Route::resource('users', UserController::class);
    Route::resource('user_week_days', UserWeekDayController::class);
    Route::resource('week_days', WeekDayController::class);
    Route::resource('supports', SupportController::class);
    Route::resource('services', ServiceController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('products', ProductController::class);
    // Route::resource('bids', BidController::class);
    // Route::resource('posts', PostController::class);
    // Route::resource('storage_assets', StorageAssetsController::class);

    Route::resource('chats', ChatController::class);
    Route::resource('fcm_tokens', FCM_TokenController::class);
    Route::resource('notifications', NotificationController::class);
});
// Route::resource('services', ServiceController::class);