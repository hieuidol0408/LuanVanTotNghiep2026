<?php

namespace Tests\Feature;

use App\Models\BaoCao;
use App\Models\ChuongTrinhHop;
use App\Models\HoSoTrinh;
use App\Models\NghiQuyet;
use App\Models\NguoiDung;
use App\Models\NhiemVu;
use App\Models\NoiDungBieuQuyet;
use App\Models\PhienHop;
use App\Models\TaiLieu;
use App\Models\ThanhVienHdt;
use App\Models\User;
use App\Support\SearchCodeHelper;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SearchByCodeTest extends TestCase
{
    public function test_search_code_helper_parses_prefixes_and_avoids_false_positives(): void
    {
        $this->assertSame(1, SearchCodeHelper::extractNumericId('1', ['bq']));
        $this->assertSame(1, SearchCodeHelper::extractNumericId('#1', ['bq']));
        $this->assertSame(1, SearchCodeHelper::extractNumericId('#BQ-001', ['bq']));
        $this->assertSame(12, SearchCodeHelper::extractNumericId('bq-012', ['bq']));
        $this->assertSame(7, SearchCodeHelper::extractNumericId('mã 7', ['nv']));
        $this->assertSame(5, SearchCodeHelper::extractNumericId('Phiên #5', ['ph']));
        $this->assertNull(SearchCodeHelper::extractNumericId('Đề án mở ngành 2026', ['nq']));
        $this->assertNull(SearchCodeHelper::extractNumericId('01/NQ-HĐT-2026', ['nq']));
    }

    public function test_web_search_supports_entity_codes_across_all_modules(): void
    {
        $user = User::where('email', 'chutich_hdt@stu.edu.vn')->firstOrFail();
        $this->actingAs($user);

        // 1. Phiên họp (#PH-..., #1)
        $meeting = PhienHop::orderBy('MaPhienHop')->firstOrFail();
        $this->get('/meetings?search='.urlencode('#'.$meeting->MaPhienHop))
            ->assertOk()
            ->assertSee($meeting->TenPhienHop);

        // 2. Hồ sơ trình (#HS-MMYYNN và MaHoSo)
        $dossier = HoSoTrinh::orderBy('MaHoSo')->firstOrFail();
        $this->get('/submissions?search='.urlencode($dossier->ma_ho_so_hien_thi))
            ->assertOk()
            ->assertSee($dossier->TieuDe);
        $this->get('/submissions?search='.$dossier->MaHoSo)
            ->assertOk()
            ->assertSee($dossier->TieuDe);

        // 3. Chương trình họp (#CT-..., #PH-..., #HS-MMYYNN)
        $agenda = ChuongTrinhHop::with(['hoSoTrinh', 'phienHop'])->orderBy('MaChuongTrinh')->firstOrFail();
        $agendaExpectedText = $agenda->hoSoTrinh?->TieuDe ?? $agenda->phienHop?->TenPhienHop ?? '';
        $this->get('/agendas?search='.urlencode('#CT-'.$agenda->MaChuongTrinh))
            ->assertOk()
            ->assertSee($agendaExpectedText);
        if ($agenda->hoSoTrinh) {
            $this->get('/agendas?search='.urlencode($agenda->hoSoTrinh->ma_ho_so_hien_thi))
                ->assertOk()
                ->assertSee($agenda->hoSoTrinh->TieuDe);
        }

        // 4. Biểu quyết (#BQ-001)
        $voting = NoiDungBieuQuyet::orderBy('MaNoiDung')->firstOrFail();
        $votingCode = '#BQ-'.str_pad((string) $voting->MaNoiDung, 3, '0', STR_PAD_LEFT);
        $this->get('/votes?search='.urlencode($votingCode))
            ->assertOk()
            ->assertSee($votingCode);

        // 5. Nghị quyết (#NQ-...)
        $resolution = NghiQuyet::orderBy('MaNghiQuyet')->firstOrFail();
        $this->get('/resolutions?search='.urlencode('#NQ-'.$resolution->MaNghiQuyet))
            ->assertOk()
            ->assertSee($resolution->SoNghiQuyet);

        // 6. Nhiệm vụ (#NV-001)
        $task = NhiemVu::orderBy('MaNhiemVu')->firstOrFail();
        $taskCode = '#NV-'.str_pad((string) $task->MaNhiemVu, 3, '0', STR_PAD_LEFT);
        $this->get('/tasks?search='.urlencode($taskCode))
            ->assertOk()
            ->assertSee($taskCode);

        // 7. Báo cáo tiến độ (#BC-...)
        $report = BaoCao::orderBy('MaBaoCao')->firstOrFail();
        $this->get('/task-reports?search='.urlencode('#BC-'.$report->MaBaoCao))
            ->assertOk()
            ->assertSee('#BC-'.$report->MaBaoCao);

        // 8. Tài liệu số (#TL-...)
        $doc = TaiLieu::orderBy('MaTaiLieu')->firstOrFail();
        $this->get('/documents?search='.urlencode('#TL-'.$doc->MaTaiLieu))
            ->assertOk()
            ->assertSee($doc->TenTaiLieu);

        // 9. Người dùng (#ND-...)
        $account = NguoiDung::orderBy('MaNguoiDung')->firstOrFail();
        $this->get('/users?search='.urlencode('#ND-'.$account->MaNguoiDung))
            ->assertOk()
            ->assertSee($account->email);

        // 10. Thành viên HĐT (#TV-...)
        $member = ThanhVienHdt::with('nguoiDung')->orderBy('MaThanhVien')->firstOrFail();
        $this->get('/members?search='.urlencode('#TV-'.$member->MaThanhVien))
            ->assertOk()
            ->assertSee('#TV-'.$member->MaThanhVien);
    }

    public function test_api_v1_search_supports_entity_codes_across_all_modules(): void
    {
        $user = User::where('email', 'chutich_hdt@stu.edu.vn')->firstOrFail();
        Sanctum::actingAs($user);

        // 1. Dossiers API (#HS-MMYYNN & MaHoSo)
        $dossier = HoSoTrinh::orderBy('MaHoSo')->firstOrFail();
        $this->getJson('/api/v1/dossiers?search='.urlencode($dossier->ma_ho_so_hien_thi))
            ->assertOk()
            ->assertJsonFragment(['id' => (string) $dossier->MaHoSo]);

        // 2. Votings API (#BQ-001)
        $voting = NoiDungBieuQuyet::with('chuongTrinhHop')->orderBy('MaNoiDung')->firstOrFail();
        $meetingId = $voting->chuongTrinhHop->MaPhienHop;
        $votingCode = '#BQ-'.str_pad((string) $voting->MaNoiDung, 3, '0', STR_PAD_LEFT);
        $this->getJson("/api/v1/meetings/{$meetingId}/votings?search=".urlencode($votingCode))
            ->assertOk()
            ->assertJsonFragment(['id' => (string) $voting->MaNoiDung]);

        // 3. Resolutions API (#NQ-...)
        $resolution = NghiQuyet::orderBy('MaNghiQuyet')->firstOrFail();
        $this->getJson('/api/v1/resolutions?search='.urlencode('#NQ-'.$resolution->MaNghiQuyet))
            ->assertOk()
            ->assertJsonFragment(['id' => (string) $resolution->MaNghiQuyet]);

        // 4. Tasks API (#NV-001)
        $task = NhiemVu::orderBy('MaNhiemVu')->firstOrFail();
        $taskCode = '#NV-'.str_pad((string) $task->MaNhiemVu, 3, '0', STR_PAD_LEFT);
        $this->getJson('/api/v1/tasks?search='.urlencode($taskCode))
            ->assertOk()
            ->assertJsonFragment(['id' => (string) $task->MaNhiemVu]);

        // 5. Documents API (#TL-...)
        $doc = TaiLieu::orderBy('MaTaiLieu')->firstOrFail();
        $this->getJson('/api/v1/documents?search='.urlencode('#TL-'.$doc->MaTaiLieu))
            ->assertOk()
            ->assertJsonFragment(['id' => (string) $doc->MaTaiLieu]);

        // 6. Users API (#ND-...)
        $account = NguoiDung::orderBy('MaNguoiDung')->firstOrFail();
        $this->getJson('/api/v1/users?search='.urlencode('#ND-'.$account->MaNguoiDung))
            ->assertOk()
            ->assertJsonFragment(['id' => (int) $account->MaNguoiDung]);

        // 7. Council Members API (#TV-...)
        $member = ThanhVienHdt::orderBy('MaThanhVien')->firstOrFail();
        $this->getJson('/api/v1/councils/members?search='.urlencode('#TV-'.$member->MaThanhVien))
            ->assertOk()
            ->assertJsonFragment(['id' => (string) $member->MaThanhVien]);
    }

    public function test_reset_button_is_always_visible_on_all_list_pages(): void
    {
        $user = User::where('email', 'chutich_hdt@stu.edu.vn')->firstOrFail();
        $this->actingAs($user);

        foreach ([
            '/meetings',
            '/meetings/history',
            '/agendas',
            '/votes',
            '/resolutions',
            '/tasks',
            '/task-reports',
            '/documents',
            '/users',
            '/members',
        ] as $route) {
            $this->get($route)
                ->assertOk()
                ->assertSee('Đặt lại');
        }
    }
}
