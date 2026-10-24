<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DossierController;
use App\Http\Controllers\Api\V1\MeetingController;
use App\Http\Controllers\Api\V1\ResolutionController;
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

    // 2. Module Quản lý Phiên họp (Meetings)
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

    // 4. Module Quản lý Biểu quyết & Kiểm phiếu (Voting)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/meetings/{meetingId}/votings', [VotingController::class, 'index']);
        Route::post('/meetings/{meetingId}/votings', [VotingController::class, 'store']);
        Route::get('/votings/{id}', [VotingController::class, 'show']);
        Route::post('/votings/{id}/open', [VotingController::class, 'open']);
        Route::post('/votings/{id}/vote', [VotingController::class, 'vote']);
        Route::post('/votings/{id}/close', [VotingController::class, 'close']);
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
});
