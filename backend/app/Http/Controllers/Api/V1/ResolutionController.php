<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\NghiQuyet;
use App\Models\PhienHop;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ResolutionController extends Controller
{
    /**
     * Danh sách Nghị quyết HĐT
     * GET /api/v1/resolutions
     */
    public function index(Request $request): JsonResponse
    {
        $query = NghiQuyet::with(['phienHop', 'hoSoTrinh', 'noiDungBieuQuyet', 'nhiemVu']);

        // Tìm kiếm theo số nghị quyết hoặc trích yếu
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('SoNghiQuyet', 'like', "%{$search}%")
                    ->orWhere('TrichYeu', 'like', "%{$search}%")
                    ->orWhere('NoiDung', 'like', "%{$search}%");
            });
        }

        // Lọc theo trạng thái
        if ($status = $request->query('status')) {
            if ($status !== 'ALL') {
                $query->where('TrangThai', $status);
            }
        }

        // Lọc theo phiên họp
        if ($meetingId = $request->query('meeting_id')) {
            if ($meetingId !== 'ALL') {
                $query->where('MaPhienHop', $meetingId);
            }
        }

        // Lọc theo năm ban hành
        if ($year = $request->query('year')) {
            if ($year !== 'ALL') {
                $query->whereYear('NgayBanHanh', $year);
            }
        }

        $perPage = (int) $request->query('perPage', 10);
        $page = (int) $request->query('page', 1);

        $paginator = $query->orderBy('NgayBanHanh', 'desc')
            ->orderBy('MaNghiQuyet', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        $items = collect($paginator->items())->map(fn (NghiQuyet $nq) => $this->formatResolutionSummary($nq));

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách Nghị quyết thành công.',
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
     * Tạo mới Nghị quyết (Dự thảo / Ban hành)
     * POST /api/v1/resolutions
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! in_array($user?->role, ['CHAIR', 'SECRETARY', 'ADMIN'])) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền soạn thảo hoặc ban hành Nghị quyết.',
                'data' => null,
                'errors' => null,
            ], 403);
        }

        $validated = $request->validate([
            'resolution_number' => ['required', 'string', 'max:191', 'unique:nghi_quyet,SoNghiQuyet'],
            'title' => ['required', 'string', 'min:10', 'max:255'],
            'content' => ['required', 'string'],
            'meeting_id' => ['required', 'integer', 'exists:phien_hop,MaPhienHop'],
            'issued_date' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'in:DRAFT,SIGNED,PUBLISHED'],
            'voting_ids' => ['nullable', 'array'],
            'voting_ids.*' => ['integer', 'exists:noi_dung_bieu_quyet,MaNoiDung'],
            'dossier_ids' => ['nullable', 'array'],
            'dossier_ids.*' => ['integer', 'exists:ho_so_trinh,MaHoSo'],
        ], [
            'resolution_number.required' => 'Vui lòng nhập số hiệu Nghị quyết.',
            'resolution_number.unique' => 'Số hiệu Nghị quyết này đã tồn tại trong hệ thống.',
            'title.required' => 'Vui lòng nhập trích yếu nội dung Nghị quyết.',
            'content.required' => 'Vui lòng nhập toàn văn quyết nghị.',
            'meeting_id.required' => 'Vui lòng chọn phiên họp Hội đồng trường thông qua.',
        ]);

        $status = $validated['status'] ?? 'DRAFT';
        $issuedDate = $validated['issued_date'] ?? now()->toDateString();

        $resolution = DB::transaction(function () use ($validated, $status, $issuedDate) {
            $nq = NghiQuyet::create([
                'SoNghiQuyet' => $validated['resolution_number'],
                'NgayBanHanh' => $issuedDate,
                'TrichYeu' => $validated['title'],
                'NoiDung' => $validated['content'],
                'TrangThai' => $status,
                'MaPhienHop' => $validated['meeting_id'],
            ]);

            // [BR-06] Khóa chính kép cho bảng trung gian NGHI_QUYET_BIEU_QUYET (MaNghiQuyet, MaNoiDung)
            if (! empty($validated['voting_ids'])) {
                $nq->noiDungBieuQuyet()->sync($validated['voting_ids']);
            }

            // [BR-06] Khóa chính kép cho bảng trung gian NGHI_QUYET_HO_SO (MaNghiQuyet, MaHoSo)
            if (! empty($validated['dossier_ids'])) {
                $nq->hoSoTrinh()->sync($validated['dossier_ids']);
            }

            return $nq;
        });

        return response()->json([
            'success' => true,
            'message' => 'Tạo Nghị quyết mới thành công.',
            'data' => $this->formatResolutionDetail($resolution->fresh([
                'phienHop',
                'hoSoTrinh',
                'noiDungBieuQuyet',
                'nhiemVu.phanCongNhiemVu.donVi',
                'taiLieu',
            ])),
            'errors' => null,
        ], 201);
    }

    /**
     * Chi tiết Nghị quyết kèm danh sách nhiệm vụ phân rã & tài liệu
     * GET /api/v1/resolutions/{id}
     */
    public function show(int|string $id): JsonResponse
    {
        $resolution = NghiQuyet::with([
            'phienHop',
            'hoSoTrinh.donVi',
            'noiDungBieuQuyet.ketQuaBieuQuyet',
            'nhiemVu.phanCongNhiemVu.donVi',
            'taiLieu',
        ])->find($id);

        if (! $resolution) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy Nghị quyết yêu cầu.',
                'data' => null,
                'errors' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Lấy chi tiết Nghị quyết thành công.',
            'data' => $this->formatResolutionDetail($resolution),
            'errors' => null,
        ]);
    }

    /**
     * Cập nhật thông tin Nghị quyết (chỉ cho phép khi ở DRAFT hoặc SIGNED)
     * PUT /api/v1/resolutions/{id}
     */
    public function update(Request $request, int|string $id): JsonResponse
    {
        $user = $request->user();
        if (! in_array($user?->role, ['CHAIR', 'SECRETARY', 'ADMIN'])) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền chỉnh sửa Nghị quyết.',
                'data' => null,
                'errors' => null,
            ], 403);
        }

        $resolution = NghiQuyet::find($id);
        if (! $resolution) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy Nghị quyết để chỉnh sửa.',
                'data' => null,
                'errors' => null,
            ], 404);
        }

        if ($resolution->TrangThai === 'PUBLISHED') {
            return response()->json([
                'success' => false,
                'message' => 'Nghị quyết đã công bố hiệu lực, không được phép sửa đổi trực tiếp.',
                'data' => null,
                'errors' => null,
            ], 422);
        }

        $validated = $request->validate([
            'resolution_number' => ['sometimes', 'required', 'string', 'max:191', "unique:nghi_quyet,SoNghiQuyet,{$id},MaNghiQuyet"],
            'title' => ['sometimes', 'required', 'string', 'min:10', 'max:255'],
            'content' => ['sometimes', 'required', 'string'],
            'meeting_id' => ['sometimes', 'required', 'integer', 'exists:phien_hop,MaPhienHop'],
            'issued_date' => ['nullable', 'date'],
            'voting_ids' => ['nullable', 'array'],
            'dossier_ids' => ['nullable', 'array'],
        ]);

        DB::transaction(function () use ($resolution, $validated) {
            $updateData = [];
            if (isset($validated['resolution_number'])) {
                $updateData['SoNghiQuyet'] = $validated['resolution_number'];
            }
            if (isset($validated['title'])) {
                $updateData['TrichYeu'] = $validated['title'];
            }
            if (isset($validated['content'])) {
                $updateData['NoiDung'] = $validated['content'];
            }
            if (isset($validated['meeting_id'])) {
                $updateData['MaPhienHop'] = $validated['meeting_id'];
            }
            if (isset($validated['issued_date'])) {
                $updateData['NgayBanHanh'] = $validated['issued_date'];
            }

            if (! empty($updateData)) {
                $resolution->update($updateData);
            }

            if (isset($validated['voting_ids'])) {
                $resolution->noiDungBieuQuyet()->sync($validated['voting_ids']);
            }
            if (isset($validated['dossier_ids'])) {
                $resolution->hoSoTrinh()->sync($validated['dossier_ids']);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật Nghị quyết thành công.',
            'data' => $this->formatResolutionDetail($resolution->fresh([
                'phienHop',
                'hoSoTrinh',
                'noiDungBieuQuyet',
                'nhiemVu.phanCongNhiemVu.donVi',
                'taiLieu',
            ])),
            'errors' => null,
        ]);
    }

    /**
     * Chủ tịch HĐT ký ban hành (SIGNED)
     * POST /api/v1/resolutions/{id}/sign
     */
    public function sign(Request $request, int|string $id): JsonResponse
    {
        $user = $request->user();
        if (! in_array($user?->role, ['CHAIR', 'ADMIN'])) {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ Chủ tịch Hội đồng trường mới có thẩm quyền ký ban hành Nghị quyết.',
                'data' => null,
                'errors' => null,
            ], 403);
        }

        $resolution = NghiQuyet::find($id);
        if (! $resolution) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy Nghị quyết.',
                'data' => null,
                'errors' => null,
            ], 404);
        }

        $resolution->update([
            'TrangThai' => 'SIGNED',
            'NgayBanHanh' => now()->toDateString(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Chủ tịch Hội đồng trường đã ký ban hành Nghị quyết số {$resolution->SoNghiQuyet}.",
            'data' => $this->formatResolutionDetail($resolution->fresh(['phienHop', 'nhiemVu'])),
            'errors' => null,
        ]);
    }

    /**
     * Công bố chính thức Nghị quyết (PUBLISHED)
     * POST /api/v1/resolutions/{id}/publish
     */
    public function publish(Request $request, int|string $id): JsonResponse
    {
        $user = $request->user();
        if (! in_array($user?->role, ['CHAIR', 'SECRETARY', 'ADMIN'])) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền công bố Nghị quyết.',
                'data' => null,
                'errors' => null,
            ], 403);
        }

        $resolution = NghiQuyet::find($id);
        if (! $resolution) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy Nghị quyết.',
                'data' => null,
                'errors' => null,
            ], 404);
        }

        $resolution->update([
            'TrangThai' => 'PUBLISHED',
        ]);

        return response()->json([
            'success' => true,
            'message' => "Nghị quyết số {$resolution->SoNghiQuyet} đã được công bố hiệu lực toàn trường.",
            'data' => $this->formatResolutionDetail($resolution->fresh(['phienHop', 'nhiemVu'])),
            'errors' => null,
        ]);
    }

    /**
     * Xóa dự thảo Nghị quyết (chỉ khi đang là DRAFT)
     * DELETE /api/v1/resolutions/{id}
     */
    public function destroy(Request $request, int|string $id): JsonResponse
    {
        $user = $request->user();
        if (! in_array($user?->role, ['CHAIR', 'SECRETARY', 'ADMIN'])) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền xóa Nghị quyết.',
                'data' => null,
                'errors' => null,
            ], 403);
        }

        $resolution = NghiQuyet::find($id);
        if (! $resolution) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy Nghị quyết để xóa.',
                'data' => null,
                'errors' => null,
            ], 404);
        }

        if ($resolution->TrangThai !== 'DRAFT') {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ được xóa Nghị quyết đang ở trạng thái Dự thảo (DRAFT).',
                'data' => null,
                'errors' => null,
            ], 422);
        }

        $resolution->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa dự thảo Nghị quyết thành công.',
            'data' => null,
            'errors' => null,
        ]);
    }

    /**
     * Lấy Biên bản phiên họp
     * GET /api/v1/meetings/{meetingId}/minutes
     */
    public function getMinutes(int|string $meetingId): JsonResponse
    {
        $meeting = PhienHop::with([
            'thamDu.thanhVienHdt.nguoiDung',
            'chuongTrinhHop.noiDungBieuQuyet.ketQuaBieuQuyet',
            'nghiQuyet',
        ])->find($meetingId);

        if (! $meeting) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy phiên họp.',
                'data' => null,
                'errors' => null,
            ], 404);
        }

        // Lấy biên bản từ ghi chú hoặc tạo cấu trúc biên bản mặc định
        $minutesData = json_decode($meeting->GhiChu ?? '{}', true) ?: [];

        $presentCount = $meeting->thamDu->where('TrangThaiThamDu', 'PRESENT')->count();
        $totalMembers = $meeting->thamDu->count();

        return response()->json([
            'success' => true,
            'message' => 'Lấy nội dung biên bản phiên họp thành công.',
            'data' => [
                'meeting_id' => (string) $meeting->MaPhienHop,
                'meeting_title' => $meeting->TenPhienHop,
                'meeting_date' => $meeting->NgayHop ? date('Y-m-d', strtotime($meeting->NgayHop)) : '',
                'location' => $meeting->DiaDiem,
                'chairperson' => 'PGS.TS. Trần Văn Chủ Tịch',
                'secretary' => 'ThS. Nguyễn Thị Thư Ký',
                'quorum' => [
                    'present_count' => $presentCount,
                    'total_members' => $totalMembers,
                    'is_valid' => $totalMembers > 0 && ($presentCount / $totalMembers) >= (2 / 3),
                ],
                'minutes_status' => $minutesData['minutes_status'] ?? 'DRAFT',
                'minutes_content' => $minutesData['minutes_content'] ?? "BIÊN BẢN PHIÊN HỌP HỘI ĐỒNG TRƯỜNG\n\nPhiên họp: {$meeting->TenPhienHop}\nThời gian: {$meeting->NgayHop}\nĐịa điểm: {$meeting->DiaDiem}\n\nI. KHAI MẠC VÀ ĐIỂM DANH THÀNH VIÊN\n- Tổng số thành viên: {$totalMembers}\n- Có mặt: {$presentCount}\n- Tỷ lệ: " . ($totalMembers > 0 ? round(($presentCount / $totalMembers) * 100, 1) : 0) . "%\n\nII. NỘI DUNG VÀ KẾT QUẢ THẢO LUẬN\n(Các mục thảo luận và kết quả biểu quyết đã được lưu trữ điện tử)\n\nIII. KẾT LUẬN VÀ BAN HÀNH NGHỊ QUYẾT\nChủ tịch Hội đồng trường kết luận thông qua các nội dung đạt chuẩn.",
                'approved_at' => $minutesData['approved_at'] ?? null,
                'approved_by' => $minutesData['approved_by'] ?? null,
            ],
            'errors' => null,
        ]);
    }

    /**
     * Lưu/cập nhật dự thảo biên bản phiên họp
     * POST /api/v1/meetings/{meetingId}/minutes
     */
    public function saveMinutes(Request $request, int|string $meetingId): JsonResponse
    {
        $user = $request->user();
        if (! in_array($user?->role, ['SECRETARY', 'ADMIN'])) {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ Thư ký Hội đồng trường mới được cập nhật biên bản.',
                'data' => null,
                'errors' => null,
            ], 403);
        }

        $meeting = PhienHop::find($meetingId);
        if (! $meeting) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy phiên họp.',
                'data' => null,
                'errors' => null,
            ], 404);
        }

        $validated = $request->validate([
            'content' => ['required', 'string'],
        ]);

        $existing = json_decode($meeting->GhiChu ?? '{}', true) ?: [];
        $existing['minutes_content'] = $validated['content'];
        $existing['minutes_status'] = 'DRAFT';
        $existing['updated_at'] = now()->toDateTimeString();

        $meeting->update([
            'GhiChu' => json_encode($existing),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lưu dự thảo biên bản phiên họp thành công.',
            'data' => $existing,
            'errors' => null,
        ]);
    }

    /**
     * Chủ tịch phê duyệt biên bản phiên họp
     * POST /api/v1/meetings/{meetingId}/minutes/approve
     */
    public function approveMinutes(Request $request, int|string $meetingId): JsonResponse
    {
        $user = $request->user();
        if (! in_array($user?->role, ['CHAIR', 'ADMIN'])) {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ Chủ tịch Hội đồng trường mới có thẩm quyền phê duyệt biên bản.',
                'data' => null,
                'errors' => null,
            ], 403);
        }

        $meeting = PhienHop::find($meetingId);
        if (! $meeting) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy phiên họp.',
                'data' => null,
                'errors' => null,
            ], 404);
        }

        $existing = json_decode($meeting->GhiChu ?? '{}', true) ?: [];
        $existing['minutes_status'] = 'APPROVED';
        $existing['approved_at'] = now()->toDateTimeString();
        $existing['approved_by'] = $user->HoTen ?? 'Chủ tịch HĐT';

        $meeting->update([
            'GhiChu' => json_encode($existing),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Chủ tịch Hội đồng trường đã phê duyệt biên bản phiên họp thành công.',
            'data' => $existing,
            'errors' => null,
        ]);
    }

    /**
     * Format tóm tắt Nghị quyết cho danh sách
     */
    private function formatResolutionSummary(NghiQuyet $nq): array
    {
        $tasks = $nq->nhiemVu ?? collect([]);
        $totalTasks = $tasks->count();
        $completedTasks = $tasks->where('TrangThai', 'COMPLETED')->count();

        $avgProgress = $totalTasks > 0
            ? round($tasks->avg(function ($t) {
                // If progress is recorded
                return $t->TrangThai === 'COMPLETED' ? 100 : ($t->TrangThai === 'IN_PROGRESS' ? 50 : 0);
            }))
            : 0;

        $statusLabels = [
            'DRAFT' => 'Dự thảo',
            'SIGNED' => 'Đã ký ban hành',
            'PUBLISHED' => 'Đã công bố hiệu lực',
        ];

        return [
            'id' => (string) $nq->MaNghiQuyet,
            'resolution_number' => $nq->SoNghiQuyet,
            'title' => $nq->TrichYeu,
            'issued_date' => $nq->NgayBanHanh ? date('Y-m-d', strtotime($nq->NgayBanHanh)) : null,
            'status' => $nq->TrangThai,
            'status_label' => $statusLabels[$nq->TrangThai] ?? $nq->TrangThai,
            'meeting_id' => (string) $nq->MaPhienHop,
            'meeting_title' => $nq->phienHop?->TenPhienHop ?? 'Phiên họp HĐT',
            'total_tasks' => $totalTasks,
            'completed_tasks' => $completedTasks,
            'progress_percentage' => $avgProgress,
        ];
    }

    /**
     * Format chi tiết Nghị quyết đầy đủ
     */
    private function formatResolutionDetail(NghiQuyet $nq): array
    {
        $summary = $this->formatResolutionSummary($nq);

        $votings = $nq->noiDungBieuQuyet ? $nq->noiDungBieuQuyet->map(fn ($v) => [
            'id' => (string) $v->MaNoiDung,
            'title' => $v->NoiDung,
            'result' => $v->KetQua,
            'result_label' => $v->KetQua === 'PASSED' ? 'Đã thông qua' : 'Không thông qua',
            'voting_type' => 'SECRET',
        ])->values()->toArray() : [];

        $dossiers = $nq->hoSoTrinh ? $nq->hoSoTrinh->map(fn ($h) => [
            'id' => (string) $h->MaHoSo,
            'title' => $h->TieuDe,
            'department' => $h->donVi?->TenDonVi ?? 'Đơn vị trình',
            'status' => $h->TrangThai,
        ])->values()->toArray() : [];

        $tasks = $nq->nhiemVu ? $nq->nhiemVu->map(function ($t) {
            $leadDept = $t->phanCongNhiemVu?->first()?->donVi?->TenDonVi ?? 'Đơn vị thi hành';

            return [
                'id' => (string) $t->MaNhiemVu,
                'title' => $t->TenNhiemVu,
                'content' => $t->NoiDung,
                'lead_department' => $leadDept,
                'start_date' => $t->NgayGiao ? date('Y-m-d', strtotime($t->NgayGiao)) : null,
                'due_date' => $t->NgayHoanThanh ? date('Y-m-d', strtotime($t->NgayHoanThanh)) : null,
                'status' => $t->TrangThai,
            ];
        })->values()->toArray() : [];

        $documents = $nq->taiLieu ? $nq->taiLieu->map(fn ($doc) => [
            'id' => (string) $doc->MaTaiLieu,
            'file_name' => $doc->TenTaiLieu,
            'file_size' => (int) $doc->DungLuong,
            'file_type' => $doc->DinhDang,
            'uploaded_at' => $doc->NgayTaiLen ? date('Y-m-d H:i', strtotime($doc->NgayTaiLen)) : null,
        ])->values()->toArray() : [];

        return array_merge($summary, [
            'content' => $nq->NoiDung,
            'related_votings' => $votings,
            'related_dossiers' => $dossiers,
            'tasks' => $tasks,
            'documents' => $documents,
        ]);
    }
}
