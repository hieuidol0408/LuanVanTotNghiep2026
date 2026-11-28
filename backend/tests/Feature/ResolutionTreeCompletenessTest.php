<?php

namespace Tests\Feature;

use App\Models\HoSoTrinh;
use App\Models\NghiQuyet;
use App\Models\NoiDungBieuQuyet;
use App\Models\PhienHop;
use App\Models\TaiLieu;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ResolutionTreeCompletenessTest extends TestCase
{
    use DatabaseTransactions;

    public function test_resolution_can_reuse_number_from_hidden_soft_deleted_resolution(): void
    {
        Storage::fake('public');
        $secretary = User::where('email', 'thuky_hdt@stu.edu.vn')->firstOrFail();
        $this->actingAs($secretary);

        $meeting = PhienHop::orderBy('MaPhienHop')->firstOrFail();
        $soNq = '99/NQ-REUSE-' . random_int(1000, 9999);

        $this->post(route('resolutions.store'), [
            'SoNghiQuyet' => $soNq,
            'TrichYeu' => 'Nghị quyết test lần 1 để kiểm tra ẩn rồi tạo lại',
            'NoiDung' => 'Điều 1. Nội dung thử nghiệm.',
            'MaPhienHop' => $meeting->MaPhienHop,
            'NgayBanHanh' => '2026-11-27',
        ])->assertRedirect();

        $first = NghiQuyet::where('SoNghiQuyet', $soNq)->firstOrFail();
        $this->delete(route('resolutions.destroy', $first->MaNghiQuyet))->assertRedirect();

        // Tạo lại với đúng Số hiệu đã ẩn
        $this->post(route('resolutions.store'), [
            'SoNghiQuyet' => $soNq,
            'TrichYeu' => 'Nghị quyết test lần 2 dùng lại số hiệu đã ẩn',
            'NoiDung' => 'Điều 1. Nội dung thử nghiệm lần 2.',
            'MaPhienHop' => $meeting->MaPhienHop,
            'NgayBanHanh' => '2026-11-27',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $second = NghiQuyet::where('SoNghiQuyet', $soNq)->first();
        $this->assertNotNull($second);
        $this->assertNotSame($first->MaNghiQuyet, $second->MaNghiQuyet);
    }

    public function test_resolution_links_dossiers_votings_meeting_and_manages_attachments(): void
    {
        Storage::fake('public');
        $secretary = User::where('email', 'thuky_hdt@stu.edu.vn')->firstOrFail();
        $this->actingAs($secretary);

        $meeting = PhienHop::orderBy('MaPhienHop')->firstOrFail();
        $dossier = HoSoTrinh::orderBy('MaHoSo')->firstOrFail();
        $voting = NoiDungBieuQuyet::orderBy('MaNoiDung')->firstOrFail();

        $soNq = '88/NQ-TREE-' . random_int(1000, 9999);
        $pdf1 = UploadedFile::fake()->create('nghi_quyet_1.pdf', 120, 'application/pdf');
        $pdf2 = UploadedFile::fake()->create('phu_luc_ky_so.pdf', 90, 'application/pdf');

        // 1. Tạo mới kèm hồ sơ, biểu quyết và nhiều tệp
        $this->post(route('resolutions.store'), [
            'SoNghiQuyet' => $soNq,
            'TrichYeu' => 'Nghị quyết kiểm thử đủ 6 nhánh cây chức năng',
            'NoiDung' => 'Điều 1. Thông qua.',
            'MaPhienHop' => $meeting->MaPhienHop,
            'NgayBanHanh' => '2026-11-27',
            'dossier_ids' => [$dossier->MaHoSo],
            'voting_ids' => [$voting->MaNoiDung],
            'files' => [$pdf1],
        ])->assertSessionHasNoErrors()->assertRedirect();

        $resolution = NghiQuyet::with(['hoSoTrinh', 'noiDungBieuQuyet', 'taiLieu'])
            ->where('SoNghiQuyet', $soNq)
            ->firstOrFail();

        $this->assertCount(1, $resolution->hoSoTrinh);
        $this->assertCount(1, $resolution->noiDungBieuQuyet);
        $this->assertCount(1, $resolution->taiLieu);

        // 2. Trang chi tiết có link phiên họp, link xem trước và link tải chuẩn
        $firstDoc = $resolution->taiLieu->first();
        $this->get(route('resolutions.show', $resolution->MaNghiQuyet))
            ->assertOk()
            ->assertSee(route('meetings.show', $meeting->MaPhienHop), false)
            ->assertSee(route('documents.preview', $firstDoc->MaTaiLieu), false)
            ->assertSee(route('documents.download', $firstDoc->MaTaiLieu), false)
            ->assertSee($dossier->ma_ho_so_hien_thi);

        // 2b. Kiểm tra liên kết 2 chiều hiển thị ngược lại trong Hồ sơ trình & Biểu quyết
        $this->get(route('submissions.index', ['search' => $dossier->MaHoSo]))
            ->assertOk()
            ->assertSee($soNq);

        $this->get(route('submissions.show', $dossier->MaHoSo))
            ->assertOk()
            ->assertSee('Nghị quyết Hội đồng trường liên kết')
            ->assertSee($soNq)
            ->assertSee(route('resolutions.links.destroy', [$resolution->MaNghiQuyet, 'dossier', $dossier->MaHoSo]), false);

        $this->get(route('votes.index', ['search' => '#BQ-' . $voting->MaNoiDung]))
            ->assertOk()
            ->assertSee($soNq);

        $this->get(route('votes.show', $voting->MaNoiDung))
            ->assertOk()
            ->assertSee('Nghị quyết Hội đồng trường liên kết')
            ->assertSee($soNq)
            ->assertSee(route('resolutions.links.destroy', [$resolution->MaNghiQuyet, 'voting', $voting->MaNoiDung]), false);

        // 2c. Gỡ liên kết trực tiếp từ thẻ Hồ sơ trình & Biểu quyết
        $this->delete(route('resolutions.links.destroy', [$resolution->MaNghiQuyet, 'dossier', $dossier->MaHoSo]))
            ->assertRedirect();
        $this->delete(route('resolutions.links.destroy', [$resolution->MaNghiQuyet, 'voting', $voting->MaNoiDung]))
            ->assertRedirect();

        $resolution->refresh()->load(['hoSoTrinh', 'noiDungBieuQuyet']);
        $this->assertCount(0, $resolution->hoSoTrinh);
        $this->assertCount(0, $resolution->noiDungBieuQuyet);

        // Liên kết lại để tiếp tục kiểm thử bước 4
        $this->put(route('resolutions.links.update', $resolution->MaNghiQuyet), [
            'dossier_ids' => [$dossier->MaHoSo],
            'voting_ids' => [$voting->MaNoiDung],
        ])->assertRedirect();

        // 3. Đính kèm thêm văn bản ngay trên chi tiết (kể cả sau khi ký)
        $resolution->update(['TrangThai' => 'Đã ký ban hành']);
        $this->post(route('resolutions.documents.store', $resolution->MaNghiQuyet), [
            'files' => [$pdf2],
        ])->assertSessionHasNoErrors()->assertRedirect();

        $resolution->refresh()->load('taiLieu');
        $this->assertCount(2, $resolution->taiLieu);

        // 4. Cập nhật liên kết qua resolutions.links.update
        $this->put(route('resolutions.links.update', $resolution->MaNghiQuyet), [
            'dossier_ids' => [],
            'voting_ids' => [$voting->MaNoiDung],
        ])->assertSessionHasNoErrors()->assertRedirect();

        $resolution->refresh()->load(['hoSoTrinh', 'noiDungBieuQuyet']);
        $this->assertCount(0, $resolution->hoSoTrinh);
        $this->assertCount(1, $resolution->noiDungBieuQuyet);

        // 5. Gỡ văn bản đính kèm
        $this->delete(route('resolutions.documents.destroy', [$resolution->MaNghiQuyet, $firstDoc->MaTaiLieu]))
            ->assertRedirect();

        $resolution->refresh()->load('taiLieu');
        $this->assertCount(1, $resolution->taiLieu);
    }

    public function test_resolutions_list_is_sorted_ascending_by_code_id(): void
    {
        $secretary = User::where('email', 'thuky_hdt@stu.edu.vn')->firstOrFail();
        $this->actingAs($secretary);

        $response = $this->get(route('resolutions.index'));
        $response->assertOk();

        $paginator = $response->viewData('resolutions');
        $ids = collect($paginator->items())->pluck('MaNghiQuyet')->all();
        $sorted = $ids;
        sort($sorted);

        $this->assertSame($sorted, $ids);
    }
}
