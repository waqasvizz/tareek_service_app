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
use App\Http\Controllers\Api\UserAssetsController;
use App\Http\Controllers\Api\AssetTypesController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductReviewController;
use App\Http\Controllers\Api\ServiceReviewController;
use App\Http\Controllers\Api\WeekDayController;
use App\Http\Controllers\Api\UserWeekDayController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserMultipleAddresseController;
use App\Http\Controllers\Api\UserCardController;
use App\Http\Controllers\Api\UserBankController;
use App\Http\Controllers\Api\UserDeliveryOptionController;
use App\Http\Controllers\Api\UserStripeInformationController;
use App\Http\Controllers\Api\PointCategorieController;
use App\Http\Controllers\Api\PromoController;
use App\Http\Controllers\Api\PaymentTransactionController;

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
Route::post('login', [RegisterController::class, 'loginUser'])->name('login');
Route::post('register', [RegisterController::class, 'registerUser']);
Route::post('forgot_password', [RegisterController::class, 'forgotPassword']);
Route::post('change_password', [RegisterController::class, 'changePassword']);
Route::get('/verify-email/{token?}', [RegisterController::class, 'verifyUserEmail'])->name('email_verify');

Route::middleware('auth:api')->group( function () {
    
    Route::post('read_chats', [ChatController::class, 'read_chats']);
    Route::get('get_profile', [RegisterController::class, 'getProfile']);
    Route::post('logout', [RegisterController::class, 'logoutUser']);
    Route::get('dashboards', [UserController::class, 'get_dashboards']);
    Route::get('revenue_stats', [UserController::class, 'get_revenue_stats']);

    // Route::post('bids/{id}', [BidController::class, 'update']);
    // Route::post('posts/{id}', [PostController::class, 'update']);
    Route::post('supports/{id}', [SupportController::class, 'update']);
    Route::post('services/{id}', [ServiceController::class, 'update']);
    Route::post('categories/{id}', [CategoryController::class, 'update']);
    Route::post('products/{id}', [ProductController::class, 'update']);
    Route::post('user_address/{id}', [UserMultipleAddresseController::class, 'update']);
    Route::post('user_cards/{id}', [UserCardController::class, 'update']);
    Route::post('user_banks/{id}', [UserBankController::class, 'update']);
    Route::post('user_delivery_option/{id}', [UserDeliveryOptionController::class, 'update']);
    Route::post('orders/{id}', [OrderController::class, 'update']);
    Route::post('users/{id}', [UserController::class, 'update']);
    Route::post('user_stripe_informations/{id}', [UserStripeInformationController::class, 'update']);
    Route::post('service_reviews/{id}', [ServiceReviewController::class, 'update']);
    Route::post('product_reviews/{id}', [ProductReviewController::class, 'update']);
    Route::post('point_categories/{id}', [PointCategorieController::class, 'update']);
    Route::post('user_assets/{id}', [UserAssetsController::class, 'update']);
    Route::post('assets_types/{id}', [AssetTypesController::class, 'update']);
    Route::post('promos/{id}', [PromoController::class, 'update']);
    Route::get('user_assets/get_request', [UserAssetsController::class, 'get_request']);
    Route::get('user_assets/request', [UserAssetsController::class, 'request']);
    Route::get('user_assets/approve', [UserAssetsController::class, 'approve']);
    Route::get('user_assets/request_update', [UserAssetsController::class, 'request_update']);
    Route::get('bulk_orders/get_details', [ProductController::class, 'get_details']);
    Route::post('bulk_orders/update_orders', [ProductController::class, 'update_bulk_orders']);

    Route::post('supplier_transactions/mark_paid', [OrderController::class, 'mark_paid']);
    Route::get('supplier_transactions/get_pending', [OrderController::class, 'get_pending']);
    Route::post('transactions/refund', [OrderController::class, 'refund_requests']);
    Route::post('transactions/refund_action', [OrderController::class, 'refund_process']);

    //resouce routes
    Route::resource('point_categories', PointCategorieController::class);
    Route::resource('product_reviews', ProductReviewController::class);
    Route::resource('service_reviews', ServiceReviewController::class);
    Route::resource('user_stripe_informations', UserStripeInformationController::class);
    Route::resource('orders', OrderController::class);
    Route::resource('user_delivery_option', UserDeliveryOptionController::class);
    Route::resource('user_cards', UserCardController::class);
    Route::resource('user_banks', UserBankController::class);
    Route::resource('user_address', UserMultipleAddresseController::class);
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
    Route::resource('user_assets', UserAssetsController::class);
    Route::resource('assets_types', AssetTypesController::class);
    Route::resource('notifications', NotificationController::class);
    Route::resource('promos', PromoController::class);
});
// Route::resource('services', ServiceController::class);