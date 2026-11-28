<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ThanhVienHdt;
use App\Support\RolePermissions;
use App\Support\SearchCodeHelper;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ThanhVienHdtWebController extends Controller
{
    /**
     * Danh sách Thành viên Hội đồng trường STU (Khóa IV)
     */
    public function index(Request $request): View|RedirectResponse
    {
        if (Auth::check() && ! RolePermissions::canAccessModule(RolePermissions::currentRole(), 'members')) {
            return redirect()->route('dashboard')->with('error', 'Vai trò của bạn không có quyền truy cập danh sách Thành viên Hội đồng trường.');
        }
        $query = ThanhVienHdt::with(['nguoiDung.donVi', 'nguoiDung.vaiTro']);

        if ($search = trim((string) $request->input('search'))) {
            $memberId = SearchCodeHelper::extractNumericId($search, ['tv']);

            $query->where(function ($q) use ($search, $memberId) {
                $q->where('ChucDanhHoiDongTruong', 'like', "%{$search}%")
                    ->orWhere('ChucDanh', 'like', "%{$search}%")
                    ->orWhereHas('nguoiDung', function ($uQ) use ($search) {
                        $uQ->where('HoTen', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });

                if ($memberId !== null) {
                    $q->orWhere('MaThanhVien', $memberId);
                }
            });
        }

        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [5, 10, 20, 50, 100])) {
            $perPage = 10;
        }

        $members = $query->orderBy('MaThanhVien')->paginate($perPage)->withQueryString();

        $stats = [
            'total' => ThanhVienHdt::count(),
            'voting_eligible' => ThanhVienHdt::where('CoQuyenBieuQuyet', true)->count(),
            'chair' => ThanhVienHdt::where('ChucDanhHoiDongTruong', 'like', '%Chủ tịch%')->count(),
        ];

        return view('pages.thanhvienhdt.danhsach', compact('members', 'stats'));
    }

    /**
     * Ẩn thành viên HĐT khỏi danh sách hiển thị (Soft Delete)
     */
    public function destroy(int|string $id): RedirectResponse
    {
        $role = Auth::user()?->role ?? (session('user_role') ?? 'CHAIR');
        if (!in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN'])) {
            return back()->with('error', 'Bạn không có thẩm quyền thực hiện thao tác này.');
        }

        $member = ThanhVienHdt::findOrFail($id);
        $member->delete();

        return redirect()->route('members.index')
            ->with('success', "Đã ẩn thành viên #{$id} khỏi danh sách hiển thị (dữ liệu được bảo toàn).");
    }
}