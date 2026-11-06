<?php

namespace Tests\Feature;

use Tests\TestCase;

class BladeLayoutAndComponentsTest extends TestCase
{
    /**
     * Kiểm tra Master Layout và toàn bộ 12 Blade Components render thành công.
     */
    public function test_design_system_showcase_page_renders_successfully(): void
    {
        $response = $this->get('/design-system');

        $response->assertStatus(200);
        $response->assertSee('Thư viện Design System');
        $response->assertSee('HỘI ĐỒNG TRƯỜNG');
        $response->assertSee('ĐH CÔNG NGHỆ SÀI GÒN');

        // Kiểm tra sự xuất hiện của các Blade Components
        $response->assertSee('stu-stat-card');
        $response->assertSee('stu-table');
        $response->assertSee('col-actions');
        $response->assertSee('stu-progress');
        $response->assertSee('stu-badge');
        $response->assertSee('stu-modal');
        $response->assertSee('stu-tabs');
        $response->assertSee('stu-alert');

        // Khẳng định chắc chắn không tồn tại nút debug "Thử lỗi"
        $response->assertDontSee('Thử lỗi');
    }

    /**
     * Kiểm tra trang chủ '/' định tuyến về giao diện Enterprise.
     */
    public function test_home_route_renders_enterprise_layout(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Hội đồng trường STU');
        $response->assertDontSee('Thử lỗi');
    }
}
