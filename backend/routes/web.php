<?php

use App\Http\Controllers\Web\DangNhapWebController;
use App\Http\Controllers\Web\ChuongTrinhHopWebController;
use App\Http\Controllers\Web\BangDieuHanhWebController;
use App\Http\Controllers\Web\TaiLieuWebController;
use App\Http\Controllers\Web\HoSoCaNhanWebController;
use App\Http\Controllers\Web\PhienHopWebController;
use App\Http\Controllers\Web\ThanhVienHdtWebController;
use App\Http\Controllers\Web\NghiQuyetWebController;
use App\Http\Controllers\Web\HoSoTrinhWebController;
use App\Http\Controllers\Web\BaoCaoWebController;
use App\Http\Controllers\Web\NhiemVuWebController;
use App\Http\Controllers\Web\NguoiDungWebController;
use App\Http\Controllers\Web\BieuQuyetWebController;
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
Route::get('/login', [DangNhapWebController::class, 'showLoginForm'])->name('login');
Route::post('/login', [DangNhapWebController::class, 'login'])->name('login.submit');
Route::match(['get', 'post'], '/logout', [DangNhapWebController::class, 'logout'])->name('logout');

// --- BẢNG ĐIỀU HÀNH TỔNG QUAN (DASHBOARD) ---
Route::get('/', [BangDieuHanhWebController::class, 'index'])->name('home');
Route::get('/dashboard', [BangDieuHanhWebController::class, 'index'])->name('dashboard');

// --- THƯ VIỆN DESIGN SYSTEM & BLADE COMPONENTS SHOWCASE ---
Route::get('/design-system', function () {
    return view('pages.hethongthietke');
})->name('design-system');

