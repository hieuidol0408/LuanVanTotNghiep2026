<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BaoCao;
use App\Models\DonVi;
use App\Models\NhiemVu;
use App\Models\PhanCongNhiemVu;
use App\Models\TaiLieu;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BaoCaoWebController extends Controller
{
    /**
     * Danh sách báo cáo tiến độ nhiệm vụ
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $isExecutingUnit = $user && $user->role === 'EXECUTING_UNIT';

        $query = BaoCao::with([
            'phanCongNhiemVu.nhiemVu.nghiQuyet',
            'phanCongNhiemVu.donVi',
            'nguoiDung',
            'taiLieu',
        ]);

        // Phân quyền RBAC: Đơn vị chỉ thấy báo cáo thuộc đơn vị mình
        if ($isExecutingUnit && $user->MaDonVi) {
            $query->whereHas('phanCongNhiemVu', function ($q) use ($user) {
                $q->where('MaDonVi', $user->MaDonVi);
            });
        }

        // Lọc theo trạng thái kiểm tra/phê duyệt
        if ($request->filled('status')) {
            $query->where('TrangThaiXacNhan', $request->status);
        }

        // Lọc theo nhiệm vụ
        if ($request->filled('task_id')) {
            $query->whereHas('phanCongNhiemVu', function ($q) use ($request) {
                $q->where('MaNhiemVu', $request->task_id);
            });
        }

        // Lọc theo đơn vị thực hiện
        if ($request->filled('department_id')) {
            $query->whereHas('phanCongNhiemVu', function ($q) use ($request) {
                $q->where('MaDonVi', $request->department_id);
            });
        }

        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [5, 10, 20, 50, 100])) {
            $perPage = 10;
        }

        $reports = $query->orderByDesc('MaBaoCao')->paginate($perPage)->withQueryString();
        $departments = DonVi::orderBy('TenDonVi')->get(['MaDonVi', 'TenDonVi']);

        return view('pages.baocao.danhsach', compact('reports', 'departments'));
    }

    /**
     * Form nộp báo cáo tiến độ
     */
    public function create(Request $request): View|RedirectResponse
    {
        $user = Auth::user();
        $isExecutingUnit = $user && $user->role === 'EXECUTING_UNIT';

        $assignmentsQuery = PhanCongNhiemVu::with(['nhiemVu.nghiQuyet', 'donVi'])
            ->whereHas('nhiemVu', function ($q) {
                $q->where('TrangThai', '!=', 'COMPLETED');
            });

        // Nếu là Đơn vị thực hiện, chỉ cho phép chọn nhiệm vụ đơn vị mình được giao
        if ($isExecutingUnit && $user->MaDonVi) {
            $assignmentsQuery->where('MaDonVi', $user->MaDonVi);
        }

        $assignments = $assignmentsQuery->get();

        if ($assignments->isEmpty()) {
            return redirect()->route('tasks.index')->with('warning', 'Hiện tại không có nhiệm vụ đang thực hiện nào cần nộp báo cáo.');
        }

        $selectedTaskId = $request->query('task_id');
        $selectedAssignmentId = null;
        if ($selectedTaskId) {
            $match = $assignments->firstWhere('MaNhiemVu', $selectedTaskId);
            if ($match) {
                $selectedAssignmentId = $match->MaPhanCong;
            }
        }

        return view('pages.baocao.taomoi', compact('assignments', 'selectedAssignmentId'));
    }

    /**
     * Lưu báo cáo tiến độ (BR-10: Tiến độ 0-100%, ghi nhận khó khăn, file đính kèm)
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $request->validate([
            'MaPhanCong' => 'required|exists:phan_cong_nhiem_vu,MaPhanCong',
            'TienDo' => 'required|integer|min:0|max:100',
            'NoiDung' => 'required|string',
            'NoiDungKhoKhan' => 'nullable|string',
            'file_minh_chung' => 'nullable|file|max:20480',
        ], [
            'MaPhanCong.required' => 'Vui lòng chọn nhiệm vụ cần báo cáo.',
            'TienDo.required' => 'Vui lòng nhập tỷ lệ tiến độ thực hiện.',
            'TienDo.min' => 'Tiến độ không thể nhỏ hơn 0%.',
            'TienDo.max' => 'Tiến độ tối đa là 100%.',
            'NoiDung.required' => 'Vui lòng nhập nội dung chi tiết tiến độ công việc.',
            'file_minh_chung.max' => 'Dung lượng tệp đính kèm không vượt quá 20MB.',
        ]);

        $assignment = PhanCongNhiemVu::with('nhiemVu')->findOrFail($request->MaPhanCong);

        // Kiểm tra quyền nộp báo cáo: Đơn vị chỉ được nộp cho phân công của đơn vị mình
        if ($user && $user->role === 'EXECUTING_UNIT' && $user->MaDonVi) {
            if ($assignment->MaDonVi != $user->MaDonVi) {
                return back()->withInput()->with('error', 'Bạn không có quyền nộp báo cáo cho đơn vị khác.');
            }
        }

        DB::beginTransaction();
        try {
            $report = BaoCao::create([
                'MaPhanCong' => $assignment->MaPhanCong,
                'MaNguoiDung' => $user ? $user->MaNguoiDung : null,
                'NgayBaoCao' => now()->toDateString(),
                'TienDo' => $request->TienDo,
                'NoiDung' => $request->NoiDung,
                'NoiDungTienDo' => $request->NoiDung,
                'NoiDungKhoKhan' => $request->NoiDungKhoKhan,
                'TrangThaiXacNhan' => 'Chờ kiểm tra',
            ]);

            // Xử lý tệp minh chứng đính kèm nếu có
            if ($request->hasFile('file_minh_chung')) {
                $file = $request->file('file_minh_chung');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('reports', $filename, 'public');

                $taiLieu = TaiLieu::create([
                    'TenTaiLieu' => $file->getClientOriginalName(),
                    'LoaiTaiLieu' => 'Minh chứng báo cáo',
                    'DuongDan' => 'storage/' . $path,
                    'DinhDang' => $file->getClientOriginalExtension(),
                    'DungLuong' => (int)($file->getSize() / 1024),
                    'NgayTaiLen' => now(),
                    'MaNguoiDung' => $user ? $user->MaNguoiDung : null,
                ]);

                DB::table('tai_lieu_bao_cao')->insert([
                    'MaTaiLieu' => $taiLieu->MaTaiLieu,
                    'MaBaoCao' => $report->MaBaoCao,
                ]);
            }

            // Chuyển trạng thái phân công & nhiệm vụ sang Đang thực hiện
            if ($assignment->TrangThaiThucHien === 'Chưa thực hiện') {
                $assignment->update(['TrangThaiThucHien' => 'Đang thực hiện']);
            }

            if ($assignment->nhiemVu && $assignment->nhiemVu->TrangThai === 'ASSIGNED') {
                $assignment->nhiemVu->update(['TrangThai' => 'IN_PROGRESS']);
            }

            DB::commit();

            return redirect()->route('tasks.show', $assignment->MaNhiemVu)
                ->with('success', 'Nộp báo cáo tiến độ thành công! Vui lòng chờ Thư ký hoặc Chủ tịch Hội đồng phê duyệt.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Lỗi khi lưu báo cáo: ' . $e->getMessage());
        }
    }

    /**
     * Xem chi tiết một báo cáo tiến độ
     */
    public function show(int $id): View|RedirectResponse
    {
        $report = BaoCao::with([
            'phanCongNhiemVu.nhiemVu.nghiQuyet',
            'phanCongNhiemVu.donVi',
            'nguoiDung',
            'taiLieu',
        ])->findOrFail($id);

        $user = Auth::user();
        if ($user && $user->role === 'EXECUTING_UNIT' && $user->MaDonVi) {
            if ($report->phanCongNhiemVu->MaDonVi != $user->MaDonVi) {
                return redirect()->route('task-reports.index')->with('error', 'Bạn không có quyền truy cập báo cáo này.');
            }
        }

        return view('pages.baocao.chitiet', compact('report'));
    }

    /**
     * Thư ký / Chủ tịch kiểm tra và phê duyệt báo cáo tiến độ (BR-10)
     */
    public function review(Request $request, int $id): RedirectResponse
    {
        $user = Auth::user();
        if ($user && !in_array($user->role, ['SECRETARY', 'CHAIR', 'VICE_CHAIR', 'ADMIN'])) {
            return redirect()->route('task-reports.show', $id)
                ->with('error', 'Chỉ Thư ký hoặc Chủ tịch Hội đồng mới có quyền phê duyệt báo cáo tiến độ.');
        }

        $request->validate([
            'action' => 'required|in:approve,reject',
            'GhiChu' => 'nullable|string|max:1000',
        ]);

        $report = BaoCao::with(['phanCongNhiemVu.nhiemVu'])->findOrFail($id);

        DB::beginTransaction();
        try {
            $isApproved = $request->action === 'approve';
            $report->update([
                'TrangThaiXacNhan' => $isApproved ? 'APPROVED' : 'REJECTED',
                'NgayXacNhan' => now()->toDateString(),
                'GhiChu' => $request->GhiChu,
            ]);

            // Nếu báo cáo được duyệt và tiến độ đạt 100% (BR-10)
            if ($isApproved && $report->TienDo >= 100) {
                $assignment = $report->phanCongNhiemVu;
                $assignment->update(['TrangThaiThucHien' => 'Hoàn tất']);

                $task = $assignment->nhiemVu;
                if ($task) {
                    $task->update([
                        'TrangThai' => 'COMPLETED',
                        'NgayXacNhanHoanThanh' => now()->toDateString(),
                    ]);
                }
            }

            DB::commit();

            $statusText = $isApproved ? 'Đã phê duyệt báo cáo tiến độ' : 'Đã yêu cầu đơn vị điều chỉnh/bổ sung báo cáo';
            return redirect()->route('tasks.show', $report->phanCongNhiemVu->MaNhiemVu)
                ->with('success', $statusText . ' thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Lỗi khi phê duyệt báo cáo: ' . $e->getMessage());
        }
    }

    /**
     * Xóa báo cáo tiến độ (Soft Delete - ẩn dữ liệu trong CSDL)
     */
    public function destroy(int|string $id): RedirectResponse
    {
        $role = Auth::user()?->role ?? (session('user_role') ?? 'CHAIR');
        if (!in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN', 'EXECUTING_UNIT'])) {
            return back()->with('error', 'Bạn không có thẩm quyền thực hiện thao tác này.');
        }

        $report = BaoCao::findOrFail($id);
        $report->update(['TrangThaiXacNhan' => 'Đã ẩn']);
        $report->delete();

        return redirect()->route('task-reports.index')
            ->with('success', "Đã xóa báo cáo tiến độ #BC-{$id} khỏi danh sách hiển thị.");
    }
}