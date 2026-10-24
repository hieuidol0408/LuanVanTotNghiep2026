<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DashboardApiTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Khách vãng lai không được truy cập số liệu tổng quan
     */
    public function test_unauthenticated_cannot_access_dashboard(): void
    {
        $response = $this->getJson('/api/v1/dashboard/summary');
        $response->assertStatus(401);
    }

    /**
     * Người dùng đã đăng nhập có thể lấy dữ liệu tổng quan thời gian thực từ 16 bảng MySQL
     */
    public function test_authenticated_can_get_dashboard_summary(): void
    {
        $user = User::where('email', 'thuky_hdt@stu.edu.vn')->first();

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/dashboard/summary');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'stats' => [
                        '*' => ['id', 'title', 'value', 'description', 'trend', 'trendType', 'statusColor', 'path'],
                    ],
                    'upcoming_meetings',
                    'pending_dossiers',
                    'urgent_tasks',
                ],
            ]);
    }
}
