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
        $response->assertSee('Quy tắc nghiệp vụ tự động (BR-02)');
    }

    /**
     * 3. Ràng buộc thời gian: Thời gian kết thúc phải sau thời gian bắt đầu.
     */
    public function test_meeting_store_validates_end_time_after_start_time(): void
    {
        $response = $this->post(route('meetings.store'), [
            'TenPhienHop' => 'Phiên họp kiểm thử thời gian lỗi',
            'NgayHop' => Carbon::now()->addDays(2)->toDateString(),
            'GioBatDau' => '14:00',
            'GioKetThuc' => '10:00', // Kết thúc trước khi bắt đầu
            'DiaDiem' => 'Phòng họp A1',
            'HinhThuc' => 'Trực tiếp',
        ]);

        $response->assertSessionHasErrors(['GioKetThuc']);
    }

    /**
     * 4. Tạo phiên họp hợp lệ & Tự động gửi thư mời thành viên HĐT chính thức (BR-02).
     */
    public function test_meeting_store_creates_meeting_and_auto_invites_active_members(): void
    {
        $response = $this->post(route('meetings.store'), [
            'TenPhienHop' => 'Phiên họp kiểm thử tự động mời thành viên BR-02 ' . uniqid(),
            'NgayHop' => Carbon::now()->addDays(5)->toDateString(),
            'GioBatDau' => '08:30',
            'GioKetThuc' => '11:30',
            'DiaDiem' => 'Phòng họp Tân Triều STU',
            'HinhThuc' => 'Trực tiếp',
            'GhiChu' => 'Phiên họp định kỳ khóa IV',
        ]);

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
}
