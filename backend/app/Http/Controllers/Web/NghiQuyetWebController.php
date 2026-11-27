<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\HoSoTrinh;
use App\Models\NghiQuyet;
use App\Models\NoiDungBieuQuyet;
use App\Models\PhienHop;
use App\Models\TaiLieu;
use App\Support\SearchCodeHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class NghiQuyetWebController extends Controller
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

        // 1. Tìm kiếm theo từ khóa & mã nghị quyết (#NQ-..., #1, 1, số hiệu, trích yếu, nội dung)
        if ($search = trim((string) $request->input('search'))) {
            $resolutionId = SearchCodeHelper::extractNumericId($search, ['nq']);

            $query->where(function ($q) use ($search, $resolutionId) {
                $q->where('SoNghiQuyet', 'like', "%{$search}%")
                    ->orWhere('TrichYeu', 'like', "%{$search}%")
                    ->orWhere('NoiDung', 'like', "%{$search}%");

                if ($resolutionId !== null) {
                    $q->orWhere('MaNghiQuyet', $resolutionId);
                }

                // Hỗ trợ tìm kiếm thông minh theo từng từ khóa nếu chuỗi có nhiều từ
                $words = array_filter(explode(' ', $search));
                if (count($words) > 1) {
                    $q->orWhere(function ($subQ) use ($words) {
                        foreach ($words as $w) {
                            $subQ->where(function ($wQ) use ($w) {
                                $wQ->where('TrichYeu', 'like', "%{$w}%")
                                    ->orWhere('NoiDung', 'like', "%{$w}%")
                                    ->orWhere('SoNghiQuyet', 'like', "%{$w}%");
                            });
                        }
                    });
                }
            });
        }

        // 2. Tìm kiếm nâng cao: Lọc theo khoảng thời gian ban hành (Từ ngày - Đến ngày)
        if ($fromDate = $request->input('from_date')) {
            $query->whereDate('NgayBanHanh', '>=', $fromDate);
        }
        if ($toDate = $request->input('to_date')) {
            $query->whereDate('NgayBanHanh', '<=', $toDate);
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

        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [5, 10, 20, 50, 100])) {
            $perPage = 10;
        }

        $resolutions = $query->orderBy('NgayBanHanh', 'desc')
            ->orderBy('MaNghiQuyet', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        $linkable = $this->loadLinkableEntities();
        $meetings = $linkable['meetings'];
        $dossiers = $linkable['dossiers'];
        $votings = $linkable['votings'];

        $years = NghiQuyet::selectRaw('YEAR(NgayBanHanh) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->filter();

        $role = Auth::user()?->role ?? (session('user_role') ?? 'CHAIR');

        return view('pages.nghiquyet.danhsach', compact('resolutions', 'meetings', 'years', 'dossiers', 'votings', 'role'));
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

        return view('pages.nghiquyet.taomoi', compact('meetings', 'selectedMeetingId', 'dossiers', 'votings', 'role'));
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
            'SoNghiQuyet' => [
                'required',
                'string',
                'max:191',
                Rule::unique('nghi_quyet', 'SoNghiQuyet')->where(fn ($q) => $q->whereNull('deleted_at')->where('TrangThai', '!=', 'Đã ẩn')),
            ],
            'TrichYeu' => ['required', 'string', 'min:5', 'max:255'],
            'NoiDung' => ['required', 'string'],
            'MaPhienHop' => ['required', 'integer', 'exists:phien_hop,MaPhienHop'],
            'NgayBanHanh' => ['required', 'date'],
            'dossier_ids' => ['nullable', 'array'],
            'dossier_ids.*' => ['integer', 'exists:ho_so_trinh,MaHoSo'],
            'voting_ids' => ['nullable', 'array'],
            'voting_ids.*' => ['integer', 'exists:noi_dung_bieu_quyet,MaNoiDung'],
            'file' => ['nullable', 'file', 'max:20480', 'mimes:pdf,doc,docx'],
            'file_dinh_kem' => ['nullable', 'file', 'max:20480', 'mimes:pdf,doc,docx'],
            'files' => ['nullable', 'array'],
            'files.*' => ['file', 'max:20480', 'mimes:pdf,doc,docx'],
        ], [
            'SoNghiQuyet.required' => 'Vui lòng nhập số hiệu nghị quyết (ví dụ: 15/NQ-HĐT).',
            'SoNghiQuyet.unique' => 'Số hiệu nghị quyết này đã tồn tại trong hệ thống.',
            'TrichYeu.required' => 'Vui lòng nhập trích yếu nghị quyết.',
            'NoiDung.required' => 'Vui lòng nhập toàn văn nội dung nghị quyết.',
            'MaPhienHop.required' => 'Vui lòng chọn phiên họp liên kết.',
            'NgayBanHanh.required' => 'Vui lòng chọn ngày ban hành.',
        ]);

        $resolution = DB::transaction(function () use ($validated, $request) {
            $this->releaseArchivedResolutionNumber($validated['SoNghiQuyet']);

            $record = NghiQuyet::create([
                'SoNghiQuyet' => $validated['SoNghiQuyet'],
                'TrichYeu' => $validated['TrichYeu'],
                'NoiDung' => $validated['NoiDung'],
                'NgayBanHanh' => $validated['NgayBanHanh'],
                'TrangThai' => 'Dự thảo',
                'MaPhienHop' => $validated['MaPhienHop'],
            ]);

            $this->syncResolutionLinks(
                $record,
                $validated['dossier_ids'] ?? [],
                $validated['voting_ids'] ?? []
            );

            $this->storeAndAttachDocuments($record, $request);

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
            'noiDungBieuQuyet.chuongTrinhHop.phienHop',
            'taiLieu' => fn ($q) => $q->orderByDesc('tai_lieu.MaTaiLieu'),
            'nhiemVu.phanCongNhiemVu.donVi',
            'nhiemVu.phanCongNhiemVu.baoCao',
        ])->findOrFail($id);

        $role = Auth::user()?->role ?? (session('user_role') ?? 'CHAIR');

        // Tính tiến độ tổng thể thực hiện nghị quyết dựa trên các nhiệm vụ
        $tasks = $resolution->nhiemVu;
        $totalTasks = $tasks->count();
        $completedTasks = $tasks->filter(fn ($t) => in_array($t->TrangThai, ['COMPLETED', 'Hoàn thành']))->count();
        $overallProgress = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0;

        $linkable = $this->loadLinkableEntities();
        $meetings = $linkable['meetings'];
        $dossiers = $linkable['dossiers'];
        $votings = $linkable['votings'];

        return view('pages.nghiquyet.chitiet', compact(
            'resolution',
            'role',
            'totalTasks',
            'completedTasks',
            'overallProgress',
            'meetings',
            'dossiers',
            'votings'
        ));
    }

    /**
     * Biểu mẫu chỉnh sửa Nghị quyết (chỉ khi chưa công bố hoặc là Chủ tịch/Admin)
     */
    public function edit(int|string $id): View|RedirectResponse
    {
        return redirect()->route('resolutions.show', ['id' => $id, 'edit' => 1]);
    }

    /**
     * Cập nhật Nghị quyết (hỗ trợ đồng bộ liên kết hồ sơ/biểu quyết & đính kèm văn bản)
     */
    public function update(Request $request, int|string $id): RedirectResponse
    {
        $role = Auth::user()?->role ?? (session('user_role') ?? 'CHAIR');
        if (! in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN'])) {
            return redirect()->route('resolutions.show', $id)->with('error', 'Từ chối quyền chỉnh sửa.');
        }

        $resolution = NghiQuyet::findOrFail($id);

        $validated = $request->validate([
            'SoNghiQuyet' => [
                'required',
                'string',
                'max:191',
                Rule::unique('nghi_quyet', 'SoNghiQuyet')
                    ->ignore($resolution->MaNghiQuyet, 'MaNghiQuyet')
                    ->where(fn ($q) => $q->whereNull('deleted_at')->where('TrangThai', '!=', 'Đã ẩn')),
            ],
            'TrichYeu' => ['required', 'string', 'min:5', 'max:255'],
            'NoiDung' => ['required', 'string'],
            'MaPhienHop' => ['required', 'integer', 'exists:phien_hop,MaPhienHop'],
            'NgayBanHanh' => ['required', 'date'],
            'TrangThai' => ['required', 'string'],
            'dossier_ids' => ['nullable', 'array'],
            'dossier_ids.*' => ['integer', 'exists:ho_so_trinh,MaHoSo'],
            'voting_ids' => ['nullable', 'array'],
            'voting_ids.*' => ['integer', 'exists:noi_dung_bieu_quyet,MaNoiDung'],
            'file' => ['nullable', 'file', 'max:20480', 'mimes:pdf,doc,docx'],
            'files' => ['nullable', 'array'],
            'files.*' => ['file', 'max:20480', 'mimes:pdf,doc,docx'],
        ]);

        DB::transaction(function () use ($resolution, $validated, $request) {
            $this->releaseArchivedResolutionNumber($validated['SoNghiQuyet'], (int) $resolution->MaNghiQuyet);

            $resolution->update([
                'SoNghiQuyet' => $validated['SoNghiQuyet'],
                'TrichYeu' => $validated['TrichYeu'],
                'NoiDung' => $validated['NoiDung'],
                'MaPhienHop' => $validated['MaPhienHop'],
                'NgayBanHanh' => $validated['NgayBanHanh'],
                'TrangThai' => $validated['TrangThai'],
            ]);

            if ($request->boolean('sync_links')) {
                $this->syncResolutionLinks(
                    $resolution,
                    $validated['dossier_ids'] ?? [],
                    $validated['voting_ids'] ?? []
                );
            }

            $this->storeAndAttachDocuments($resolution, $request);
        });

        return redirect()->route('resolutions.show', $resolution->MaNghiQuyet)
            ->with('success', 'Cập nhật nội dung Nghị quyết thành công!');
    }

    /**
     * Đính kèm thêm văn bản / bản scan có chữ ký & đóng dấu vào Nghị quyết (hỗ trợ mọi trạng thái)
     */
    public function attachDocument(Request $request, int|string $id): RedirectResponse
    {
        $role = Auth::user()?->role ?? (session('user_role') ?? 'CHAIR');
        if (! in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN'])) {
            return back()->with('error', 'Bạn không có thẩm quyền đính kèm văn bản vào Nghị quyết.');
        }

        $resolution = NghiQuyet::findOrFail($id);

        $request->validate([
            'file' => ['required_without:files', 'nullable', 'file', 'max:20480', 'mimes:pdf,doc,docx'],
            'files' => ['nullable', 'array'],
            'files.*' => ['file', 'max:20480', 'mimes:pdf,doc,docx'],
            'LoaiTaiLieu' => ['nullable', 'string', 'max:100'],
        ], [
            'file.required_without' => 'Vui lòng chọn ít nhất một tệp văn bản (.pdf, .doc, .docx) để đính kèm.',
        ]);

        $docType = trim((string) $request->input('LoaiTaiLieu')) ?: 'Văn bản Nghị quyết chính thức';
        $attachedCount = DB::transaction(fn () => $this->storeAndAttachDocuments($resolution, $request, $docType));

        if ($attachedCount === 0) {
            return back()->with('error', 'Không có tệp hợp lệ nào được tải lên.');
        }

        return back()->with('success', "Đã đính kèm thành công {$attachedCount} văn bản vào Nghị quyết {$resolution->SoNghiQuyet}!");
    }

    /**
     * Gỡ văn bản đính kèm khỏi Nghị quyết
     */
    public function detachDocument(int|string $id, int|string $docId): RedirectResponse
    {
        $role = Auth::user()?->role ?? (session('user_role') ?? 'CHAIR');
        if (! in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN'])) {
            return back()->with('error', 'Bạn không có thẩm quyền gỡ văn bản đính kèm khỏi Nghị quyết.');
        }

        $resolution = NghiQuyet::findOrFail($id);

        DB::transaction(function () use ($resolution, $docId) {
            $resolution->taiLieu()->detach($docId);

            $stillReferenced = DB::table('tai_lieu_nghi_quyet')->where('MaTaiLieu', $docId)->exists()
                || DB::table('tai_lieu_phien_hop')->where('MaTaiLieu', $docId)->exists()
                || DB::table('tai_lieu_ho_so')->where('MaTaiLieu', $docId)->exists()
                || DB::table('tai_lieu_bao_cao')->where('MaTaiLieu', $docId)->exists();

            if (! $stillReferenced) {
                TaiLieu::where('MaTaiLieu', $docId)->delete();
            }
        });

        return back()->with('success', 'Đã gỡ văn bản đính kèm khỏi Nghị quyết.');
    }

    /**
     * Cập nhật liên kết Nghị quyết với Hồ sơ trình và Nội dung biểu quyết
     */
    public function updateLinks(Request $request, int|string $id): RedirectResponse
    {
        $role = Auth::user()?->role ?? (session('user_role') ?? 'CHAIR');
        if (! in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN'])) {
            return back()->with('error', 'Bạn không có thẩm quyền cập nhật liên kết căn cứ Nghị quyết.');
        }

        $resolution = NghiQuyet::findOrFail($id);

        $validated = $request->validate([
            'dossier_ids' => ['nullable', 'array'],
            'dossier_ids.*' => ['integer', 'exists:ho_so_trinh,MaHoSo'],
            'voting_ids' => ['nullable', 'array'],
            'voting_ids.*' => ['integer', 'exists:noi_dung_bieu_quyet,MaNoiDung'],
        ]);

        DB::transaction(function () use ($resolution, $validated) {
            $this->syncResolutionLinks(
                $resolution,
                $validated['dossier_ids'] ?? [],
                $validated['voting_ids'] ?? []
            );
        });

        return back()->with('success', 'Đã cập nhật liên kết Hồ sơ trình & Biểu quyết cho Nghị quyết!');
    }

    /**
     * Nạp danh sách Phiên họp, Hồ sơ trình và Nội dung biểu quyết dùng để liên kết
     *
     * @return array{meetings: \Illuminate\Support\Collection, dossiers: \Illuminate\Support\Collection, votings: \Illuminate\Support\Collection}
     */
    private function loadLinkableEntities(): array
    {
        return [
            'meetings' => PhienHop::orderBy('ThoiGianBatDau', 'desc')->get(),
            'dossiers' => HoSoTrinh::with(['donVi', 'chuongTrinhHop'])->orderBy('MaHoSo', 'desc')->get(),
            'votings' => NoiDungBieuQuyet::with(['chuongTrinhHop.hoSoTrinh', 'chuongTrinhHop.phienHop'])->orderBy('MaNoiDung', 'desc')->get(),
        ];
    }

    /**
     * Giải phóng số hiệu nghị quyết nếu đang bị giữ bởi bản ghi đã ẩn / xóa mềm
     */
    private function releaseArchivedResolutionNumber(string $soNghiQuyet, ?int $excludeId = null): void
    {
        $archivedQuery = NghiQuyet::withoutGlobalScopes()
            ->withTrashed()
            ->where('SoNghiQuyet', $soNghiQuyet)
            ->where(function ($q) {
                $q->whereNotNull('deleted_at')->orWhere('TrangThai', 'Đã ẩn');
            });

        if ($excludeId !== null) {
            $archivedQuery->where('MaNghiQuyet', '!=', $excludeId);
        }

        foreach ($archivedQuery->get() as $archived) {
            $suffix = ' (Đã ẩn #' . $archived->MaNghiQuyet . ')';
            $newCode = mb_substr($soNghiQuyet, 0, 191 - mb_strlen($suffix), 'UTF-8') . $suffix;
            DB::table('nghi_quyet')
                ->where('MaNghiQuyet', $archived->MaNghiQuyet)
                ->update(['SoNghiQuyet' => $newCode]);
        }
    }

    /**
     * Đồng bộ liên kết N-N giữa Nghị quyết với Hồ sơ trình và Nội dung biểu quyết
     *
     * @param  array<int, int|string>  $dossierIds
     * @param  array<int, int|string>  $votingIds
     */
    private function syncResolutionLinks(NghiQuyet $resolution, array $dossierIds, array $votingIds): void
    {
        $resolution->hoSoTrinh()->sync(array_values(array_unique(array_map('intval', $dossierIds))));
        $resolution->noiDungBieuQuyet()->sync(array_values(array_unique(array_map('intval', $votingIds))));
    }

    /**
     * Lưu và đính kèm một hoặc nhiều tệp văn bản vào Nghị quyết
     */
    private function storeAndAttachDocuments(NghiQuyet $resolution, Request $request, string $docType = 'Văn bản Nghị quyết chính thức'): int
    {
        $uploadedFiles = [];
        foreach (['file', 'file_dinh_kem'] as $singleKey) {
            if ($request->hasFile($singleKey) && $request->file($singleKey) instanceof UploadedFile) {
                $uploadedFiles[] = $request->file($singleKey);
            }
        }
        if ($request->hasFile('files')) {
            foreach ((array) $request->file('files') as $item) {
                if ($item instanceof UploadedFile) {
                    $uploadedFiles[] = $item;
                }
            }
        }

        $count = 0;
        foreach ($uploadedFiles as $idx => $file) {
            $safeName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $file->getClientOriginalName());
            $fileName = 'NQ_' . $resolution->MaNghiQuyet . '_' . time() . '_' . ($idx + 1) . '_' . $safeName;
            $path = $file->storeAs('documents/resolutions', $fileName, 'public');

            $taiLieu = TaiLieu::create([
                'TenTaiLieu' => $file->getClientOriginalName(),
                'LoaiTaiLieu' => $docType,
                'DuongDan' => '/storage/' . $path,
                'DinhDang' => $file->getClientOriginalExtension(),
                'DungLuong' => $file->getSize(),
                'NgayTaiLen' => now(),
                'MaNguoiDung' => Auth::id() ?? 1,
            ]);

            DB::table('tai_lieu_nghi_quyet')->insertOrIgnore([
                'MaTaiLieu' => $taiLieu->MaTaiLieu,
                'MaNghiQuyet' => $resolution->MaNghiQuyet,
            ]);
            $count++;
        }

        return $count;
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
        $resolution->delete();

        return redirect()->route('resolutions.index')
            ->with('success', "Đã ẩn Nghị quyết {$soNghiQuyet} khỏi giao diện (Soft Delete).");
    }
}
