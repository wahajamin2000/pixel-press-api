<?php

use App\Http\Controllers\Api\V1\Modules\Payment\PaymentApiController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->away('https://pixelpressdtf.com/');
});


Route::get('/run-command', function () {
//    \Illuminate\Support\Facades\Artisan::call('db:seed');
//    \Illuminate\Support\Facades\Artisan::call('migrate');
    \Illuminate\Support\Facades\Artisan::call('storage:link');
//    \Illuminate\Support\Facades\Artisan::call('cache:clear');
//    \Illuminate\Support\Facades\Artisan::call('config:cache');
//    \Illuminate\Support\Facades\Artisan::call('view:clear');
//    \Illuminate\Support\Facades\Artisan::call('route:clear');
});

Auth::routes([
    'register' => false,
    'verify'   => false,
]);

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
