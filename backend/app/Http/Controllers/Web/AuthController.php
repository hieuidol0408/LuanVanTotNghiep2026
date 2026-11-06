<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Hiển thị biểu mẫu Đăng nhập hệ thống
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('pages.auth.login');
    }

    /**
     * Xử lý xác thực đăng nhập
     */
    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Vui lòng nhập địa chỉ email.',
            'email.email' => 'Địa chỉ email không đúng định dạng.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
        ]);

        $email = strtolower(trim($validated['email']));
        $user = User::with(['vaiTro', 'donVi'])->where('email', $email)->first();

        // 1. Kiểm tra sự tồn tại của người dùng và mật khẩu
        if (! $user || ! Hash::check($validated['password'], $user->getAuthPassword())) {
            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors([
                    'email' => 'Thông tin email hoặc mật khẩu không chính xác.',
                ]);
        }

        // 2. Kiểm tra trạng thái tài khoản (chống đăng nhập tài khoản bị khóa)
        if (! $user->is_active) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => 'Tài khoản đã bị tạm khóa. Vui lòng liên hệ Quản trị viên.',
                ]);
        }

        // 3. Tiến hành đăng nhập phiên làm việc (Session Web)
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        // 4. Lưu thông tin quyền vào session phục vụ giao diện
        session([
            'user_id' => $user->id,
            'user_name' => $user->full_name,
            'user_role' => $user->role,
            'department_id' => $user->department_id,
        ]);

        return redirect()->intended(route('dashboard'))
            ->with('success', 'Đăng nhập thành công! Chào mừng ' . $user->full_name . ' (' . $user->role . ').');
    }

    /**
     * Xử lý đăng xuất phiên làm việc
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('info', 'Bạn đã đăng xuất an toàn khỏi hệ thống.');
    }
}
