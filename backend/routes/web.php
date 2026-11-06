<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Hệ thống Quản lý Hoạt động Hội đồng trường STU
|--------------------------------------------------------------------------
|
| Kiến trúc Laravel Blade MVC theo phong cách Modern Enterprise Dashboard.
|
*/

// --- XÁC THỰC NGƯỜI DÙNG (AUTHENTICATION) ---
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// --- BẢNG ĐIỀU HÀNH TỔNG QUAN (DASHBOARD) ---
Route::get('/', [DashboardController::class, 'index'])->name('home');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// --- THƯ VIỆN DESIGN SYSTEM & BLADE COMPONENTS SHOWCASE ---
Route::get('/design-system', function () {
    return view('pages.design-system-showcase');
})->name('design-system');
