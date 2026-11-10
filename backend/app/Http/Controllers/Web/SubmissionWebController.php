<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\DonVi;
use App\Models\HoSoTrinh;
use App\Models\TaiLieu;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SubmissionWebController extends Controller
{
    /**
     * Danh sách hồ sơ trình (Phân quyền đơn vị, Lọc, Tìm kiếm, Phân trang)
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $role = $user?->role ?? (session('user_role') ?? 'CHAIR');
        $userDeptId = $user?->department_id ?? session('department_id');

        $query = HoSoTrinh::with(['donVi', 'nguoiDung', 'taiLieu']);

        // [RBAC] Đại diện đơn vị (UNIT_REP) chỉ được quản lý hồ sơ thuộc đơn vị mình
        if ($role === 'UNIT_REP' && $userDeptId) {
            $query->where('MaDonVi', $userDeptId);
        }

        // 1. Tìm kiếm từ khóa
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('TieuDe', 'like', "%{$search}%")
                    ->orWhere('NoiDungTomTat', 'like', "%{$search}%")
                    ->orWhere('LinhVuc', 'like', "%{$search}%");
            });
        }

        // 2. Lọc theo trạng thái
        if ($status = $request->input('status')) {
            $query->where('TrangThai', $status);
        }

        // 3. Lọc theo đơn vị (đối với các vai trò có quyền xem toàn trường)
        if ($deptId = $request->input('department_id')) {
            if ($deptId !== 'ALL') {
                $query->where('MaDonVi', $deptId);
            }
        }

        // 4. Lọc theo lĩnh vực
        if ($field = $request->input('field')) {
            $query->where('LinhVuc', $field);
        }

        $submissions = $query->orderBy('NgayTrinh', 'desc')
            ->orderBy('MaHoSo', 'desc')
            ->paginate(10)
            ->withQueryString();

        $departments = DonVi::where('TrangThai', 'ACTIVE')->orWhere('TrangThai', 'Hoạt động')->get();
        $fields = HoSoTrinh::select('LinhVuc')->distinct()->pluck('LinhVuc');

        return view('pages.submissions.index', compact('submissions', 'departments', 'fields', 'role', 'userDeptId'));
    }

    /**
     * Biểu mẫu tạo mới hồ sơ trình
     */
    public function create(): View|RedirectResponse
    {
        $user = Auth::user();
        $role = $user?->role ?? (session('user_role') ?? 'CHAIR');

        $departments = DonVi::all();
        $userDeptId = $user?->department_id ?? session('department_id');

        return view('pages.submissions.create', compact('departments', 'role', 'userDeptId'));
    }

    /**
     * Lưu hồ sơ trình mới kèm tài liệu đính kèm (Lưu trữ Laravel Storage)
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $role = $user?->role ?? (session('user_role') ?? 'CHAIR');

        $validated = $request->validate([
            'TieuDe' => ['required', 'string', 'min:5', 'max:255'],
            'NoiDungTomTat' => ['required', 'string'],
            'LinhVuc' => ['required', 'string', 'max:255'],
            'MaDonVi' => ['required', 'integer', 'exists:don_vi,MaDonVi'],
            'GhiChu' => ['nullable', 'string'],
            'file' => ['nullable', 'file', 'max:20480', 'mimes:pdf,doc,docx,xls,xlsx'],
        ], [
            'TieuDe.required' => 'Vui lòng nhập tiêu đề hồ sơ tờ trình.',
            'NoiDungTomTat.required' => 'Vui lòng nhập tóm tắt nội dung đề xuất.',
            'LinhVuc.required' => 'Vui lòng chọn lĩnh vực hồ sơ.',
            'MaDonVi.required' => 'Vui lòng chọn đơn vị trình.',
            'file.max' => 'Tập tin đính kèm không vượt quá 20MB.',
        ]);

        // Nếu là UNIT_REP, bắt buộc lưu theo đơn vị của người dùng
        if ($role === 'UNIT_REP' && $user?->MaDonVi) {
            $validated['MaDonVi'] = $user->MaDonVi;
        }

        $dossier = DB::transaction(function () use ($validated, $request, $user) {
            $record = HoSoTrinh::create([
                'TieuDe' => $validated['TieuDe'],
                'NoiDungTomTat' => $validated['NoiDungTomTat'],
                'NgayTrinh' => now()->toDateString(),
                'LinhVuc' => $validated['LinhVuc'],
                'TrangThai' => 'Mới tạo',
                'GhiChu' => $validated['GhiChu'] ?? null,
                'MaDonVi' => $validated['MaDonVi'],
                'MaNguoiDung' => $user?->MaNguoiDung ?? 1,
            ]);

            // Xử lý tệp đính kèm an toàn nếu có
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $fileName = time() . '_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $file->getClientOriginalName());
                $path = $file->storeAs('documents/submissions', $fileName, 'public');

                $taiLieu = TaiLieu::create([
                    'TenTaiLieu' => $file->getClientOriginalName(),
                    'LoaiTaiLieu' => 'Tờ trình đính kèm',
                    'DuongDan' => '/storage/' . $path,
                    'DinhDang' => $file->getClientOriginalExtension(),
                    'DungLuong' => $file->getSize(),
                    'NgayTaiLen' => now(),
                    'MaNguoiDung' => $user?->MaNguoiDung ?? 1,
                ]);

                DB::table('tai_lieu_ho_so')->insertOrIgnore([
                    'MaTaiLieu' => $taiLieu->MaTaiLieu,
                    'MaHoSo' => $record->MaHoSo,
                ]);
            }

            return $record;
        });

        return redirect()->route('submissions.show', $dossier->MaHoSo)
            ->with('success', 'Tạo hồ sơ trình thành công (Trạng thái: Mới tạo)!');
    }

    /**
     * Chi tiết và quy trình thẩm tra hồ sơ trình
     */
    public function show(int|string $id): View|RedirectResponse
    {
        $dossier = HoSoTrinh::with(['donVi', 'nguoiDung', 'taiLieu', 'chuongTrinhHop.phienHop'])
            ->findOrFail($id);

        $user = Auth::user();
        $role = $user?->role ?? (session('user_role') ?? 'CHAIR');
        $userDeptId = $user?->department_id ?? session('department_id');

        // [RBAC Security] Chặn người dùng UNIT_REP xem hồ sơ của đơn vị khác
        if ($role === 'UNIT_REP' && $userDeptId && $dossier->MaDonVi !== (int) $userDeptId) {
            return redirect()->route('submissions.index')
                ->with('error', 'Bạn không có quyền truy cập hồ sơ của đơn vị khác.');
        }

        return view('pages.submissions.show', compact('dossier', 'role'));
    }

    /**
     * Biểu mẫu chỉnh sửa / cập nhật bổ sung hồ sơ trình
     */
    public function edit(int|string $id): View|RedirectResponse
    {
        $dossier = HoSoTrinh::with(['donVi', 'taiLieu'])->findOrFail($id);

        $user = Auth::user();
        $role = $user?->role ?? (session('user_role') ?? 'CHAIR');
        $userDeptId = $user?->department_id ?? session('department_id');

        if ($role === 'UNIT_REP' && $userDeptId && $dossier->MaDonVi !== (int) $userDeptId) {
            return redirect()->route('submissions.index')
                ->with('error', 'Bạn không có quyền chỉnh sửa hồ sơ của đơn vị khác.');
        }

        if (! in_array($role, ['UNIT_REP', 'SECRETARY', 'CHAIR', 'ADMIN'])) {
            return redirect()->route('submissions.show', $dossier->MaHoSo)
                ->with('error', 'Bạn không có quyền chỉnh sửa hồ sơ trình.');
        }

        $departments = DonVi::all();

        return view('pages.submissions.edit', compact('dossier', 'departments', 'role', 'userDeptId'));
    }

    /**
     * Lưu thông tin cập nhật hồ sơ trình và đính kèm thêm tài liệu (nếu có)
     */
    public function update(Request $request, int|string $id): RedirectResponse
    {
        $dossier = HoSoTrinh::findOrFail($id);

        $user = Auth::user();
        $role = $user?->role ?? (session('user_role') ?? 'CHAIR');
        $userDeptId = $user?->department_id ?? session('department_id');

        if ($role === 'UNIT_REP' && $userDeptId && $dossier->MaDonVi !== (int) $userDeptId) {
            return redirect()->route('submissions.index')
                ->with('error', 'Bạn không có quyền cập nhật hồ sơ của đơn vị khác.');
        }

        if (! in_array($role, ['UNIT_REP', 'SECRETARY', 'CHAIR', 'ADMIN'])) {
            return redirect()->route('submissions.show', $dossier->MaHoSo)
                ->with('error', 'Bạn không có quyền cập nhật hồ sơ trình.');
        }

        $validated = $request->validate([
            'TieuDe' => ['required', 'string', 'min:5', 'max:255'],
            'NoiDungTomTat' => ['required', 'string'],
            'LinhVuc' => ['required', 'string', 'max:255'],
            'MaDonVi' => ['required', 'integer', 'exists:don_vi,MaDonVi'],
            'GhiChu' => ['nullable', 'string'],
            'file' => ['nullable', 'file', 'max:20480', 'mimes:pdf,doc,docx,xls,xlsx'],
        ], [
            'TieuDe.required' => 'Vui lòng nhập tiêu đề hồ sơ tờ trình.',
            'NoiDungTomTat.required' => 'Vui lòng nhập tóm tắt nội dung đề xuất.',
            'LinhVuc.required' => 'Vui lòng chọn lĩnh vực hồ sơ.',
            'MaDonVi.required' => 'Vui lòng chọn đơn vị trình.',
            'file.max' => 'Tập tin đính kèm không vượt quá 20MB.',
        ]);

        if ($role === 'UNIT_REP' && $user?->MaDonVi) {
            $validated['MaDonVi'] = $user->MaDonVi;
        }

        DB::transaction(function () use ($dossier, $validated, $request, $user) {
            $updateData = [
                'TieuDe' => $validated['TieuDe'],
                'NoiDungTomTat' => $validated['NoiDungTomTat'],
                'LinhVuc' => $validated['LinhVuc'],
                'MaDonVi' => $validated['MaDonVi'],
                'GhiChu' => $validated['GhiChu'] ?? $dossier->GhiChu,
            ];

            // Nếu hồ sơ đang ở trạng thái Chờ bổ sung, sau khi đơn vị cập nhật sẽ chuyển sang Đầy đủ để chờ thẩm tra lại
            if ($dossier->TrangThai === 'Chờ bổ sung') {
                $updateData['TrangThai'] = 'Đầy đủ';
            }

            $dossier->update($updateData);

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $fileName = time() . '_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $file->getClientOriginalName());
                $path = $file->storeAs('documents/submissions', $fileName, 'public');

                $taiLieu = TaiLieu::create([
                    'TenTaiLieu' => $file->getClientOriginalName(),
                    'LoaiTaiLieu' => 'Tài liệu bổ sung',
                    'DuongDan' => '/storage/' . $path,
                    'DinhDang' => $file->getClientOriginalExtension(),
                    'DungLuong' => $file->getSize(),
                    'NgayTaiLen' => now(),
                    'MaNguoiDung' => $user?->MaNguoiDung ?? 1,
                ]);

                DB::table('tai_lieu_ho_so')->insertOrIgnore([
                    'MaTaiLieu' => $taiLieu->MaTaiLieu,
                    'MaHoSo' => $dossier->MaHoSo,
                ]);
            }
        });

        return redirect()->route('submissions.show', $dossier->MaHoSo)
            ->with('success', 'Đã cập nhật thông tin hồ sơ trình thành công!');
    }

    /**
     * Thẩm tra hồ sơ trình (Chỉ Thư ký, Chủ tịch, Quản trị viên)
     */
    public function review(Request $request, int|string $id): RedirectResponse
    {
        $role = Auth::user()?->role ?? (session('user_role') ?? 'CHAIR');
        if (! in_array($role, ['SECRETARY', 'CHAIR', 'ADMIN'])) {
            return back()->with('error', 'Bạn không có thẩm quyền thẩm tra hồ sơ trình.');
        }

        $dossier = HoSoTrinh::findOrFail($id);

        $validated = $request->validate([
            'decision' => ['required', 'string', 'in:ELIGIBLE,REVISE,COMPLETE'],
            'GhiChu' => ['nullable', 'string'],
        ]);

        $newStatus = match ($validated['decision']) {
            'ELIGIBLE' => 'Đủ điều kiện',
            'REVISE'   => 'Chờ bổ sung',
            'COMPLETE' => 'Đầy đủ',
        };

        $dossier->update([
            'TrangThai' => $newStatus,
            'GhiChu' => $validated['GhiChu'] ? ($dossier->GhiChu . "\n[Ý kiến thẩm định " . date('d/m/Y') . "]: " . $validated['GhiChu']) : $dossier->GhiChu,
        ]);

        return back()->with('success', 'Đã cập nhật trạng thái thẩm định hồ sơ: ' . $newStatus);
    }
}
