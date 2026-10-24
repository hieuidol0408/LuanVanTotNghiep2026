<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\BaoCao;
use App\Models\DonVi;
use App\Models\NghiQuyet;
use App\Models\NhiemVu;
use App\Models\PhanCongNhiemVu;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    /**
     * Danh sách Nhiệm vụ phân rã từ Nghị quyết
     * GET /api/v1/tasks
     */
    public function index(Request $request): JsonResponse
    {
        $query = NhiemVu::with([
            'nghiQuyet',
            'phanCongNhiemVu.donVi',
            'phanCongNhiemVu.baoCao',
        ]);

        // Tìm kiếm theo tên hoặc nội dung nhiệm vụ hoặc số nghị quyết
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('TenNhiemVu', 'like', "%{$search}%")
                    ->orWhere('NoiDung', 'like', "%{$search}%")
                    ->orWhereHas('nghiQuyet', function ($nqQ) use ($search) {
                        $nqQ->where('SoNghiQuyet', 'like', "%{$search}%")
                            ->orWhere('TrichYeu', 'like', "%{$search}%");
                    });
            });
        }

        // Lọc theo đơn vị (chủ trì hoặc phối hợp)
        if ($departmentId = $request->query('department_id')) {
            if ($departmentId !== 'ALL') {
                $query->whereHas('phanCongNhiemVu', function ($pcQ) use ($departmentId) {
                    $pcQ->where('MaDonVi', $departmentId);
                });
            }
        }

        // Lọc theo Nghị quyết
        if ($resolutionId = $request->query('resolution_id')) {
            if ($resolutionId !== 'ALL') {
                $query->where('MaNghiQuyet', $resolutionId);
            }
        }

        // Lọc theo trạng thái
        if ($status = $request->query('status')) {
            if ($status !== 'ALL') {
                if ($status === 'OVERDUE') {
                    $today = now()->toDateString();
                    $query->where('TrangThai', '!=', 'COMPLETED')
                        ->where('NgayHoanThanh', '<', $today);
                } else {
                    $query->where('TrangThai', $status);
                }
            }
        }

        // Lọc theo quá hạn
        if ($request->boolean('is_overdue')) {
            $today = now()->toDateString();
            $query->where('TrangThai', '!=', 'COMPLETED')
                ->where('NgayHoanThanh', '<', $today);
        }

        $perPage = (int) $request->query('perPage', 10);
        $page = (int) $request->query('page', 1);

        $paginator = $query->orderBy('NgayGiao', 'desc')
            ->orderBy('MaNhiemVu', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        $items = collect($paginator->items())->map(fn (NhiemVu $nv) => $this->formatTaskSummary($nv));

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách Nhiệm vụ thành công.',
            'data' => $items,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'total_pages' => $paginator->lastPage(),
            ],
            'errors' => null,
        ]);
    }

    /**
     * Tách nhiệm vụ mới từ Nghị quyết
     * POST /api/v1/tasks
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! in_array($user?->role, ['CHAIR', 'SECRETARY', 'ADMIN'])) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền phân rã hoặc gán nhiệm vụ.',
                'data' => null,
                'errors' => null,
            ], 403);
        }

        $validated = $request->validate([
            'resolution_id' => ['required', 'integer', 'exists:nghi_quyet,MaNghiQuyet'],
            'title' => ['required', 'string', 'min:5', 'max:255'],
            'content' => ['required', 'string'],
            'lead_department_id' => ['required', 'integer', 'exists:don_vi,MaDonVi'],
            'coordinating_department_ids' => ['nullable', 'array'],
            'coordinating_department_ids.*' => ['integer', 'exists:don_vi,MaDonVi'],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:start_date'],
            'person_in_charge' => ['nullable', 'string', 'max:255'],
        ], [
            'resolution_id.required' => 'Vui lòng chọn Nghị quyết gốc để phân rã nhiệm vụ.',
            'title.required' => 'Vui lòng nhập tên đầu việc nhiệm vụ.',
            'lead_department_id.required' => 'Vui lòng chọn đơn vị chủ trì thực hiện.',
            'due_date.required' => 'Vui lòng chọn hạn chót hoàn thành nhiệm vụ.',
            'due_date.after_or_equal' => 'Hạn hoàn thành phải sau hoặc bằng ngày bắt đầu.',
        ]);

        $task = DB::transaction(function () use ($validated) {
            $startDate = $validated['start_date'] ?? now()->toDateString();

            $nv = NhiemVu::create([
                'TenNhiemVu' => $validated['title'],
                'NoiDung' => $validated['content'],
                'NgayGiao' => $startDate,
                'NgayHoanThanh' => $validated['due_date'],
                'TrangThai' => 'ASSIGNED',
                'MaNghiQuyet' => $validated['resolution_id'],
            ]);

            // Gán đơn vị chủ trì
            PhanCongNhiemVu::create([
                'VaiTroThucHien' => 'CHỦ TRÌ',
                'TrangThaiThucHien' => 'Chưa thực hiện',
                'TenNguoiPhuTrach' => $validated['person_in_charge'] ?? null,
                'MaNhiemVu' => $nv->MaNhiemVu,
                'MaDonVi' => $validated['lead_department_id'],
            ]);

            // Gán các đơn vị phối hợp (nếu có)
            if (! empty($validated['coordinating_department_ids'])) {
                foreach ($validated['coordinating_department_ids'] as $deptId) {
                    if ($deptId != $validated['lead_department_id']) {
                        PhanCongNhiemVu::create([
                            'VaiTroThucHien' => 'PHỐI HỢP',
                            'TrangThaiThucHien' => 'Chưa thực hiện',
                            'TenNguoiPhuTrach' => null,
                            'MaNhiemVu' => $nv->MaNhiemVu,
                            'MaDonVi' => $deptId,
                        ]);
                    }
                }
            }

            return $nv;
        });

        return response()->json([
            'success' => true,
            'message' => 'Phân rã nhiệm vụ thành công.',
            'data' => $this->formatTaskDetail($task->fresh([
                'nghiQuyet',
                'phanCongNhiemVu.donVi',
                'phanCongNhiemVu.baoCao',
            ])),
            'errors' => null,
        ], 201);
    }

    /**
     * Chi tiết Nhiệm vụ kèm nhật ký tiến độ & minh chứng
     * GET /api/v1/tasks/{id}
     */
    public function show(int|string $id): JsonResponse
    {
        $task = NhiemVu::with([
            'nghiQuyet.phienHop',
            'phanCongNhiemVu.donVi',
            'phanCongNhiemVu.baoCao.nguoiDung',
            'phanCongNhiemVu.baoCao.taiLieu',
        ])->find($id);

        if (! $task) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy Nhiệm vụ yêu cầu.',
                'data' => null,
                'errors' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Lấy chi tiết Nhiệm vụ thành công.',
            'data' => $this->formatTaskDetail($task),
            'errors' => null,
        ]);
    }

    /**
     * Cập nhật thông tin phân công Nhiệm vụ
     * PUT /api/v1/tasks/{id}
     */
    public function update(Request $request, int|string $id): JsonResponse
    {
        $user = $request->user();
        if (! in_array($user?->role, ['CHAIR', 'SECRETARY', 'ADMIN'])) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền điều chỉnh phân công nhiệm vụ.',
                'data' => null,
                'errors' => null,
            ], 403);
        }

        $task = NhiemVu::find($id);
        if (! $task) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy Nhiệm vụ để điều chỉnh.',
                'data' => null,
                'errors' => null,
            ], 404);
        }

        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'min:5', 'max:255'],
            'content' => ['sometimes', 'required', 'string'],
            'due_date' => ['sometimes', 'required', 'date'],
            'lead_department_id' => ['sometimes', 'required', 'integer', 'exists:don_vi,MaDonVi'],
            'coordinating_department_ids' => ['nullable', 'array'],
        ]);

        DB::transaction(function () use ($task, $validated) {
            $taskData = [];
            if (isset($validated['title'])) $taskData['TenNhiemVu'] = $validated['title'];
            if (isset($validated['content'])) $taskData['NoiDung'] = $validated['content'];
            if (isset($validated['due_date'])) $taskData['NgayHoanThanh'] = $validated['due_date'];

            if (! empty($taskData)) {
                $task->update($taskData);
            }

            if (isset($validated['lead_department_id'])) {
                // Cập nhật đơn vị chủ trì
                $leadAssignment = $task->phanCongNhiemVu()->where('VaiTroThucHien', 'CHỦ TRÌ')->first();
                if ($leadAssignment) {
                    $leadAssignment->update(['MaDonVi' => $validated['lead_department_id']]);
                }
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật phân công nhiệm vụ thành công.',
            'data' => $this->formatTaskDetail($task->fresh([
                'nghiQuyet',
                'phanCongNhiemVu.donVi',
                'phanCongNhiemVu.baoCao',
            ])),
            'errors' => null,
        ]);
    }

    /**
     * Xóa nhiệm vụ (khi chưa có nhật ký báo cáo tiến độ)
     * DELETE /api/v1/tasks/{id}
     */
    public function destroy(Request $request, int|string $id): JsonResponse
    {
        $user = $request->user();
        if (! in_array($user?->role, ['CHAIR', 'SECRETARY', 'ADMIN'])) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền xóa nhiệm vụ.',
                'data' => null,
                'errors' => null,
            ], 403);
        }

        $task = NhiemVu::find($id);
        if (! $task) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy Nhiệm vụ để xóa.',
                'data' => null,
                'errors' => null,
            ], 404);
        }

        $task->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa nhiệm vụ thành công.',
            'data' => null,
            'errors' => null,
        ]);
    }

    /**
     * Đơn vị nộp báo cáo tiến độ (%) và minh chứng
     * POST /api/v1/tasks/{id}/progress-logs
     */
    public function submitProgressLog(Request $request, int|string $id): JsonResponse
    {
        $user = $request->user();
        $task = NhiemVu::with(['phanCongNhiemVu'])->find($id);

        if (! $task) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy Nhiệm vụ.',
                'data' => null,
                'errors' => null,
            ], 404);
        }

        // Tìm phân công tương ứng của người dùng này hoặc đơn vị của người dùng
        $userDeptId = $user->MaDonVi;
        $assignment = $task->phanCongNhiemVu->firstWhere('MaDonVi', $userDeptId);

        // Nếu user là SECRETARY hoặc CHAIR hoặc ADMIN mà nộp thay thì lấy assignment chủ trì
        if (! $assignment && in_array($user->role, ['SECRETARY', 'CHAIR', 'ADMIN'])) {
            $assignment = $task->phanCongNhiemVu->firstWhere('VaiTroThucHien', 'CHỦ TRÌ')
                ?: $task->phanCongNhiemVu->first();
        }

        if (! $assignment) {
            return response()->json([
                'success' => false,
                'message' => 'Đơn vị của bạn không được phân công thực hiện nhiệm vụ này.',
                'data' => null,
                'errors' => null,
            ], 403);
        }

        $validated = $request->validate([
            'progress_percentage' => ['required', 'integer', 'min:0', 'max:100'],
            'report_content' => ['required', 'string', 'min:5'],
            'challenges' => ['nullable', 'string'],
        ], [
            'progress_percentage.required' => 'Vui lòng nhập tỷ lệ phần trăm tiến độ (0-100).',
            'report_content.required' => 'Vui lòng nhập nội dung chi tiết báo cáo tiến độ.',
        ]);

        $report = DB::transaction(function () use ($validated, $assignment, $user, $task) {
            $progress = $validated['progress_percentage'];

            $baoCao = BaoCao::create([
                'NgayBaoCao' => now()->toDateString(),
                'TienDo' => $progress,
                'NoiDungTienDo' => $validated['report_content'],
                'NoiDungKhoKhan' => $validated['challenges'] ?? null,
                'TrangThaiXacNhan' => 'PENDING',
                'NgayXacNhan' => null,
                'GhiChu' => null,
                'MaPhanCong' => $assignment->MaPhanCong,
                'MaNguoiDung' => $user->MaNguoiDung,
            ]);

            // Cập nhật trạng thái nhiệm vụ sang IN_PROGRESS nếu đang là ASSIGNED
            if ($task->TrangThai === 'ASSIGNED') {
                $task->update(['TrangThai' => 'IN_PROGRESS']);
            }

            return $baoCao;
        });

        return response()->json([
            'success' => true,
            'message' => 'Nộp báo cáo tiến độ nhiệm vụ thành công.',
            'data' => [
                'id' => (string) $report->MaBaoCao,
                'progress_percentage' => $report->TienDo,
                'report_date' => $report->NgayBaoCao ? date('Y-m-d', strtotime($report->NgayBaoCao)) : null,
                'content' => $report->NoiDungTienDo,
                'challenges' => $report->NoiDungKhoKhan,
                'status' => $report->TrangThaiXacNhan,
            ],
            'errors' => null,
        ], 201);
    }

    /**
     * Thư ký / Chủ tịch nghiệm thu báo cáo tiến độ và xác nhận hoàn thành
     * POST /api/v1/tasks/progress-logs/{logId}/review
     */
    public function reviewProgressLog(Request $request, int|string $logId): JsonResponse
    {
        $user = $request->user();
        if (! in_array($user?->role, ['CHAIR', 'SECRETARY', 'ADMIN'])) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền nghiệm thu báo cáo tiến độ.',
                'data' => null,
                'errors' => null,
            ], 403);
        }

        $report = BaoCao::with(['phanCongNhiemVu.nhiemVu'])->find($logId);
        if (! $report) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy báo cáo tiến độ yêu cầu.',
                'data' => null,
                'errors' => null,
            ], 404);
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:APPROVED,REVISION_REQUIRED'],
            'notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($report, $validated, $user) {
            $report->update([
                'TrangThaiXacNhan' => $validated['status'],
                'NgayXacNhan' => now()->toDateString(),
                'GhiChu' => $validated['notes'] ?? null,
            ]);

            $task = $report->phanCongNhiemVu?->nhiemVu;
            if ($task && $validated['status'] === 'APPROVED' && $report->TienDo >= 100) {
                $task->update([
                    'TrangThai' => 'COMPLETED',
                    'NgayXacNhanHoanThanh' => now()->toDateString(),
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => $validated['status'] === 'APPROVED'
                ? 'Đã nghiệm thu báo cáo tiến độ thành công.'
                : 'Đã yêu cầu đơn vị chỉnh sửa/bổ sung báo cáo tiến độ.',
            'data' => [
                'id' => (string) $report->MaBaoCao,
                'status' => $validated['status'],
                'confirmed_date' => now()->toDateString(),
                'confirmed_by' => $user->HoTen ?? 'Ban Thư ký HĐT',
            ],
            'errors' => null,
        ]);
    }

    /**
     * Format tóm tắt Nhiệm vụ
     */
    private function formatTaskSummary(NhiemVu $nv): array
    {
        $leadAssignment = $nv->phanCongNhiemVu?->firstWhere('VaiTroThucHien', 'CHỦ TRÌ');
        $leadDept = $leadAssignment?->donVi?->TenDonVi ?? 'Chưa phân công';
        $leadDeptId = $leadAssignment?->MaDonVi;

        $coordinatingAssignments = $nv->phanCongNhiemVu?->where('VaiTroThucHien', 'PHỐI HỢP') ?? collect([]);
        $coordinatingDepts = $coordinatingAssignments->map(fn ($ca) => $ca->donVi?->TenDonVi)->values()->toArray();

        // Tính tiến độ từ báo cáo mới nhất đã được nộp
        $allReports = collect();
        if ($nv->phanCongNhiemVu) {
            foreach ($nv->phanCongNhiemVu as $pc) {
                if ($pc->baoCao) {
                    $allReports = $allReports->merge($pc->baoCao);
                }
            }
        }

        $latestReport = $allReports->sortByDesc('MaBaoCao')->first();
        $progress = $latestReport ? (int) $latestReport->TienDo : 0;
        if ($nv->TrangThai === 'COMPLETED') {
            $progress = 100;
        }

        // Kiểm tra quá hạn
        $isOverdue = false;
        $status = $nv->TrangThai;
        if ($nv->NgayHoanThanh && $status !== 'COMPLETED') {
            $today = now()->toDateString();
            if ($today > $nv->NgayHoanThanh->toDateString()) {
                $isOverdue = true;
                $status = 'OVERDUE';
            }
        }

        $statusLabels = [
            'ASSIGNED' => 'Đã giao việc',
            'IN_PROGRESS' => 'Đang thực hiện',
            'COMPLETED' => 'Đã hoàn thành',
            'OVERDUE' => 'Quá hạn tiến độ',
        ];

        return [
            'id' => (string) $nv->MaNhiemVu,
            'title' => $nv->TenNhiemVu,
            'content' => $nv->NoiDung,
            'resolution_id' => (string) $nv->MaNghiQuyet,
            'resolution_number' => $nv->nghiQuyet?->SoNghiQuyet ?? 'Chưa rõ',
            'resolution_title' => $nv->nghiQuyet?->TrichYeu ?? '',
            'lead_department' => $leadDept,
            'lead_department_id' => $leadDeptId,
            'coordinating_departments' => $coordinatingDepts,
            'person_in_charge' => $leadAssignment?->TenNguoiPhuTrach,
            'start_date' => $nv->NgayGiao ? date('Y-m-d', strtotime($nv->NgayGiao)) : null,
            'due_date' => $nv->NgayHoanThanh ? date('Y-m-d', strtotime($nv->NgayHoanThanh)) : null,
            'completed_date' => $nv->NgayXacNhanHoanThanh ? date('Y-m-d', strtotime($nv->NgayXacNhanHoanThanh)) : null,
            'status' => $status,
            'status_label' => $statusLabels[$status] ?? $status,
            'progress_percentage' => $progress,
            'is_overdue' => $isOverdue,
            'reports_count' => $allReports->count(),
        ];
    }

    /**
     * Format chi tiết Nhiệm vụ kèm lịch sử báo cáo & tài liệu minh chứng
     */
    private function formatTaskDetail(NhiemVu $nv): array
    {
        $summary = $this->formatTaskSummary($nv);

        $reports = [];
        if ($nv->phanCongNhiemVu) {
            foreach ($nv->phanCongNhiemVu as $pc) {
                if ($pc->baoCao) {
                    foreach ($pc->baoCao as $bc) {
                        $docs = $bc->taiLieu ? $bc->taiLieu->map(fn ($d) => [
                            'id' => (string) $d->MaTaiLieu,
                            'file_name' => $d->TenTaiLieu,
                            'file_size' => (int) $d->DungLuong,
                            'file_type' => $d->DinhDang,
                        ])->values()->toArray() : [];

                        $reports[] = [
                            'id' => (string) $bc->MaBaoCao,
                            'report_date' => $bc->NgayBaoCao ? date('Y-m-d', strtotime($bc->NgayBaoCao)) : null,
                            'progress_percentage' => (int) $bc->TienDo,
                            'content' => $bc->NoiDungTienDo,
                            'challenges' => $bc->NoiDungKhoKhan,
                            'status' => $bc->TrangThaiXacNhan,
                            'status_label' => match ($bc->TrangThaiXacNhan) {
                                'APPROVED' => 'Đã nghiệm thu',
                                'REVISION_REQUIRED' => 'Yêu cầu sửa đổi',
                                default => 'Chờ nghiệm thu',
                            },
                            'confirmed_date' => $bc->NgayXacNhan ? date('Y-m-d', strtotime($bc->NgayXacNhan)) : null,
                            'notes' => $bc->GhiChu,
                            'reporter_name' => $bc->nguoiDung?->HoTen ?? 'Đại diện đơn vị',
                            'department' => $pc->donVi?->TenDonVi ?? 'Đơn vị thi hành',
                            'documents' => $docs,
                        ];
                    }
                }
            }
        }

        // Sắp xếp nhật ký báo cáo mới nhất lên đầu
        usort($reports, fn ($a, $b) => strcmp($b['id'], $a['id']));

        return array_merge($summary, [
            'meeting_title' => $nv->nghiQuyet?->phienHop?->TenPhienHop ?? 'Phiên họp HĐT',
            'progress_logs' => $reports,
        ]);
    }
}
