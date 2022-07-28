<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Controller;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MosqueController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\StorageAssetsController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\Api\PaymentTransactionController;
use RahulHaque\Filepond\Http\Controllers\FilepondController;



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

Route::get('/clear-cache', function() {
    Artisan::call('cache:clear');
    Artisan::call('optimize');
    Artisan::call('view:clear');
    Artisan::call('route:cache');
    Artisan::call('route:clear');
    Artisan::call('config:cache');
    return '<h1>Cache facade value cleared</h1>';
});

Route::get('/schedule-run', function() {
    Artisan::call("schedule:run");
    return '<h1>schedule run activated</h1>';
});

Route::get('/site-down', function() {
    Artisan::call('down --secret="harrypotter"');
    return '<h1>Application is now in maintenance mode.</h1>';
});

Route::get('/site-up', function() {
    Artisan::call('up');
    return '<h1>Application is now live..</h1>';
});

Route::get('/run-seeder', function() {
    Artisan::call("db:seed");
    return '<h1>Dummy data added successfully</h1>';
});

Route::get('/storage-link', function() {
    Artisan::call("storage:link");
    return '<h1>storage link activated</h1>';
});
    
Route::get('/queue-work', function() {
    Artisan::call("queue:work");
    return '<h1>queue work activated</h1>';
});
    
// Route::get('/migration-refresh', function() {
//     // Artisan::call("migrate:fresh");
//     Artisan::call('migrate:refresh');    
//     // Artisan::call('passport:install --force');    
//     Artisan::call('passport:install');

//     return '<h1>Migration refresh successfully</h1>';
// });
    
Route::get('/migration-refresh', function() {
    Artisan::call('migrate:refresh');
    return '<h1>Migration refresh successfully</h1>';
});


Route::get('/migration-fresh', function() {
    Artisan::call("migrate:fresh");
    return '<h1>Migration fresh successfully</h1>';
});
    
Route::get('/passport-install', function() {   
    Artisan::call('passport:install');
    return '<h1>Passport install successfully</h1>';
});

Route::get('/', function () {
    return view('welcome');
});
Route::get('/fcm', [Controller::class, 'firebase']);
Route::get('/send_notification', [Controller::class, 'sendNotification']);
Route::get('process_payments', [PaymentTransactionController::class, 'start_payment_transaction']);
Route::get('process_min_discounts', [Controller::class, 'calculate_orders_min_discounts']);
Route::get('process_max_discounts', [Controller::class, 'calculate_orders_max_discounts']);

// Auth::routes();

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Auth::routes();
Route::get('/test', [UserController::class, 'testing']);
Route::get('/payment-test', [UserController::class, 'benefit_testing']);
Route::get('/response', [UserController::class, 'response']);
Route::post('/error', [UserController::class, 'error']);
// Route::get('/', [UserController::class, 'welcome']);
// Route::get('/login', [UserController::class, 'login'])->name('login');
// Route::get('/logout', [UserController::class, 'logout']);
// // ->name('logout');

// Route::get('/register', [UserController::class, 'register'])->name('register');
// Route::get('/forgot-password', [UserController::class, 'forgotPassword'])->name('forgotPassword');
// Route::get('/reset-password', [UserController::class, 'resetPassword'])->name('resetPassword');

// Route::post('/accountRegister', [UserController::class, 'accountRegister'])->name('accountRegister');
// Route::post('/accountLogin', [UserController::class, 'accountLogin'])->name('accountLogin');
// Route::post('/resetPassword', [UserController::class, 'accountResetPassword'])->name('accountResetPassword');

// Route::middleware(['auth'])->group(function () {    
//     Route::get('/admin', [UserController::class, 'dashboard']);
//     Route::get('/live_chat', [UserController::class, 'liveChatSample']);
// //     Route::resource('service', ServiceController::class);
// //     Route::resource('role', RoleController::class);
// //     Route::resource('user', UserController::class);
// });