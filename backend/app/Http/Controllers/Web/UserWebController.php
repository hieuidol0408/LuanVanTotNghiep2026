<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\DonVi;
use App\Models\User;
use App\Models\VaiTro;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UserWebController extends Controller
{
    /**
     * Danh sách tài khoản người dùng và phân quyền RBAC
     */
    public function index(Request $request): View|RedirectResponse
    {
        $currentUser = Auth::user();
        if ($currentUser && !in_array($currentUser->role, ['ADMIN', 'CHAIR', 'SECRETARY'])) {
            return redirect()->route('dashboard')->with('error', 'Bạn không có quyền truy cập danh sách quản trị người dùng.');
        }

        $query = User::with(['vaiTro', 'donVi', 'thanhVienHdt']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('HoTen', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($roleId = $request->input('role_id')) {
            $query->where('MaVaiTro', $roleId);
        }

        if ($deptId = $request->input('department_id')) {
            $query->where('MaDonVi', $deptId);
        }

        $users = $query->orderBy('MaNguoiDung')->paginate(15)->withQueryString();
        $roles = VaiTro::all();
        $departments = DonVi::orderBy('TenDonVi')->get();

        return view('pages.users.index', compact('users', 'roles', 'departments'));
    }
}
