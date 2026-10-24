<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DossierController;
use App\Http\Controllers\Api\V1\MeetingController;
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
});
