<?php

namespace Tests\Feature;

use App\Models\PhienHop;
use App\Models\ThanhVienHDT;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MeetingWebTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $secretary = User::where('email', 'thuky_hdt@stu.edu.vn')->first();
        if ($secretary) {
            $this->actingAs($secretary);
        }
    }

    /**
     * 1. Danh sách phiên họp hiển thị thành công với bộ lọc và danh sách dữ liệu.
     */
    public function test_meeting_index_renders_successfully(): void
    {
        $response = $this->get(route('meetings.index'));

        $response->assertStatus(200);
        $response->assertSee('Danh sách Phiên Họp Hội Đồng Trường');
        $response->assertSee('Hình thức họp');
        $response->assertSee('Trạng thái họp');
        $response->assertSee('Tạo phiên họp mới');
    }

    /**
     * 2. Biểu mẫu tạo mới phiên họp hiển thị đầy đủ các trường thông tin.
     */
    public function test_meeting_create_renders_form(): void
    {
        $response = $this->get(route('meetings.create'));

        $response->assertStatus(200);
        $response->assertSee('Lập Kế Hoạch');
        $response->assertSee('Tên phiên họp');
        $response->assertSee('Ngày tổ chức họp');
        $response->assertSee('Giờ bắt đầu');
        $response->assertSee('Giờ kết thúc');
        $response->assertSee('Địa điểm tổ chức');
        $response->assertSee('Thành viên Hội đồng trường tham gia');
    }

    /**
     * 3. Ràng buộc thời gian: Thời gian kết thúc phải sau thời gian bắt đầu.
     */
    public function test_meeting_store_validates_end_time_after_start_time(): void
    {
        $memberIds = ThanhVienHdt::pluck('MaThanhVien')->take(3)->toArray();

        $response = $this->post(route('meetings.store'), [
            'TenPhienHop' => 'Phiên họp kiểm thử thời gian lỗi',
            'NgayHop' => Carbon::now()->addDays(2)->toDateString(),
            'GioBatDau' => '14:00',
            'GioKetThuc' => '10:00', // Kết thúc trước khi bắt đầu
            'DiaDiem' => 'Phòng họp A1',
            'HinhThuc' => 'Trực tiếp',
            'member_ids' => $memberIds,
        ]);

        $response->assertSessionHasErrors(['GioKetThuc']);
    }

    /**
     * 4. Tạo phiên họp hợp lệ & Tự động gửi thư mời thành viên HĐT chính thức (BR-02).
     */
    public function test_meeting_store_creates_meeting_and_auto_invites_active_members(): void
    {
        $memberIds = ThanhVienHdt::pluck('MaThanhVien')->take(3)->toArray();

        $response = $this->post(route('meetings.store'), [
            'TenPhienHop' => 'Phiên họp kiểm thử tự động mời thành viên BR-02 ' . uniqid(),
            'NgayHop' => Carbon::now()->addDays(20)->toDateString(),
            'GioBatDau' => '08:30',
            'GioKetThuc' => '11:30',
            'DiaDiem' => 'Phòng họp Tân Triều STU',
            'HinhThuc' => 'Trực tiếp',
            'GhiChu' => 'Phiên họp định kỳ khóa IV',
            'member_ids' => $memberIds,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        // Kiểm tra phiên họp có trong CSDL
        $meeting = PhienHop::where('DiaDiem', 'Phòng họp Tân Triều STU')
            ->orderBy('MaPhienHop', 'desc')
            ->first();
        $this->assertNotNull($meeting);

        // Kiểm tra các thành viên HĐT đang hoạt động được tự động thêm vào bảng tham_du
        $activeMembersCount = ThanhVienHdt::count();
        if ($activeMembersCount > 0) {
            $invitedCount = DB::table('tham_du')
                ->where('MaPhienHop', $meeting->MaPhienHop)
                ->count();
            $this->assertGreaterThanOrEqual(1, $invitedCount);
        }
    }

    /**
     * 5. Xem chi tiết phiên họp hiển thị đầy đủ 7 Tab chuyên dụng & Kiểm tra biểu quyết kín (BR-09).
     */
    public function test_meeting_show_renders_7_tabs_and_secret_ballot_tally(): void
    {
        $meeting = PhienHop::first();
        if (! $meeting) {
            $this->markTestSkipped('Chưa có phiên họp trong cơ sở dữ liệu để kiểm thử.');
        }

        $response = $this->get(route('meetings.show', $meeting->MaPhienHop));

        $response->assertStatus(200);
        $response->assertSee('Thông tin chung');
        $response->assertSee('Thành viên & Điểm danh');
        $response->assertSee('Khách mời');
        $response->assertSee('Chương trình họp');
        $response->assertSee('Tài liệu phiên họp');
        $response->assertSee('Biểu quyết & Kết quả');
        $response->assertSee('Nghị quyết ban hành');
        $response->assertSee('nguyên tắc bỏ phiếu kín'); // BR-09
    }

    /**
     * 6. Chống trùng lặp thành viên tham dự trong cùng một phiên họp (BR-02).
     */
    public function test_meeting_add_member_prevents_duplicates(): void
    {
        $meeting = PhienHop::first();
        if (! $meeting) {
            $this->markTestSkipped('Chưa có phiên họp.');
        }

        $existingAttendance = DB::table('tham_du')
            ->where('MaPhienHop', $meeting->MaPhienHop)
            ->first();

        if ($existingAttendance) {
            // Thử thêm lại chính thành viên này
            $response = $this->post(route('meetings.members.add', $meeting->MaPhienHop), [
                'MaThanhVien' => $existingAttendance->MaThanhVien,
                'TrangThaiThamDu' => 'Đã gửi giấy mời',
            ]);

            $response->assertSessionHas('warning');
        }
    }

    /**
     * 7. Chống trùng lặp khách mời trong cùng một phiên họp (BR-03).
     */
    public function test_meeting_add_guest_prevents_duplicates(): void
    {
        $meeting = PhienHop::first();
        if (! $meeting) {
            $this->markTestSkipped('Chưa có phiên họp.');
        }

        $existingGuest = DB::table('khach_moi')
            ->join('khach_moi_phien_hop', 'khach_moi.MaKhachMoi', '=', 'khach_moi_phien_hop.MaKhachMoi')
            ->where('khach_moi_phien_hop.MaPhienHop', $meeting->MaPhienHop)
            ->first();

        if ($existingGuest) {
            // Thử thêm lại chính khách mời này
            $response = $this->post(route('meetings.guests.add', $meeting->MaPhienHop), [
                'HoTen' => $existingGuest->HoTen,
                'DonViCongTac' => $existingGuest->DonViCongTac,
                'NoiDungMoi' => 'Tham dự đóng góp ý kiến',
                'TrangThaiThamDu' => 'Đã gửi thư mời',
            ]);

            $response->assertSessionHas('warning');
        }
    }

    /**
     * 8. [BR-08] Chống xung đột trùng lịch họp: Thư ký và HĐT không thể họp 2 phiên cùng một khung giờ.
     */
    public function test_meeting_prevents_scheduling_conflict_overlapping_times(): void
    {
        $testDate = '2029-05-20';

        // Tạo phiên họp ban đầu: 10:00 - 10:30
        $meeting1 = PhienHop::create([
            'TenPhienHop' => 'Phiên họp kiểm thử lịch 1',
            'NgayHop' => $testDate,
            'ThoiGianBatDau' => "{$testDate} 10:00:00",
            'ThoiGianKetThuc' => "{$testDate} 10:30:00",
            'DiaDiem' => 'Phòng họp A',
            'HinhThuc' => 'IN_PERSON',
            'TrangThai' => 'Sắp diễn ra',
        ]);

        // Cố tình tạo phiên họp thứ 2 trùng giờ: 10:15 - 11:00
        $memberIds = ThanhVienHdt::pluck('MaThanhVien')->take(3)->toArray();

        $response = $this->post(route('meetings.store'), [
            'TenPhienHop' => 'Phiên họp kiểm thử xung đột trùng lịch',
            'NgayHop' => $testDate,
            'GioBatDau' => '10:15',
            'GioKetThuc' => '11:00',
            'DiaDiem' => 'Phòng họp B',
            'HinhThuc' => 'IN_PERSON',
            'member_ids' => $memberIds,
        ]);

        $response->assertSessionHasErrors('GioBatDau');

        // Tạo phiên họp tiếp nối hợp lệ: 10:30 - 11:30 (không giao nhau)
        $responseValid = $this->post(route('meetings.store'), [
            'TenPhienHop' => 'Phiên họp tiếp nối hợp lệ',
            'NgayHop' => $testDate,
            'GioBatDau' => '10:30',
            'GioKetThuc' => '11:30',
            'DiaDiem' => 'Phòng họp A',
            'HinhThuc' => 'IN_PERSON',
            'member_ids' => $memberIds,
        ]);

        $responseValid->assertSessionHasNoErrors();

        // Dọn dẹp dữ liệu test
        PhienHop::where('NgayHop', $testDate)->delete();
    }

    /**
     * 9. Cập nhật Chương trình họp (Thứ tự xem xét, Trạng thái xử lý, Ghi chú).
     */
    public function test_agenda_update_order_and_status_works(): void
    {
        $secretary = \App\Models\User::where('email', 'thuky_hdt@stu.edu.vn')->first();
        $this->actingAs($secretary);

        $agenda = \App\Models\ChuongTrinhHop::first();
        if (! $agenda) {
            $this->markTestSkipped('Chưa có mục chương trình họp.');
        }

        $response = $this->put(route('agendas.update', $agenda->MaChuongTrinh), [
            'ThuTu' => $agenda->ThuTu,
            'TrangThai' => 'Đã thông qua',
            'GhiChu' => 'Đã thảo luận và nhất trí thông qua tại phiên họp.',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $agenda->refresh();
        $this->assertEquals('Đã thông qua', $agenda->TrangThai);
        $this->assertEquals('Đã thảo luận và nhất trí thông qua tại phiên họp.', $agenda->GhiChu);
    }

    /**
     * 10. Ràng buộc dữ liệu: Cột Thứ Tự khi cập nhật không được trùng với mục khác trong cùng phiên họp.
     */
    public function test_agenda_update_blocks_duplicate_order_in_same_meeting(): void
    {
        $secretary = \App\Models\User::where('email', 'thuky_hdt@stu.edu.vn')->first();
        $this->actingAs($secretary);

        // Lấy 2 mục chương trình họp trong cùng 1 phiên họp
        $agendas = \App\Models\ChuongTrinhHop::all()->groupBy('MaPhienHop')->first(function ($group) {
            return $group->count() >= 2;
        });

        if (! $agendas || $agendas->count() < 2) {
            $this->markTestSkipped('Cần ít nhất 2 mục chương trình họp trong cùng 1 phiên.');
        }

        $item1 = $agendas[0];
        $item2 = $agendas[1];

        // Cố tình cập nhật item2 có ThuTu trùng với item1
        $response = $this->put(route('agendas.update', $item2->MaChuongTrinh), [
            'ThuTu' => $item1->ThuTu,
            'TrangThai' => 'Đang thảo luận',
            'GhiChu' => 'Thử nghiệm trùng lặp thứ tự',
        ]);

        $response->assertSessionHas('error');
        $response->assertSessionHasErrors(['ThuTu']);
    }

    /**
     * 11. Ràng buộc dữ liệu: Cột Thứ Tự khi thêm mới không được trùng với mục đã có trong cùng phiên họp.
     */
    public function test_agenda_store_blocks_duplicate_order_in_same_meeting(): void
    {
        $secretary = \App\Models\User::where('email', 'thuky_hdt@stu.edu.vn')->first();
        $this->actingAs($secretary);

        $existing = \App\Models\ChuongTrinhHop::first();
        if (! $existing) {
            $this->markTestSkipped('Chưa có mục chương trình họp mẫu.');
        }

        // Tạo hồ sơ mới chưa nằm trong phiên
        $dossier = \App\Models\HoSoTrinh::create([
            'TieuDe' => 'Hồ sơ kiểm thử trùng thứ tự ' . uniqid(),
            'NoiDungTomTat' => 'Nội dung kiểm thử',
            'NgayTrinh' => now()->toDateString(),
            'LinhVuc' => 'Tổng hợp',
            'TrangThai' => 'Đủ điều kiện',
            'MaDonVi' => 1,
            'MaNguoiDung' => $secretary->MaNguoiDung ?? 1,
        ]);

        // Cố tình thêm mới với ThuTu trùng với $existing->ThuTu trong cùng MaPhienHop
        $response = $this->post(route('agendas.store'), [
            'MaPhienHop' => $existing->MaPhienHop,
            'MaHoSo' => $dossier->MaHoSo,
            'ThuTu' => $existing->ThuTu,
            'TrangThai' => 'Chưa xem xét',
        ]);

        $response->assertSessionHas('error');
        $response->assertSessionHasErrors(['ThuTu']);
    }

    /**
     * 12. Quản lý thành phần tham dự: Thêm và bớt thành viên Hội đồng trường (BR-02 & Quyền biểu quyết).
     */
    public function test_meeting_add_and_remove_member_works(): void
    {
        $secretary = \App\Models\User::where('email', 'thuky_hdt@stu.edu.vn')->first();
        $this->actingAs($secretary);

        $meeting = PhienHop::first();
        if (! $meeting) {
            $this->markTestSkipped('Chưa có phiên họp.');
        }

        // Lấy thành viên HĐT chưa tham gia phiên
        $existingMemberIds = \App\Models\ThamDu::where('MaPhienHop', $meeting->MaPhienHop)->pluck('MaThanhVien')->toArray();
        $newMember = \App\Models\ThanhVienHdt::whereNotIn('MaThanhVien', $existingMemberIds)->first();

        if (! $newMember) {
            $user = \App\Models\NguoiDung::create([
                'HoTen' => 'Thành Viên Test ' . uniqid(),
                'email' => 'member_test_' . uniqid() . '@stu.edu.vn',
                'MatKhau' => bcrypt('password'),
                'TrangThai' => 'Hoạt động',
                'MaVaiTro' => 2,
                'MaDonVi' => 1,
            ]);
            $newMember = \App\Models\ThanhVienHdt::create([
                'ChucDanh' => 'Ủy viên HĐT',
                'NhiemKy' => '2020-2025',
                'NgayBatDauThamGia' => '2020-01-01',
                'CoQuyenBieuQuyet' => 1,
                'MaNguoiDung' => $user->MaNguoiDung,
            ]);
        }

        // Thêm thành viên
        $addResponse = $this->post(route('meetings.members.add', $meeting->MaPhienHop), [
            'MaThanhVien' => $newMember->MaThanhVien,
            'TrangThaiThamDu' => 'Có mặt',
            'GhiChu' => 'Thêm phục vụ kiểm thử',
        ]);

        $addResponse->assertRedirect();
        $addResponse->assertSessionHas('success');

        $attendee = \App\Models\ThamDu::where('MaPhienHop', $meeting->MaPhienHop)
            ->where('MaThanhVien', $newMember->MaThanhVien)
            ->first();
        $this->assertNotNull($attendee);

        // Chống thêm trùng (BR-02)
        $dupResponse = $this->post(route('meetings.members.add', $meeting->MaPhienHop), [
            'MaThanhVien' => $newMember->MaThanhVien,
            'TrangThaiThamDu' => 'Có mặt',
        ]);
        $dupResponse->assertSessionHas('warning');

        // Bớt thành viên
        $removeResponse = $this->delete(route('meetings.members.remove', [$meeting->MaPhienHop, $attendee->MaThamDu]));
        $removeResponse->assertRedirect();
        $removeResponse->assertSessionHas('success');

        $this->assertSoftDeleted('tham_du', ['MaThamDu' => $attendee->MaThamDu]);
    }

    /**
     * 13. Quản lý thành phần tham dự: Thêm và bớt khách mời phiên họp (BR-03).
     */
    public function test_meeting_add_and_remove_guest_works(): void
    {
        $secretary = \App\Models\User::where('email', 'thuky_hdt@stu.edu.vn')->first();
        $this->actingAs($secretary);

        $meeting = PhienHop::first();
        if (! $meeting) {
            $this->markTestSkipped('Chưa có phiên họp.');
        }

        $uniqueName = 'Khách Mời Test ' . uniqid();

        // Thêm khách mời mới
        $addResponse = $this->post(route('meetings.guests.add', $meeting->MaPhienHop), [
            'HoTen' => $uniqueName,
            'DonViCongTac' => 'Đơn vị Kiểm thử STU',
            'ChucVu' => 'Chuyên gia tư vấn',
            'Email' => 'guest_test@stu.edu.vn',
            'NoiDungMoi' => 'Tham vấn ý kiến chuyên môn',
            'TrangThaiThamDu' => 'Đã gửi giấy mời',
        ]);

        $addResponse->assertRedirect();
        $addResponse->assertSessionHas('success');

        $guest = \App\Models\KhachMoi::where('HoTen', $uniqueName)->first();
        $this->assertNotNull($guest);

        $invite = \App\Models\KhachMoiPhienHop::where('MaPhienHop', $meeting->MaPhienHop)
            ->where('MaKhachMoi', $guest->MaKhachMoi)
            ->first();
        $this->assertNotNull($invite);

        // Chống thêm trùng (BR-03)
        $dupResponse = $this->post(route('meetings.guests.add', $meeting->MaPhienHop), [
            'MaKhachMoi' => $guest->MaKhachMoi,
            'NoiDungMoi' => 'Tham vấn ý kiến',
            'TrangThaiThamDu' => 'Đã gửi giấy mời',
        ]);
        $dupResponse->assertSessionHas('warning');

        // Bớt khách mời (Hủy lời mời)
        $removeResponse = $this->delete(route('meetings.guests.remove', [$meeting->MaPhienHop, $invite->MaMoi]));
        $removeResponse->assertRedirect();
        $removeResponse->assertSessionHas('success');

        $this->assertSoftDeleted('khach_moi_phien_hop', ['MaMoi' => $invite->MaMoi]);
    }

    /**
     * 14. Quản lý thành phần tham dự: Không cho phép bớt thành viên đã tham gia biểu quyết trong phiên (bảo toàn dữ liệu).
     */
    public function test_meeting_remove_member_blocked_if_voted(): void
    {
        $secretary = \App\Models\User::where('email', 'thuky_hdt@stu.edu.vn')->first();
        $this->actingAs($secretary);

        // Tìm 1 người đã có phiếu biểu quyết trong hệ thống
        $vote = \App\Models\KetQuaBieuQuyet::whereNotNull('MaThamDu')->first();
        if (! $vote || ! $vote->thamDu) {
            $this->markTestSkipped('Chưa có phiếu biểu quyết liên kết MaThamDu để kiểm thử.');
        }

        $attendee = $vote->thamDu;
        $response = $this->delete(route('meetings.members.remove', [$attendee->MaPhienHop, $attendee->MaThamDu]));

        $response->assertSessionHas('error');
        // Đảm bảo không bị xóa mềm
        $this->assertDatabaseHas('tham_du', [
            'MaThamDu' => $attendee->MaThamDu,
            'deleted_at' => null,
        ]);
    }
}
