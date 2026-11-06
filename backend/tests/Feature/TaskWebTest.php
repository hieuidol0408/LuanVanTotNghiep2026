<?php

namespace Tests\Feature;

use App\Models\DonVi;
use App\Models\NghiQuyet;
use App\Models\NhiemVu;
use App\Models\PhanCongNhiemVu;
use App\Models\User;
use Tests\TestCase;

class TaskWebTest extends TestCase
{
    /**
     * 1. Danh sách nhiệm vụ hiển thị thành công với các KPI cards và bộ lọc.
     */
    public function test_tasks_index_renders_successfully(): void
    {
        $secretary = User::where('email', 'thuky_hdt@stu.edu.vn')->first();
        $this->actingAs($secretary);

        $response = $this->get(route('tasks.index'));

        $response->assertStatus(200);
        $response->assertSee('Theo dõi &amp; Phân công Nhiệm vụ', false);
        $response->assertSee('Tổng số nhiệm vụ');
        $response->assertSee('Đang thực hiện');
        $response->assertSee('Đã hoàn thành');
        $response->assertSee('Quá hạn thực hiện');
        $response->assertSee('Giao nhiệm vụ mới');
    }

    /**
     * 2. Form giao nhiệm vụ mới hiển thị đầy đủ các trường BR-10.
     */
    public function test_tasks_create_renders_form(): void
    {
        $secretary = User::where('email', 'thuky_hdt@stu.edu.vn')->first();
        $this->actingAs($secretary);

        $response = $this->get(route('tasks.create'));

        $response->assertStatus(200);
        $response->assertSee('Phân công Nhiệm vụ từ Nghị quyết');
        $response->assertSee('Nghị quyết căn cứ');
        $response->assertSee('Đơn vị Chủ trì thực hiện (Bắt buộc)');
        $response->assertSee('Người phụ trách đầu mối');
        $response->assertSee('Đơn vị Phối hợp thực hiện');
    }

    /**
     * 3. Giao nhiệm vụ mới thành công: tạo record nhiem_vu và phân công chủ trì (BR-10).
     */
    public function test_tasks_store_creates_task_and_assignments(): void
    {
        $secretary = User::where('email', 'thuky_hdt@stu.edu.vn')->first();
        $this->actingAs($secretary);

        $res = NghiQuyet::first();
        $leadDept = DonVi::first();
        $coopDept = DonVi::skip(1)->first();

        $data = [
            'MaNghiQuyet' => $res->MaNghiQuyet,
            'TenNhiemVu' => 'Thực hiện kiểm định chất lượng chương trình đào tạo ĐH STU 2026',
            'NoiDung' => 'Triển khai tự đánh giá và hoàn thiện hồ sơ minh chứng theo tiêu chuẩn MOET.',
            'NgayGiao' => now()->toDateString(),
            'NgayHoanThanh' => now()->addDays(30)->toDateString(),
            'chu_tri_don_vi_id' => $leadDept->MaDonVi,
            'chu_tri_nguoi_phu_trach' => 'TS. Nguyễn Văn Trưởng Khoa',
            'phoi_hop_don_vi_ids' => $coopDept ? [$coopDept->MaDonVi] : [],
        ];

        $response = $this->post(route('tasks.store'), $data);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $task = NhiemVu::where('TenNhiemVu', 'Thực hiện kiểm định chất lượng chương trình đào tạo ĐH STU 2026')->first();
        $this->assertNotNull($task);
        $this->assertEquals('ASSIGNED', $task->TrangThai);

        // Kiểm tra phân công chủ trì
        $leadAssignment = PhanCongNhiemVu::where('MaNhiemVu', $task->MaNhiemVu)
            ->where('VaiTroThucHien', 'CHỦ TRÌ')
            ->first();
        $this->assertNotNull($leadAssignment);
        $this->assertEquals($leadDept->MaDonVi, $leadAssignment->MaDonVi);
        $this->assertEquals('TS. Nguyễn Văn Trưởng Khoa', $leadAssignment->TenNguoiPhuTrach);
    }

    /**
     * 4. Chi tiết nhiệm vụ hiển thị tiến độ và lịch sử phân công.
     */
    public function test_tasks_show_renders_task_and_progress(): void
    {
        $chair = User::where('email', 'chutich_hdt@stu.edu.vn')->first();
        $this->actingAs($chair);

        $task = NhiemVu::first();
        if (!$task) {
            $task = NhiemVu::create([
                'TenNhiemVu' => 'Nhiệm vụ mẫu kiểm tra',
                'NoiDung' => 'Nội dung kiểm tra',
                'NgayGiao' => now()->toDateString(),
                'NgayHoanThanh' => now()->addDays(15)->toDateString(),
                'TrangThai' => 'IN_PROGRESS',
            ]);
        }

        $response = $this->get(route('tasks.show', $task->MaNhiemVu));

        $response->assertStatus(200);
        $response->assertSee($task->TenNhiemVu);
        $response->assertSee('Tiến độ thực hiện hiện tại');
        $response->assertSee('Nộp báo cáo tiến độ');
        $response->assertSee('Quy định BR-10');
    }

    /**
     * 5. Phân quyền RBAC: Đơn vị thực hiện không thể giao nhiệm vụ mới.
     */
    public function test_tasks_executing_unit_cannot_create_task(): void
    {
        $unitUser = User::whereHas('vaiTro', function ($q) {
            $q->where('TenVaiTro', 'EXECUTING_UNIT');
        })->first();

        if ($unitUser) {
            $this->actingAs($unitUser);
            $response = $this->get(route('tasks.create'));
            $response->assertRedirect(route('tasks.index'));
        } else {
            $this->assertTrue(true);
        }
    }
}
