<?php

namespace Tests\Feature;

use App\Models\NoiDungBieuQuyet;
use App\Models\PhienHop;
use App\Models\ThamDu;
use App\Models\ThanhVienHdt;
use App\Models\User;
use App\Models\UyQuyenBieuQuyet;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class VoteWebTest extends TestCase
{
    /**
     * 1. Danh sách biểu quyết hiển thị đầy đủ thẻ KPI, bộ lọc và bảng dữ liệu.
     */
    public function test_votes_index_renders_successfully(): void
    {
        $chair = User::where('email', 'chutich_hdt@stu.edu.vn')->first();
        $this->actingAs($chair);

        $response = $this->get(route('votes.index'));

        $response->assertStatus(200);
        $response->assertSee('Biểu quyết &amp; Bỏ phiếu', false);
        $response->assertSee('Tổng nội dung BQ');
        $response->assertSee('Đang mở cổng bỏ phiếu');
        $response->assertSee('Đã kết thúc kiểm phiếu');
        $response->assertSee('Tìm kiếm');
    }

    /**
     * 2. Chi tiết biểu quyết hiển thị đúng cam kết bảo mật bỏ phiếu kín (BR-09).
     */
    public function test_votes_show_renders_detail_and_secret_ballot_guarantee(): void
    {
        $vote = NoiDungBieuQuyet::first();
        if (! $vote) {
            $this->markTestSkipped('Chưa có nội dung biểu quyết.');
        }

        $chair = User::where('email', 'chutich_hdt@stu.edu.vn')->first();
        $this->actingAs($chair);

        $response = $this->get(route('votes.show', $vote->MaNoiDung));

        $response->assertStatus(200);
        $response->assertSee('Bảo mật Bỏ phiếu kín (Ràng buộc BR-09)');
        $response->assertSee('Kết quả Kiểm phiếu Tổng hợp');
        $response->assertSee('Ngưỡng yêu cầu');
    }

    /**
     * 3. Mở và Đóng cổng biểu quyết theo quyền của Chủ tịch / Thư ký.
     */
    public function test_votes_open_and_close_by_chair(): void
    {
        $chair = User::where('email', 'chutich_hdt@stu.edu.vn')->first();
        $this->actingAs($chair);

        $vote = NoiDungBieuQuyet::first();
        if (! $vote) {
            $this->markTestSkipped('Chưa có nội dung biểu quyết.');
        }

        // Mở biểu quyết
        $openResponse = $this->post(route('votes.open', $vote->MaNoiDung));
        $openResponse->assertRedirect();
        $openResponse->assertSessionHas('success');

        $vote->refresh();
        $this->assertEquals('OPENING', $vote->TrangThai);

        // Đóng biểu quyết
        $closeResponse = $this->post(route('votes.close', $vote->MaNoiDung));
        $closeResponse->assertRedirect();
        $closeResponse->assertSessionHas('success');

        $vote->refresh();
        $this->assertEquals('CLOSED', $vote->TrangThai);
    }

    /**
     * 4. Bỏ phiếu trực tiếp: Thành viên HĐT có mặt (PRESENT) gửi phiếu thành công (BR-11 MaUyQuyen = NULL).
     */
    public function test_votes_cast_direct_vote_successfully(): void
    {
        $vote = NoiDungBieuQuyet::with('chuongTrinhHop.phienHop')->first();
        if (! $vote) {
            $this->markTestSkipped('Chưa có nội dung biểu quyết.');
        }

        // Đảm bảo biểu quyết đang mở
        $vote->update(['TrangThai' => 'OPENING']);

        $meeting = $vote->chuongTrinhHop?->phienHop;

        // Tìm thành viên HĐT có mặt tại phiên họp
        $attendance = ThamDu::where('MaPhienHop', $meeting->MaPhienHop)
            ->whereIn('TrangThaiThamDu', ['PRESENT', 'Có mặt'])
            ->first();

        if (! $attendance) {
            $member = ThanhVienHdt::first();
            $attendance = ThamDu::firstOrCreate([
                'MaPhienHop' => $meeting->MaPhienHop,
                'MaThanhVien' => $member->MaThanhVien,
            ], [
                'TrangThaiThamDu' => 'PRESENT',
            ]);
            $attendance->update(['TrangThaiThamDu' => 'PRESENT']);
        }

        $voter = User::find($attendance->thanhVienHdt?->MaNguoiDung);
        if (! $voter) {
            $this->markTestSkipped('Không tìm thấy tài khoản cử tri.');
        }

        // Xóa phiếu cũ nếu có để test sạch
        DB::table('ket_qua_bieu_quyet')
            ->where('MaNoiDung', $vote->MaNoiDung)
            ->where('MaThamDu', $attendance->MaThamDu)
            ->whereNull('MaUyQuyen')
            ->delete();

        $this->actingAs($voter);

        $response = $this->post(route('votes.vote', $vote->MaNoiDung), [
            'choice' => 'Tán thành',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Kiểm tra trong CSDL: MaUyQuyen bắt buộc NULL (BR-11)
        $ballot = DB::table('ket_qua_bieu_quyet')
            ->where('MaNoiDung', $vote->MaNoiDung)
            ->where('MaThamDu', $attendance->MaThamDu)
            ->whereNull('MaUyQuyen')
            ->first();

        $this->assertNotNull($ballot);
        $this->assertEquals('Tán thành', $ballot->LuaChon);
    }

    /**
     * 5. Chống bỏ phiếu trùng lặp: Một cử tri chỉ được bỏ phiếu trực tiếp 1 lần duy nhất.
     */
    public function test_votes_prevents_duplicate_direct_vote(): void
    {
        $vote = NoiDungBieuQuyet::with('chuongTrinhHop.phienHop')->first();
        if (! $vote) {
            $this->markTestSkipped('Chưa có nội dung biểu quyết.');
        }

        $vote->update(['TrangThai' => 'OPENING']);
        $meeting = $vote->chuongTrinhHop?->phienHop;

        $attendance = ThamDu::where('MaPhienHop', $meeting->MaPhienHop)
            ->whereIn('TrangThaiThamDu', ['PRESENT', 'Có mặt'])
            ->first();

        if ($attendance) {
            $voter = User::find($attendance->thanhVienHdt?->MaNguoiDung);

            // Tạo sẵn 1 lá phiếu
            DB::table('ket_qua_bieu_quyet')->updateOrInsert([
                'MaNoiDung' => $vote->MaNoiDung,
                'MaThamDu' => $attendance->MaThamDu,
                'MaUyQuyen' => null,
            ], [
                'LuaChon' => 'Tán thành',
                'ThoiDiem' => now(),
            ]);

            $this->actingAs($voter);

            // Thử bỏ phiếu lần thứ 2
            $response = $this->post(route('votes.vote', $vote->MaNoiDung), [
                'choice' => 'Không tán thành',
            ]);

            $response->assertSessionHas('warning');
        }
    }

    /**
     * 6. Đăng ký ủy quyền biểu quyết kiểm tra ràng buộc không được tự ủy quyền cho chính mình (BR-04).
     */
    public function test_votes_delegation_creation_validation(): void
    {
        $chair = User::where('email', 'chutich_hdt@stu.edu.vn')->first();
        $this->actingAs($chair);

        $member = ThanhVienHdt::first();

        $response = $this->post(route('votes.delegations.store'), [
            'MaPhienHop' => 1,
            'MaThanhVienUyQuyen' => $member->MaThanhVien,
            'MaThanhVienNhanUyQuyen' => $member->MaThanhVien, // Trùng nhau
            'PhamViUyQuyen' => 'Kiểm thử lỗi trùng người',
            'NgayUyQuyen' => now()->toDateString(),
        ]);

        $response->assertSessionHasErrors(['MaThanhVienNhanUyQuyen']);
    }

    /**
     * 7. Tạo mới nội dung biểu quyết trực tiếp trên giao diện Web.
     */
    public function test_votes_store_creates_new_voting_item(): void
    {
        $secretary = User::where('email', 'thuky_hdt@stu.edu.vn')->first();
        $this->actingAs($secretary);

        $agenda = \App\Models\ChuongTrinhHop::first();
        if (! $agenda) {
            $this->markTestSkipped('Chưa có mục chương trình họp.');
        }

        $topicTitle = 'Thông qua kế hoạch chiến lược chuyển đổi số STU ' . uniqid();

        $response = $this->post(route('votes.store'), [
            'MaChuongTrinh' => $agenda->MaChuongTrinh,
            'NoiDung' => $topicTitle,
            'voting_type' => 'SECRET',
            'pass_threshold' => 50,
            'notes' => 'Bỏ phiếu kín theo quy chế Hội đồng trường.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('noi_dung_bieu_quyet', [
            'MaChuongTrinh' => $agenda->MaChuongTrinh,
            'NoiDung' => $topicTitle,
            'TrangThai' => 'PENDING',
        ]);
    }
}
