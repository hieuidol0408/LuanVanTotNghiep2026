<?php

namespace Tests\Feature;

use App\Models\HoSoTrinh;
use App\Models\NghiQuyet;
use App\Models\NoiDungBieuQuyet;
use App\Models\PhienHop;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ResolutionApiTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Khách vãng lai không được truy cập danh sách Nghị quyết
     */
    public function test_unauthenticated_cannot_access_resolutions(): void
    {
        $response = $this->getJson('/api/v1/resolutions');
        $response->assertStatus(401);
    }

    /**
     * Người dùng đã xác thực có thể xem danh sách Nghị quyết
     */
    public function test_authenticated_can_list_resolutions(): void
    {
        $user = User::where('email', 'thuky_hdt@stu.edu.vn')->first();

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/resolutions');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
                'meta' => ['total', 'current_page', 'total_pages'],
            ]);
    }

    /**
     * Thư ký có thể khởi tạo Dự thảo Nghị quyết liên kết với Phiên họp, Hồ sơ và Biểu quyết
     */
    public function test_secretary_can_create_draft_resolution(): void
    {
        $secretary = User::where('email', 'thuky_hdt@stu.edu.vn')->first();
        $meeting = PhienHop::first();
        $dossier = HoSoTrinh::first();
        $vote = NoiDungBieuQuyet::first();

        $payload = [
            'resolution_number' => '99/NQ-HĐT-' . uniqid(),
            'title' => 'Nghị quyết về việc thông qua kế hoạch đầu tư phòng thí nghiệm AI',
            'content' => 'Hội đồng trường quyết nghị phê duyệt chủ trương và giao Hiệu trưởng tổ chức mua sắm.',
            'meeting_id' => $meeting->MaPhienHop,
            'issued_date' => now()->toDateString(),
            'status' => 'DRAFT',
            'dossier_ids' => $dossier ? [$dossier->MaHoSo] : [],
            'voting_ids' => $vote ? [$vote->MaNoiDung] : [],
        ];

        $response = $this->actingAs($secretary, 'sanctum')->postJson('/api/v1/resolutions', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'DRAFT')
            ->assertJsonPath('data.resolution_number', $payload['resolution_number']);

        $this->assertDatabaseHas('nghi_quyet', [
            'SoNghiQuyet' => $payload['resolution_number'],
            'TrangThai' => 'DRAFT',
        ]);
    }

    /**
     * Đại diện đơn vị (UNIT_REP) không được quyền soạn thảo hoặc ban hành Nghị quyết
     */
    public function test_unit_rep_cannot_create_resolution(): void
    {
        $unitRep = User::where('email', 'daotao@stu.edu.vn')->first();
        $meeting = PhienHop::first();

        $response = $this->actingAs($unitRep, 'sanctum')->postJson('/api/v1/resolutions', [
            'resolution_number' => '00/NQ-INVALID',
            'title' => 'Nghị quyết không có thẩm quyền',
            'content' => 'Nội dung',
            'meeting_id' => $meeting->MaPhienHop,
        ]);

        $response->assertStatus(403);
    }

    /**
     * Chủ tịch Hội đồng trường ký ban hành Nghị quyết (SIGNED)
     */
    public function test_chair_can_sign_resolution(): void
    {
        $chair = User::where('email', 'chutich_hdt@stu.edu.vn')->first();
        $meeting = PhienHop::first();

        $nq = NghiQuyet::create([
            'SoNghiQuyet' => 'TEST-SIGN-' . uniqid(),
            'NgayBanHanh' => now()->toDateString(),
            'TrichYeu' => 'Nghị quyết chờ ký ban hành',
            'NoiDung' => 'Nội dung chi tiết',
            'TrangThai' => 'DRAFT',
            'MaPhienHop' => $meeting->MaPhienHop,
        ]);

        $response = $this->actingAs($chair, 'sanctum')->postJson("/api/v1/resolutions/{$nq->MaNghiQuyet}/sign");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'SIGNED');

        $this->assertDatabaseHas('nghi_quyet', [
            'MaNghiQuyet' => $nq->MaNghiQuyet,
            'TrangThai' => 'SIGNED',
        ]);
    }

    /**
     * Thư ký hoặc Chủ tịch công bố hiệu lực Nghị quyết (PUBLISHED) và chặn sửa trực tiếp
     */
    public function test_publish_resolution_and_prevent_modification(): void
    {
        $secretary = User::where('email', 'thuky_hdt@stu.edu.vn')->first();
        $meeting = PhienHop::first();

        $nq = NghiQuyet::create([
            'SoNghiQuyet' => 'TEST-PUB-' . uniqid(),
            'NgayBanHanh' => now()->toDateString(),
            'TrichYeu' => 'Nghị quyết chuẩn bị công bố',
            'NoiDung' => 'Nội dung chi tiết',
            'TrangThai' => 'SIGNED',
            'MaPhienHop' => $meeting->MaPhienHop,
        ]);

        // 1. Công bố
        $publishRes = $this->actingAs($secretary, 'sanctum')->postJson("/api/v1/resolutions/{$nq->MaNghiQuyet}/publish");
        $publishRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'PUBLISHED');

        // 2. Chặn sửa đổi trực tiếp khi đã PUBLISHED
        $editRes = $this->actingAs($secretary, 'sanctum')->putJson("/api/v1/resolutions/{$nq->MaNghiQuyet}", [
            'title' => 'Cố gắng sửa trích yếu sau khi đã ban hành công khai',
        ]);
        $editRes->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    /**
     * Quy trình Biên bản phiên họp: Thư ký cập nhật dự thảo và Chủ tịch phê duyệt
     */
    public function test_meeting_minutes_workflow(): void
    {
        $secretary = User::where('email', 'thuky_hdt@stu.edu.vn')->first();
        $chair = User::where('email', 'chutich_hdt@stu.edu.vn')->first();
        $meeting = PhienHop::first();

        // 1. Thư ký lưu dự thảo biên bản
        $saveRes = $this->actingAs($secretary, 'sanctum')->postJson("/api/v1/meetings/{$meeting->MaPhienHop}/minutes", [
            'content' => 'Toàn văn biên bản phiên họp thứ 4 Hội đồng trường khóa IV...',
        ]);
        $saveRes->assertStatus(200)
            ->assertJsonPath('success', true);

        // 2. Chủ tịch phê duyệt biên bản
        $approveRes = $this->actingAs($chair, 'sanctum')->postJson("/api/v1/meetings/{$meeting->MaPhienHop}/minutes/approve");
        $approveRes->assertStatus(200)
            ->assertJsonPath('success', true);

        // 3. Đọc lại nội dung biên bản
        $getRes = $this->actingAs($secretary, 'sanctum')->getJson("/api/v1/meetings/{$meeting->MaPhienHop}/minutes");
        $getRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.minutes_status', 'APPROVED');
    }
}
