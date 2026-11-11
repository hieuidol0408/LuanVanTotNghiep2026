<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ChuongTrinhHop;
use App\Models\HoSoTrinh;
use App\Models\PhienHop;
use Illuminate\Http\RedirectResponse;
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
        $role = $user?->role ?? (session('user_role') ?? 'CHAIR');

        // 1. Danh sách phiên họp phục vụ bộ lọc và thêm mới
        $meetings = PhienHop::orderBy('MaPhienHop', 'asc')->get();

        // Danh sách hồ sơ trình đưa vào chương trình họp (BR-08)
        $availableDossiers = HoSoTrinh::with('donVi')
            ->orderBy('MaHoSo', 'asc')
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
            $query->orderBy('MaPhienHop', 'asc')->orderBy('ThuTu', 'asc');
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

        return view('pages.chuongtrinhhop.danhsach', compact(
            'agendas',
            'meetings',
            'availableDossiers',
            'selectedMeeting',
            'totalAgendas',
            'totalDossiers',
            'approvedAgendas',
            'activeMeetingsCount',
            'role'
        ));
    }

    /**
     * [BR-08] Đưa hồ sơ trình mới vào chương trình họp
     */
    public function store(Request $request): RedirectResponse
    {
        $role = Auth::user()?->role ?? (session('user_role') ?? 'CHAIR');
        if (! in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN'])) {
            return back()->with('error', 'Chỉ Chủ tịch hoặc Thư ký Hội đồng trường mới có quyền cập nhật chương trình họp.');
        }

        $validated = $request->validate([
            'MaPhienHop' => ['required', 'integer', 'exists:phien_hop,MaPhienHop'],
            'MaHoSo' => ['required', 'integer', 'exists:ho_so_trinh,MaHoSo'],
            'ThuTu' => ['nullable', 'integer', 'min:1'],
            'TrangThai' => ['nullable', 'string'],
            'GhiChu' => ['nullable', 'string', 'max:1000'],
        ], [
            'MaPhienHop.required' => 'Vui lòng chọn phiên họp.',
            'MaHoSo.required' => 'Vui lòng chọn hồ sơ trình đưa vào chương trình.',
        ]);

        $maxOrder = ChuongTrinhHop::where('MaPhienHop', $validated['MaPhienHop'])->max('ThuTu') ?? 0;
        $thuTu = $validated['ThuTu'] ?? ($maxOrder + 1);

        ChuongTrinhHop::create([
            'MaPhienHop' => $validated['MaPhienHop'],
            'MaHoSo' => $validated['MaHoSo'],
            'ThuTu' => $thuTu,
            'TrangThai' => $validated['TrangThai'] ?? 'Chưa xem xét',
            'GhiChu' => $validated['GhiChu'] ?? null,
        ]);

        return back()->with('success', "Đã thêm mục mới vào chương trình họp (Thứ tự #{$thuTu}) thành công!");
    }

    /**
     * Cập nhật mục chương trình họp (Thứ tự xem xét, Trạng thái xử lý, Ghi chú kịch bản)
     */
    public function update(Request $request, int|string $id): RedirectResponse
    {
        $role = Auth::user()?->role ?? (session('user_role') ?? 'CHAIR');
        if (! in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN'])) {
            return back()->with('error', 'Chỉ Chủ tịch hoặc Thư ký Hội đồng trường mới có quyền cập nhật chương trình họp.');
        }

        $agenda = ChuongTrinhHop::findOrFail($id);

        $validated = $request->validate([
            'ThuTu' => ['required', 'integer', 'min:1'],
            'TrangThai' => ['required', 'string', 'in:Chưa xem xét,Đang thảo luận,Đã thông qua,Tạm hoãn'],
            'GhiChu' => ['nullable', 'string', 'max:1000'],
        ], [
            'ThuTu.required' => 'Vui lòng nhập thứ tự xem xét.',
            'ThuTu.min' => 'Thứ tự xem xét phải lớn hơn hoặc bằng 1.',
            'TrangThai.required' => 'Vui lòng chọn trạng thái xử lý.',
        ]);

        $agenda->update([
            'ThuTu' => $validated['ThuTu'],
            'TrangThai' => $validated['TrangThai'],
            'GhiChu' => $validated['GhiChu'] ?? null,
        ]);

        return back()->with('success', "Đã cập nhật mục chương trình họp (Thứ tự #{$agenda->ThuTu} - {$agenda->TrangThai}) thành công!");
    }

    /**
     * Ẩn mục khỏi chương trình họp (Soft Delete - Không xóa cứng khỏi CSDL)
     */
    public function destroy(int|string $id): RedirectResponse
    {
        $role = Auth::user()?->role ?? (session('user_role') ?? 'CHAIR');
        if (! in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN'])) {
            return back()->with('error', 'Bạn không có quyền ẩn mục chương trình họp.');
        }

        $agenda = ChuongTrinhHop::findOrFail($id);
        $agenda->update(['TrangThai' => 'Đã ẩn']);
        $agenda->delete();

        return back()->with('success', 'Đã ẩn mục khỏi chương trình họp (Soft Delete).');
    }
}
