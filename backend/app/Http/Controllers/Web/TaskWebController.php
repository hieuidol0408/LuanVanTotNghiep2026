<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\DonVi;
use App\Models\NghiQuyet;
use App\Models\NhiemVu;
use App\Models\PhanCongNhiemVu;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TaskWebController extends Controller
{
    /**
     * Danh sách nhiệm vụ phân rã từ Nghị quyết
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $isExecutingUnit = $user && $user->role === 'EXECUTING_UNIT';

        $query = NhiemVu::with(['nghiQuyet', 'phanCongNhiemVu.donVi']);

        // Phân quyền RBAC: Đơn vị thực hiện chỉ thấy nhiệm vụ đơn vị mình tham gia
        if ($isExecutingUnit && $user->MaDonVi) {
            $query->whereHas('phanCongNhiemVu', function ($q) use ($user) {
                $q->where('MaDonVi', $user->MaDonVi);
            });
        }

        // Tìm kiếm
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('TenNhiemVu', 'like', "%{$search}%")
                  ->orWhere('NoiDung', 'like', "%{$search}%");
            });
        }

        // Lọc theo Nghị quyết
        if ($request->filled('resolution_id')) {
            $query->where('MaNghiQuyet', $request->resolution_id);
        }

        // Lọc theo Trạng thái
        if ($request->filled('status')) {
            $query->where('TrangThai', $request->status);
        }

        // Lọc theo Đơn vị thực hiện
        if ($request->filled('department_id')) {
            $query->whereHas('phanCongNhiemVu', function ($q) use ($request) {
                $q->where('MaDonVi', $request->department_id);
            });
        }

        // Thống kê KPIs
        $baseKpiQuery = NhiemVu::query();
        if ($isExecutingUnit && $user->MaDonVi) {
            $baseKpiQuery->whereHas('phanCongNhiemVu', function ($q) use ($user) {
                $q->where('MaDonVi', $user->MaDonVi);
            });
        }

        $totalTasks = (clone $baseKpiQuery)->count();
        $inProgressTasks = (clone $baseKpiQuery)->whereIn('TrangThai', ['IN_PROGRESS', 'Đang thực hiện'])->count();
        $completedTasks = (clone $baseKpiQuery)->whereIn('TrangThai', ['COMPLETED', 'Hoàn tất', 'Đã hoàn thành'])->count();
        $overdueTasks = (clone $baseKpiQuery)
            ->whereNotIn('TrangThai', ['COMPLETED', 'Hoàn tất', 'Đã hoàn thành'])
            ->where(function ($q) {
                $q->where('TrangThai', 'OVERDUE')
                  ->orWhere(function ($sub) {
                      $sub->whereNotNull('NgayHoanThanh')
                          ->where('NgayHoanThanh', '<', now()->toDateString());
                  });
            })->count();

        $tasks = $query->orderByDesc('MaNhiemVu')->paginate(10)->withQueryString();
        $resolutions = NghiQuyet::orderByDesc('MaNghiQuyet')->get(['MaNghiQuyet', 'SoNghiQuyet', 'TrichYeu']);
        $departments = DonVi::orderBy('TenDonVi')->get(['MaDonVi', 'TenDonVi']);

        return view('pages.tasks.index', compact(
            'tasks',
            'resolutions',
            'departments',
            'totalTasks',
            'inProgressTasks',
            'completedTasks',
            'overdueTasks'
        ));
    }

    /**
     * Form giao nhiệm vụ mới từ Nghị quyết
     */
    public function create(Request $request): View|RedirectResponse
    {
        $user = Auth::user();
        if ($user && $user->role === 'EXECUTING_UNIT') {
            return redirect()->route('tasks.index')->with('error', 'Đơn vị thực hiện không có quyền giao nhiệm vụ mới.');
        }

        $resolutions = NghiQuyet::where('TrangThai', 'PUBLISHED')
            ->orderByDesc('NgayBanHanh')
            ->get(['MaNghiQuyet', 'SoNghiQuyet', 'TrichYeu']);

        $departments = DonVi::orderBy('TenDonVi')->get(['MaDonVi', 'TenDonVi']);
        $selectedResolutionId = $request->query('resolution_id');

        return view('pages.tasks.create', compact('resolutions', 'departments', 'selectedResolutionId'));
    }

    /**
     * Lưu nhiệm vụ mới và phân công đơn vị chủ trì, phối hợp (BR-10)
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if ($user && $user->role === 'EXECUTING_UNIT') {
            return redirect()->route('tasks.index')->with('error', 'Bạn không có quyền thực hiện thao tác này.');
        }

        $request->validate([
            'MaNghiQuyet' => 'required|exists:nghi_quyet,MaNghiQuyet',
            'TenNhiemVu' => 'required|string|max:255',
            'NoiDung' => 'nullable|string',
            'NgayGiao' => 'required|date',
            'NgayHoanThanh' => 'required|date|after_or_equal:NgayGiao',
            'chu_tri_don_vi_id' => 'required|exists:don_vi,MaDonVi',
            'chu_tri_nguoi_phu_trach' => 'required|string|max:255',
            'phoi_hop_don_vi_ids' => 'nullable|array',
            'phoi_hop_don_vi_ids.*' => 'exists:don_vi,MaDonVi',
        ], [
            'MaNghiQuyet.required' => 'Vui lòng chọn Nghị quyết căn cứ.',
            'TenNhiemVu.required' => 'Tên nhiệm vụ không được để trống.',
            'NgayGiao.required' => 'Vui lòng chọn ngày giao nhiệm vụ.',
            'NgayHoanThanh.required' => 'Vui lòng chọn hạn hoàn thành nhiệm vụ.',
            'NgayHoanThanh.after_or_equal' => 'Hạn hoàn thành phải sau hoặc bằng ngày giao nhiệm vụ.',
            'chu_tri_don_vi_id.required' => 'Mỗi nhiệm vụ phải có ít nhất 1 đơn vị Chủ trì thực hiện (BR-10).',
            'chu_tri_nguoi_phu_trach.required' => 'Vui lòng nhập tên người phụ trách đầu mối phía đơn vị chủ trì.',
        ]);

        DB::beginTransaction();
        try {
            // Tạo bản ghi nhiệm vụ
            $task = NhiemVu::create([
                'MaNghiQuyet' => $request->MaNghiQuyet,
                'TenNhiemVu' => $request->TenNhiemVu,
                'NoiDung' => $request->NoiDung,
                'NgayGiao' => $request->NgayGiao,
                'NgayGiaoNhiemVu' => $request->NgayGiao,
                'NgayHoanThanh' => $request->NgayHoanThanh,
                'NgayHoanThanhNhiemVu' => $request->NgayHoanThanh,
                'TrangThai' => 'ASSIGNED',
            ]);

            // Ghi nhận Đơn vị Chủ trì (Bắt buộc theo BR-10)
            PhanCongNhiemVu::create([
                'MaNhiemVu' => $task->MaNhiemVu,
                'MaDonVi' => $request->chu_tri_don_vi_id,
                'VaiTroThucHien' => 'CHỦ TRÌ',
                'TrangThaiThucHien' => 'Chưa thực hiện',
                'TenNguoiPhuTrach' => $request->chu_tri_nguoi_phu_trach,
            ]);

            // Ghi nhận Đơn vị Phối hợp (Nếu có)
            if ($request->filled('phoi_hop_don_vi_ids')) {
                foreach ($request->phoi_hop_don_vi_ids as $coopUnitId) {
                    if ($coopUnitId != $request->chu_tri_don_vi_id) {
                        $contactPerson = $request->input("phoi_hop_nguoi_phu_trach_{$coopUnitId}", 'Đại diện phối hợp');
                        PhanCongNhiemVu::create([
                            'MaNhiemVu' => $task->MaNhiemVu,
                            'MaDonVi' => $coopUnitId,
                            'VaiTroThucHien' => 'PHỐI HỢP',
                            'TrangThaiThucHien' => 'Chưa thực hiện',
                            'TenNguoiPhuTrach' => $contactPerson ?: 'Đại diện phối hợp',
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('tasks.show', $task->MaNhiemVu)
                ->with('success', 'Giao nhiệm vụ và phân công đơn vị chủ trì, phối hợp thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Lỗi khi giao nhiệm vụ: ' . $e->getMessage());
        }
    }

    /**
     * Chi tiết nhiệm vụ, tiến độ và lịch sử báo cáo
     */
    public function show(int $id): View|RedirectResponse
    {
        $task = NhiemVu::with([
            'nghiQuyet',
            'phanCongNhiemVu.donVi',
            'phanCongNhiemVu.baoCao.nguoiDung',
            'phanCongNhiemVu.baoCao.taiLieu',
        ])->findOrFail($id);

        $user = Auth::user();
        $isExecutingUnit = $user && $user->role === 'EXECUTING_UNIT';

        // Nếu là Đơn vị thực hiện, kiểm tra xem đơn vị này có được phân công nhiệm vụ hay không
        if ($isExecutingUnit && $user->MaDonVi) {
            $isAssigned = $task->phanCongNhiemVu->contains('MaDonVi', $user->MaDonVi);
            if (!$isAssigned) {
                return redirect()->route('tasks.index')->with('error', 'Bạn không có quyền truy cập nhiệm vụ của đơn vị khác.');
            }
        }

        // Tính toán tiến độ cao nhất từ các báo cáo đã duyệt hoặc mới nhất
        $allReports = $task->phanCongNhiemVu->flatMap->baoCao;
        $latestReport = $allReports->sortByDesc('MaBaoCao')->first();
        $currentProgress = $latestReport ? (int)$latestReport->TienDo : 0;

        return view('pages.tasks.show', compact('task', 'allReports', 'currentProgress'));
    }

    /**
     * Form chỉnh sửa nhiệm vụ
     */
    public function edit(int $id): View|RedirectResponse
    {
        $user = Auth::user();
        if ($user && $user->role === 'EXECUTING_UNIT') {
            return redirect()->route('tasks.index')->with('error', 'Bạn không có quyền chỉnh sửa thông tin nhiệm vụ.');
        }

        $task = NhiemVu::with(['nghiQuyet', 'phanCongNhiemVu.donVi'])->findOrFail($id);
        $resolutions = NghiQuyet::orderByDesc('MaNghiQuyet')->get(['MaNghiQuyet', 'SoNghiQuyet', 'TrichYeu']);
        $departments = DonVi::orderBy('TenDonVi')->get(['MaDonVi', 'TenDonVi']);

        $leadAssignment = $task->phanCongNhiemVu->firstWhere('VaiTroThucHien', 'CHỦ TRÌ');
        $coopAssignments = $task->phanCongNhiemVu->where('VaiTroThucHien', 'PHỐI HỢP');

        return view('pages.tasks.edit', compact('task', 'resolutions', 'departments', 'leadAssignment', 'coopAssignments'));
    }

    /**
     * Cập nhật thông tin nhiệm vụ
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $user = Auth::user();
        if ($user && $user->role === 'EXECUTING_UNIT') {
            return redirect()->route('tasks.index')->with('error', 'Bạn không có quyền chỉnh sửa thông tin nhiệm vụ.');
        }

        $task = NhiemVu::findOrFail($id);

        $request->validate([
            'TenNhiemVu' => 'required|string|max:255',
            'NoiDung' => 'nullable|string',
            'NgayGiao' => 'required|date',
            'NgayHoanThanh' => 'required|date|after_or_equal:NgayGiao',
            'TrangThai' => 'required|string|in:ASSIGNED,IN_PROGRESS,COMPLETED,OVERDUE',
        ]);

        $task->update([
            'TenNhiemVu' => $request->TenNhiemVu,
            'NoiDung' => $request->NoiDung,
            'NgayGiao' => $request->NgayGiao,
            'NgayGiaoNhiemVu' => $request->NgayGiao,
            'NgayHoanThanh' => $request->NgayHoanThanh,
            'NgayHoanThanhNhiemVu' => $request->NgayHoanThanh,
            'TrangThai' => $request->TrangThai,
            'NgayXacNhanHoanThanh' => $request->TrangThai === 'COMPLETED' ? ($task->NgayXacNhanHoanThanh ?? now()->toDateString()) : null,
        ]);

        return redirect()->route('tasks.show', $task->MaNhiemVu)->with('success', 'Cập nhật thông tin nhiệm vụ thành công!');
    }

    /**
     * Hủy/Xóa nhiệm vụ
     */
    public function destroy(int $id): RedirectResponse
    {
        $user = Auth::user();
        if ($user && !in_array($user->role, ['SECRETARY', 'CHAIR', 'ADMIN'])) {
            return redirect()->route('tasks.index')->with('error', 'Bạn không có quyền xóa nhiệm vụ.');
        }

        $task = NhiemVu::findOrFail($id);

        DB::beginTransaction();
        try {
            // Xóa các phân công và liên kết
            foreach ($task->phanCongNhiemVu as $pc) {
                $pc->baoCao()->delete();
                $pc->delete();
            }
            $task->delete();
            DB::commit();

            return redirect()->route('tasks.index')->with('success', 'Đã xóa nhiệm vụ và các phân công thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Lỗi khi xóa nhiệm vụ: ' . $e->getMessage());
        }
    }
}
