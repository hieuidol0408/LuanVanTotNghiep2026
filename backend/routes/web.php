<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Hệ thống Quản lý Hoạt động Hội đồng trường STU
|--------------------------------------------------------------------------
|
| Định tuyến giao diện Laravel Blade MVC theo chuẩn Modern Enterprise Dashboard.
|
*/

// Trang Design System & Showcase các Blade Components dùng chung (Giai đoạn 1)
Route::get('/', function () {
    return view('pages.design-system-showcase');
})->name('home');

Route::get('/design-system', function () {
    return view('pages.design-system-showcase');
})->name('design-system');

Route::get('/dashboard', function () {
    return view('pages.design-system-showcase');
})->name('dashboard');
