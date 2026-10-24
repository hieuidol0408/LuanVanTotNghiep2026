<?php

namespace Tests\Feature;

use App\Models\DonVi;
use App\Models\User;
use App\Models\VaiTro;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UserApiTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Khách vãng lai không được truy cập danh sách người dùng
     */
    public function test_unauthenticated_cannot_access_users(): void
    {
        $response = $this->getJson('/api/v1/users');
        $response->assertStatus(401);
    }

    /**
     * Thành viên thường hoặc đại diện đơn vị không có quyền quản trị người dùng
     */
    public function test_unauthorized_member_cannot_list_users(): void
    {
        $unitRep = User::where('email', 'daotao@stu.edu.vn')->first();
        if (!$unitRep) {
            $unitRep = User::whereHas('vaiTro', fn($q) => $q->where('TenVaiTro', 'UNIT_REP'))->first();
        }

        $response = $this->actingAs($unitRep, 'sanctum')->getJson('/api/v1/users');
        $response->assertStatus(403);
    }

    /**
     * Quản trị viên và Thư ký có thể lấy danh mục vai trò và danh sách tài khoản
     */
    public function test_admin_can_list_roles_and_users(): void
    {
        $admin = User::where('email', 'admin@stu.edu.vn')->first();
        if (!$admin) {
            $admin = User::whereHas('vaiTro', fn($q) => $q->where('TenVaiTro', 'ADMIN'))->first();
        }

        // 1. Kiểm tra API danh sách vai trò
        $rolesRes = $this->actingAs($admin, 'sanctum')->getJson('/api/v1/roles');
        $rolesRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['success', 'data']);

        // 2. Kiểm tra API danh sách tài khoản
        $usersRes = $this->actingAs($admin, 'sanctum')->getJson('/api/v1/users');
        $usersRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'full_name',
                        'email',
                        'role',
                        'role_name',
                        'department_name',
                        'status',
                        'is_active',
                    ],
                ],
                'meta' => ['total', 'current_page', 'per_page'],
            ]);
    }

    /**
     * Quản trị viên có thể tạo mới một tài khoản người dùng
     */
    public function test_admin_can_create_new_user(): void
    {
        $admin = User::where('email', 'admin@stu.edu.vn')->first()
            ?? User::whereHas('vaiTro', fn($q) => $q->where('TenVaiTro', 'ADMIN'))->first();

        $role = VaiTro::first();
        $dept = DonVi::first();

        $uniqueEmail = 'test_user_' . uniqid() . '@stu.edu.vn';

        $payload = [
            'HoTen' => 'Nguyễn Văn Test',
            'email' => $uniqueEmail,
            'MatKhau' => 'STU@Secret123',
            'MaVaiTro' => $role->MaVaiTro,
            'MaDonVi' => $dept->MaDonVi,
            'TrangThai' => 'Hoạt động',
        ];

        $response = $this->actingAs($admin, 'sanctum')->postJson('/api/v1/users', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.full_name', 'Nguyễn Văn Test')
            ->assertJsonPath('data.email', $uniqueEmail);

        $this->assertDatabaseHas('nguoi_dung', [
            'email' => $uniqueEmail,
            'HoTen' => 'Nguyễn Văn Test',
        ]);
    }

    /**
     * Kiểm tra xác thực tính hợp lệ của dữ liệu khi tạo người dùng (email trùng)
     */
    public function test_create_user_rejects_duplicate_email(): void
    {
        $admin = User::where('email', 'admin@stu.edu.vn')->first()
            ?? User::whereHas('vaiTro', fn($q) => $q->where('TenVaiTro', 'ADMIN'))->first();

        $existingUser = User::first();
        $role = VaiTro::first();

        $payload = [
            'HoTen' => 'Người Dùng Trùng Email',
            'email' => $existingUser->email,
            'MaVaiTro' => $role->MaVaiTro,
        ];

        $response = $this->actingAs($admin, 'sanctum')->postJson('/api/v1/users', $payload);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Quản trị viên có thể cập nhật thông tin và khóa/mở khóa tài khoản
     */
    public function test_admin_can_update_and_toggle_status_user(): void
    {
        $admin = User::where('email', 'admin@stu.edu.vn')->first()
            ?? User::whereHas('vaiTro', fn($q) => $q->where('TenVaiTro', 'ADMIN'))->first();

        $role = VaiTro::first();
        $dept = DonVi::first();

        // Tạo riêng một user độc lập để test thao tác cập nhật, khóa và reset password
        $targetUser = User::create([
            'HoTen' => 'User Riêng Để Test Sửa',
            'email' => 'temp_update_' . uniqid() . '@stu.edu.vn',
            'MatKhau' => \Illuminate\Support\Facades\Hash::make('initial_pass'),
            'MaVaiTro' => $role->MaVaiTro,
            'MaDonVi' => $dept->MaDonVi,
            'TrangThai' => 'Hoạt động',
        ]);

        // 1. Cập nhật họ tên
        $updateRes = $this->actingAs($admin, 'sanctum')->putJson("/api/v1/users/{$targetUser->MaNguoiDung}", [
            'HoTen' => 'Họ Tên Đã Cập Nhật',
        ]);
        $updateRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.full_name', 'Họ Tên Đã Cập Nhật');

        // 2. Đổi trạng thái khóa/mở khóa
        $toggleRes = $this->actingAs($admin, 'sanctum')->postJson("/api/v1/users/{$targetUser->MaNguoiDung}/toggle-status");
        $toggleRes->assertStatus(200)
            ->assertJsonPath('success', true);

        // 3. Đặt lại mật khẩu
        $resetRes = $this->actingAs($admin, 'sanctum')->postJson("/api/v1/users/{$targetUser->MaNguoiDung}/reset-password", [
            'MatKhau' => 'STU@NewPass999',
        ]);
        $resetRes->assertStatus(200)
            ->assertJsonPath('success', true);
    }
}
