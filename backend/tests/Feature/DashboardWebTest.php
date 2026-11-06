<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class DashboardWebTest extends TestCase
{
    /**
     * Dashboard hiển thị đầy đủ các thẻ thống kê KPI và dữ liệu từ CSDL.
     */
    public function test_dashboard_renders_with_real_database_metrics(): void
    {
        $user = User::where('email', 'chutich_hdt@stu.edu.vn')->first();
        $this->actingAs($user);

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Bảng Điều Hành Hoạt Động Hội Đồng Trường STU');
        $response->assertSee('Tổng số phiên họp');
        $response->assertSee('Nghị quyết ban hành');
        $response->assertSee('Nhiệm vụ đang thực hiện');
        $response->assertSee('Lịch phiên họp Hội đồng trường');
        $response->assertSee('Nhiệm vụ phân công theo Nghị quyết');
        $response->assertSee('Tiến độ thực hiện nhiệm vụ');
        $response->assertSee('Nghị quyết mới ban hành');
        $response->assertSee('Báo cáo tiến độ chờ duyệt');

        // Khẳng định không có nút "Thử lỗi"
        $response->assertDontSee('Thử lỗi');
    }

    /**
     * Kiểm tra phân quyền Dashboard đối với vai trò Đơn vị thi hành (EXECUTING_UNIT).
     */
    public function test_dashboard_for_executing_unit_shows_relevant_kpi(): void
    {
        $user = User::where('email', 'cntt@stu.edu.vn')->first();
        $this->actingAs($user);

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Tiến độ hoàn thành');
        $response->assertSee('EXECUTING_UNIT');
    }

    /**
     * Kiểm tra phân quyền Dashboard đối với vai trò Thư ký (SECRETARY).
     */
    public function test_dashboard_for_secretary_shows_moderation_tools(): void
    {
        $user = User::where('email', 'thuky_hdt@stu.edu.vn')->first();
        $this->actingAs($user);

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('SECRETARY');
        $response->assertSee('Tạo phiên họp mới');
        $response->assertSee('Báo cáo tiến độ chờ duyệt');
    }
}
