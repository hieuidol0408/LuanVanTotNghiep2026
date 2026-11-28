<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ChuongTrinhHop;
use App\Models\HoSoTrinh;
use App\Models\PhienHop;
use App\Support\RolePermissions;
use App\Support\SearchCodeHelper;
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
    public function index(Request $request): View|RedirectResponse
    {
        if (Auth::check() && ! RolePermissions::canAccessModule(RolePermissions::currentRole(), 'agendas')) {
            return redirect()->route('dashboard')->with('error', 'Vai trò của bạn không có quyền truy cập phân hệ Chương trình họp.');
        }
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
            'phienHop.nguoiDung.vaiTro',
            'hoSoTrinh.donVi',
            'hoSoTrinh.nguoiDung',
            'nguoiDung.vaiTro',
            'noiDungBieuQuyet',
        ]);

        // 3. Lọc theo Phiên họp
        $selectedMeetingId = $request->input('meeting_id');
        if ($selectedMeetingId && $selectedMeetingId !== 'ALL') {
            $query->where('MaPhienHop', $selectedMeetingId);
        }

        // 4. Tìm kiếm từ khóa (Tiêu đề hồ sơ, mã chương trình, mã hồ sơ, mã phiên họp, ghi chú)
        if ($search = trim((string) $request->input('search'))) {
            $agendaId = SearchCodeHelper::extractNumericId($search, ['ct', 'cth']);
            $meetingId = SearchCodeHelper::extractNumericId($search, ['ph']);
            $matchedDossierIds = SearchCodeHelper::findMatchingDossierIds($search);

            $query->where(function ($q) use ($search, $agendaId, $meetingId, $matchedDossierIds) {
                $q->where('GhiChu', 'like', "%{$search}%")
                    ->orWhereHas('hoSoTrinh', function ($hq) use ($search, $matchedDossierIds) {
                        $hq->where('TieuDe', 'like', "%{$search}%")
                            ->orWhere('NoiDungTomTat', 'like', "%{$search}%")
                            ->orWhere('MaHoSo', 'like', "%{$search}%");

                        if (!empty($matchedDossierIds)) {
                            $hq->orWhereIn('MaHoSo', $matchedDossierIds);
                        }
                    })
                    ->orWhereHas('phienHop', function ($mq) use ($search, $meetingId) {
                        $mq->where('TenPhienHop', 'like', "%{$search}%");

                        if ($meetingId !== null) {
                            $mq->orWhere('MaPhienHop', $meetingId);
                        }
                    });

                if ($agendaId !== null) {
                    $q->orWhere('MaChuongTrinh', $agendaId);
                }
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
        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [5, 10, 20, 50, 100])) {
            $perPage = 10;
        }

        $agendas = $query->paginate($perPage)->withQueryString();

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
            'MaPhienHop.exists' => 'Phiên họp được chọn không tồn tại.',
            'MaHoSo.required' => 'Vui lòng chọn hồ sơ trình đưa vào chương trình.',
            'MaHoSo.exists' => 'Hồ sơ trình được chọn không tồn tại.',
            'ThuTu.min' => 'Thứ tự xem xét phải lớn hơn hoặc bằng 1.',
        ]);

        // [Ràng buộc 1] Chống đưa trùng 1 hồ sơ nhiều lần vào cùng một phiên họp (BR-08)
        $dossierExists = ChuongTrinhHop::where('MaPhienHop', $validated['MaPhienHop'])
            ->where('MaHoSo', $validated['MaHoSo'])
            ->exists();
        if ($dossierExists) {
            $msg = 'Hồ sơ trình này đã có trong chương trình nghị sự của phiên họp này (Tuân thủ BR-08).';
            return back()
                ->withInput()
                ->with('error', $msg)
                ->withErrors(['MaHoSo' => $msg]);
        }

        // [Ràng buộc 2] Cột Thứ Tự không được trùng khi thêm mới trong cùng một phiên họp
        if (! empty($validated['ThuTu'])) {
            $thuTu = (int) $validated['ThuTu'];
            $orderConflict = ChuongTrinhHop::where('MaPhienHop', $validated['MaPhienHop'])
                ->where('ThuTu', $thuTu)
                ->first();

            if ($orderConflict) {
                $conflictName = $orderConflict->hoSoTrinh?->TieuDe ?? ('Hồ sơ #' . $orderConflict->MaHoSo);
                $msg = "Thứ tự xem xét #{$thuTu} đã được sử dụng cho mục: \"{$conflictName}\" trong phiên họp này. Vui lòng chọn số thứ tự khác hoặc để trống để hệ thống tự động tăng.";
                return back()
                    ->withInput()
                    ->with('error', $msg)
                    ->withErrors(['ThuTu' => $msg]);
            }
        } else {
            $maxOrder = ChuongTrinhHop::where('MaPhienHop', $validated['MaPhienHop'])->max('ThuTu') ?? 0;
            $thuTu = $maxOrder + 1;
        }

        ChuongTrinhHop::create([
            'MaPhienHop' => $validated['MaPhienHop'],
            'MaHoSo' => $validated['MaHoSo'],
            'ThuTu' => $thuTu,
            'TrangThai' => $validated['TrangThai'] ?? 'Chưa xem xét',
            'GhiChu' => $validated['GhiChu'] ?? null,
            'MaNguoiDung' => Auth::id(),
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
            'ThuTu.integer' => 'Thứ tự xem xét phải là số nguyên.',
            'ThuTu.min' => 'Thứ tự xem xét phải lớn hơn hoặc bằng 1.',
            'TrangThai.required' => 'Vui lòng chọn trạng thái xử lý.',
        ]);

        $newOrder = (int) $validated['ThuTu'];

        // [Ràng buộc dữ liệu] Cột Thứ Tự không được trùng khi thay đổi/cập nhật với mục khác trong cùng một phiên họp
        $duplicate = ChuongTrinhHop::where('MaPhienHop', $agenda->MaPhienHop)
            ->where('ThuTu', $newOrder)
            ->where('MaChuongTrinh', '!=', $agenda->MaChuongTrinh)
            ->first();

        if ($duplicate) {
            $conflictName = $duplicate->hoSoTrinh?->TieuDe ?? ('Hồ sơ #' . $duplicate->MaHoSo);
            $msg = "Thứ tự xem xét #{$newOrder} đã được sử dụng cho mục: \"{$conflictName}\" trong phiên họp này. Vui lòng chọn số thứ tự khác để tránh trùng lặp.";
            return back()
                ->withInput()
                ->with('error', $msg)
                ->withErrors(['ThuTu' => $msg]);
        }

        $agenda->update([
            'ThuTu' => $newOrder,
            'TrangThai' => $validated['TrangThai'],
            'GhiChu' => $validated['GhiChu'] ?? null,
        ]);

        $msg = "Đã cập nhật mục chương trình họp (Thứ tự #{$agenda->ThuTu} - {$agenda->TrangThai}) thành công!";
        if (str_contains((string) url()->previous(), '/meetings/')) {
            return redirect()->route('meetings.show', ['id' => $agenda->MaPhienHop, 'tab' => 'agenda'])->with('success', $msg);
        }

        return back()->with('success', $msg);
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
        $meetingId = $agenda->MaPhienHop;
        $agenda->update(['TrangThai' => 'Đã ẩn']);
        $agenda->delete();

        if (str_contains((string) url()->previous(), '/meetings/')) {
            return redirect()->route('meetings.show', ['id' => $meetingId, 'tab' => 'agenda'])
                ->with('success', 'Đã ẩn mục khỏi chương trình họp (Soft Delete).');
        }

        return back()->with('success', 'Đã ẩn mục khỏi chương trình họp (Soft Delete).');
    }
}
