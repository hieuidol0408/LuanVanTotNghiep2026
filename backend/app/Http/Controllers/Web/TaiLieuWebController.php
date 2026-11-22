<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\TaiLieu;
use App\Services\DocumentPreviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class TaiLieuWebController extends Controller
{
    public function __construct(
        protected DocumentPreviewService $previewService
    ) {}

    /**
     * Danh sách tài liệu và văn bản số (BR-05, BR-06)
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $isCouncilMember = $user && in_array($user->role, ['CHAIR', 'VICE_CHAIR', 'SECRETARY', 'MEMBER', 'ADMIN']);

        $query = TaiLieu::with(['nguoiDung', 'phienHop', 'nghiQuyet', 'hoSoTrinh']);

        // Tìm kiếm
        if ($search = $request->input('search')) {
            $query->where('TenTaiLieu', 'like', "%{$search}%");
        }

        // Lọc theo loại tài liệu
        if ($type = $request->input('type')) {
            if ($type !== 'ALL') {
                $query->where('LoaiTaiLieu', $type);
            }
        }

        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [5, 10, 20, 50, 100])) {
            $perPage = 10;
        }

        $documents = $query->orderByDesc('NgayTaiLen')
            ->orderByDesc('MaTaiLieu')
            ->paginate($perPage)
            ->withQueryString();

        $types = TaiLieu::select('LoaiTaiLieu')->distinct()->pluck('LoaiTaiLieu')->filter();

        return view('pages.tailieu.danhsach', compact('documents', 'types', 'isCouncilMember'));
    }

    /**
     * Tải tệp tài liệu an toàn có kiểm soát phân quyền (Access Control Matrix)
     */
    public function download(int $id): Response|RedirectResponse
    {
        $user = Auth::user();
        $doc = TaiLieu::findOrFail($id);

        $isCouncilMember = $user && in_array($user->role, ['CHAIR', 'VICE_CHAIR', 'SECRETARY', 'MEMBER', 'ADMIN']);

        // [Security] Chặn người ngoài/đại diện đơn vị tải tài liệu nội bộ HĐT
        if ($doc->LoaiTaiLieu === 'INTERNAL' && !$isCouncilMember) {
            return back()->with('error', 'Bạn không có thẩm quyền truy cập tài liệu nội bộ bảo mật của Hội đồng trường.');
        }

        $realPath = $this->getRealFilePath($doc);
        if ($realPath && file_exists($realPath)) {
            $ext = pathinfo($realPath, PATHINFO_EXTENSION);
            $downloadName = $doc->TenTaiLieu;
            if (!str_ends_with(strtolower($downloadName), '.' . strtolower($ext))) {
                $downloadName .= '.' . $ext;
            }
            return response()->download($realPath, $downloadName);
        }

        // Fallback: Trả về file văn bản bảo mật nếu là file minh họa
        $content = "%PDF-1.4\n% STU Council Official Document: {$doc->TenTaiLieu}\n1 0 obj\n<< /Title ({$doc->TenTaiLieu}) >>\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF";
        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $doc->TenTaiLieu . '.pdf"',
        ]);
    }

    /**
     * Xem tệp trực tiếp trong trình duyệt (Stream Inline) không ép tải về
     */
    public function view(int $id): Response|RedirectResponse
    {
        $user = Auth::user();
        $doc = TaiLieu::findOrFail($id);

        $isCouncilMember = $user && in_array($user->role, ['CHAIR', 'VICE_CHAIR', 'SECRETARY', 'MEMBER', 'ADMIN']);
        if ($doc->LoaiTaiLieu === 'INTERNAL' && !$isCouncilMember) {
            return back()->with('error', 'Bạn không có thẩm quyền xem tài liệu nội bộ này.');
        }

        $realPath = $this->getRealFilePath($doc);
        $ext = strtolower($doc->DinhDang ?: pathinfo($realPath ?: $doc->DuongDan, PATHINFO_EXTENSION));

        // Đối với file Office (.docx, .xlsx, .pptx), trình duyệt không thể xem raw trực tiếp mà sẽ tự tải về,
        // do đó chuyển hướng sang trang preview trực quan của hệ thống
        if (in_array($ext, ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'])) {
            return redirect()->route('documents.preview', $id);
        }

        if ($realPath && file_exists($realPath)) {
            $mime = match ($ext) {
                'pdf' => 'application/pdf',
                'png' => 'image/png',
                'jpg', 'jpeg' => 'image/jpeg',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                'svg' => 'image/svg+xml',
                'txt' => 'text/plain; charset=UTF-8',
                'csv' => 'text/csv; charset=UTF-8',
                default => mime_content_type($realPath) ?: 'application/octet-stream',
            };

            return response()->file($realPath, [
                'Content-Type' => $mime,
                'Content-Disposition' => 'inline; filename="' . rawurlencode($doc->TenTaiLieu) . '"',
            ]);
        }

        return redirect()->route('documents.preview', $id);
    }

    /**
     * Trang xem trước tài liệu trực tiếp toàn màn hình (Trình xem trực tuyến chuyên dụng)
     */
    public function preview(int $id): View|RedirectResponse
    {
        $user = Auth::user();
        $doc = TaiLieu::with(['hoSoTrinh', 'phienHop', 'nghiQuyet'])->findOrFail($id);

        $isCouncilMember = $user && in_array($user->role, ['CHAIR', 'VICE_CHAIR', 'SECRETARY', 'MEMBER', 'ADMIN']);
        if ($doc->LoaiTaiLieu === 'INTERNAL' && !$isCouncilMember) {
            return redirect()->route('submissions.index')->with('error', 'Bạn không có thẩm quyền xem tài liệu nội bộ này.');
        }

        $backUrl = route('submissions.index');
        if (request('back')) {
            $backUrl = request('back');
        } elseif ($doc->hoSoTrinh->isNotEmpty()) {
            $backUrl = route('submissions.show', $doc->hoSoTrinh->first()->MaHoSo);
        } elseif ($doc->phienHop->isNotEmpty()) {
            $backUrl = route('meetings.show', ['id' => $doc->phienHop->first()->MaPhienHop, 'tab' => 'docs']);
        } elseif ($doc->nghiQuyet->isNotEmpty()) {
            $backUrl = route('resolutions.show', $doc->nghiQuyet->first()->MaNghiQuyet);
        } elseif (url()->previous() && url()->previous() !== url()->current()) {
            $backUrl = url()->previous();
        }

        $realPath = $this->getRealFilePath($doc);
        $ext = strtolower($doc->DinhDang ?: pathinfo($realPath ?: $doc->DuongDan, PATHINFO_EXTENSION));

        $previewHtml = '';
        $sheets = [];
        $slides = [];

        if ($realPath && file_exists($realPath)) {
            if (in_array($ext, ['docx', 'doc'])) {
                $previewHtml = $this->previewService->convertDocxToHtml($realPath);
            } elseif (in_array($ext, ['xlsx', 'xls'])) {
                $res = $this->previewService->convertXlsxToHtml($realPath);
                $sheets = $res['sheets'] ?? [];
            } elseif (in_array($ext, ['pptx', 'ppt'])) {
                $res = $this->previewService->convertPptxToHtml($realPath);
                $slides = $res['slides'] ?? [];
            }
        }

        return view('pages.tailieu.xemtruoc', compact('doc', 'ext', 'previewHtml', 'sheets', 'slides', 'realPath', 'backUrl'));
    }

    /**
     * API trả về nội dung phân tích để hiển thị ngay trong popup modal
     */
    public function content(int $id): JsonResponse
    {
        $user = Auth::user();
        $doc = TaiLieu::findOrFail($id);

        $isCouncilMember = $user && in_array($user->role, ['CHAIR', 'VICE_CHAIR', 'SECRETARY', 'MEMBER', 'ADMIN']);
        if ($doc->LoaiTaiLieu === 'INTERNAL' && !$isCouncilMember) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền truy cập tài liệu này.',
            ], 403);
        }

        $realPath = $this->getRealFilePath($doc);
        $ext = strtolower($doc->DinhDang ?: pathinfo($realPath ?: $doc->DuongDan, PATHINFO_EXTENSION));

        $html = '';
        $sheets = [];
        $slides = [];

        if ($realPath && file_exists($realPath)) {
            if (in_array($ext, ['docx', 'doc'])) {
                $html = $this->previewService->convertDocxToHtml($realPath);
            } elseif (in_array($ext, ['xlsx', 'xls'])) {
                $res = $this->previewService->convertXlsxToHtml($realPath);
                $sheets = $res['sheets'] ?? [];
            } elseif (in_array($ext, ['pptx', 'ppt'])) {
                $res = $this->previewService->convertPptxToHtml($realPath);
                $slides = $res['slides'] ?? [];
            } elseif (in_array($ext, ['txt', 'csv'])) {
                $content = file_get_contents($realPath);
                $html = '<pre style="padding:16px; background:#f8fafc; border-radius:6px; font-size:13px; line-height:1.5;">' . htmlspecialchars($content) . '</pre>';
            }
        }

        return response()->json([
            'success' => true,
            'id' => $doc->MaTaiLieu,
            'title' => $doc->TenTaiLieu,
            'type' => $doc->LoaiTaiLieu ?? 'Tài liệu đính kèm',
            'extension' => $ext,
            'size' => $doc->DungLuong,
            'size_formatted' => $doc->DungLuong ? number_format($doc->DungLuong / 1024, 1) . ' KB' : '—',
            'file_url' => asset($doc->DuongDan),
            'view_url' => route('documents.view', $doc->MaTaiLieu),
            'preview_url' => route('documents.preview', $doc->MaTaiLieu),
            'download_url' => route('documents.download', $doc->MaTaiLieu),
            'html' => $html,
            'sheets' => $sheets,
            'slides' => $slides,
        ]);
    }

    /**
     * Ẩn tài liệu (Soft Delete - bảo toàn dữ liệu trong CSDL)
     */
    public function destroy(int|string $id): RedirectResponse
    {
        $role = Auth::user()?->role ?? (session('user_role') ?? 'CHAIR');
        if (!in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN'])) {
            return back()->with('error', 'Bạn không có thẩm quyền ẩn tài liệu.');
        }

        $doc = TaiLieu::findOrFail($id);
        $doc->delete();

        return back()->with('success', "Đã ẩn tài liệu \"{$doc->TenTaiLieu}\" khỏi danh sách hiển thị (dữ liệu được bảo toàn).");
    }

    /**
     * Xác định đường dẫn vật lý thực tế của file trên server
     */
    protected function getRealFilePath(TaiLieu $doc): ?string
    {
        $cleanRel = str_replace(['/storage/', 'storage/'], '', $doc->DuongDan);
        if (Storage::disk('public')->exists($cleanRel)) {
            return Storage::disk('public')->path($cleanRel);
        }

        $localPath = public_path($doc->DuongDan);
        if (file_exists($localPath)) {
            return $localPath;
        }

        $storagePath = storage_path('app/public/' . $cleanRel);
        if (file_exists($storagePath)) {
            return $storagePath;
        }

        return null;
    }
}
