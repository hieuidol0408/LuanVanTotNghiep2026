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

    /**
     * 6. Admin tạo mới người dùng thành công với vai trò RBAC và liên kết đơn vị.
     */
    public function test_admin_can_create_new_user(): void
    {
        $admin = User::whereHas('vaiTro', fn($q) => $q->where('TenVaiTro', 'ADMIN'))->first();
        $this->actingAs($admin);

        $uniqueEmail = 'giangvien_' . time() . '@stu.edu.vn';
        $postData = [
            'HoTen' => 'TS. Kiểm Thử Hệ Thống Mới',
            'email' => $uniqueEmail,
            'password' => 'stu@2026',
            'MaVaiTro' => 4, // MEMBER
            'MaDonVi' => 1,
            'TrangThai' => 'Hoạt động',
        ];

        $response = $this->post(route('users.store'), $postData);

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('nguoi_dung', [
            'email' => $uniqueEmail,
            'HoTen' => 'TS. Kiểm Thử Hệ Thống Mới',
        ]);
    }

    /**
     * 7. Quy tắc nghiệp vụ BR-01: Ngăn chặn tạo tài khoản trùng email.
     */
    public function test_user_creation_validates_unique_email(): void
    {
        $admin = User::whereHas('vaiTro', fn($q) => $q->where('TenVaiTro', 'ADMIN'))->first();
        $this->actingAs($admin);

        $existingUser = User::first();

        $postData = [
            'HoTen' => 'Người Dùng Trùng Email',
            'email' => $existingUser->email,
            'password' => 'stu@2026',
            'MaVaiTro' => 4,
            'MaDonVi' => 1,
        ];

        $response = $this->post(route('users.store'), $postData);

        $response->assertSessionHasErrors(['email']);
    }
}
