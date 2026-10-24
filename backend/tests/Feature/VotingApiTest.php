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

    /**
     * Kiểm tra nghiệp vụ biểu quyết theo ủy quyền và bảo mật bỏ phiếu kín
     */
    public function test_proxy_voting_and_secret_ballot_anonymity(): void
    {
        $secretary = User::where('email', 'thuky_hdt@stu.edu.vn')->first();
        $agenda = ChuongTrinhHop::first();
        $phienHop = $agenda->phienHop;

        // Lấy 2 thành viên HĐT khác nhau
        $members = ThanhVienHdt::take(2)->get();
        $this->assertCount(2, $members);

        $absentMember = $members[0]; // Người vắng mặt ủy quyền
        $presentMember = $members[1]; // Người có mặt nhận ủy quyền

        // Điểm danh có mặt cho người nhận ủy quyền
        $thamDu = ThamDu::updateOrCreate(
            ['MaPhienHop' => $phienHop->MaPhienHop, 'MaThanhVien' => $presentMember->MaThanhVien],
            ['TrangThaiThamDu' => 'PRESENT', 'ThoiDiemXacNhan' => now()]
        );

        // Tạo giấy ủy quyền hợp lệ
        $auth = \App\Models\UyQuyenBieuQuyet::create([
            'NgayUyQuyen' => now()->toDateString(),
            'PhamViUyQuyen' => 'Ủy quyền biểu quyết toàn bộ phiên họp',
            'TaiLieuUyQuyen' => 'giay_uy_quyen_test.pdf',
            'TrangThai' => 'Hợp lệ',
            'MaPhienHop' => $phienHop->MaPhienHop,
            'MaThanhVienUyQuyen' => $absentMember->MaThanhVien,
            'MaThanhVienNhanUyQuyen' => $presentMember->MaThanhVien,
        ]);

        // Tạo nội dung biểu quyết hình thức Bỏ phiếu kín (SECRET)
        $vote = NoiDungBieuQuyet::create([
            'NoiDung' => 'Nội dung test ủy quyền bỏ phiếu ' . uniqid(),
            'ThoiGianMo' => now(),
            'ThoiGianDong' => now()->addHour(),
            'TrangThai' => 'OPENING',
            'KetQua' => 'PENDING',
            'GhiChu' => json_encode(['voting_type' => 'SECRET', 'pass_threshold' => 50]),
            'MaChuongTrinh' => $agenda->MaChuongTrinh,
        ]);

        $presentUser = $presentMember->nguoiDung;

        // 1. Người nhận ủy quyền thực hiện bỏ phiếu thay (proxy vote)
        $proxyResponse = $this->actingAs($presentUser, 'sanctum')->postJson("/api/v1/votings/{$vote->MaNoiDung}/vote", [
            'choice' => 'VOTE_FOR',
            'authorization_id' => $auth->MaUyQuyen,
        ]);

        $proxyResponse->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.is_proxy', true);

        $this->assertDatabaseHas('ket_qua_bieu_quyet', [
            'MaNoiDung' => $vote->MaNoiDung,
            'MaUyQuyen' => $auth->MaUyQuyen,
            'LuaChon' => 'VOTE_FOR',
        ]);

        // 2. Không được bỏ phiếu lần 2 cho cùng một giấy ủy quyền
        $duplicateProxy = $this->actingAs($presentUser, 'sanctum')->postJson("/api/v1/votings/{$vote->MaNoiDung}/vote", [
            'choice' => 'VOTE_AGAINST',
            'authorization_id' => $auth->MaUyQuyen,
        ]);
        $duplicateProxy->assertStatus(422);

        // 3. Kiểm tra bảo mật danh tính khi xem chi tiết biểu quyết bỏ phiếu kín
        $showResponse = $this->actingAs($presentUser, 'sanctum')->getJson("/api/v1/votings/{$vote->MaNoiDung}");
        $showResponse->assertStatus(200)
            ->assertJsonPath('success', true);

        $ballots = $showResponse->json('data.ballots');
        $this->assertNotEmpty($ballots);
        // Với bỏ phiếu kín: Lựa chọn phải là SECRET và tên cử tri phải ẩn danh
        $this->assertEquals('SECRET', $ballots[0]['choice']);
        $this->assertStringContainsString('Phiếu biểu quyết ẩn danh', $ballots[0]['voter_name']);
    }
}
