<?php

use Illuminate\Support\Facades\Route;
use App\Thinkific\ThinkificController;

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



Route::get("/install", [ThinkificController::class, 'install'])->name("install");
Route::get("/pages", [ThinkificController::class, 'authourizedPage'])->name("authourizedPage");
Route::get("/oauth/callback", [ThinkificController::class, 'callback'])->name("thinkific.oauth.callback");
Route::get("/support", [ThinkificController::class, 'support'])->name('support');
Route::post("/authorize/thinkific", [ThinkificController::class, 'startOauthFlow'])->name('startOauthFlow');
Route::get("/courses", [ThinkificController::class, 'courses'])->name('courses');
Route::post("/courses", [ThinkificController::class, 'enroll'])->name('enroll');
Route::post("/enroll/deactivate", [ThinkificController::class, 'deactivateEnrollment'])->name('enroll.deactivate');
Route::get("/view/webhooks", [ThinkificController::class, 'listRecievedWebhooks'])->name('webhooks.view');
Route::get("/view/register/webhooks", [ThinkificController::class, 'listRegisteredWebhooks'])->name('webhooks.registered.view');
Route::get("/register/webhooks", [ThinkificController::class, 'viewRegisterAppWebhook'])->name('webhooks.register.view');
Route::post("/register/webhooks", [ThinkificController::class, 'registerAppWebhook'])->name('webhooks.register');
Route::post("/remove/webhooks", [ThinkificController::class, 'deleteWebhooks'])->name('webhooks.delete');
Route::get("/webhooks", [ThinkificController::class, 'webhooks'])->name('webhooks.get');
Route::get("/orders", [ThinkificController::class, 'listOrders'])->name('order');
Route::post("/refund", [ThinkificController::class, 'refund'])->name('refund');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('landing');
