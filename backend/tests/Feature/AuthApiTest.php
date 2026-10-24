<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test đăng nhập thành công với thông tin hợp lệ
     */
    public function test_user_can_login_with_valid_credentials(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'thuky_hdt@stu.edu.vn',
            'password' => 'stu@2026',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token',
                    'user' => [
                        'id',
                        'key',
                        'name',
                        'role_name',
                        'user_name',
                        'email',
                        'department',
                        'role',
                        'can_vote',
                        'allowed_routes',
                    ],
                ],
                'errors',
                'meta',
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'user' => [
                        'email' => 'thuky_hdt@stu.edu.vn',
                        'role' => 'SECRETARY',
                        'can_vote' => true,
                    ],
                ],
            ]);

        $this->assertNotEmpty($response->json('data.token'));
    }

    /**
     * Test đăng nhập thất bại khi sai mật khẩu
     */
    public function test_login_fails_with_invalid_password(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'thuky_hdt@stu.edu.vn',
            'password' => 'sai_mat_khau_123',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ])
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test đăng nhập thất bại khi để trống trường bắt buộc
     */
    public function test_login_fails_with_empty_fields(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => '',
            'password' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    /**
     * Test lấy thông tin người dùng qua endpoint /api/v1/auth/me
     */
    public function test_authenticated_user_can_fetch_me_profile(): void
    {
        $user = User::where('email', 'chutich_hdt@stu.edu.vn')->first();
        $this->assertNotNull($user);

        $token = $user->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'user' => [
                        'email' => 'chutich_hdt@stu.edu.vn',
                        'role' => 'CHAIR',
                        'can_vote' => true,
                    ],
                ],
            ]);
    }

    /**
     * Test không thể truy cập /api/v1/auth/me khi chưa xác thực (401)
     */
    public function test_unauthenticated_user_cannot_access_me(): void
    {
        $response = $this->getJson('/api/v1/auth/me');

        $response->assertStatus(401);
    }

    /**
     * Test đăng xuất thành công và token bị thu hồi
     */
    public function test_user_can_logout_and_token_is_revoked(): void
    {
        $user = User::where('email', 'admin@stu.edu.vn')->first();
        $this->assertNotNull($user);

        $token = $user->createToken('logout_test_token')->plainTextToken;

        // Gọi đăng xuất
        $logoutResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/auth/logout');

        $logoutResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Đăng xuất thành công.',
            ]);

        // Xóa cache guard trong môi trường test để mô phỏng request mới
        $this->app['auth']->forgetGuards();

        // Gọi lại /auth/me với token đã thu hồi -> Phải trả về 401
        $meResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/auth/me');

        $meResponse->assertStatus(401);
    }

    /**
     * Test cả 7 vai trò chuẩn của Hội đồng trường đều xác thực thành công
     */
    public function test_all_seven_roles_exist_and_can_authenticate(): void
    {
        $roleEmails = [
            'ADMIN' => 'admin@stu.edu.vn',
            'CHAIR' => 'chutich_hdt@stu.edu.vn',
            'SECRETARY' => 'thuky_hdt@stu.edu.vn',
            'MEMBER' => 'thanhvien_hdt@stu.edu.vn',
            'RECTOR_OFFICE' => 'vphieutruong@stu.edu.vn',
            'UNIT_REP' => 'daotao@stu.edu.vn',
            'EXECUTING_UNIT' => 'cntt@stu.edu.vn',
        ];

        foreach ($roleEmails as $expectedRole => $email) {
            $response = $this->postJson('/api/v1/auth/login', [
                'email' => $email,
                'password' => 'stu@2026',
            ]);

            $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'user' => [
                            'email' => $email,
                            'role' => $expectedRole,
                        ],
                    ],
                ]);

            $userData = $response->json('data.user');

            // Kiểm tra quy tắc nghiệp vụ: Chỉ CHAIR, SECRETARY, MEMBER mới được can_vote = true
            if (in_array($expectedRole, ['CHAIR', 'SECRETARY', 'MEMBER'])) {
                $this->assertTrue($userData['can_vote'], "Vai trò {$expectedRole} phải có can_vote = true");
            } else {
                $this->assertFalse($userData['can_vote'], "Vai trò {$expectedRole} tuyệt đối không có can_vote = true");
            }
        }
    }
}
