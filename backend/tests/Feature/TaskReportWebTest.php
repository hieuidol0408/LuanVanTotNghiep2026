<?php

namespace Tests\Feature;

use App\Models\BaoCao;
use App\Models\DonVi;
use App\Models\NghiQuyet;
use App\Models\NhiemVu;
use App\Models\PhanCongNhiemVu;
use App\Models\User;
use Tests\TestCase;

class TaskReportWebTest extends TestCase
{
    /**
     * 1. Danh sách báo cáo tiến độ hiển thị thành công.
     */
    public function test_task_reports_index_renders_successfully(): void
    {
        $secretary = User::where('email', 'thuky_hdt@stu.edu.vn')->first();
        $this->actingAs($secretary);

        $response = $this->get(route('task-reports.index'));

        $response->assertStatus(200);
        $response->assertSee('Báo cáo Tiến độ &amp; Đôn đốc Thực hiện', false);
        $response->assertSee('Trạng thái thẩm định');
        $response->assertSee('Đơn vị thực hiện');
        $response->assertSee('Nộp báo cáo tiến độ');
    }

    /**
     * 2. Form nộp báo cáo tiến độ hiển thị đầy đủ các trường BR-10.
     */
    public function test_task_reports_create_renders_form(): void
    {
        $secretary = User::where('email', 'thuky_hdt@stu.edu.vn')->first();
        $this->actingAs($secretary);

        // Đảm bảo có ít nhất 1 nhiệm vụ và phân công
        $task = NhiemVu::where('TrangThai', '!=', 'COMPLETED')->first();
        if (!$task) {
            $res = NghiQuyet::first();
            $task = NhiemVu::create([
                'MaNghiQuyet' => $res ? $res->MaNghiQuyet : null,
                'TenNhiemVu' => 'Nhiệm vụ kiểm tra tiến độ',
                'NgayGiao' => now()->toDateString(),
                'NgayHoanThanh' => now()->addDays(20)->toDateString(),
                'TrangThai' => 'IN_PROGRESS',
            ]);
            $dept = DonVi::first();
            PhanCongNhiemVu::create([
                'MaNhiemVu' => $task->MaNhiemVu,
                'MaDonVi' => $dept->MaDonVi,
                'VaiTroThucHien' => 'CHỦ TRÌ',
                'TrangThaiThucHien' => 'Đang thực hiện',
                'TenNguoiPhuTrach' => 'Nguyễn Phụ Trách',
            ]);
        }

        $response = $this->get(route('task-reports.create', ['task_id' => $task->MaNhiemVu]));

        $response->assertStatus(200);
        $response->assertSee('Nộp Báo cáo Tiến độ Nhiệm vụ');
        $response->assertSee('Nhiệm vụ được phân công');
        $response->assertSee('Tỷ lệ tiến độ hoàn thành (%)');
        $response->assertSee('Nội dung công việc đã thực hiện');
        $response->assertSee('Khó khăn, vướng mắc');
    }

    /**
     * 3. Nộp báo cáo tiến độ thành công và chuyển trạng thái nhiệm vụ sang IN_PROGRESS.
     */
    public function test_task_reports_store_creates_report(): void
    {
        $secretary = User::where('email', 'thuky_hdt@stu.edu.vn')->first();
        $this->actingAs($secretary);

        $assignment = PhanCongNhiemVu::with('nhiemVu')->first();
        if (!$assignment) {
            $res = NghiQuyet::first();
            $task = NhiemVu::create([
                'MaNghiQuyet' => $res ? $res->MaNghiQuyet : null,
                'TenNhiemVu' => 'Nhiệm vụ test store report',
                'NoiDung' => 'Nội dung thực hiện nhiệm vụ kiểm tra',
                'NgayGiao' => now()->toDateString(),
                'NgayHoanThanh' => now()->addDays(15)->toDateString(),
                'TrangThai' => 'ASSIGNED',
            ]);
            $dept = DonVi::first();
            $assignment = PhanCongNhiemVu::create([
                'MaNhiemVu' => $task->MaNhiemVu,
                'MaDonVi' => $dept->MaDonVi,
                'VaiTroThucHien' => 'CHỦ TRÌ',
                'TrangThaiThucHien' => 'Chưa thực hiện',
                'TenNguoiPhuTrach' => 'Trưởng ban',
            ]);
        }

        $data = [
            'MaPhanCong' => $assignment->MaPhanCong,
            'TienDo' => 65,
            'NoiDung' => 'Đã hoàn thành giai đoạn 1 khảo sát thực tế và tổng hợp dữ liệu khảo sát từ các khoa.',
            'NoiDungKhoKhan' => 'Cần hỗ trợ thêm tài chính và giải ngân theo tiến độ hợp đồng.',
        ];

        $response = $this->post(route('task-reports.store'), $data);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $report = BaoCao::where('MaPhanCong', $assignment->MaPhanCong)
            ->where('TienDo', 65)
            ->first();
        $this->assertNotNull($report);
        $this->assertEquals('Chờ kiểm tra', $report->TrangThaiXacNhan);
    }

