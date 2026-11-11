<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ThanhVienHdt;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ThanhVienHdtWebController extends Controller
{
    /**
     * Danh sách Thành viên Hội đồng trường STU (Khóa IV)
     */
    public function index(Request $request): View
    {
        $query = ThanhVienHdt::with(['nguoiDung.donVi', 'nguoiDung.vaiTro']);

        if ($search = $request->input('search')) {
            $query->whereHas('nguoiDung', function ($q) use ($search) {
                $q->where('HoTen', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $members = $query->orderBy('MaThanhVien')->paginate(12)->withQueryString();

        $stats = [
            'total' => ThanhVienHdt::count(),
            'voting_eligible' => ThanhVienHdt::where('CoQuyenBieuQuyet', true)->count(),
            'chair' => ThanhVienHdt::where('ChucDanhHoiDongTruong', 'like', '%Chủ tịch%')->count(),
        ];

        return view('pages.thanhvienhdt.danhsach', compact('members', 'stats'));
    }

    /**
     * Xóa thành viên HĐT khỏi danh sách hiển thị (Soft Delete)
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
            ->with('success', "Đã xóa thành viên #{$id} khỏi danh sách hiển thị.");
    }
}