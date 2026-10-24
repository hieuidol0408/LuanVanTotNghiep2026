<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\HoSoTrinh;
use App\Models\NghiQuyet;
use App\Models\PhienHop;
use App\Models\TaiLieu;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class DocumentController extends Controller
{
    /**
     * Danh sách Kho Văn bản & Tài liệu
     * [BR-05] Quản lý Tài liệu riêng của Phiên họp qua bảng trung gian TAI_LIEU_PHIEN_HOP
     * [BR-06] Áp dụng Khóa chính kép cho các bảng trung gian tài liệu (TAI_LIEU_PHIEN_HOP, TAI_LIEU_HO_SO, TAI_LIEU_NGHI_QUYET, TAI_LIEU_BAO_CAO)
     * GET /api/v1/documents
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = TaiLieu::with(['nguoiDung', 'phienHop', 'nghiQuyet', 'hoSoTrinh']);

        // Tìm kiếm theo tên tài liệu
        if ($search = $request->query('search')) {
            $query->where('TenTaiLieu', 'like', "%{$search}%");
        }

        // Lọc theo loại / cấp độ bảo mật
        if ($type = $request->query('type')) {
            if ($type !== 'ALL') {
                $query->where('LoaiTaiLieu', $type);
            }
        }

        // Lọc theo phiên họp
        if ($meetingId = $request->query('meeting_id')) {
            if ($meetingId !== 'ALL') {
                $query->whereHas('phienHop', fn ($q) => $q->where('phien_hop.MaPhienHop', $meetingId));
            }
        }

        // Lọc theo nghị quyết
        if ($resolutionId = $request->query('resolution_id')) {
            if ($resolutionId !== 'ALL') {
                $query->whereHas('nghiQuyet', fn ($q) => $q->where('nghi_quyet.MaNghiQuyet', $resolutionId));
            }
        }

        // Lọc theo hồ sơ trình
        if ($dossierId = $request->query('dossier_id')) {
            if ($dossierId !== 'ALL') {
                $query->whereHas('hoSoTrinh', fn ($q) => $q->where('ho_so_trinh.MaHoSo', $dossierId));
            }
        }

        $perPage = (int) $request->query('perPage', 15);
        $page = (int) $request->query('page', 1);

        $paginator = $query->orderBy('NgayTaiLen', 'desc')
            ->orderBy('MaTaiLieu', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        $isCouncilMember = in_array($user?->role, ['CHAIR', 'VICE_CHAIR', 'SECRETARY', 'MEMBER', 'ADMIN']);

        $items = collect($paginator->items())->map(function (TaiLieu $doc) use ($isCouncilMember) {
            return $this->formatDocument($doc, $isCouncilMember);
        });

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách tài liệu thành công.',
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
     * Tải lên tài liệu mới
     * POST /api/v1/documents
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'file_name' => ['required', 'string', 'min:3', 'max:255'],
            'access_level' => ['required', 'string', 'in:INTERNAL,PUBLIC_AGENDA,REGULATION'],
            'file_type' => ['nullable', 'string'],
            'file_size' => ['nullable', 'integer'],
            'meeting_id' => ['nullable', 'integer', 'exists:phien_hop,MaPhienHop'],
            'resolution_id' => ['nullable', 'integer', 'exists:nghi_quyet,MaNghiQuyet'],
            'dossier_id' => ['nullable', 'integer', 'exists:ho_so_trinh,MaHoSo'],
        ], [
            'file_name.required' => 'Vui lòng nhập tên tệp tài liệu.',
            'access_level.required' => 'Vui lòng chọn mức độ bảo mật (Nội bộ / Công khai / Quy chế).',
        ]);

        $doc = TaiLieu::create([
            'TenTaiLieu' => $validated['file_name'],
            'LoaiTaiLieu' => $validated['access_level'],
            'DuongDan' => 'documents/' . strtolower($validated['access_level']) . '/' . $validated['file_name'],
            'DinhDang' => $validated['file_type'] ?? 'application/pdf',
            'DungLuong' => $validated['file_size'] ?? 1048576,
            'NgayTaiLen' => now(),
            'MaNguoiDung' => $user->MaNguoiDung,
        ]);

        if (! empty($validated['meeting_id'])) {
            $doc->phienHop()->syncWithoutDetaching([$validated['meeting_id']]);
        }
        if (! empty($validated['resolution_id'])) {
            $doc->nghiQuyet()->syncWithoutDetaching([$validated['resolution_id']]);
        }
        if (! empty($validated['dossier_id'])) {
            $doc->hoSoTrinh()->syncWithoutDetaching([$validated['dossier_id']]);
        }

        $isCouncilMember = in_array($user->role, ['CHAIR', 'VICE_CHAIR', 'SECRETARY', 'MEMBER', 'ADMIN']);

        return response()->json([
            'success' => true,
            'message' => 'Tải lên tài liệu thành công.',
            'data' => $this->formatDocument($doc, $isCouncilMember),
            'errors' => null,
        ], 201);
    }

    /**
     * Tải tệp an toàn có kiểm soát phân quyền chặt chẽ (Safe Document Download)
     * GET /api/v1/documents/{id}/download
     */
    public function download(Request $request, int|string $id): Response
    {
        $user = $request->user();
        $doc = TaiLieu::find($id);

        if (! $doc) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy tài liệu.',
                'data' => null,
                'errors' => null,
            ], 404);
        }

        // KIỂM SOÁT PHÂN QUYỀN TRUY CẬP (Access Control Matrix):
        // Nếu là tài liệu INTERNAL: Bắt buộc người dùng phải là Thành viên chính thức HĐT hoặc Admin
        $isCouncilMember = in_array($user?->role, ['CHAIR', 'VICE_CHAIR', 'SECRETARY', 'MEMBER', 'ADMIN']);

        if ($doc->LoaiTaiLieu === 'INTERNAL' && ! $isCouncilMember) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có thẩm quyền truy cập tài liệu nội bộ bảo mật của Hội đồng trường.',
                'data' => null,
                'errors' => [
                    'access_denied' => 'Chỉ Thành viên Hội đồng trường mới được phép tải tệp nội bộ này.',
                ],
            ], 403);
        }

        // Trả về luồng tải tệp giả lập an toàn kèm headers
        $content = "%PDF-1.4\n% STU Council Secure Document: {$doc->TenTaiLieu}\n1 0 obj\n<< /Title ({$doc->TenTaiLieu}) >>\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF";

        return response($content, 200, [
            'Content-Type' => $doc->DinhDang ?: 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $doc->TenTaiLieu . '"',
            'X-Access-Level' => $doc->LoaiTaiLieu,
        ]);
    }

    /**
     * Xóa tài liệu
     * DELETE /api/v1/documents/{id}
     */
    public function destroy(Request $request, int|string $id): JsonResponse
    {
        $user = $request->user();
        if (! in_array($user?->role, ['CHAIR', 'SECRETARY', 'ADMIN'])) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền xóa tài liệu.',
                'data' => null,
                'errors' => null,
            ], 403);
        }

        $doc = TaiLieu::find($id);
        if (! $doc) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy tài liệu để xóa.',
                'data' => null,
                'errors' => null,
            ], 404);
        }

        $doc->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa tài liệu thành công.',
            'data' => null,
            'errors' => null,
        ]);
    }

    /**
     * Format tài liệu
     */
    private function formatDocument(TaiLieu $doc, bool $isCouncilMember): array
    {
        $accessLabels = [
            'INTERNAL' => 'Nội bộ HĐT',
            'PUBLIC_AGENDA' => 'Công khai phiên họp',
            'REGULATION' => 'Văn bản quy chế',
        ];

        // Format dung lượng dễ đọc
        $bytes = (int) $doc->DungLuong;
        $formattedSize = $bytes >= 1048576
            ? round($bytes / 1048576, 1) . ' MB'
            : round($bytes / 1024, 0) . ' KB';

        $canDownload = $doc->LoaiTaiLieu !== 'INTERNAL' || $isCouncilMember;

        return [
            'id' => (string) $doc->MaTaiLieu,
            'file_name' => $doc->TenTaiLieu,
            'access_level' => $doc->LoaiTaiLieu,
            'access_level_label' => $accessLabels[$doc->LoaiTaiLieu] ?? $doc->LoaiTaiLieu,
            'file_size' => $bytes,
            'file_size_formatted' => $formattedSize,
            'file_type' => $doc->DinhDang,
            'uploaded_at' => $doc->NgayTaiLen ? date('Y-m-d H:i', strtotime($doc->NgayTaiLen)) : null,
            'uploaded_by' => $doc->nguoiDung?->HoTen ?? 'Ban Thư ký HĐT',
            'can_download' => $canDownload,
            'related_meetings' => $doc->phienHop->map(fn ($m) => ['id' => (string) $m->MaPhienHop, 'title' => $m->TenPhienHop])->values()->toArray(),
            'related_resolutions' => $doc->nghiQuyet->map(fn ($r) => ['id' => (string) $r->MaNghiQuyet, 'number' => $r->SoNghiQuyet])->values()->toArray(),
        ];
    }
}