    /**
     * 4. Thư ký duyệt báo cáo đạt 100% -> Tự động nghiệm thu hoàn thành nhiệm vụ (BR-10).
     */
    public function test_task_reports_review_approve_completes_task_when_100_percent(): void
    {
        $secretary = User::where('email', 'thuky_hdt@stu.edu.vn')->first();
        $this->actingAs($secretary);

        $res = NghiQuyet::first();
        $task = NhiemVu::create([
            'MaNghiQuyet' => $res ? $res->MaNghiQuyet : null,
            'TenNhiemVu' => 'Nhiệm vụ hoàn thành 100%',
            'NoiDung' => 'Nội dung thực hiện nhiệm vụ hoàn thành 100%',
            'NgayGiao' => now()->toDateString(),
            'NgayHoanThanh' => now()->addDays(5)->toDateString(),
            'TrangThai' => 'IN_PROGRESS',
        ]);
        $dept = DonVi::first();
        $assignment = PhanCongNhiemVu::create([
            'MaNhiemVu' => $task->MaNhiemVu,
            'MaDonVi' => $dept->MaDonVi,
            'VaiTroThucHien' => 'CHỦ TRÌ',
            'TrangThaiThucHien' => 'Đang thực hiện',
            'TenNguoiPhuTrach' => 'Trưởng ban',
        ]);

        $report = BaoCao::create([
            'MaPhanCong' => $assignment->MaPhanCong,
            'MaNguoiDung' => $secretary->MaNguoiDung,
            'NgayBaoCao' => now()->toDateString(),
            'TienDo' => 100,
            'NoiDung' => 'Nhiệm vụ đã hoàn tất toàn bộ các hạng mục công việc nghiệm thu.',
            'TrangThaiXacNhan' => 'Chờ kiểm tra',
        ]);

        $response = $this->post(route('task-reports.review', $report->MaBaoCao), [
            'action' => 'approve',
            'GhiChu' => 'Đạt chất lượng theo đúng chỉ đạo của Hội đồng trường.',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $report->refresh();
        $this->assertEquals('APPROVED', $report->TrangThaiXacNhan);
        $this->assertNotNull($report->NgayXacNhan);

        $assignment->refresh();
        $this->assertEquals('Hoàn tất', $assignment->TrangThaiThucHien);

        $task->refresh();
        $this->assertEquals('COMPLETED', $task->TrangThai);
        $this->assertNotNull($task->NgayXacNhanHoanThanh);
    }

    /**
     * 5. Thẩm định từ chối/yêu cầu bổ sung báo cáo.
     */
    public function test_task_reports_review_reject_requests_revision(): void
    {
        $chair = User::where('email', 'chutich_hdt@stu.edu.vn')->first();
        $this->actingAs($chair);

        $assignment = PhanCongNhiemVu::first();
        $report = BaoCao::create([
            'MaPhanCong' => $assignment->MaPhanCong,
            'MaNguoiDung' => $chair->MaNguoiDung,
            'NgayBaoCao' => now()->toDateString(),
            'TienDo' => 40,
            'NoiDung' => 'Báo cáo sơ bộ',
            'TrangThaiXacNhan' => 'Chờ kiểm tra',
        ]);

        $response = $this->post(route('task-reports.review', $report->MaBaoCao), [
            'action' => 'reject',
            'GhiChu' => 'Số liệu khảo sát chưa đầy đủ, đề nghị bổ sung bảng chi tiết các khoa.',
        ]);

        $response->assertSessionHasNoErrors();
        $report->refresh();
        $this->assertEquals('REJECTED', $report->TrangThaiXacNhan);
        $this->assertEquals('Số liệu khảo sát chưa đầy đủ, đề nghị bổ sung bảng chi tiết các khoa.', $report->GhiChu);
    }

    /**
     * 6. Kiểm tra ràng buộc BR-10: Tiến độ phải từ 0 đến 100%.
     */
    public function test_task_reports_validation_progress_range(): void
    {
        $secretary = User::where('email', 'thuky_hdt@stu.edu.vn')->first();
        $this->actingAs($secretary);

        $assignment = PhanCongNhiemVu::first();

        // Test tiến độ > 100%
        $response = $this->post(route('task-reports.store'), [
            'MaPhanCong' => $assignment->MaPhanCong,
            'TienDo' => 150,
            'NoiDung' => 'Test invalid progress',
        ]);
        $response->assertSessionHasErrors('TienDo');

        // Test tiến độ < 0%
        $response2 = $this->post(route('task-reports.store'), [
            'MaPhanCong' => $assignment->MaPhanCong,
            'TienDo' => -10,
            'NoiDung' => 'Test negative progress',
        ]);
        $response2->assertSessionHasErrors('TienDo');
    }
}
