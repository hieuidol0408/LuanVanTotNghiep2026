<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\HoSoTrinh;
use App\Models\NghiQuyet;
use App\Models\NoiDungBieuQuyet;
use App\Models\PhienHop;
use App\Models\TaiLieu;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ResolutionWebController extends Controller
{
    /**
     * Danh sách Nghị quyết Hội đồng trường STU
     */
    public function index(Request $request): View
    {
        $query = NghiQuyet::with([
            'phienHop',
            'hoSoTrinh',
            'nhiemVu.phanCongNhiemVu.baoCao',
            'taiLieu',
        ]);

        // 1. Tìm kiếm theo số hiệu hoặc nội dung trích yếu
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('SoNghiQuyet', 'like', "%{$search}%")
                    ->orWhere('TrichYeu', 'like', "%{$search}%")
                    ->orWhere('NoiDung', 'like', "%{$search}%");
            });
        }

        // 2. Lọc theo trạng thái
        if ($status = $request->input('status')) {
            if ($status !== 'ALL') {
                if ($status === 'DRAFT') {
                    $query->whereIn('TrangThai', ['DRAFT', 'Dự thảo']);
                } elseif ($status === 'SIGNED') {
                    $query->whereIn('TrangThai', ['SIGNED', 'Đã ký ban hành', 'Đã ký']);
                } elseif ($status === 'PUBLISHED') {
                    $query->whereIn('TrangThai', ['PUBLISHED', 'Đã công bố hiệu lực', 'Đã công bố']);
                } else {
                    $query->where('TrangThai', $status);
                }
            }
        }

        // 3. Lọc theo phiên họp
        if ($meetingId = $request->input('meeting_id')) {
            if ($meetingId !== 'ALL') {
                $query->where('MaPhienHop', $meetingId);
            }
        }

        // 4. Lọc theo năm
        if ($year = $request->input('year')) {
            if ($year !== 'ALL') {
                $query->whereYear('NgayBanHanh', $year);
            }
        }

        $resolutions = $query->orderBy('NgayBanHanh', 'desc')
            ->orderBy('MaNghiQuyet', 'desc')
            ->paginate(10)
            ->withQueryString();

        $meetings = PhienHop::orderBy('ThoiGianBatDau', 'desc')->get();
        $years = NghiQuyet::selectRaw('YEAR(NgayBanHanh) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->filter();

        $role = Auth::user()?->role ?? (session('user_role') ?? 'CHAIR');

        return view('pages.resolutions.index', compact('resolutions', 'meetings', 'years', 'role'));
    }

    /**
     * Biểu mẫu soạn thảo Nghị quyết mới
     */
    public function create(Request $request): View|RedirectResponse
    {
        $role = Auth::user()?->role ?? (session('user_role') ?? 'CHAIR');
        if (! in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN'])) {
            return redirect()->route('resolutions.index')->with('error', 'Bạn không có thẩm quyền soạn thảo Nghị quyết.');
        }

        $meetings = PhienHop::orderBy('ThoiGianBatDau', 'desc')->get();
        $selectedMeetingId = $request->input('meeting_id') ?? $meetings->first()?->MaPhienHop;

        // Lấy danh sách hồ sơ trình và nội dung biểu quyết của phiên họp để liên kết
        $dossiers = HoSoTrinh::whereHas('chuongTrinhHop', function ($q) use ($selectedMeetingId) {
            $q->where('MaPhienHop', $selectedMeetingId);
        })->get();

        $votings = NoiDungBieuQuyet::whereHas('chuongTrinhHop', function ($q) use ($selectedMeetingId) {
            $q->where('MaPhienHop', $selectedMeetingId);
        })->get();

        return view('pages.resolutions.create', compact('meetings', 'selectedMeetingId', 'dossiers', 'votings', 'role'));
    }

    /**
     * Lưu Nghị quyết mới (Dự thảo)
     */
    public function store(Request $request): RedirectResponse
    {
        $role = Auth::user()?->role ?? (session('user_role') ?? 'CHAIR');
        if (! in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN'])) {
            return redirect()->route('resolutions.index')->with('error', 'Bạn không có thẩm quyền ban hành Nghị quyết.');
        }

        $validated = $request->validate([
            'SoNghiQuyet' => ['required', 'string', 'max:191', 'unique:nghi_quyet,SoNghiQuyet'],
            'TrichYeu' => ['required', 'string', 'min:5', 'max:255'],
            'NoiDung' => ['required', 'string'],
            'MaPhienHop' => ['required', 'integer', 'exists:phien_hop,MaPhienHop'],
            'NgayBanHanh' => ['required', 'date'],
            'dossier_ids' => ['nullable', 'array'],
            'dossier_ids.*' => ['integer', 'exists:ho_so_trinh,MaHoSo'],
            'voting_ids' => ['nullable', 'array'],
            'voting_ids.*' => ['integer', 'exists:noi_dung_bieu_quyet,MaNoiDung'],
            'file' => ['nullable', 'file', 'max:20480', 'mimes:pdf,doc,docx'],
        ], [
            'SoNghiQuyet.required' => 'Vui lòng nhập số hiệu nghị quyết (ví dụ: 15/NQ-HĐT).',
            'SoNghiQuyet.unique' => 'Số hiệu nghị quyết này đã tồn tại trong hệ thống.',
            'TrichYeu.required' => 'Vui lòng nhập trích yếu nội dung nghị quyết.',
            'NoiDung.required' => 'Vui lòng nhập toàn văn nội dung nghị quyết.',
            'MaPhienHop.required' => 'Vui lòng chọn phiên họp liên kết.',
            'NgayBanHanh.required' => 'Vui lòng chọn ngày ban hành.',
        ]);

        $resolution = DB::transaction(function () use ($validated, $request) {
            $record = NghiQuyet::create([
                'SoNghiQuyet' => $validated['SoNghiQuyet'],
                'TrichYeu' => $validated['TrichYeu'],
                'NoiDung' => $validated['NoiDung'],
                'NgayBanHanh' => $validated['NgayBanHanh'],
                'TrangThai' => 'Dự thảo',
                'MaPhienHop' => $validated['MaPhienHop'],
            ]);

            // Liên kết hồ sơ trình (Khóa chính kép)
            if (! empty($validated['dossier_ids'])) {
                foreach ($validated['dossier_ids'] as $hoSoId) {
                    DB::table('nghi_quyet_ho_so')->insertOrIgnore([
                        'MaNghiQuyet' => $record->MaNghiQuyet,
                        'MaHoSo' => $hoSoId,
                    ]);
                }
            }

            // Liên kết nội dung biểu quyết (Khóa chính kép)
            if (! empty($validated['voting_ids'])) {
                foreach ($validated['voting_ids'] as $voteId) {
                    DB::table('nghi_quyet_bieu_quyet')->insertOrIgnore([
                        'MaNghiQuyet' => $record->MaNghiQuyet,
                        'MaNoiDung' => $voteId,
                    ]);
                }
            }

            // Xử lý tệp đính kèm văn bản nếu có
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $fileName = 'NQ_' . time() . '_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $file->getClientOriginalName());
                $path = $file->storeAs('documents/resolutions', $fileName, 'public');

                $taiLieu = TaiLieu::create([
                    'TenTaiLieu' => $file->getClientOriginalName(),
                    'LoaiTaiLieu' => 'Văn bản Nghị quyết chính thức',
                    'DuongDan' => '/storage/' . $path,
                    'DinhDang' => $file->getClientOriginalExtension(),
                    'DungLuong' => $file->getSize(),
                    'NgayTaiLen' => now(),
                    'MaNguoiDung' => Auth::id() ?? 1,
                ]);

                DB::table('tai_lieu_nghi_quyet')->insertOrIgnore([
                    'MaTaiLieu' => $taiLieu->MaTaiLieu,
                    'MaNghiQuyet' => $record->MaNghiQuyet,
                ]);
            }

            return $record;
        });

        return redirect()->route('resolutions.show', $resolution->MaNghiQuyet)
            ->with('success', 'Soạn thảo dự thảo Nghị quyết thành công (Số hiệu: ' . $resolution->SoNghiQuyet . ')!');
    }

    /**
     * Xem chi tiết toàn văn Nghị quyết & Danh sách nhiệm vụ phân rã
     */
    public function show(int|string $id): View
    {
        $resolution = NghiQuyet::with([
            'phienHop',
            'hoSoTrinh.donVi',
            'noiDungBieuQuyet',
            'taiLieu',
            'nhiemVu.phanCongNhiemVu.donVi',
            'nhiemVu.phanCongNhiemVu.baoCao',
        ])->findOrFail($id);

        $role = Auth::user()?->role ?? (session('user_role') ?? 'CHAIR');

        // Tính tiến độ tổng thể thực hiện nghị quyết dựa trên các nhiệm vụ
        $tasks = $resolution->nhiemVu;
        $totalTasks = $tasks->count();
        $completedTasks = $tasks->filter(fn ($t) => in_array($t->TrangThai, ['COMPLETED', 'Hoàn thành']))->count();
        $overallProgress = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0;

        return view('pages.resolutions.show', compact('resolution', 'role', 'totalTasks', 'completedTasks', 'overallProgress'));
    }

    /**
     * Biểu mẫu chỉnh sửa Nghị quyết (chỉ khi chưa công bố hoặc là Chủ tịch/Admin)
     */
    public function edit(int|string $id): View|RedirectResponse
    {
        $role = Auth::user()?->role ?? (session('user_role') ?? 'CHAIR');
        if (! in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN'])) {
            return redirect()->route('resolutions.show', $id)->with('error', 'Bạn không có quyền chỉnh sửa Nghị quyết này.');
        }

        $resolution = NghiQuyet::with(['hoSoTrinh', 'noiDungBieuQuyet', 'taiLieu'])->findOrFail($id);
        $meetings = PhienHop::orderBy('ThoiGianBatDau', 'desc')->get();

        $dossiers = HoSoTrinh::whereHas('chuongTrinhHop', function ($q) use ($resolution) {
            $q->where('MaPhienHop', $resolution->MaPhienHop);
        })->get();

        $votings = NoiDungBieuQuyet::whereHas('chuongTrinhHop', function ($q) use ($resolution) {
            $q->where('MaPhienHop', $resolution->MaPhienHop);
        })->get();

        return view('pages.resolutions.edit', compact('resolution', 'meetings', 'dossiers', 'votings', 'role'));
    }

    /**
     * Cập nhật Nghị quyết
     */
    public function update(Request $request, int|string $id): RedirectResponse
    {
        $role = Auth::user()?->role ?? (session('user_role') ?? 'CHAIR');
        if (! in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN'])) {
            return redirect()->route('resolutions.show', $id)->with('error', 'Từ chối quyền chỉnh sửa.');
        }

        $resolution = NghiQuyet::findOrFail($id);

        $validated = $request->validate([
            'SoNghiQuyet' => ['required', 'string', 'max:191', 'unique:nghi_quyet,SoNghiQuyet,' . $resolution->MaNghiQuyet . ',MaNghiQuyet'],
            'TrichYeu' => ['required', 'string', 'min:5', 'max:255'],
            'NoiDung' => ['required', 'string'],
            'MaPhienHop' => ['required', 'integer', 'exists:phien_hop,MaPhienHop'],
            'NgayBanHanh' => ['required', 'date'],
            'TrangThai' => ['required', 'string'],
        ]);

        $resolution->update($validated);

        return redirect()->route('resolutions.show', $resolution->MaNghiQuyet)
            ->with('success', 'Cập nhật nội dung Nghị quyết thành công!');
    }

    /**
     * Ký ban hành Nghị quyết (Đặc quyền của CHỦ TỊCH HỘI ĐỒNG TRƯỜNG)
     */
    public function sign(int|string $id): RedirectResponse
    {
        $role = Auth::user()?->role ?? (session('user_role') ?? 'CHAIR');
        if ($role !== 'CHAIR' && $role !== 'ADMIN') {
            return back()->with('error', 'Chỉ Chủ tịch Hội đồng trường mới có thẩm quyền ký ban hành Nghị quyết.');
        }

        $resolution = NghiQuyet::findOrFail($id);
        $resolution->update([
            'TrangThai' => 'Đã ký ban hành',
            'NgayBanHanh' => now()->toDateString(),
        ]);

        return back()->with('success', 'Chủ tịch Hội đồng trường đã ký ban hành Nghị quyết thành công!');
    }

    /**
     * Công bố hiệu lực thi hành Nghị quyết toàn trường (Thư ký / Chủ tịch)
     */
    public function publish(int|string $id): RedirectResponse
    {
        $role = Auth::user()?->role ?? (session('user_role') ?? 'CHAIR');
        if (! in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN'])) {
            return back()->with('error', 'Bạn không có thẩm quyền công bố Nghị quyết.');
        }

        $resolution = NghiQuyet::findOrFail($id);
        $resolution->update([
            'TrangThai' => 'Đã công bố hiệu lực',
        ]);

        return back()->with('success', 'Nghị quyết đã được công bố hiệu lực thi hành tới các đơn vị trực thuộc STU!');
    }

    /**
     * Ẩn Nghị quyết (Soft Delete - Không xóa cứng khỏi CSDL)
     */
    public function destroy(int|string $id): RedirectResponse
    {
        $role = Auth::user()?->role ?? (session('user_role') ?? 'CHAIR');
        if (! in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN'])) {
            return back()->with('error', 'Bạn không có thẩm quyền ẩn Nghị quyết.');
        }

        $resolution = NghiQuyet::findOrFail($id);
        $soNghiQuyet = $resolution->SoNghiQuyet;
        $resolution->update(['TrangThai' => 'Đã ẩn']);

        return redirect()->route('resolutions.index')
            ->with('success', "Đã ẩn Nghị quyết {$soNghiQuyet} khỏi giao diện (Soft Delete).");
    }
}
