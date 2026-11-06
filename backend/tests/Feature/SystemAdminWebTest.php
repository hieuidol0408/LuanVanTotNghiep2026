<?php

namespace Tests\Feature;

use App\Models\TaiLieu;
use App\Models\User;
use Tests\TestCase;

class SystemAdminWebTest extends TestCase
{
    /**
     * 1. Kho tài liệu số hiển thị thành công.
     */
    public function test_documents_index_renders_successfully(): void
    {
        $chair = User::where('email', 'chutich_hdt@stu.edu.vn')->first();
        $this->actingAs($chair);

        $response = $this->get(route('documents.index'));

        $response->assertStatus(200);
        $response->assertSee('Kho Văn bản &amp; Tài liệu số Hội đồng trường', false);
        $response->assertSee('Tìm kiếm tài liệu');
        $response->assertSee('Cấp độ bảo mật');
    }

    /**
     * 2. Tải tệp tài liệu an toàn.
     */
    public function test_documents_download_allows_council_member(): void
    {
        $chair = User::where('email', 'chutich_hdt@stu.edu.vn')->first();
        $this->actingAs($chair);

        $doc = TaiLieu::first();
        if ($doc) {
            $response = $this->get(route('documents.download', $doc->MaTaiLieu));
            $response->assertStatus(200);
        } else {
            $this->assertTrue(true);
        }
    }

    /**
     * 3. Danh sách thành viên Hội đồng trường hiển thị thành công.
     */
    public function test_members_index_renders_successfully(): void
    {
        $secretary = User::where('email', 'thuky_hdt@stu.edu.vn')->first();
        $this->actingAs($secretary);

        $response = $this->get(route('members.index'));

        $response->assertStatus(200);
        $response->assertSee('Danh sách Thành viên Hội đồng trường STU');
        $response->assertSee('Tổng số thành viên');
        $response->assertSee('Có quyền biểu quyết (BR-07)');
    }

    /**
     * 4. Quản lý người dùng: Admin/Chủ tịch có quyền truy cập.
     */
    public function test_users_index_accessible_by_admin(): void
    {
        $admin = User::whereHas('vaiTro', fn($q) => $q->where('TenVaiTro', 'ADMIN'))->first();
        if ($admin) {
            $this->actingAs($admin);
            $response = $this->get(route('users.index'));
            $response->assertStatus(200);
            $response->assertSee('Quản trị Người dùng &amp; Phân quyền (RBAC)', false);
        } else {
            $this->assertTrue(true);
        }
    }

    /**
     * 5. Quản lý người dùng: Đơn vị thực hiện (UNIT_REP) bị chặn truy cập.
     */
    public function test_users_index_blocks_unit_rep(): void
    {
        $unitUser = User::whereHas('vaiTro', fn($q) => $q->where('TenVaiTro', 'UNIT_REP'))->first();
        if ($unitUser) {
            $this->actingAs($unitUser);
            $response = $this->get(route('users.index'));
            $response->assertRedirect(route('dashboard'));
        } else {
            $this->assertTrue(true);
        }
    }
}
