<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ThanhVienHdt;
use Illuminate\Http\Request;
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
}
