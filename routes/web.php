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


Route::controller(ThinkificController::class)->group(function () {
    Route::get("/install", 'install')->name("install");
    Route::get("/pages", 'authourizedPage')->name("authourizedPage");
    Route::get("/oauth/callback", 'callback')->name("thinkific.oauth.callback");
    Route::get("/support", 'support')->name('support');
    Route::post("/authorize/thinkific", 'startOauthFlow')->name('startOauthFlow');
    Route::get("/courses", 'courses')->name('courses');
    Route::post("/courses", 'enroll')->name('enroll');
    Route::post("/enroll/deactivate", 'deactivateEnrollment')->name('enroll.deactivate');
    Route::get("/view/webhooks", 'listRecievedWebhooks')->name('webhooks.view');
    Route::get("/view/register/webhooks", 'listRegisteredWebhooks')->name('webhooks.registered.view');
    Route::get("/register/webhooks", 'viewRegisterAppWebhook')->name('webhooks.register.view');
    Route::post("/register/webhooks", 'registerAppWebhook')->name('webhooks.register');
    Route::post("/remove/webhooks", 'deleteWebhooks')->name('webhooks.delete');
    Route::get("/webhooks", 'webhooks')->name('webhooks.get');
    Route::get("/orders", 'listOrders')->name('order');
    Route::post("/refund", 'refund')->name('refund');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('landing');
