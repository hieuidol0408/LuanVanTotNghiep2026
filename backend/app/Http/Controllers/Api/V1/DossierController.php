<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\HoSoTrinh;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DossierController extends Controller
{
    /**
     * Danh sách hồ sơ trình (hỗ trợ tìm kiếm, lọc trạng thái, lọc theo đơn vị, phân trang)
     * GET /api/v1/dossiers
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = HoSoTrinh::with(['donVi', 'nguoiDung', 'taiLieu']);

        // Nếu là Đại diện đơn vị (UNIT_REP), chỉ xem hồ sơ của đơn vị mình
        if ($user && $user->role === 'UNIT_REP' && $user->MaDonVi) {
            $query->where('MaDonVi', $user->MaDonVi);
        }

        // 1. Tìm kiếm từ khóa
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('TieuDe', 'like', "%{$search}%")
                    ->orWhere('NoiDungTomTat', 'like', "%{$search}%")
                    ->orWhere('LinhVuc', 'like', "%{$search}%");
            });
        }

        // 2. Lọc trạng thái
        if ($status = $request->input('status')) {
            if ($status !== 'ALL') {
                $query->where('TrangThai', $status);
            }
        }

        // 3. Lọc theo đơn vị
        if ($deptId = $request->input('department_id')) {
            if ($deptId !== 'ALL') {
                $query->where('MaDonVi', $deptId);
            }
        }

        $perPage = min((int) $request->input('perPage', 10), 100);
        $paginated = $query->orderBy('NgayTrinh', 'desc')
            ->orderBy('MaHoSo', 'desc')
            ->paginate($perPage);

        $items = collect($paginated->items())->map(fn (HoSoTrinh $dossier) => $this->formatDossierSummary($dossier));

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách hồ sơ trình thành công.',
            'data' => $items,
            'meta' => [
                'page' => $paginated->currentPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
                'total_pages' => $paginated->lastPage(),
            ],
            'errors' => null,
        ]);
    }

    /**
     * Tạo mới hồ sơ trình (DRAFT)
     * POST /api/v1/dossiers
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'title' => ['required', 'string', 'min:5', 'max:255'],
            'summary' => ['required', 'string'],
            'field' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'department_id' => ['nullable', 'integer', 'exists:don_vi,MaDonVi'],
        ], [
            'title.required' => 'Vui lòng nhập tiêu đề hồ sơ tờ trình.',
            'summary.required' => 'Vui lòng nhập nội dung tóm tắt đề xuất.',
            'field.required' => 'Vui lòng chọn lĩnh vực hồ sơ.',
        ]);

        $maDonVi = $user?->MaDonVi ?? $validated['department_id'] ?? 1;

        $dossier = HoSoTrinh::create([
            'TieuDe' => $validated['title'],
            'NoiDungTomTat' => $validated['summary'],
            'NgayTrinh' => now()->toDateString(),
            'LinhVuc' => $validated['field'],
            'TrangThai' => 'DRAFT',
            'GhiChu' => $validated['notes'] ?? null,
            'MaDonVi' => $maDonVi,
            'MaNguoiDung' => $user?->MaNguoiDung ?? 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tạo hồ sơ trình thành công (Dự thảo).',
            'data' => $this->formatDossierDetail($dossier->load(['donVi', 'nguoiDung', 'taiLieu'])),
            'errors' => null,
        ], 201);
    }

    /**
     * Xem chi tiết hồ sơ trình
     * GET /api/v1/dossiers/{id}
     */
    public function show(int|string $id): JsonResponse
    {
        $dossier = HoSoTrinh::with(['donVi', 'nguoiDung', 'taiLieu', 'chuongTrinhHop.phienHop'])->find($id);

        if (! $dossier) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy hồ sơ trình yêu cầu.',
                'data' => null,
                'errors' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Lấy thông tin chi tiết hồ sơ trình thành công.',
            'data' => $this->formatDossierDetail($dossier),
            'errors' => null,
        ]);
    }

    /**
     * Cập nhật hồ sơ trình (chỉ khi đang ở DRAFT hoặc REVISION_REQUIRED)
     * PUT /api/v1/dossiers/{id}
     */
    public function update(Request $request, int|string $id): JsonResponse
    {
        $dossier = HoSoTrinh::find($id);
        if (! $dossier) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy hồ sơ trình để chỉnh sửa.',
                'data' => null,
                'errors' => null,
            ], 404);
        }

        // Quy tắc BR-DOS-03: Không cho sửa khi hồ sơ đã nộp hoặc đang thẩm tra
        if (! in_array($dossier->TrangThai, ['DRAFT', 'REVISION_REQUIRED'])) {
            return response()->json([
                'success' => false,
                'message' => 'Hồ sơ đã được nộp hoặc đang trong quá trình xử lý, không thể chỉnh sửa.',
                'data' => null,
                'errors' => null,
            ], 422);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'min:5', 'max:255'],
            'summary' => ['required', 'string'],
            'field' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $dossier->update([
            'TieuDe' => $validated['title'],
            'NoiDungTomTat' => $validated['summary'],
            'LinhVuc' => $validated['field'],
            'GhiChu' => $validated['notes'] ?? $dossier->GhiChu,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật hồ sơ trình thành công.',
            'data' => $this->formatDossierDetail($dossier->load(['donVi', 'nguoiDung', 'taiLieu'])),
            'errors' => null,
        ]);
    }

    /**
     * Nộp hồ sơ chính thức lên Thư ký HĐT
     * POST /api/v1/dossiers/{id}/submit
     */
    public function submit(int|string $id): JsonResponse
    {
        $dossier = HoSoTrinh::find($id);
        if (! $dossier) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy hồ sơ trình.',
                'data' => null,
                'errors' => null,
            ], 404);
        }

        if (! in_array($dossier->TrangThai, ['DRAFT', 'REVISION_REQUIRED'])) {
            return response()->json([
                'success' => false,
                'message' => 'Hồ sơ hiện không ở trạng thái dự thảo để nộp.',
                'data' => null,
                'errors' => null,
            ], 422);
        }

        $dossier->update([
            'TrangThai' => 'SUBMITTED',
            'NgayTrinh' => now()->toDateString(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đã nộp hồ sơ trình chính thức lên Hội đồng trường.',
            'data' => $this->formatDossierDetail($dossier->fresh(['donVi', 'nguoiDung'])),
            'errors' => null,
        ]);
    }

    /**
     * Thẩm tra hồ sơ trình (Duyệt đạt / Yêu cầu chỉnh sửa / Từ chối)
     * POST /api/v1/dossiers/{id}/review
     */
    public function review(Request $request, int|string $id): JsonResponse
    {
        $user = $request->user();

        // Chỉ Thư ký hoặc Chủ tịch mới có thẩm quyền thẩm tra
        if (! in_array($user?->role, ['SECRETARY', 'CHAIR', 'ADMIN'])) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có thẩm quyền thẩm tra hồ sơ trình.',
                'data' => null,
                'errors' => null,
            ], 403);
        }

        $dossier = HoSoTrinh::find($id);
        if (! $dossier) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy hồ sơ trình.',
                'data' => null,
                'errors' => null,
            ], 404);
        }

        $validated = $request->validate([
            'decision' => ['required', 'string', 'in:APPROVED,REVISE,REJECT'],
            'comments' => ['nullable', 'string'],
        ], [
            'decision.required' => 'Vui lòng chọn quyết định thẩm tra (APPROVED, REVISE, REJECT).',
        ]);

        $newStatus = match ($validated['decision']) {
            'APPROVED' => 'APPROVED_FOR_MEETING',
            'REVISE' => 'REVISION_REQUIRED',
            'REJECT' => 'REJECTED',
        };

        $comments = $validated['comments'] ?? null;
        $ghiChu = $comments ? ($dossier->GhiChu ? "{$dossier->GhiChu}\n[Thẩm tra]: {$comments}" : "[Thẩm tra]: {$comments}") : $dossier->GhiChu;

        $dossier->update([
            'TrangThai' => $newStatus,
            'GhiChu' => $ghiChu,
        ]);

        $statusMsg = match ($newStatus) {
            'APPROVED_FOR_MEETING' => 'Hồ sơ đã được thẩm tra ĐẠT và sẵn sàng xếp lịch họp.',
            'REVISION_REQUIRED' => 'Đã yêu cầu đơn vị chỉnh sửa/bổ sung hồ sơ.',
            'REJECTED' => 'Đã từ chối tiếp nhận hồ sơ.',
        };

        return response()->json([
            'success' => true,
            'message' => $statusMsg,
            'data' => $this->formatDossierDetail($dossier->fresh(['donVi', 'nguoiDung'])),
            'errors' => null,
        ]);
    }

    /**
     * Xóa hồ sơ (chỉ khi đang ở trạng thái DRAFT)
     * DELETE /api/v1/dossiers/{id}
     */
    public function destroy(int|string $id): JsonResponse
    {
        $dossier = HoSoTrinh::find($id);
        if (! $dossier) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy hồ sơ để xóa.',
                'data' => null,
                'errors' => null,
            ], 404);
        }

        if ($dossier->TrangThai !== 'DRAFT') {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ có thể xóa hồ sơ ở trạng thái Dự thảo (DRAFT).',
                'data' => null,
                'errors' => null,
            ], 422);
        }

        $dossier->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa hồ sơ dự thảo thành công.',
            'data' => null,
            'errors' => null,
        ]);
    }

    /**
     * Format thông tin tóm tắt hồ sơ
     */
    private function formatDossierSummary(HoSoTrinh $dossier): array
    {
        $statusLabels = [
            'DRAFT' => 'Dự thảo',
            'SUBMITTED' => 'Đã nộp',
            'IN_REVIEW' => 'Đang thẩm tra',
            'APPROVED_FOR_MEETING' => 'Đã duyệt - Sẵn sàng xếp lịch',
            'REVISION_REQUIRED' => 'Yêu cầu bổ sung',
            'REJECTED' => 'Từ chối tiếp nhận',
            'SCHEDULED' => 'Đã xếp lịch họp',
        ];

        return [
            'id' => (string) $dossier->MaHoSo,
            'code' => sprintf('HST-2026-%03d', $dossier->MaHoSo),
            'title' => $dossier->TieuDe,
            'summary' => $dossier->NoiDungTomTat,
            'submitted_date' => $dossier->NgayTrinh ? date('Y-m-d', strtotime($dossier->NgayTrinh)) : '',
            'field' => $dossier->LinhVuc,
            'status' => $dossier->TrangThai,
            'status_label' => $statusLabels[$dossier->TrangThai] ?? $dossier->TrangThai,
            'notes' => $dossier->GhiChu ?? '',
            'department_id' => $dossier->MaDonVi,
            'department' => $dossier->donVi?->TenDonVi ?? 'Chưa xác định',
            'submitter_id' => $dossier->MaNguoiDung,
            'submitter' => $dossier->nguoiDung?->HoTen ?? 'Đại diện đơn vị',
            'files_count' => $dossier->taiLieu ? $dossier->taiLieu->count() : 0,
        ];
    }

    /**
     * Format chi tiết hồ sơ
     */
    private function formatDossierDetail(HoSoTrinh $dossier): array
    {
        $summary = $this->formatDossierSummary($dossier);

        $documents = $dossier->taiLieu ? $dossier->taiLieu->map(function ($doc) {
            return [
                'id' => (string) $doc->MaTaiLieu,
                'file_name' => $doc->TenTaiLieu,
                'file_size' => (int) $doc->DungLuong,
                'file_type' => $doc->DinhDang,
                'access_level' => $doc->LoaiTaiLieu,
                'uploaded_at' => $doc->NgayTaiLen ? date('Y-m-d H:i', strtotime($doc->NgayTaiLen)) : null,
            ];
        })->values()->toArray() : [];

        return array_merge($summary, [
            'documents' => $documents,
        ]);
    }
}
