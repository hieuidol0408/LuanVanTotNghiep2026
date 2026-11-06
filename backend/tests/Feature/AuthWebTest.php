<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AuthWebTest extends TestCase
{
    /**
     * Màn hình đăng nhập hiển thị đầy đủ các thành phần giao diện.
     */
    public function test_login_page_renders_with_brand_and_controls(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('HỘI ĐỒNG TRƯỜNG STU');
        $response->assertSee('HỆ THỐNG QUẢN LÝ HOẠT ĐỘNG');
        $response->assertSee('loginEmail');
        $response->assertSee('loginPassword');
        $response->assertSee('togglePasswordBtn');
        $response->assertSee('Ghi nhớ đăng nhập');
        $response->assertSee('Đăng nhập vào Hệ thống');
        $response->assertSee('chutich_hdt@stu.edu.vn');
    }

    /**
     * Đăng nhập thất bại khi sai email hoặc mật khẩu.
     */
    public function test_login_fails_with_invalid_credentials(): void
    {
        $response = $this->from('/login')->post('/login', [
            'email' => 'wrong@stu.edu.vn',
            'password' => 'wrong_password',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertFalse(Auth::check());
    }

    /**
     * Đăng nhập thất bại và thông báo rõ khi tài khoản bị khóa (is_active = false).
     */
    public function test_login_fails_for_inactive_account(): void
    {
        $user = User::first();
        if ($user) {
            $originalStatus = $user->TrangThai;
            $user->update(['TrangThai' => 'Bị khóa']);

            $response = $this->from('/login')->post('/login', [
                'email' => $user->email,
                'password' => 'stu@2026',
            ]);

            $response->assertRedirect('/login');
            $response->assertSessionHasErrors('email');
            $this->assertFalse(Auth::check());

            // Phục hồi lại trạng thái ban đầu
            $user->update(['TrangThai' => $originalStatus]);
        }
    }

    /**
     * Đăng nhập thành công với tài khoản hợp lệ, chuyển hướng vào Dashboard.
     */
    public function test_login_succeeds_with_valid_credentials(): void
    {
        $response = $this->post('/login', [
            'email' => 'chutich_hdt@stu.edu.vn',
            'password' => 'stu@2026',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertTrue(Auth::check());
        $this->assertEquals('chutich_hdt@stu.edu.vn', Auth::user()->email);
    }

    /**
     * Đăng xuất xóa bỏ session và chuyển về trang đăng nhập.
     */
    public function test_logout_clears_session_and_redirects(): void
    {
        $user = User::where('email', 'chutich_hdt@stu.edu.vn')->first();
        $this->actingAs($user);

        $response = $this->post('/logout');

        $response->assertRedirect(route('login'));
        $this->assertFalse(Auth::check());
    }
}
