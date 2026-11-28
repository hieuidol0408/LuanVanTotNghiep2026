<?php

namespace Tests\Feature;

use App\Models\ChuongTrinhHop;
use App\Models\DonVi;
use App\Models\HoSoTrinh;
use App\Models\KhachMoi;
use App\Models\NghiQuyet;
use App\Models\NguoiDung;
use App\Models\NhiemVu;
use App\Models\NoiDungBieuQuyet;
use App\Models\PhienHop;
use App\Models\TaiLieu;
use App\Models\ThanhVienHdt;
use App\Models\User;
use App\Models\VaiTro;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DuplicateNameValidationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_meeting_store_rejects_duplicate_name_case_insensitive(): void
    {
        $secretary = User::where('email', 'thuky_hdt@stu.edu.vn')->firstOrFail();
        $this->actingAs($secretary);

        $existing = PhienHop::firstOrFail();
        $memberIds = ThanhVienHdt::pluck('MaThanhVien')->take(2)->all();

        $res = $this->post(route('meetings.store'), [
            'TenPhienHop' => '  ' . mb_strtolower($existing->TenPhienHop, 'UTF-8') . '  ',
            'NgayHop' => Carbon::now()->addDays(30)->toDateString(),
            'GioBatDau' => '09:00',
            'GioKetThuc' => '11:00',
            'DiaDiem' => 'Phòng họp Test',
            'HinhThuc' => 'Trực tiếp',
            'member_ids' => $memberIds,
        ]);

        $res->assertSessionHasErrors(['TenPhienHop']);
        $res->assertSessionHas('error');
    }

    public function test_meeting_add_guest_rejects_duplicate_guest_name(): void
    {
        $secretary = User::where('email', 'thuky_hdt@stu.edu.vn')->firstOrFail();
        $this->actingAs($secretary);

        $meeting = PhienHop::firstOrFail();
        $guest = KhachMoi::create(['HoTen' => 'Khách Mời Duy Nhất ' . uniqid()]);

        $res = $this->post(route('meetings.guests.add', $meeting->MaPhienHop), [
            'HoTen' => '  ' . $guest->HoTen . ' ',
            'DonViCongTac' => 'Đơn vị khác',
            'NoiDungMoi' => 'Tham dự họp',
            'TrangThaiThamDu' => 'Đã gửi giấy mời',
        ]);

        $res->assertSessionHasErrors(['HoTen']);
        $res->assertSessionHas('error');
    }

    public function test_meeting_upload_document_rejects_duplicate_name_in_same_meeting(): void
    {
        Storage::fake('public');
        $secretary = User::where('email', 'thuky_hdt@stu.edu.vn')->firstOrFail();
        $this->actingAs($secretary);

        $meeting = PhienHop::firstOrFail();
        $docName = 'Tài liệu họp duy nhất ' . uniqid();

        $this->post(route('meetings.documents.upload', $meeting->MaPhienHop), [
            'TenTaiLieu' => $docName,
            'LoaiTaiLieu' => 'Kịch bản họp',
            'file' => UploadedFile::fake()->create('doc1.pdf', 100, 'application/pdf'),
        ])->assertSessionHas('success');

        $dupRes = $this->post(route('meetings.documents.upload', $meeting->MaPhienHop), [
            'TenTaiLieu' => ' ' . mb_strtoupper($docName, 'UTF-8') . ' ',
            'LoaiTaiLieu' => 'Kịch bản họp',
            'file' => UploadedFile::fake()->create('doc2.pdf', 100, 'application/pdf'),
        ]);

        $dupRes->assertSessionHasErrors(['TenTaiLieu']);
        $dupRes->assertSessionHas('error');
    }

    public function test_submission_store_rejects_duplicate_title(): void
    {
        $secretary = User::where('email', 'thuky_hdt@stu.edu.vn')->firstOrFail();
        $this->actingAs($secretary);

        $existing = HoSoTrinh::firstOrFail();
        $dept = DonVi::firstOrFail();

        $res = $this->post(route('submissions.store'), [
            'TieuDe' => ' ' . $existing->TieuDe . ' ',
            'NoiDungTomTat' => 'Nội dung tóm tắt thử nghiệm đủ 5 ký tự.',
            'LinhVuc' => 'Đào tạo',
            'MaDonVi' => $dept->MaDonVi,
        ]);

        $res->assertSessionHasErrors(['TieuDe']);
        $res->assertSessionHas('error');
    }

    public function test_vote_store_rejects_duplicate_content(): void
    {
        $secretary = User::where('email', 'thuky_hdt@stu.edu.vn')->firstOrFail();
        $this->actingAs($secretary);

        $existing = NoiDungBieuQuyet::firstOrFail();
        $agenda = ChuongTrinhHop::firstOrFail();

        $res = $this->post(route('votes.store'), [
            'MaChuongTrinh' => $agenda->MaChuongTrinh,
            'NoiDung' => ' ' . mb_strtolower($existing->NoiDung, 'UTF-8') . ' ',
        ]);

        $res->assertSessionHasErrors(['NoiDung']);
        $res->assertSessionHas('error');
    }

    public function test_resolution_store_rejects_duplicate_trich_yeu_and_attachment(): void
    {
        Storage::fake('public');
        $secretary = User::where('email', 'thuky_hdt@stu.edu.vn')->firstOrFail();
        $this->actingAs($secretary);

        $existing = NghiQuyet::firstOrFail();
        $meeting = PhienHop::firstOrFail();

        $res = $this->post(route('resolutions.store'), [
            'SoNghiQuyet' => '99/NQ-UNIQUE-' . uniqid(),
            'TrichYeu' => ' ' . $existing->TrichYeu . ' ',
            'NoiDung' => 'Điều 1. Nội dung quyết nghị.',
            'MaPhienHop' => $meeting->MaPhienHop,
            'NgayBanHanh' => '2026-11-28',
        ]);

        $res->assertSessionHasErrors(['TrichYeu']);
        $res->assertSessionHas('error');

        $attachRes = $this->post(route('resolutions.documents.store', $existing->MaNghiQuyet), [
            'files' => [UploadedFile::fake()->create('unique_doc.pdf', 50, 'application/pdf')],
        ]);
        $attachRes->assertSessionHas('success');

        $dupAttachRes = $this->post(route('resolutions.documents.store', $existing->MaNghiQuyet), [
            'files' => [UploadedFile::fake()->create('unique_doc.pdf', 50, 'application/pdf')],
        ]);
        $dupAttachRes->assertSessionHasErrors(['files']);
        $dupAttachRes->assertSessionHas('error');
    }

    public function test_task_store_rejects_duplicate_name(): void
    {
        $secretary = User::where('email', 'thuky_hdt@stu.edu.vn')->firstOrFail();
        $this->actingAs($secretary);

        $existing = NhiemVu::firstOrFail();
        $res = NghiQuyet::firstOrFail();
        $dept = DonVi::firstOrFail();

        $response = $this->post(route('tasks.store'), [
            'MaNghiQuyet' => $res->MaNghiQuyet,
            'TenNhiemVu' => ' ' . $existing->TenNhiemVu . ' ',
            'NoiDung' => 'Chi tiết',
            'NgayGiao' => '2026-11-28',
            'NgayHoanThanh' => '2026-12-28',
            'chu_tri_don_vi_id' => $dept->MaDonVi,
            'chu_tri_nguoi_phu_trach' => 'Nguyễn Văn A',
        ]);

        $response->assertSessionHasErrors(['TenNhiemVu']);
        $response->assertSessionHas('error');
    }

    public function test_user_store_rejects_duplicate_name(): void
    {
        $admin = User::whereHas('vaiTro', fn ($q) => $q->where('TenVaiTro', 'ADMIN'))->firstOrFail();
        $this->actingAs($admin);

        $existing = NguoiDung::firstOrFail();
        $role = VaiTro::firstOrFail();
        $dept = DonVi::firstOrFail();

        $response = $this->post(route('users.store'), [
            'HoTen' => ' ' . mb_strtolower($existing->HoTen, 'UTF-8') . ' ',
            'email' => 'unique_' . uniqid() . '@stu.edu.vn',
            'password' => 'stu@2026',
            'MaVaiTro' => $role->MaVaiTro,
            'MaDonVi' => $dept->MaDonVi,
        ]);

        $response->assertSessionHasErrors(['HoTen']);
        $response->assertSessionHas('error');
    }
}
