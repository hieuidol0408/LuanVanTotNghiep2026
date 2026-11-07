<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ChuongTrinhHop;
use App\Models\PhienHop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Controller Quản lý Chương trình họp Hội đồng trường (BR-08)
 * Tên file: ChuongTrinhHopWebController.php (Quy chuẩn Tiếng Việt không dấu không cách)
 */
class ChuongTrinhHopWebController extends Controller
{
    /**
     * Danh sách chương trình nghị sự tổng hợp qua các phiên họp
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $role = $user?->role ?? 'GUEST';

        // 1. Danh sách phiên họp phục vụ bộ lọc
        $meetings = PhienHop::orderByDesc('NgayHop')
            ->orderByDesc('MaPhienHop')
            ->get();

        // 2. Query chương trình họp có eager load
        $query = ChuongTrinhHop::with([
            'phienHop',
            'hoSoTrinh.donVi',
            'hoSoTrinh.nguoiDung',
            'noiDungBieuQuyet',
        ]);

        // 3. Lọc theo Phiên họp
        $selectedMeetingId = $request->input('meeting_id');
        if ($selectedMeetingId && $selectedMeetingId !== 'ALL') {
            $query->where('MaPhienHop', $selectedMeetingId);
        }

        // 4. Tìm kiếm từ khóa (Tiêu đề hồ sơ, mã hồ sơ, ghi chú)
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('GhiChu', 'like', "%{$search}%")
                    ->orWhereHas('hoSoTrinh', function ($hq) use ($search) {
                        $hq->where('TieuDe', 'like', "%{$search}%")
                            ->orWhere('NoiDungTomTat', 'like', "%{$search}%")
                            ->orWhere('MaHoSo', 'like', "%{$search}%");
                    })
                    ->orWhereHas('phienHop', function ($mq) use ($search) {
                        $mq->where('TenPhienHop', 'like', "%{$search}%");
                    });
            });
        }

        // 5. Lọc theo trạng thái xử lý
        if ($status = $request->input('status')) {
            if ($status !== 'ALL') {
                $query->where('TrangThai', $status);
            }
        }

        // 6. Sắp xếp thứ tự
        if ($selectedMeetingId && $selectedMeetingId !== 'ALL') {
            $query->orderBy('ThuTu', 'asc');
        } else {
            $query->orderByDesc('MaPhienHop')->orderBy('ThuTu', 'asc');
        }

        // 7. Phân trang
        $agendas = $query->paginate(15)->withQueryString();

        // 8. Chỉ số KPI thống kê
        $totalAgendas = ChuongTrinhHop::count();
        $totalDossiers = ChuongTrinhHop::distinct('MaHoSo')->count('MaHoSo');
        $approvedAgendas = ChuongTrinhHop::where('TrangThai', 'Đã thông qua')->count();
        $activeMeetingsCount = ChuongTrinhHop::distinct('MaPhienHop')->count('MaPhienHop');

        // 9. Phiên họp đang được chọn chi tiết nếu có
        $selectedMeeting = ($selectedMeetingId && $selectedMeetingId !== 'ALL')
            ? $meetings->firstWhere('MaPhienHop', $selectedMeetingId)
            : null;

        return view('pages.chuongtrinhhop.index', compact(
            'agendas',
            'meetings',
            'selectedMeeting',
            'totalAgendas',
            'totalDossiers',
            'approvedAgendas',
            'activeMeetingsCount',
            'role'
        ));
    }
}
