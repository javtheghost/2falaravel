<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\SmsController;
use App\Http\Controllers\Auth\VeryfiController;

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



Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return view('home');
    })->name('home');

    // Route::get('/phone', [SmsController::class, 'create'])->name('auth.phone');
    // Route::post('/phone', [SmsController::class, 'store'])->name('auth.store');

    // Route::get('/verification', [VeryfiController::class, 'create'])->name('auth.verification');
    // Route::post('/verification', [VeryfiController::class, 'store'])->name('auth.storeve');

    Route::get('/logout', [LoginController::class, 'logout'])->name('login.logout');
});



// Route::get('/confirm-code', [VeryfiController::class, 'confirm']);


Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register.index');
    Route::post('/register', [RegisterController::class, 'register'])->name('register.store');

    Route::get('/login', [LoginController::class, 'create'])->name('login.index');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');

    Route::get('/phone', [SmsController::class, 'create'])->name('auth.phone');
    Route::post('/phone', [SmsController::class, 'store'])->name('auth.store');
    Route::get('/verification', [VeryfiController::class, 'create'])->name('auth.verification') ;

  
    Route::post('/verification', [VeryfiController::class, 'store'])->name('auth.storeve');
});
