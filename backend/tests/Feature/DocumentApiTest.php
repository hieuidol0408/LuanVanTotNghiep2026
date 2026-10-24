<?php

namespace Tests\Feature;

use App\Models\TaiLieu;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DocumentApiTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Khách vãng lai không được truy cập kho tài liệu
     */
    public function test_unauthenticated_cannot_access_documents(): void
    {
        $response = $this->getJson('/api/v1/documents');
        $response->assertStatus(401);
    }

    /**
     * Người dùng đã xác thực có thể xem danh sách tài liệu
     */
    public function test_authenticated_can_list_documents(): void
    {
        $user = User::where('email', 'thuky_hdt@stu.edu.vn')->first();

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/documents');

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
     * Đại diện đơn vị có thể tải tài liệu công khai (PUBLIC_AGENDA hoặc REGULATION)
     */
    public function test_anyone_can_download_public_document(): void
    {
        $unitRep = User::where('email', 'daotao@stu.edu.vn')->first();
        $publicDoc = TaiLieu::where('LoaiTaiLieu', 'PUBLIC_AGENDA')->first();

        $this->assertNotNull($publicDoc);

        $response = $this->actingAs($unitRep, 'sanctum')->get("/api/v1/documents/{$publicDoc->MaTaiLieu}/download");

        $response->assertStatus(200);
        $this->assertStringContainsString('STU Council Secure Document', $response->getContent());
    }

    /**
     * Thành viên HĐT chính thức có thể tải tài liệu nội bộ bảo mật (INTERNAL)
     */
    public function test_council_member_can_download_internal_document(): void
    {
        $member = User::where('email', 'thanhvien_hdt@stu.edu.vn')->first();
        $internalDoc = TaiLieu::where('LoaiTaiLieu', 'INTERNAL')->first();

        $this->assertNotNull($internalDoc);

        $response = $this->actingAs($member, 'sanctum')->get("/api/v1/documents/{$internalDoc->MaTaiLieu}/download");

        $response->assertStatus(200);
        $this->assertStringContainsString('STU Council Secure Document', $response->getContent());
    }

    /**
     * Đơn vị ngoài/đơn vị trình (UNIT_REP) bị CHẶN khi cố gắng tải tài liệu nội bộ (INTERNAL) -> 403 Forbidden
     */
    public function test_unit_rep_cannot_download_internal_document(): void
    {
        $unitRep = User::where('email', 'daotao@stu.edu.vn')->first();
        $internalDoc = TaiLieu::where('LoaiTaiLieu', 'INTERNAL')->first();

        $this->assertNotNull($internalDoc);

        $response = $this->actingAs($unitRep, 'sanctum')->getJson("/api/v1/documents/{$internalDoc->MaTaiLieu}/download");

        $response->assertStatus(403)
            ->assertJsonPath('success', false)
            ->assertJsonPath('errors.access_denied', 'Chỉ Thành viên Hội đồng trường mới được phép tải tệp nội bộ này.');
    }

    /**
     * Lấy danh sách thành viên Hội đồng trường theo khóa nhiệm kỳ
     */
    public function test_council_members_list(): void
    {
        $user = User::where('email', 'thuky_hdt@stu.edu.vn')->first();

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/councils/members');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertNotEmpty($response->json('data'));
    }

    /**
     * Danh sách phòng ban, đơn vị trong trường
     */
    public function test_departments_list(): void
    {
        $response = $this->getJson('/api/v1/departments');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertNotEmpty($response->json('data'));
    }
}
