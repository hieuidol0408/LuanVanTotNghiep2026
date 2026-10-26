<?php

use App\Http\Controllers\Api\V1\DangNhapController;
use App\Http\Controllers\Api\V1\ThanhVienHdtController;
use App\Http\Controllers\Api\V1\BangDieuHanhController;
use App\Http\Controllers\Api\V1\TaiLieuController;
use App\Http\Controllers\Api\V1\HoSoTrinhController;
use App\Http\Controllers\Api\V1\PhienHopController;
use App\Http\Controllers\Api\V1\NghiQuyetController;
use App\Http\Controllers\Api\V1\NhiemVuController;
use App\Http\Controllers\Api\V1\NguoiDungController;
use App\Http\Controllers\Api\V1\BieuQuyetController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes V1 - Hệ thống Quản lý Hội đồng trường STU
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    // 1. Module Xác thực (Auth)
    Route::prefix('auth')->group(function () {
        Route::post('/login', [DangNhapController::class, 'login']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/me', [DangNhapController::class, 'me']);
            Route::post('/logout', [DangNhapController::class, 'logout']);
        });
    });

    // 2. Module Bảng điều hành Tổng quan (Dashboard)
    Route::middleware('auth:sanctum')->get('/dashboard/summary', [BangDieuHanhController::class, 'summary']);

    // 3. Module Quản lý Phiên họp (Meetings)
    Route::middleware('auth:sanctum')->prefix('meetings')->group(function () {
        Route::get('/', [PhienHopController::class, 'index']);
        Route::post('/', [PhienHopController::class, 'store']);
        Route::get('/{id}', [PhienHopController::class, 'show']);
        Route::put('/{id}', [PhienHopController::class, 'update']);
        Route::delete('/{id}', [PhienHopController::class, 'destroy']);

        Route::post('/{id}/agendas', [PhienHopController::class, 'addAgenda']);
        Route::delete('/{id}/agendas/{agendaId}', [PhienHopController::class, 'deleteAgenda']);

        Route::post('/{id}/attendees', [PhienHopController::class, 'addGuest']);
        Route::post('/{id}/checkin', [PhienHopController::class, 'checkin']);
        Route::post('/{id}/documents', [PhienHopController::class, 'attachDocument']); // [BR-05]
        Route::post('/{id}/send-invitations', [PhienHopController::class, 'sendInvitations']);
        Route::post('/{id}/status', [PhienHopController::class, 'changeStatus']);
    });

    // 3. Module Quản lý Hồ sơ trình (Dossiers)
    Route::middleware('auth:sanctum')->prefix('dossiers')->group(function () {
        Route::get('/', [HoSoTrinhController::class, 'index']);
        Route::post('/', [HoSoTrinhController::class, 'store']);
        Route::get('/{id}', [HoSoTrinhController::class, 'show']);
        Route::put('/{id}', [HoSoTrinhController::class, 'update']);
        Route::delete('/{id}', [HoSoTrinhController::class, 'destroy']);

        Route::post('/{id}/submit', [HoSoTrinhController::class, 'submit']);
        Route::post('/{id}/review', [HoSoTrinhController::class, 'review']);
    });

    // 4. Module Quản lý Biểu quyết & Kiểm phiếu (Voting & Authorizations)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/meetings/{meetingId}/votings', [BieuQuyetController::class, 'index']);
        Route::post('/meetings/{meetingId}/votings', [BieuQuyetController::class, 'store']);
        Route::get('/votings/{id}', [BieuQuyetController::class, 'show']);
        Route::post('/votings/{id}/open', [BieuQuyetController::class, 'open']);
        Route::post('/votings/{id}/vote', [BieuQuyetController::class, 'vote']);
        Route::post('/votings/{id}/close', [BieuQuyetController::class, 'close']);

        // Giấy ủy quyền biểu quyết (Bảng 14 - uy_quyen_bieu_quyet)
        Route::get('/meetings/{meetingId}/authorizations', [BieuQuyetController::class, 'authorizations']);
        Route::post('/meetings/{meetingId}/authorizations', [BieuQuyetController::class, 'storeAuthorization']);
    });

    // 5. Module Quản lý Biên bản & Nghị quyết (Resolutions & Minutes)
    Route::middleware('auth:sanctum')->group(function () {
        // Biên bản phiên họp
        Route::get('/meetings/{meetingId}/minutes', [NghiQuyetController::class, 'getMinutes']);
        Route::post('/meetings/{meetingId}/minutes', [NghiQuyetController::class, 'saveMinutes']);
        Route::post('/meetings/{meetingId}/minutes/approve', [NghiQuyetController::class, 'approveMinutes']);

        // Nghị quyết HĐT
        Route::get('/resolutions', [NghiQuyetController::class, 'index']);
        Route::post('/resolutions', [NghiQuyetController::class, 'store']);
        Route::get('/resolutions/{id}', [NghiQuyetController::class, 'show']);
        Route::put('/resolutions/{id}', [NghiQuyetController::class, 'update']);
        Route::delete('/resolutions/{id}', [NghiQuyetController::class, 'destroy']);
        Route::post('/resolutions/{id}/sign', [NghiQuyetController::class, 'sign']);
        Route::post('/resolutions/{id}/publish', [NghiQuyetController::class, 'publish']);
    });

    // 6. Module Phân rã Nhiệm vụ & Theo dõi thực hiện (Tasks)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/tasks', [NhiemVuController::class, 'index']);
        Route::post('/tasks', [NhiemVuController::class, 'store']);
        Route::get('/tasks/{id}', [NhiemVuController::class, 'show']);
        Route::put('/tasks/{id}', [NhiemVuController::class, 'update']);
        Route::delete('/tasks/{id}', [NhiemVuController::class, 'destroy']);
        Route::post('/tasks/{id}/progress-logs', [NhiemVuController::class, 'submitProgressLog']);
        Route::post('/tasks/progress-logs/{logId}/review', [NhiemVuController::class, 'reviewProgressLog']);
    });

    // 7. Module Thành viên Hội đồng trường & Đơn vị (Councils & Departments)
    Route::get('/departments', [ThanhVienHdtController::class, 'departments']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/councils/members', [ThanhVienHdtController::class, 'members']);
        Route::get('/councils/terms', [ThanhVienHdtController::class, 'terms']);
    });

    // 8. Module Kho Văn bản & Tài liệu an toàn (Documents)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/documents', [TaiLieuController::class, 'index']);
        Route::post('/documents', [TaiLieuController::class, 'store']);
        Route::get('/documents/{id}/download', [TaiLieuController::class, 'download']);
        Route::delete('/documents/{id}', [TaiLieuController::class, 'destroy']);
    });

    // 9. Module Quản lý Người dùng & Phân quyền (Users & Roles - UC6)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/roles', [NguoiDungController::class, 'roles']);
        Route::get('/users', [NguoiDungController::class, 'index']);
        Route::post('/users', [NguoiDungController::class, 'store']);
        Route::get('/users/{id}', [NguoiDungController::class, 'show']);
        Route::put('/users/{id}', [NguoiDungController::class, 'update']);
        Route::delete('/users/{id}', [NguoiDungController::class, 'destroy']);
        Route::post('/users/{id}/toggle-status', [NguoiDungController::class, 'toggleStatus']);
        Route::post('/users/{id}/reset-password', [NguoiDungController::class, 'resetPassword']);
    });
});
