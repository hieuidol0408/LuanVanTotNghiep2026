<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\MeetingWebController;
use App\Http\Controllers\Web\SubmissionWebController;
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

// --- PHÂN HỆ QUẢN LÝ PHIÊN HỌP (MEETINGS) ---
Route::prefix('meetings')->name('meetings.')->group(function () {
    Route::get('/', [MeetingWebController::class, 'index'])->name('index');
    Route::get('/create', [MeetingWebController::class, 'create'])->name('create');
    Route::post('/', [MeetingWebController::class, 'store'])->name('store');
    Route::get('/{id}', [MeetingWebController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [MeetingWebController::class, 'edit'])->name('edit');
    Route::put('/{id}', [MeetingWebController::class, 'update'])->name('update');

    // Thao tác nghiệp vụ phiên họp (7 Tabs & BR-02, BR-03, BR-05, BR-08)
    Route::post('/{id}/members', [MeetingWebController::class, 'addMember'])->name('members.add');
    Route::post('/{id}/attendance', [MeetingWebController::class, 'updateAttendance'])->name('attendance');
    Route::post('/{id}/guests', [MeetingWebController::class, 'addGuest'])->name('guests.add');
    Route::post('/{id}/agenda', [MeetingWebController::class, 'addAgendaItem'])->name('agenda.add');
    Route::post('/{id}/documents', [MeetingWebController::class, 'uploadDocument'])->name('documents.upload');
});

// --- PHÂN HỆ QUẢN LÝ HỒ SƠ TRÌNH (SUBMISSIONS) ---
Route::prefix('submissions')->name('submissions.')->group(function () {
    Route::get('/', [SubmissionWebController::class, 'index'])->name('index');
    Route::get('/create', [SubmissionWebController::class, 'create'])->name('create');
    Route::post('/', [SubmissionWebController::class, 'store'])->name('store');
    Route::get('/{id}', [SubmissionWebController::class, 'show'])->name('show');
    Route::post('/{id}/review', [SubmissionWebController::class, 'review'])->name('review');
});

