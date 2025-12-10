<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return view('welcome');
});
// REGISTER
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');

// LOGIN
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

// LOGOUT
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
// HOME / DASHBOARD (only after login)
Route::get('/', [AuthController::class, 'dashboard'])
     ->middleware('auth')
     ->name('dashboard');
