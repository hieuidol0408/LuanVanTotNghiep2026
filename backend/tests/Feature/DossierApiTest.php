<?php

namespace Tests\Feature;

use App\Models\HoSoTrinh;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DossierApiTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Khách vãng lai không được phép truy cập hồ sơ trình
     */
    public function test_unauthenticated_cannot_access_dossiers(): void
    {
        $response = $this->getJson('/api/v1/dossiers');
        $response->assertStatus(401);
    }

    /**
     * Đại diện đơn vị có thể tạo hồ sơ trình ở trạng thái DRAFT
     */
    public function test_unit_rep_can_create_draft_dossier(): void
    {
        $unitRep = User::where('email', 'daotao@stu.edu.vn')->first();
        $this->assertNotNull($unitRep);

        $payload = [
            'title' => 'Tờ trình về việc mở ngành đào tạo Công nghệ Vi mạch bán dẫn',
            'summary' => 'Đề xuất thành lập ngành đào tạo chuyên sâu về thiết kế và kiểm thử vi mạch bán dẫn.',
            'field' => 'Đào tạo & Công nghệ cao',
            'notes' => 'Hồ sơ chuẩn bị cho năm học 2026-2027',
        ];

        $response = $this->actingAs($unitRep, 'sanctum')->postJson('/api/v1/dossiers', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.title', $payload['title'])
            ->assertJsonPath('data.status', 'DRAFT');

        $this->assertDatabaseHas('ho_so_trinh', [
            'TieuDe' => $payload['title'],
            'TrangThai' => 'DRAFT',
            'MaNguoiDung' => $unitRep->MaNguoiDung,
        ]);
    }

    /**
     * Đại diện đơn vị có thể nộp hồ sơ chính thức (DRAFT -> SUBMITTED)
     */
    public function test_unit_rep_can_submit_dossier(): void
    {
        $unitRep = User::where('email', 'daotao@stu.edu.vn')->first();
        $dossier = HoSoTrinh::create([
            'TieuDe' => 'Tờ trình điều chỉnh mức học phí năm 2026',
            'NoiDungTomTat' => 'Điều chỉnh học phí theo lộ trình tự chủ',
            'NgayTrinh' => now()->toDateString(),
            'LinhVuc' => 'Tài chính',
            'TrangThai' => 'DRAFT',
            'MaDonVi' => $unitRep->MaDonVi,
            'MaNguoiDung' => $unitRep->MaNguoiDung,
        ]);

        $response = $this->actingAs($unitRep, 'sanctum')->postJson("/api/v1/dossiers/{$dossier->MaHoSo}/submit");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'SUBMITTED');

        $this->assertDatabaseHas('ho_so_trinh', [
            'MaHoSo' => $dossier->MaHoSo,
            'TrangThai' => 'SUBMITTED',
        ]);
    }

    /**
     * Quy tắc BR-DOS-03: Không cho phép chỉnh sửa hồ sơ khi đã ở trạng thái SUBMITTED
     */
    public function test_cannot_edit_submitted_dossier(): void
    {
        $unitRep = User::where('email', 'daotao@stu.edu.vn')->first();
        $dossier = HoSoTrinh::create([
            'TieuDe' => 'Tờ trình đã nộp',
            'NoiDungTomTat' => 'Nội dung',
            'NgayTrinh' => now()->toDateString(),
            'LinhVuc' => 'Đào tạo',
            'TrangThai' => 'SUBMITTED',
            'MaDonVi' => $unitRep->MaDonVi,
            'MaNguoiDung' => $unitRep->MaNguoiDung,
        ]);

        $response = $this->actingAs($unitRep, 'sanctum')->putJson("/api/v1/dossiers/{$dossier->MaHoSo}", [
            'title' => 'Tờ trình sửa đổi sau khi nộp',
            'summary' => 'Nội dung sửa',
            'field' => 'Đào tạo',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    /**
     * Thư ký HĐT có thể thẩm tra hồ sơ: Duyệt đạt (APPROVED_FOR_MEETING)
     */
    public function test_secretary_can_review_and_approve_dossier(): void
    {
        $secretary = User::where('email', 'thuky_hdt@stu.edu.vn')->first();
        $dossier = HoSoTrinh::create([
            'TieuDe' => 'Hồ sơ chờ duyệt trình phiên họp',
            'NoiDungTomTat' => 'Nội dung chờ duyệt',
            'NgayTrinh' => now()->toDateString(),
            'LinhVuc' => 'Nhân sự',
            'TrangThai' => 'SUBMITTED',
            'MaDonVi' => 1,
            'MaNguoiDung' => 1,
        ]);

        $payload = [
            'decision' => 'APPROVED',
            'comments' => 'Hồ sơ đầy đủ thủ tục pháp lý, thống nhất trình HĐT.',
        ];

        $response = $this->actingAs($secretary, 'sanctum')->postJson("/api/v1/dossiers/{$dossier->MaHoSo}/review", $payload);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'APPROVED_FOR_MEETING');

        $this->assertDatabaseHas('ho_so_trinh', [
            'MaHoSo' => $dossier->MaHoSo,
            'TrangThai' => 'APPROVED_FOR_MEETING',
        ]);
    }

    /**
     * Vai trò không có thẩm quyền (UNIT_REP) không được phép thẩm tra hồ sơ (403 Forbidden)
     */
    public function test_unauthorized_role_cannot_review_dossier(): void
    {
        $unitRep = User::where('email', 'daotao@stu.edu.vn')->first();
        $dossier = HoSoTrinh::create([
            'TieuDe' => 'Hồ sơ kiểm tra phân quyền',
            'NoiDungTomTat' => 'Nội dung',
            'NgayTrinh' => now()->toDateString(),
            'LinhVuc' => 'Tài chính',
            'TrangThai' => 'SUBMITTED',
            'MaDonVi' => $unitRep->MaDonVi,
            'MaNguoiDung' => $unitRep->MaNguoiDung,
        ]);

        $response = $this->actingAs($unitRep, 'sanctum')->postJson("/api/v1/dossiers/{$dossier->MaHoSo}/review", [
            'decision' => 'APPROVED',
        ]);

        $response->assertStatus(403);
    }
}
