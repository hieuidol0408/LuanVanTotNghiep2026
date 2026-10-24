<?php

namespace Tests\Feature;

use App\Models\DonVi;
use App\Models\NghiQuyet;
use App\Models\NhiemVu;
use App\Models\PhanCongNhiemVu;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TaskApiTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Khách vãng lai không được truy cập danh sách nhiệm vụ
     */
    public function test_unauthenticated_cannot_access_tasks(): void
    {
        $response = $this->getJson('/api/v1/tasks');
        $response->assertStatus(401);
    }

    /**
     * Người dùng đã xác thực có thể xem danh sách nhiệm vụ
     */
    public function test_authenticated_can_list_tasks(): void
    {
        $user = User::where('email', 'thuky_hdt@stu.edu.vn')->first();

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/tasks');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
                'meta' => ['total', 'current_page', 'total_pages'],
            ]);
    }

    /**
     * Thư ký có thể phân rã nhiệm vụ từ Nghị quyết và chỉ định đơn vị chủ trì
     */
    public function test_secretary_can_create_task(): void
    {
        $secretary = User::where('email', 'thuky_hdt@stu.edu.vn')->first();
        $resolution = NghiQuyet::first();
        $leadDept = DonVi::where('TenDonVi', 'like', '%Công nghệ Thông tin%')->first() ?: DonVi::first();

        $payload = [
            'resolution_id' => $resolution->MaNghiQuyet,
            'title' => 'Xây dựng giáo trình chuyên sâu Kỹ thuật Robot & AI',
            'content' => 'Biên soạn 03 giáo trình trọng điểm và thẩm định cấp trường.',
            'lead_department_id' => $leadDept->MaDonVi,
            'start_date' => now()->toDateString(),
            'due_date' => now()->addMonths(6)->toDateString(),
            'person_in_charge' => 'Trưởng bộ môn AI',
        ];

        $response = $this->actingAs($secretary, 'sanctum')->postJson('/api/v1/tasks', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.title', $payload['title'])
            ->assertJsonPath('data.status', 'ASSIGNED')
            ->assertJsonPath('data.lead_department', $leadDept->TenDonVi);

        $this->assertDatabaseHas('nhiem_vu', [
            'TenNhiemVu' => $payload['title'],
            'TrangThai' => 'ASSIGNED',
        ]);
    }

    /**
     * Người dùng không có quyền (không phải CHAIR/SECRETARY/ADMIN) không được phân rã nhiệm vụ
     */
    public function test_unauthorized_cannot_create_task(): void
    {
        $unitRep = User::where('email', 'daotao@stu.edu.vn')->first();
        $resolution = NghiQuyet::first();
        $dept = DonVi::first();

        $response = $this->actingAs($unitRep, 'sanctum')->postJson('/api/v1/tasks', [
            'resolution_id' => $resolution->MaNghiQuyet,
            'title' => 'Nhiệm vụ không hợp lệ',
            'content' => 'Nội dung',
            'lead_department_id' => $dept->MaDonVi,
            'due_date' => now()->addMonth()->toDateString(),
        ]);

        $response->assertStatus(403);
    }

    /**
     * Đơn vị thi hành được phân công có thể nộp báo cáo tiến độ và chuyển trạng thái sang IN_PROGRESS
     */
    public function test_executing_unit_can_submit_progress_log(): void
    {
        $userCntt = User::where('email', 'cntt@stu.edu.vn')->first();
        $deptCntt = $userCntt->donVi;
        $resolution = NghiQuyet::first();

        // Tạo 1 nhiệm vụ mới giao cho khoa CNTT
        $task = NhiemVu::create([
            'TenNhiemVu' => 'Test nộp báo cáo tiến độ ' . uniqid(),
            'NoiDung' => 'Nội dung test',
            'NgayGiao' => now()->toDateString(),
            'NgayHoanThanh' => now()->addMonths(2)->toDateString(),
            'TrangThai' => 'ASSIGNED',
            'MaNghiQuyet' => $resolution->MaNghiQuyet,
        ]);

        PhanCongNhiemVu::create([
            'VaiTroThucHien' => 'CHỦ TRÌ',
            'TrangThaiThucHien' => 'Đang thực hiện',
            'MaNhiemVu' => $task->MaNhiemVu,
            'MaDonVi' => $deptCntt->MaDonVi,
        ]);

        $response = $this->actingAs($userCntt, 'sanctum')->postJson("/api/v1/tasks/{$task->MaNhiemVu}/progress-logs", [
            'progress_percentage' => 70,
            'report_content' => 'Đã hoàn tất 70% nội dung biên soạn và phản biện nội bộ.',
            'challenges' => 'Không có khó khăn phát sinh.',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.progress_percentage', 70);

        // Kiểm tra nhiệm vụ chuyển sang IN_PROGRESS
        $this->assertEquals('IN_PROGRESS', $task->fresh()->TrangThai);
    }

    /**
     * Thư ký nghiệm thu báo cáo 100% và xác nhận hoàn thành nhiệm vụ (COMPLETED)
     */
    public function test_secretary_can_review_log_and_complete_task_at_100_percent(): void
    {
        $secretary = User::where('email', 'thuky_hdt@stu.edu.vn')->first();
        $userCntt = User::where('email', 'cntt@stu.edu.vn')->first();
        $deptCntt = $userCntt->donVi;
        $resolution = NghiQuyet::first();

        $task = NhiemVu::create([
            'TenNhiemVu' => 'Nhiệm vụ sắp hoàn thành ' . uniqid(),
            'NoiDung' => 'Nội dung test',
            'NgayGiao' => now()->subMonth()->toDateString(),
            'NgayHoanThanh' => now()->addMonth()->toDateString(),
            'TrangThai' => 'IN_PROGRESS',
            'MaNghiQuyet' => $resolution->MaNghiQuyet,
        ]);

        $pc = PhanCongNhiemVu::create([
            'VaiTroThucHien' => 'CHỦ TRÌ',
            'TrangThaiThucHien' => 'Hoàn tất',
            'MaNhiemVu' => $task->MaNhiemVu,
            'MaDonVi' => $deptCntt->MaDonVi,
        ]);

        // Đơn vị nộp báo cáo 100%
        $reportRes = $this->actingAs($userCntt, 'sanctum')->postJson("/api/v1/tasks/{$task->MaNhiemVu}/progress-logs", [
            'progress_percentage' => 100,
            'report_content' => 'Đã hoàn thành 100% các mục tiêu và biên bản nghiệm thu đính kèm.',
        ]);
        $reportId = $reportRes->json('data.id');

        // Thư ký duyệt nghiệm thu APPROVED
        $reviewRes = $this->actingAs($secretary, 'sanctum')->postJson("/api/v1/tasks/progress-logs/{$reportId}/review", [
            'status' => 'APPROVED',
            'notes' => 'Hồ sơ minh chứng đầy đủ, đồng ý nghiệm thu.',
        ]);

        $reviewRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'APPROVED');

        // Xác nhận nhiệm vụ chuyển sang COMPLETED và có ngày xác nhận
        $this->assertEquals('COMPLETED', $task->fresh()->TrangThai);
        $this->assertNotNull($task->fresh()->NgayXacNhanHoanThanh);
    }

    /**
     * Tự động phát hiện nhiệm vụ quá hạn (OVERDUE) khi ngày hoàn thành trước hôm nay và chưa xong
     */
    public function test_overdue_task_detection(): void
    {
        $secretary = User::where('email', 'thuky_hdt@stu.edu.vn')->first();
        $resolution = NghiQuyet::first();
        $dept = DonVi::first();

        $overdueTask = NhiemVu::create([
            'TenNhiemVu' => 'Nhiệm vụ bị trễ hạn ' . uniqid(),
            'NoiDung' => 'Nội dung trễ hạn',
            'NgayGiao' => now()->subMonths(3)->toDateString(),
            'NgayHoanThanh' => now()->subDay()->toDateString(), // Hạn chót là hôm qua
            'TrangThai' => 'IN_PROGRESS',
            'MaNghiQuyet' => $resolution->MaNghiQuyet,
        ]);

        PhanCongNhiemVu::create([
            'VaiTroThucHien' => 'CHỦ TRÌ',
            'TrangThaiThucHien' => 'Đang thực hiện',
            'MaNhiemVu' => $overdueTask->MaNhiemVu,
            'MaDonVi' => $dept->MaDonVi,
        ]);

        $response = $this->actingAs($secretary, 'sanctum')->getJson("/api/v1/tasks/{$overdueTask->MaNhiemVu}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.is_overdue', true)
            ->assertJsonPath('data.status', 'OVERDUE');
    }
}
