<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\HoSoTrinh;
use App\Models\PhanCongNhiemVu;
use App\Models\ThamDu;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class HoSoCaNhanWebController extends Controller
{
    /**
     * Hiển thị thông tin Hồ sơ cá nhân của người dùng đang đăng nhập
     */
    public function show(Request $request): View
    {
        /** @var User|null $user */
        $user = Auth::user();

        // Fallback lấy tài khoản đầu tiên nếu chạy chế độ test / demo chưa qua session
        if (! $user) {
            $user = User::with(['vaiTro', 'donVi', 'thanhVienHdt'])->first();
        } else {
            $user->loadMissing(['vaiTro', 'donVi', 'thanhVienHdt']);
        }

        $thanhVien = $user->thanhVienHdt;

        // Thống kê hoạt động của người dùng
        $meetingsAttendedCount = 0;
        $recentAttendances = collect();
        if ($thanhVien) {
            $meetingsAttendedCount = ThamDu::where('MaThanhVien', $thanhVien->MaThanhVien)->count();
            $recentAttendances = ThamDu::with('phienHop')
                ->where('MaThanhVien', $thanhVien->MaThanhVien)
                ->orderByDesc('MaThamDu')
                ->take(5)
                ->get();
        }

        $submissionsCount = HoSoTrinh::where('MaNguoiDung', $user->MaNguoiDung)->count();
        $tasksCount = $user->MaDonVi ? PhanCongNhiemVu::where('MaDonVi', $user->MaDonVi)->count() : 0;

        $stats = [
            'meetings_count' => $meetingsAttendedCount,
            'submissions_count' => $submissionsCount,
            'tasks_count' => $tasksCount,
            'role' => $user->role,
            'nhiem_ky' => $thanhVien?->NhiemKy ?? 'Khóa IV (2020 - 2025)',
            'chuc_danh_hdt' => $thanhVien?->ChucDanh ?? ($user->role === 'CHAIR' ? 'Chủ tịch Hội đồng trường' : ($user->role === 'SECRETARY' ? 'Thư ký Hội đồng trường' : 'Thành viên Hội đồng trường')),
            'co_quyen_bieu_quyet' => (bool) ($thanhVien?->CoQuyenBieuQuyet ?? in_array($user->role, ['CHAIR', 'SECRETARY', 'MEMBER'])),
        ];

        return view('pages.hosocanhan.index', compact('user', 'thanhVien', 'stats', 'recentAttendances'));
    }

    /**
     * Cập nhật thông tin cơ bản & đổi mật khẩu tài khoản cá nhân
     */
    public function update(Request $request): RedirectResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            $user = User::first();
        }

        $validated = $request->validate([
            'HoTen' => ['required', 'string', 'max:255'],
            'current_password' => ['nullable', 'string'],
            'new_password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ], [
            'HoTen.required' => 'Họ và tên không được để trống.',
            'new_password.min' => 'Mật khẩu mới phải có ít nhất 6 ký tự.',
            'new_password.confirmed' => 'Xác nhận mật khẩu mới không khớp.',
        ]);

        $user->HoTen = trim($validated['HoTen']);

        // Nếu người dùng có yêu cầu đổi mật khẩu
        if (! empty($validated['new_password'])) {
            if (! empty($validated['current_password'])) {
                if (! Hash::check($validated['current_password'], $user->getAuthPassword())) {
                    return back()->withErrors([
                        'current_password' => 'Mật khẩu hiện tại không chính xác.',
                    ]);
                }
            }
            $user->MatKhau = Hash::make($validated['new_password']);
        }

        $user->save();

        // Cập nhật lại session hiển thị nếu đổi tên
        session(['user_name' => $user->HoTen]);

        return redirect()->route('profile.show')
            ->with('success', 'Cập nhật thông tin hồ sơ cá nhân thành công!');
    }
}