// --- PHÂN HỆ QUẢN LÝ PHIÊN HỌP (MEETINGS) ---
Route::prefix('meetings')->name('meetings.')->group(function () {
    Route::get('/', [PhienHopWebController::class, 'index'])->name('index');
    Route::get('/create', [PhienHopWebController::class, 'create'])->name('create');
    Route::post('/', [PhienHopWebController::class, 'store'])->name('store');
    Route::get('/{id}', [PhienHopWebController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [PhienHopWebController::class, 'edit'])->name('edit');
    Route::put('/{id}', [PhienHopWebController::class, 'update'])->name('update');
    Route::delete('/{id}', [PhienHopWebController::class, 'destroy'])->name('destroy');

    // Thao tác nghiệp vụ phiên họp (7 Tabs & BR-02, BR-03, BR-05, BR-08)
    Route::post('/{id}/members', [PhienHopWebController::class, 'addMember'])->name('members.add');
    Route::post('/{id}/attendance/{attendeeId}', [PhienHopWebController::class, 'updateAttendance'])->name('attendance');
    Route::post('/{id}/guests', [PhienHopWebController::class, 'addGuest'])->name('guests.add');
    Route::post('/{id}/agenda', [PhienHopWebController::class, 'addAgendaItem'])->name('agenda.add');
    Route::post('/{id}/documents', [PhienHopWebController::class, 'uploadDocument'])->name('documents.upload');
});

// --- PHÂN HỆ QUẢN LÝ HỒ SƠ TRÌNH (SUBMISSIONS) ---
Route::prefix('submissions')->name('submissions.')->group(function () {
    Route::get('/', [HoSoTrinhWebController::class, 'index'])->name('index');
    Route::get('/create', [HoSoTrinhWebController::class, 'create'])->name('create');
    Route::post('/', [HoSoTrinhWebController::class, 'store'])->name('store');
    Route::get('/{id}', [HoSoTrinhWebController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [HoSoTrinhWebController::class, 'edit'])->name('edit');
    Route::put('/{id}', [HoSoTrinhWebController::class, 'update'])->name('update');
    Route::delete('/{id}', [HoSoTrinhWebController::class, 'destroy'])->name('destroy');
    Route::post('/{id}/review', [HoSoTrinhWebController::class, 'review'])->name('review');
});

// --- PHÂN HỆ BIỂU QUYẾT & BỎ PHIẾU (VOTES) ---
Route::get('/voting', fn () => redirect()->route('votes.index'));
Route::prefix('votes')->name('votes.')->group(function () {
    Route::get('/', [BieuQuyetWebController::class, 'index'])->name('index');
    Route::post('/', [BieuQuyetWebController::class, 'store'])->name('store');
    Route::get('/{id}', [BieuQuyetWebController::class, 'show'])->name('show');
    Route::delete('/{id}', [BieuQuyetWebController::class, 'destroy'])->name('destroy');
    Route::post('/{id}/open', [BieuQuyetWebController::class, 'open'])->name('open');
    Route::post('/{id}/close', [BieuQuyetWebController::class, 'close'])->name('close');
    Route::post('/{id}/vote', [BieuQuyetWebController::class, 'vote'])->name('vote');
    Route::post('/delegations', [BieuQuyetWebController::class, 'storeDelegation'])->name('delegations.store');
});

// --- PHÂN HỆ QUẢN LÝ NGHỊ QUYẾT (RESOLUTIONS) ---
Route::prefix('resolutions')->name('resolutions.')->group(function () {
    Route::get('/', [NghiQuyetWebController::class, 'index'])->name('index');
    Route::get('/create', [NghiQuyetWebController::class, 'create'])->name('create');
    Route::post('/', [NghiQuyetWebController::class, 'store'])->name('store');
    Route::get('/{id}', [NghiQuyetWebController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [NghiQuyetWebController::class, 'edit'])->name('edit');
    Route::put('/{id}', [NghiQuyetWebController::class, 'update'])->name('update');
    Route::delete('/{id}', [NghiQuyetWebController::class, 'destroy'])->name('destroy');
    Route::post('/{id}/sign', [NghiQuyetWebController::class, 'sign'])->name('sign');
    Route::post('/{id}/publish', [NghiQuyetWebController::class, 'publish'])->name('publish');
});

// --- PHÂN HỆ PHÂN CÔNG NHIỆM VỤ (TASKS) ---
Route::prefix('tasks')->name('tasks.')->group(function () {
    Route::get('/', [NhiemVuWebController::class, 'index'])->name('index');
    Route::get('/create', [NhiemVuWebController::class, 'create'])->name('create');
    Route::post('/', [NhiemVuWebController::class, 'store'])->name('store');
    Route::get('/{id}', [NhiemVuWebController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [NhiemVuWebController::class, 'edit'])->name('edit');
    Route::put('/{id}', [NhiemVuWebController::class, 'update'])->name('update');
    Route::delete('/{id}', [NhiemVuWebController::class, 'destroy'])->name('destroy');
});

// --- PHÂN HỆ BÁO CÁO & THEO DÕI TIẾN ĐỘ (TASK REPORTS) ---
Route::get('/reports', fn () => redirect()->route('task-reports.index'));
Route::prefix('task-reports')->name('task-reports.')->group(function () {
    Route::get('/', [BaoCaoWebController::class, 'index'])->name('index');
    Route::get('/create', [BaoCaoWebController::class, 'create'])->name('create');
    Route::post('/', [BaoCaoWebController::class, 'store'])->name('store');
    Route::get('/{id}', [BaoCaoWebController::class, 'show'])->name('show');
    Route::post('/{id}/review', [BaoCaoWebController::class, 'review'])->name('review');
    Route::delete('/{id}', [BaoCaoWebController::class, 'destroy'])->name('destroy');
});

// --- PHÂN HỆ QUẢN LÝ CHƯƠNG TRÌNH HỌP (AGENDAS / BR-08) ---
Route::prefix('agendas')->name('agendas.')->group(function () {
    Route::get('/', [ChuongTrinhHopWebController::class, 'index'])->name('index');
    Route::post('/', [ChuongTrinhHopWebController::class, 'store'])->name('store');
    Route::put('/{id}', [ChuongTrinhHopWebController::class, 'update'])->name('update');
    Route::delete('/{id}', [ChuongTrinhHopWebController::class, 'destroy'])->name('destroy');
});

Route::prefix('documents')->name('documents.')->group(function () {
    Route::get('/', [TaiLieuWebController::class, 'index'])->name('index');
    Route::get('/{id}/download', [TaiLieuWebController::class, 'download'])->name('download');
    Route::delete('/{id}', [TaiLieuWebController::class, 'destroy'])->name('destroy');
});

Route::prefix('members')->name('members.')->group(function () {
    Route::get('/', [ThanhVienHdtWebController::class, 'index'])->name('index');
    Route::delete('/{id}', [ThanhVienHdtWebController::class, 'destroy'])->name('destroy');
});

Route::prefix('users')->name('users.')->group(function () {
    Route::get('/', [NguoiDungWebController::class, 'index'])->name('index');
    Route::post('/', [NguoiDungWebController::class, 'store'])->name('store');
    Route::delete('/{id}', [NguoiDungWebController::class, 'destroy'])->name('destroy');
});

// --- PHÂN HỆ HỒ SƠ CÁ NHÂN (USER PROFILE) ---
Route::get('/profile', [HoSoCaNhanWebController::class, 'show'])->name('profile.show');
Route::put('/profile', [HoSoCaNhanWebController::class, 'update'])->name('profile.update');
Route::get('/ho-so-ca-nhan', fn () => redirect()->route('profile.show'));



