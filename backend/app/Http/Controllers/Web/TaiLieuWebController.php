<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\TaiLieu;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class TaiLieuWebController extends Controller
{
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

        $perPage = (int) $request->input('per_page', 12);
        if (!in_array($perPage, [6, 12, 24, 48, 96])) {
            $perPage = 12;
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

        // Nếu file thực sự tồn tại trong Storage
        $filePath = str_replace(['/storage/', 'storage/'], '', $doc->DuongDan);
        if (Storage::disk('public')->exists($filePath)) {
            return Storage::disk('public')->download($filePath, $doc->TenTaiLieu);
        }

        // Fallback: Trả về file văn bản bảo mật nếu là file minh họa
        $content = "%PDF-1.4\n% STU Council Official Document: {$doc->TenTaiLieu}\n1 0 obj\n<< /Title ({$doc->TenTaiLieu}) >>\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF";
        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $doc->TenTaiLieu . '.pdf"',
        ]);
    }

    /**
     * Xóa tài liệu (Soft Delete - ẩn dữ liệu trong CSDL)
     */
    public function destroy(int|string $id): RedirectResponse
    {
        $role = Auth::user()?->role ?? (session('user_role') ?? 'CHAIR');
        if (!in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN'])) {
            return back()->with('error', 'Bạn không có thẩm quyền xóa tài liệu.');
        }

        $doc = TaiLieu::findOrFail($id);
        $doc->delete();

        return back()->with('success', "Đã xóa tài liệu \"{$doc->TenTaiLieu}\" khỏi danh sách hiển thị.");
    }
}