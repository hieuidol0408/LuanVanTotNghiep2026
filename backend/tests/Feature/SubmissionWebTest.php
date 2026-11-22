<?php

namespace Tests\Feature;

use App\Models\DonVi;
use App\Models\HoSoTrinh;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SubmissionWebTest extends TestCase
{
    /**
     * 1. Danh sách hồ sơ trình hiển thị thành công với bộ lọc và bảng dữ liệu.
     */
    public function test_submission_index_renders_successfully(): void
    {
        $chair = User::where('email', 'chutich_hdt@stu.edu.vn')->first();
        $this->actingAs($chair);

        $response = $this->get(route('submissions.index'));

        $response->assertStatus(200);
        $response->assertSee('Hồ sơ trình Hội đồng trường');
        $response->assertSee('Tìm kiếm');
        $response->assertSee('Đơn vị trình');
        $response->assertSee('Lĩnh vực');
        $response->assertSee('Trạng thái');
    }

    /**
     * 2. Biểu mẫu tạo mới hồ sơ trình hiển thị đúng các trường nghiệp vụ.
     */
    public function test_submission_create_renders_form(): void
    {
        $secretary = User::where('email', 'thuky_hdt@stu.edu.vn')->first();
        $this->actingAs($secretary);

        $response = $this->get(route('submissions.create'));

        $response->assertStatus(200);
        $response->assertSee('Tạo mới Hồ sơ trình');
        $response->assertSee('Tiêu đề hồ sơ trình');
        $response->assertSee('Đơn vị trình');
        $response->assertSee('Lĩnh vực');
        $response->assertSee('Tài liệu đính kèm');
    }

    /**
     * 3. Lưu hồ sơ trình mới kèm tài liệu đính kèm (Lưu trữ an toàn Laravel Storage).
     */
    public function test_submission_store_creates_submission_with_attached_file(): void
    {
        Storage::fake('public');

        $secretary = User::where('email', 'thuky_hdt@stu.edu.vn')->first();
        $this->actingAs($secretary);

        $department = DonVi::first();
        $file = UploadedFile::fake()->create('to_trinh_thi_nghiem.pdf', 1024, 'application/pdf');

        $response = $this->post(route('submissions.store'), [
            'TieuDe' => 'Tờ trình kiểm thử tự động lưu trữ tài liệu ' . uniqid(),
            'NoiDungTomTat' => 'Căn cứ nhu cầu nâng cấp hạ tầng thực hành, đơn vị kính trình Hội đồng trường xem xét.',
            'LinhVuc' => 'Cơ sở vật chất & Đầu tư xây dựng',
            'MaDonVi' => $department->MaDonVi,
            'file' => $file,
        ]);

        $response->assertRedirect();

        // Kiểm tra hồ sơ được tạo trong CSDL
        $dossier = HoSoTrinh::where('LinhVuc', 'Cơ sở vật chất & Đầu tư xây dựng')
            ->orderBy('MaHoSo', 'desc')
            ->first();

        $this->assertNotNull($dossier);
        $this->assertEquals('Mới tạo', $dossier->TrangThai);

        // Kiểm tra tài liệu được liên kết qua bảng tai_lieu_ho_so
        $linkedDoc = DB::table('tai_lieu_ho_so')
            ->where('MaHoSo', $dossier->MaHoSo)
            ->first();

        $this->assertNotNull($linkedDoc);
    }

    /**
     * 4. Phân quyền cô lập dữ liệu theo Đơn vị trình (RBAC Unit Isolation).
     */
    public function test_submission_unit_isolation_rbac(): void
    {
        $unitRepUser = User::where('email', 'daotao@stu.edu.vn')->first();
        if (! $unitRepUser) {
            $this->markTestSkipped('Chưa có tài khoản đại diện đơn vị để kiểm thử.');
        }

        // Chọn đơn vị khác với đơn vị của daotao@stu.edu.vn (MaDonVi = 5)
        $diffDept = DonVi::where('MaDonVi', '!=', $unitRepUser->MaDonVi)->first();
        if (! $diffDept) {
            $this->markTestSkipped('Cần ít nhất 2 đơn vị để kiểm thử.');
        }

        // Tạo hồ sơ thuộc đơn vị khác
        $dossierOther = HoSoTrinh::create([
            'TieuDe' => 'Hồ sơ bảo mật của đơn vị khác ' . uniqid(),
            'NoiDungTomTat' => 'Nội dung nội bộ không được chia sẻ cho đơn vị khác.',
            'NgayTrinh' => now()->toDateString(),
            'LinhVuc' => 'Tài chính & Kế hoạch ngân sách',
            'TrangThai' => 'Mới tạo',
            'MaDonVi' => $diffDept->MaDonVi,
            'MaNguoiDung' => 1,
        ]);

        // Đăng nhập với tài khoản UNIT_REP
        $this->actingAs($unitRepUser);

        // Cố gắng truy cập hồ sơ của đơn vị khác
        $response = $this->get(route('submissions.show', $dossierOther->MaHoSo));

        // Phải bị chuyển hướng về danh sách kèm thông báo lỗi bảo mật
        $response->assertRedirect(route('submissions.index'));
        $response->assertSessionHas('error');
    }

    /**
     * 5. Quy trình thẩm tra hồ sơ: Thư ký thẩm định đạt "Đủ điều kiện" (BR-08).
     */
    public function test_submission_review_flow_updates_status(): void
    {
        $secretary = User::where('email', 'thuky_hdt@stu.edu.vn')->first();
        $this->actingAs($secretary);

        $dossier = HoSoTrinh::first();
        if (! $dossier) {
            $this->markTestSkipped('Chưa có hồ sơ trình để thẩm tra.');
        }

        $response = $this->post(route('submissions.review', $dossier->MaHoSo), [
            'decision' => 'ELIGIBLE',
            'GhiChu' => 'Hồ sơ đạt tiêu chuẩn, đồng ý chuyển sang chương trình họp phiên kế tiếp.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $dossier->refresh();
        $this->assertEquals('Đủ điều kiện', $dossier->TrangThai);
    }

    /**
     * 6. Cập nhật / Chỉnh sửa hồ sơ trình thành công trên giao diện Web.
     */
    public function test_submission_edit_and_update_works(): void
    {
        $secretary = User::where('email', 'thuky_hdt@stu.edu.vn')->first();
        $this->actingAs($secretary);

        $dossier = HoSoTrinh::first();
        if (! $dossier) {
            $this->markTestSkipped('Chưa có hồ sơ trình để cập nhật.');
        }

        // Kiểm tra hiển thị form edit (Chuyển hướng mở modal chỉnh sửa)
        $editResponse = $this->get(route('submissions.edit', $dossier->MaHoSo));
        $editResponse->assertRedirect(route('submissions.show', ['id' => $dossier->MaHoSo, 'edit' => 1]));

        $showResponse = $this->get(route('submissions.show', ['id' => $dossier->MaHoSo, 'edit' => 1]));
        $showResponse->assertStatus(200);

        // Kiểm tra lưu cập nhật kèm tài liệu word/excel/powerpoint
        $newTitle = 'Tờ trình cập nhật hoàn thiện thuyết minh ' . uniqid();
        $fakeDoc = UploadedFile::fake()->create('bao_cao_thuyet_minh.docx', 500);
        $updateResponse = $this->put(route('submissions.update', $dossier->MaHoSo), [
            'TieuDe' => $newTitle,
            'NoiDungTomTat' => 'Nội dung đã được bổ sung và hoàn thiện theo ý kiến thẩm tra.',
            'LinhVuc' => 'Đào tạo & Đảm bảo chất lượng',
            'MaDonVi' => $dossier->MaDonVi,
            'file' => $fakeDoc,
            'GhiChu' => 'Đã cập nhật thuyết minh bổ sung bằng file Word.',
        ]);

        $updateResponse->assertRedirect(route('submissions.show', $dossier->MaHoSo));
        $this->assertDatabaseHas('ho_so_trinh', [
            'MaHoSo' => $dossier->MaHoSo,
            'TieuDe' => $newTitle,
        ]);
    }

    /**
     * 7. Kiểm tra xác thực định dạng file (Chỉ chấp nhận PDF, Word, Excel, PowerPoint - Chặn các định dạng khác).
     */
    public function test_submission_rejects_invalid_file_formats(): void
    {
        $secretary = User::where('email', 'thuky_hdt@stu.edu.vn')->first();
        $this->actingAs($secretary);

        $department = DonVi::first();

        // Thử upload file exe nguy hiểm
        $badFile = UploadedFile::fake()->create('malicious.exe', 1024);
        $resBad = $this->post(route('submissions.store'), [
            'TieuDe' => 'Tờ trình thử nghiệm file độc hại',
            'NoiDungTomTat' => 'Thử nghiệm hệ thống bảo mật tệp.',
            'LinhVuc' => 'Khoa học công nghệ & Dự án',
            'MaDonVi' => $department->MaDonVi,
            'file' => $badFile,
        ]);
        $resBad->assertSessionHasErrors(['file']);

        // Upload file PowerPoint (.pptx) và Excel (.xlsx) hợp lệ
        $pptxFile = UploadedFile::fake()->create('slide_trinh_bay.pptx', 1024);
        $resGood = $this->post(route('submissions.store'), [
            'TieuDe' => 'Tờ trình đính kèm slide PowerPoint ' . uniqid(),
            'NoiDungTomTat' => 'Thuyết minh kèm bài trình chiếu slide PowerPoint.',
            'LinhVuc' => 'Khoa học công nghệ & Dự án',
            'MaDonVi' => $department->MaDonVi,
            'file' => $pptxFile,
        ]);
        $resGood->assertSessionHasNoErrors();
        $resGood->assertRedirect();
    }
}
