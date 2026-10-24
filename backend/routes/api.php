<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CouncilController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\DocumentController;
use App\Http\Controllers\Api\V1\DossierController;
use App\Http\Controllers\Api\V1\MeetingController;
use App\Http\Controllers\Api\V1\ResolutionController;
use App\Http\Controllers\Api\V1\TaskController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\VotingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes V1 - Hệ thống Quản lý Hội đồng trường STU
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    // 1. Module Xác thực (Auth)
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/me', [AuthController::class, 'me']);
            Route::post('/logout', [AuthController::class, 'logout']);
        });
    });

    // 2. Module Bảng điều hành Tổng quan (Dashboard)
    Route::middleware('auth:sanctum')->get('/dashboard/summary', [DashboardController::class, 'summary']);

    // 3. Module Quản lý Phiên họp (Meetings)
    Route::middleware('auth:sanctum')->prefix('meetings')->group(function () {
        Route::get('/', [MeetingController::class, 'index']);
        Route::post('/', [MeetingController::class, 'store']);
        Route::get('/{id}', [MeetingController::class, 'show']);
        Route::put('/{id}', [MeetingController::class, 'update']);
        Route::delete('/{id}', [MeetingController::class, 'destroy']);

        Route::post('/{id}/agendas', [MeetingController::class, 'addAgenda']);
        Route::delete('/{id}/agendas/{agendaId}', [MeetingController::class, 'deleteAgenda']);

        Route::post('/{id}/attendees', [MeetingController::class, 'addGuest']);
        Route::post('/{id}/checkin', [MeetingController::class, 'checkin']);
        Route::post('/{id}/send-invitations', [MeetingController::class, 'sendInvitations']);
        Route::post('/{id}/status', [MeetingController::class, 'changeStatus']);
    });

    // 3. Module Quản lý Hồ sơ trình (Dossiers)
    Route::middleware('auth:sanctum')->prefix('dossiers')->group(function () {
        Route::get('/', [DossierController::class, 'index']);
        Route::post('/', [DossierController::class, 'store']);
        Route::get('/{id}', [DossierController::class, 'show']);
        Route::put('/{id}', [DossierController::class, 'update']);
        Route::delete('/{id}', [DossierController::class, 'destroy']);

        Route::post('/{id}/submit', [DossierController::class, 'submit']);
        Route::post('/{id}/review', [DossierController::class, 'review']);
    });

    // 4. Module Quản lý Biểu quyết & Kiểm phiếu (Voting & Authorizations)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/meetings/{meetingId}/votings', [VotingController::class, 'index']);
        Route::post('/meetings/{meetingId}/votings', [VotingController::class, 'store']);
        Route::get('/votings/{id}', [VotingController::class, 'show']);
        Route::post('/votings/{id}/open', [VotingController::class, 'open']);
        Route::post('/votings/{id}/vote', [VotingController::class, 'vote']);
        Route::post('/votings/{id}/close', [VotingController::class, 'close']);

        // Giấy ủy quyền biểu quyết (Bảng 14 - uy_quyen_bieu_quyet)
        Route::get('/meetings/{meetingId}/authorizations', [VotingController::class, 'authorizations']);
        Route::post('/meetings/{meetingId}/authorizations', [VotingController::class, 'storeAuthorization']);
    });

    // 5. Module Quản lý Biên bản & Nghị quyết (Resolutions & Minutes)
    Route::middleware('auth:sanctum')->group(function () {
        // Biên bản phiên họp
        Route::get('/meetings/{meetingId}/minutes', [ResolutionController::class, 'getMinutes']);
        Route::post('/meetings/{meetingId}/minutes', [ResolutionController::class, 'saveMinutes']);
        Route::post('/meetings/{meetingId}/minutes/approve', [ResolutionController::class, 'approveMinutes']);

        // Nghị quyết HĐT
        Route::get('/resolutions', [ResolutionController::class, 'index']);
        Route::post('/resolutions', [ResolutionController::class, 'store']);
        Route::get('/resolutions/{id}', [ResolutionController::class, 'show']);
        Route::put('/resolutions/{id}', [ResolutionController::class, 'update']);
        Route::delete('/resolutions/{id}', [ResolutionController::class, 'destroy']);
        Route::post('/resolutions/{id}/sign', [ResolutionController::class, 'sign']);
        Route::post('/resolutions/{id}/publish', [ResolutionController::class, 'publish']);
    });

    // 6. Module Phân rã Nhiệm vụ & Theo dõi thực hiện (Tasks)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/tasks', [TaskController::class, 'index']);
        Route::post('/tasks', [TaskController::class, 'store']);
        Route::get('/tasks/{id}', [TaskController::class, 'show']);
        Route::put('/tasks/{id}', [TaskController::class, 'update']);
        Route::delete('/tasks/{id}', [TaskController::class, 'destroy']);
        Route::post('/tasks/{id}/progress-logs', [TaskController::class, 'submitProgressLog']);
        Route::post('/tasks/progress-logs/{logId}/review', [TaskController::class, 'reviewProgressLog']);
    });

    // 7. Module Thành viên Hội đồng trường & Đơn vị (Councils & Departments)
    Route::get('/departments', [CouncilController::class, 'departments']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/councils/members', [CouncilController::class, 'members']);
        Route::get('/councils/terms', [CouncilController::class, 'terms']);
    });

    // 8. Module Kho Văn bản & Tài liệu an toàn (Documents)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/documents', [DocumentController::class, 'index']);
        Route::post('/documents', [DocumentController::class, 'store']);
        Route::get('/documents/{id}/download', [DocumentController::class, 'download']);
        Route::delete('/documents/{id}', [DocumentController::class, 'destroy']);
    });

    // 9. Module Quản lý Người dùng & Phân quyền (Users & Roles - UC6)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/roles', [UserController::class, 'roles']);
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        Route::get('/users/{id}', [UserController::class, 'show']);
        Route::put('/users/{id}', [UserController::class, 'update']);
        Route::delete('/users/{id}', [UserController::class, 'destroy']);
        Route::post('/users/{id}/toggle-status', [UserController::class, 'toggleStatus']);
        Route::post('/users/{id}/reset-password', [UserController::class, 'resetPassword']);
    });
});
