<?php

namespace Tests\Feature;

use App\Models\ChuongTrinhHop;
use App\Models\NoiDungBieuQuyet;
use App\Models\PhienHop;
use App\Models\ThamDu;
use App\Models\ThanhVienHdt;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class VotingApiTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Khách vãng lai không được truy cập nội dung biểu quyết
     */
    public function test_unauthenticated_cannot_access_voting(): void
    {
        $response = $this->getJson('/api/v1/meetings/1/votings');
        $response->assertStatus(401);
    }

    /**
     * Thư ký hoặc Chủ tịch có thể tạo nội dung biểu quyết cho phiên họp
     */
    public function test_secretary_can_create_voting_content(): void
    {
        $secretary = User::where('email', 'thuky_hdt@stu.edu.vn')->first();
        $agenda = ChuongTrinhHop::first();
        $this->assertNotNull($agenda);

        $payload = [
            'agenda_id' => $agenda->MaChuongTrinh,
            'title' => 'Biểu quyết thông qua Đề án thành lập Trung tâm Đào tạo Kỹ thuật Bán dẫn STU',
            'voting_type' => 'SECRET',
            'pass_threshold' => 65,
            'notes' => 'Yêu cầu tối thiểu 65% thành viên tán thành',
        ];

        $response = $this->actingAs($secretary, 'sanctum')->postJson("/api/v1/meetings/{$agenda->MaPhienHop}/votings", $payload);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.title', $payload['title'])
            ->assertJsonPath('data.status', 'PENDING');

        $this->assertDatabaseHas('noi_dung_bieu_quyet', [
            'NoiDung' => $payload['title'],
            'TrangThai' => 'PENDING',
        ]);
    }

    /**
     * Thư ký có thể mở cổng biểu quyết (OPENING)
     */
    public function test_secretary_can_open_voting(): void
    {
        $secretary = User::where('email', 'thuky_hdt@stu.edu.vn')->first();
        $vote = NoiDungBieuQuyet::first();
        $this->assertNotNull($vote);

        $response = $this->actingAs($secretary, 'sanctum')->postJson("/api/v1/votings/{$vote->MaNoiDung}/open");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'OPENING');

        $this->assertDatabaseHas('noi_dung_bieu_quyet', [
            'MaNoiDung' => $vote->MaNoiDung,
            'TrangThai' => 'OPENING',
        ]);
    }

    /**
     * Thành viên HĐT có mặt (PRESENT) có thể gửi phiếu biểu quyết và bị từ chối nếu bỏ lần 2
     */
    public function test_present_member_can_vote_and_duplicate_is_rejected(): void
    {
        $member = User::where('email', 'thanhvien_hdt@stu.edu.vn')->first();
        $agenda = ChuongTrinhHop::first();
        $this->assertNotNull($agenda);

        $vote = NoiDungBieuQuyet::create([
            'NoiDung' => 'Nội dung test biểu quyết độc lập ' . uniqid(),
            'ThoiGianMo' => now(),
            'ThoiGianDong' => now()->addHour(),
            'TrangThai' => 'OPENING',
            'KetQua' => 'PENDING',
            'MaChuongTrinh' => $agenda->MaChuongTrinh,
        ]);

        // Đảm bảo thành viên này đã được điểm danh có mặt trong phiên họp
        $phienHop = $agenda->phienHop;
        $thanhVien = ThanhVienHdt::where('MaNguoiDung', $member->MaNguoiDung)->first();
        ThamDu::updateOrCreate(
            ['MaPhienHop' => $phienHop->MaPhienHop, 'MaThanhVien' => $thanhVien->MaThanhVien],
            ['TrangThaiThamDu' => 'PRESENT', 'ThoiDiemXacNhan' => now()]
        );

        // 1. Lần bỏ phiếu thứ nhất: Thành công
        $firstVote = $this->actingAs($member, 'sanctum')->postJson("/api/v1/votings/{$vote->MaNoiDung}/vote", [
            'choice' => 'VOTE_FOR',
        ]);

        $firstVote->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.has_voted', true);

        // 2. Lần bỏ phiếu thứ hai: Bị từ chối ngay lập tức (Single vote constraint)
        $secondVote = $this->actingAs($member, 'sanctum')->postJson("/api/v1/votings/{$vote->MaNoiDung}/vote", [
            'choice' => 'VOTE_AGAINST',
        ]);

        $secondVote->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    /**
     * Khách mời hoặc vai trò ngoài HĐT (UNIT_REP) bị chặn bỏ phiếu (403 Forbidden)
     */
    public function test_guest_or_external_unit_cannot_vote(): void
    {
        $unitRep = User::where('email', 'daotao@stu.edu.vn')->first();
        $vote = NoiDungBieuQuyet::first();
        $vote->update(['TrangThai' => 'OPENING', 'ThoiGianMo' => now()]);

        $response = $this->actingAs($unitRep, 'sanctum')->postJson("/api/v1/votings/{$vote->MaNoiDung}/vote", [
            'choice' => 'VOTE_FOR',
        ]);

        $response->assertStatus(403);
    }

    /**
     * Thư ký đóng biểu quyết và tự động tổng hợp kết quả (PASSED)
     */
    public function test_close_voting_and_calculate_passed_result(): void
    {
        $secretary = User::where('email', 'thuky_hdt@stu.edu.vn')->first();
        $vote = NoiDungBieuQuyet::first();
        $vote->update(['TrangThai' => 'OPENING', 'ThoiGianMo' => now()]);

        // Giả lập bỏ phiếu tán thành
        $thanhVien = ThanhVienHdt::where('MaNguoiDung', $secretary->MaNguoiDung)->first();
        $thamDu = ThamDu::where('MaThanhVien', $thanhVien->MaThanhVien)->first();

        \App\Models\KetQuaBieuQuyet::updateOrCreate(
            ['MaNoiDung' => $vote->MaNoiDung, 'MaThamDu' => $thamDu->MaThamDu],
            ['LuaChon' => 'VOTE_FOR', 'ThoiDiem' => now()]
        );

        $response = $this->actingAs($secretary, 'sanctum')->postJson("/api/v1/votings/{$vote->MaNoiDung}/close");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'CLOSED');

        $this->assertDatabaseHas('noi_dung_bieu_quyet', [
            'MaNoiDung' => $vote->MaNoiDung,
            'TrangThai' => 'CLOSED',
        ]);
    }
}
