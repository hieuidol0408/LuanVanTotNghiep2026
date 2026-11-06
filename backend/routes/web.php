<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\DocumentWebController;
use App\Http\Controllers\Web\MeetingWebController;
use App\Http\Controllers\Web\MemberWebController;
use App\Http\Controllers\Web\ResolutionWebController;
use App\Http\Controllers\Web\SubmissionWebController;
use App\Http\Controllers\Web\TaskReportWebController;
use App\Http\Controllers\Web\TaskWebController;
use App\Http\Controllers\Web\UserWebController;
use App\Http\Controllers\Web\VoteWebController;
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

// --- PHÂN HỆ BIỂU QUYẾT & BỎ PHIẾU (VOTES) ---
Route::get('/voting', fn () => redirect()->route('votes.index'));
Route::prefix('votes')->name('votes.')->group(function () {
    Route::get('/', [VoteWebController::class, 'index'])->name('index');
    Route::get('/{id}', [VoteWebController::class, 'show'])->name('show');
    Route::post('/{id}/open', [VoteWebController::class, 'open'])->name('open');
    Route::post('/{id}/close', [VoteWebController::class, 'close'])->name('close');
    Route::post('/{id}/vote', [VoteWebController::class, 'vote'])->name('vote');
    Route::post('/delegations', [VoteWebController::class, 'storeDelegation'])->name('delegations.store');
});

// --- PHÂN HỆ QUẢN LÝ NGHỊ QUYẾT (RESOLUTIONS) ---
Route::prefix('resolutions')->name('resolutions.')->group(function () {
    Route::get('/', [ResolutionWebController::class, 'index'])->name('index');
    Route::get('/create', [ResolutionWebController::class, 'create'])->name('create');
    Route::post('/', [ResolutionWebController::class, 'store'])->name('store');
    Route::get('/{id}', [ResolutionWebController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [ResolutionWebController::class, 'edit'])->name('edit');
    Route::put('/{id}', [ResolutionWebController::class, 'update'])->name('update');
    Route::post('/{id}/sign', [ResolutionWebController::class, 'sign'])->name('sign');
    Route::post('/{id}/publish', [ResolutionWebController::class, 'publish'])->name('publish');
});

// --- PHÂN HỆ PHÂN CÔNG NHIỆM VỤ (TASKS) ---
Route::prefix('tasks')->name('tasks.')->group(function () {
    Route::get('/', [TaskWebController::class, 'index'])->name('index');
    Route::get('/create', [TaskWebController::class, 'create'])->name('create');
    Route::post('/', [TaskWebController::class, 'store'])->name('store');
    Route::get('/{id}', [TaskWebController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [TaskWebController::class, 'edit'])->name('edit');
    Route::put('/{id}', [TaskWebController::class, 'update'])->name('update');
    Route::delete('/{id}', [TaskWebController::class, 'destroy'])->name('destroy');
});

// --- PHÂN HỆ BÁO CÁO & THEO DÕI TIẾN ĐỘ (TASK REPORTS) ---
Route::get('/reports', fn () => redirect()->route('task-reports.index'));
Route::prefix('task-reports')->name('task-reports.')->group(function () {
    Route::get('/', [TaskReportWebController::class, 'index'])->name('index');
    Route::get('/create', [TaskReportWebController::class, 'create'])->name('create');
    Route::post('/', [TaskReportWebController::class, 'store'])->name('store');
    Route::get('/{id}', [TaskReportWebController::class, 'show'])->name('show');
    Route::post('/{id}/review', [TaskReportWebController::class, 'review'])->name('review');
});

// --- PHÂN HỆ QUẢN TRỊ DỮ LIỆU & HỆ THỐNG ---
Route::get('/agendas', fn () => redirect()->route('meetings.index'));

Route::prefix('documents')->name('documents.')->group(function () {
    Route::get('/', [DocumentWebController::class, 'index'])->name('index');
    Route::get('/{id}/download', [DocumentWebController::class, 'download'])->name('download');
});

Route::prefix('members')->name('members.')->group(function () {
    Route::get('/', [MemberWebController::class, 'index'])->name('index');
});

Route::prefix('users')->name('users.')->group(function () {
    Route::get('/', [UserWebController::class, 'index'])->name('index');
});


