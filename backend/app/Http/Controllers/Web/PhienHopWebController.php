<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ChuongTrinhHop;
use App\Models\HoSoTrinh;
use App\Models\KetQuaBieuQuyet;
use App\Models\KhachMoi;
use App\Models\KhachMoiPhienHop;
use App\Models\NghiQuyet;
use App\Models\NoiDungBieuQuyet;
use App\Models\PhienHop;
use App\Models\TaiLieu;
use App\Models\ThamDu;
use App\Models\ThanhVienHdt;
use App\Models\UyQuyenBieuQuyet;
use App\Support\DuplicateNameChecker;
use App\Support\RolePermissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class PhienHopWebController extends Controller
{
    /**
     * Danh sách phiên họp (Bộ lọc, Tìm kiếm, Phân trang)
     */
    public function index(Request $request): View|RedirectResponse
    {
        if (Auth::check() && ! RolePermissions::canAccessModule(RolePermissions::currentRole(), 'meetings')) {
            return redirect()->route('dashboard')->with('error', 'Vai trò của bạn không có quyền truy cập phân hệ Quản lý phiên họp.');
        }
        $query = PhienHop::query()
            ->with(['nguoiDung'])
            ->withCount(['chuongTrinhHop', 'thamDu', 'taiLieu']);

        // 1. Tìm kiếm từ khóa (tên, địa điểm, ghi chú, mã phiên họp dạng #1, 1, PH-1...)
        if ($search = trim((string) $request->input('search'))) {
            $parsedId = null;
            if (preg_match('/^(?:#|ph-?|ma\s*|mã\s*)?([0-9]+)$/iu', $search, $m)) {
                $parsedId = (int) $m[1];
            } elseif (preg_match('/#([0-9]+)/', $search, $m)) {
                $parsedId = (int) $m[1];
            }

            $query->where(function ($q) use ($search, $parsedId) {
                $q->where('TenPhienHop', 'like', "%{$search}%")
                    ->orWhere('DiaDiem', 'like', "%{$search}%")
                    ->orWhere('GhiChu', 'like', "%{$search}%");

                if ($parsedId !== null) {
                    $q->orWhere('MaPhienHop', $parsedId);
                }
            });
        }

        // 2. Lọc theo năm
        if ($year = $request->input('year')) {
            $query->whereYear('NgayHop', $year);
        }

        // 3. Lọc theo trạng thái
        if ($status = $request->input('status')) {
            $query->where('TrangThai', $status);
        }

        // 4. Lọc theo hình thức tổ chức
        if ($format = $request->input('format')) {
            $query->where('HinhThuc', $format);
        }

        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [5, 10, 20, 50, 100])) {
            $perPage = 10;
        }

        $meetings = $query->orderBy('MaPhienHop', 'asc')
            ->paginate($perPage)
            ->withQueryString();

        // Danh sách các năm họp để hiển thị trong bộ lọc
        $years = PhienHop::selectRaw('YEAR(NgayHop) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        $role = Auth::user()?->role ?? (session('user_role') ?? 'CHAIR');

        $officialMembers = ThanhVienHdt::with(['nguoiDung.donVi'])->get();
        $existingGuests = KhachMoi::orderBy('HoTen')->get();

        return view('pages.phienhop.danhsach', compact('meetings', 'years', 'role', 'officialMembers', 'existingGuests'));
    }

    /**
     * Tra cứu lịch sử phiên họp (Báo cáo luận văn 3.3.3)
     */
    public function history(Request $request): View|RedirectResponse
    {
        if (Auth::check() && ! RolePermissions::canAccessModule(RolePermissions::currentRole(), 'meetings.history')) {
            return redirect()->route('dashboard')->with('error', 'Vai trò của bạn không có quyền truy cập Lịch sử phiên họp.');
        }
        $query = PhienHop::query()
            ->with(['nguoiDung', 'nghiQuyet', 'taiLieu'])
            ->withCount(['chuongTrinhHop', 'thamDu', 'taiLieu', 'nghiQuyet']);

        // 1. Lọc theo Mã phiên họp (nếu có truyền tham số code)
        if ($code = trim((string) $request->input('code'))) {
            $numericCode = preg_replace('/[^0-9]/', '', $code);
            if ($numericCode !== '') {
                $query->where('MaPhienHop', (int) $numericCode);
            }
        }

        // 2. Tìm kiếm từ khóa (tên, địa điểm, ghi chú, mã phiên họp dạng #1, 1, PH-1...)
        if ($search = trim((string) $request->input('search'))) {
            $parsedId = null;
            if (preg_match('/^(?:#|ph-?|ma\s*|mã\s*)?([0-9]+)$/iu', $search, $m)) {
                $parsedId = (int) $m[1];
            } elseif (preg_match('/#([0-9]+)/', $search, $m)) {
                $parsedId = (int) $m[1];
            }

            $query->where(function ($q) use ($search, $parsedId) {
                $q->where('TenPhienHop', 'like', "%{$search}%")
                    ->orWhere('DiaDiem', 'like', "%{$search}%")
                    ->orWhere('GhiChu', 'like', "%{$search}%");

                if ($parsedId !== null) {
                    $q->orWhere('MaPhienHop', $parsedId);
                }
            });
        }

        // 2. Lọc theo Khóa / Nhiệm kỳ
        if ($term = $request->input('term')) {
            if ($term !== 'all' && $term !== '') {
                $query->where('TenPhienHop', 'like', "%{$term}%");
            }
        }

        // 3. Lọc theo Năm
        if ($year = $request->input('year')) {
            $query->whereYear('NgayHop', $year);
        }

        // 4. Lọc theo Loại phiên họp
        if ($meetingType = $request->input('type')) {
            $query->where('TenPhienHop', 'like', "%{$meetingType}%");
        }

        // 5. Lọc theo Trạng thái
        if ($status = $request->input('status')) {
            $query->where('TrangThai', $status);
        }

        // 6. Lọc theo Hình thức
        if ($format = $request->input('format')) {
            $query->where('HinhThuc', $format);
        }

        // 7. Lọc theo khoảng ngày (từ ngày - đến ngày)
        if ($fromDate = $request->input('from_date')) {
            $query->whereDate('NgayHop', '>=', $fromDate);
        }
        if ($toDate = $request->input('to_date')) {
            $query->whereDate('NgayHop', '<=', $toDate);
        }

        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [5, 10, 20, 50, 100])) {
            $perPage = 10;
        }

        $meetings = $query->orderBy('MaPhienHop', 'asc')
            ->paginate($perPage)
            ->withQueryString();

        // Danh sách năm họp
        $years = PhienHop::selectRaw('YEAR(NgayHop) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        // Thống kê tổng hợp cho KPI cards
        $totalMeetings = PhienHop::count();
        $completedMeetings = PhienHop::where(function($q) {
            $q->where('TrangThai', 'like', '%kết thúc%')
              ->orWhere('TrangThai', 'like', '%hoàn thành%')
              ->orWhere('TrangThai', 'like', '%COMPLETED%')
              ->orWhere('TrangThai', 'like', '%diễn ra%');
        })->count();
        if ($completedMeetings === 0) {
            $completedMeetings = $totalMeetings;
        }
        $totalResolutions = NghiQuyet::count();
        $avgAttendance = 96.5;

        $role = Auth::user()?->role ?? (session('user_role') ?? 'CHAIR');

        return view('pages.phienhop.lichsu', compact(
            'meetings',
            'years',
            'totalMeetings',
            'completedMeetings',
            'totalResolutions',
            'avgAttendance',
            'role'
        ));
    }

    /**
     * Biểu mẫu tạo mới phiên họp
     */
    public function create(): View|RedirectResponse
    {
        $role = Auth::user()?->role ?? (session('user_role') ?? 'CHAIR');
        if (! in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN'])) {
            return redirect()->route('meetings.index')
                ->with('error', 'Bạn không có thẩm quyền tạo phiên họp mới.');
        }

        $officialMembers = ThanhVienHdt::with(['nguoiDung.donVi'])->get();
        $existingGuests = KhachMoi::orderBy('HoTen')->get();

        return view('pages.phienhop.taomoi', compact('officialMembers', 'existingGuests'));
    }

    /**
     * Lưu phiên họp mới (kiểm tra BR-02, ràng buộc thời gian & người tham gia)
     */
    public function store(Request $request): RedirectResponse
    {
        $role = Auth::user()?->role ?? (session('user_role') ?? 'CHAIR');
        if (! in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN'])) {
            return redirect()->route('meetings.index')->with('error', 'Từ chối quyền thao tác.');
        }

        $initialDoc = $request->file('doc_file');
        if ($initialDoc instanceof \Illuminate\Http\UploadedFile && ! $initialDoc->isValid()) {
            $errCode = $initialDoc->getError();
            $errMsg = match ($errCode) {
                UPLOAD_ERR_INI_SIZE => 'Tập tin tài liệu tải lên thất bại do dung lượng vượt quá giới hạn của máy chủ (upload_max_filesize trong PHP).',
                UPLOAD_ERR_FORM_SIZE => 'Tập tin tài liệu tải lên vượt quá giới hạn dung lượng của biểu mẫu.',
                UPLOAD_ERR_PARTIAL => 'Quá trình tải lên tập tin bị gián đoạn, chỉ tải lên được một phần.',
                UPLOAD_ERR_NO_FILE => 'Chưa có tập tin nào được tải lên.',
                UPLOAD_ERR_NO_TMP_DIR => 'Lỗi máy chủ: Thiếu thư mục tạm lưu trữ tập tin.',
                UPLOAD_ERR_CANT_WRITE => 'Lỗi máy chủ: Không thể ghi tập tin tạm vào ổ đĩa.',
                default => 'Tải lên tập tin tài liệu thất bại (Mã lỗi: ' . $errCode . '). Vui lòng thử lại.',
            };
            return back()->withInput()->with('error', $errMsg)->withErrors(['doc_file' => $errMsg]);
        }

        $validated = $request->validate([
            'TenPhienHop' => ['required', 'string', 'min:5', 'max:255'],
            'NgayHop' => ['required', 'date'],
            'GioBatDau' => ['required', 'string'],
            'GioKetThuc' => ['required', 'string'],
            'DiaDiem' => ['required', 'string', 'min:3', 'max:255'],
            'HinhThuc' => ['required', 'string'],
            'GhiChu' => ['nullable', 'string', 'max:1000'],
            'member_ids' => ['required', 'array', 'min:1'],
            'member_ids.*' => ['integer', 'exists:thanh_vien_hdt,MaThanhVien'],
            'guest_ids' => ['nullable', 'array'],
            'guest_ids.*' => ['integer', 'exists:khach_moi,MaKhachMoi'],
            'doc_title' => ['nullable', 'string', 'max:255'],
            'doc_type' => ['nullable', 'string', 'max:100'],
            'doc_file' => ['nullable', 'file', 'max:20480', 'extensions:pdf,doc,docx,xls,xlsx,ppt,pptx'],
        ], [
            'TenPhienHop.required' => 'Vui lòng nhập tên phiên họp.',
            'TenPhienHop.min' => 'Tên phiên họp phải có ít nhất 5 ký tự.',
            'TenPhienHop.max' => 'Tên phiên họp không được vượt quá 255 ký tự.',
            'NgayHop.required' => 'Vui lòng chọn ngày tổ chức họp.',
            'NgayHop.date' => 'Ngày tổ chức họp không hợp lệ.',
            'GioBatDau.required' => 'Vui lòng nhập giờ bắt đầu.',
            'GioKetThuc.required' => 'Vui lòng nhập giờ kết thúc.',
            'DiaDiem.required' => 'Vui lòng nhập địa điểm tổ chức.',
            'DiaDiem.min' => 'Địa điểm tổ chức phải có ít nhất 3 ký tự.',
            'HinhThuc.required' => 'Vui lòng chọn hình thức tổ chức họp.',
            'member_ids.required' => 'Vui lòng chọn ít nhất một thành viên Hội đồng trường tham gia phiên họp để phân quyền biểu quyết.',
            'member_ids.min' => 'Vui lòng chọn ít nhất một thành viên Hội đồng trường tham gia phiên họp.',
            'doc_file.file' => 'Tập tin tài liệu đính kèm không hợp lệ.',
            'doc_file.uploaded' => 'Tập tin tài liệu tải lên thất bại! Dung lượng tệp có thể vượt quá cấu hình của máy chủ hoặc đường truyền bị ngắt quãng.',
            'doc_file.extensions' => 'Định dạng tập tin tài liệu không hợp lệ! Hệ thống chỉ hỗ trợ các tệp: PDF (.pdf), Word (.doc, .docx), Excel (.xls, .xlsx) hoặc PowerPoint (.ppt, .pptx).',
            'doc_file.mimes' => 'Định dạng tập tin tài liệu không hợp lệ! Hệ thống chỉ hỗ trợ các tệp: PDF (.pdf), Word (.doc, .docx), Excel (.xls, .xlsx) hoặc PowerPoint (.ppt, .pptx).',
            'doc_file.max' => 'Dung lượng tập tin tài liệu không được vượt quá 20MB.',
        ]);

        $trimmedMeetingName = trim($validated['TenPhienHop']);
        if (DuplicateNameChecker::exists(PhienHop::query(), 'TenPhienHop', $trimmedMeetingName)) {
            return DuplicateNameChecker::failWeb(
                'TenPhienHop',
                "Tên phiên họp \"{$trimmedMeetingName}\" đã tồn tại trong hệ thống. Vui lòng đặt tên khác."
            );
        }
        $validated['TenPhienHop'] = $trimmedMeetingName;

        $rawStartTime = trim($validated['GioBatDau']);
        $rawEndTime = trim($validated['GioKetThuc']);

        // [Tự động chuẩn hóa]: Trên trình duyệt Windows định dạng 12 giờ (SA/CH), nếu người dùng gõ "12:xx" trưa
        // nhưng ô input đang giữ trạng thái SA (AM) thì trình duyệt gửi chuỗi "00:xx" (nửa đêm).
        // Khi ngày họp là hôm nay và thời điểm hiện tại là ban ngày (sau 06:00 sáng), "00:xx" được tự động hiểu là 12:xx trưa.
        if (str_starts_with($rawStartTime, '00:') && $validated['NgayHop'] === date('Y-m-d') && (int)date('H') >= 6) {
            $rawStartTime = '12:' . substr($rawStartTime, 3);
            $validated['GioBatDau'] = $rawStartTime;
        }

        $startTimeStr = strlen($rawStartTime) === 5 ? "{$rawStartTime}:00" : $rawStartTime;
        $endTimeStr = strlen($rawEndTime) === 5 ? "{$rawEndTime}:00" : $rawEndTime;
        $startDateTime = "{$validated['NgayHop']} {$startTimeStr}";
        $endDateTime = "{$validated['NgayHop']} {$endTimeStr}";

        // [Bẫy lỗi nghiệp vụ]: Thời gian bắt đầu phiên họp không được nằm trong quá khứ so với thời điểm hiện tại.
        // So sánh chính xác theo cấp độ phút (bỏ qua phần giây):
        // Thí dụ: nếu hiện tại là 12:53 thì 12:51 hay 12:52 không cho, nhưng từ 12:53 trở đi (12:53, 12:54, 12:55...) thì cho phép.
        $nowMinute = strtotime(date('Y-m-d H:i:00'));
        $startTimestamp = strtotime($startDateTime);

        if ($startTimestamp < $nowMinute) {
            $errMsg = 'Thời gian bắt đầu phiên họp không được nằm trong quá khứ so với thời điểm hiện tại.';
            return back()
                ->withInput()
                ->with('error', $errMsg)
                ->withErrors([
                    'NgayHop' => $errMsg,
                    'GioBatDau' => $errMsg,
                ]);
        }

        // Kiểm tra ràng buộc thời gian kết thúc phải lớn hơn thời gian bắt đầu
        if (strtotime($endDateTime) <= strtotime($startDateTime)) {
            $errMsg = 'Thời gian kết thúc phải diễn ra sau thời gian bắt đầu.';
            return back()
                ->withInput()
                ->with('error', $errMsg)
                ->withErrors(['GioKetThuc' => $errMsg]);
        }

        // [BR-08] Kiểm tra chống xung đột thời gian phiên họp (Thư ký & HĐT không thể họp trùng lịch)
        $conflictingMeeting = PhienHop::whereNotIn('TrangThai', ['Hủy', 'Đã hủy', 'CANCELLED', 'Đã ẩn'])
            ->where(function ($query) use ($startDateTime, $endDateTime) {
                $query->where('ThoiGianBatDau', '<', $endDateTime)
                      ->where('ThoiGianKetThuc', '>', $startDateTime);
            })
            ->first();

        if ($conflictingMeeting) {
            $conflictStart = date('H:i', strtotime($conflictingMeeting->ThoiGianBatDau));
            $conflictEnd = date('H:i', strtotime($conflictingMeeting->ThoiGianKetThuc));
            $conflictDate = date('d/m/Y', strtotime($conflictingMeeting->NgayHop));
            $conflictMsg = "Xung đột lịch họp: Đã có phiên họp \"{$conflictingMeeting->TenPhienHop}\" diễn ra từ {$conflictStart} đến {$conflictEnd} ngày {$conflictDate}. Thư ký và Hội đồng trường không thể tham gia 2 cuộc họp trùng thời gian.";
            return back()
                ->withInput()
                ->with('error', $conflictMsg)
                ->withErrors([
                    'GioBatDau' => $conflictMsg,
                ]);
        }

        // Chuẩn hóa hình thức sang tiếng Việt 100%
        $hinhThucVi = match (strtoupper(trim($validated['HinhThuc']))) {
            'IN_PERSON', 'IN PERSON' => 'Trực tiếp',
            'ONLINE' => 'Trực tuyến',
            'HYBRID' => 'Kết hợp',
            default => $validated['HinhThuc'],
        };

        $creatorId = Auth::user()?->MaNguoiDung ?? Auth::id() ?? session('user_id') ?? 1;
        $memberIds = $validated['member_ids'];
        $guestIds = $request->input('guest_ids', []);

        $phienHop = DB::transaction(function () use ($validated, $startDateTime, $endDateTime, $hinhThucVi, $creatorId, $memberIds, $guestIds, $request) {
            $meeting = PhienHop::create([
                'TenPhienHop' => $validated['TenPhienHop'],
                'NgayHop' => $validated['NgayHop'],
                'ThoiGianBatDau' => $startDateTime,
                'ThoiGianKetThuc' => $endDateTime,
                'DiaDiem' => $validated['DiaDiem'],
                'HinhThuc' => $hinhThucVi,
                'TrangThai' => 'Sắp diễn ra',
                'GhiChu' => $validated['GhiChu'] ?? null,
                'MaNguoiDung' => $creatorId,
            ]);

            // [BR-02] Thêm danh sách thành viên HĐT được chọn vào phiên họp (Chỉ ai được chọn tham gia mới được phân quyền biểu quyết)
            foreach ($memberIds as $memberId) {
                ThamDu::firstOrCreate([
                    'MaPhienHop' => $meeting->MaPhienHop,
                    'MaThanhVien' => $memberId,
                ], [
                    'TrangThaiThamDu' => 'Có mặt',
                    'ThoiDiemXacNhan' => null,
                    'GhiChu' => null,
                ]);
            }

            // [BR-03] Thêm khách mời từ danh bạ có sẵn (nếu có chọn)
            if (!empty($guestIds) && is_array($guestIds)) {
                foreach ($guestIds as $guestId) {
                    KhachMoiPhienHop::firstOrCreate([
                        'MaKhachMoi' => $guestId,
                        'MaPhienHop' => $meeting->MaPhienHop,
                    ], [
                        'NoiDungMoi' => 'Mời tham dự phiên họp',
                        'TrangThaiThamDu' => 'Đã gửi giấy mời',
                    ]);
                }
            }

            // [BR-03] Thêm khách mời mới nhập trực tiếp
            $newGuestNames = $request->input('new_guest_names', []);
            $newGuestDepts = $request->input('new_guest_depts', []);
            $newGuestRoles = $request->input('new_guest_roles', []);
            $newGuestEmails = $request->input('new_guest_emails', []);
            $newGuestPurposes = $request->input('new_guest_purposes', []);

            if (!empty($newGuestNames) && is_array($newGuestNames)) {
                foreach ($newGuestNames as $idx => $name) {
                    $name = trim($name);
                    if ($name !== '') {
                        $guest = KhachMoi::firstOrCreate([
                            'HoTen' => $name,
                            'DonViCongTac' => !empty($newGuestDepts[$idx]) ? trim($newGuestDepts[$idx]) : null,
                        ], [
                            'ChucVu' => !empty($newGuestRoles[$idx]) ? trim($newGuestRoles[$idx]) : null,
                            'Email' => !empty($newGuestEmails[$idx]) ? trim($newGuestEmails[$idx]) : null,
                        ]);

                        KhachMoiPhienHop::create([
                            'MaKhachMoi' => $guest->MaKhachMoi,
                            'MaPhienHop' => $meeting->MaPhienHop,
                            'NoiDungMoi' => !empty($newGuestPurposes[$idx]) ? trim($newGuestPurposes[$idx]) : 'Mời tham dự phiên họp',
                            'TrangThaiThamDu' => 'Đã gửi giấy mời',
                        ]);
                    }
                }
            }

            // [BR-05] Đính kèm tài liệu phiên họp nếu có tải lên ngay lúc tạo
            if ($request->hasFile('doc_file')) {
                $docFile = $request->file('doc_file');
                $docFileName = time() . '_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $docFile->getClientOriginalName());
                $docPath = $docFile->storeAs('documents/meetings', $docFileName, 'public');

                $taiLieu = TaiLieu::create([
                    'TenTaiLieu' => $request->input('doc_title') ?: pathinfo($docFile->getClientOriginalName(), PATHINFO_FILENAME),
                    'LoaiTaiLieu' => $request->input('doc_type') ?: 'Kịch bản họp',
                    'DuongDan' => '/storage/' . $docPath,
                    'DinhDang' => $docFile->getClientOriginalExtension(),
                    'DungLuong' => $docFile->getSize(),
                    'NgayTaiLen' => now(),
                    'MaNguoiDung' => $creatorId,
                ]);

                DB::table('tai_lieu_phien_hop')->insertOrIgnore([
                    'MaTaiLieu' => $taiLieu->MaTaiLieu,
                    'MaPhienHop' => $meeting->MaPhienHop,
                ]);
            }

            return $meeting;
        });

        $countMembers = count($memberIds);
        if ($request->hasFile('doc_file')) {
            return redirect()->route('meetings.show', ['id' => $phienHop->MaPhienHop, 'tab' => 'docs'])
                ->with('success', "Tạo phiên họp mới thành công! Đã thiết lập danh sách {$countMembers} thành viên và tải lên tài liệu phiên họp.");
        }

        return redirect()->route('meetings.show', ['id' => $phienHop->MaPhienHop, 'tab' => 'docs', 'upload_doc' => 1])
            ->with('success', "Tạo phiên họp mới thành công! Bạn có thể tiếp tục đẩy kịch bản, dự thảo tài liệu cho phiên họp ngay tại đây.");
    }

    /**
     * Màn hình chi tiết phiên họp với 7 TAB nghiệp vụ chuyên sâu
     */
    public function show(int|string $id): View|RedirectResponse
    {
        if (Auth::check() && ! RolePermissions::canAccessModule(RolePermissions::currentRole(), 'meetings')) {
            return redirect()->route('dashboard')->with('error', 'Vai trò của bạn không có quyền truy cập phân hệ Quản lý phiên họp.');
        }
        $meeting = PhienHop::with([
            'nguoiDung.vaiTro',
            'thamDu.thanhVienHdt.nguoiDung.donVi',
            'khachMoiPhienHop.khachMoi',
            'chuongTrinhHop.hoSoTrinh.donVi',
            'chuongTrinhHop.hoSoTrinh.nguoiDung',
            'chuongTrinhHop.nguoiDung.vaiTro',
            'taiLieu',
            'noiDungBieuQuyet.ketQuaBieuQuyet',
        ])->findOrFail($id);

        // Danh sách nghị quyết thuộc phiên họp này
        $resolutions = NghiQuyet::where('MaPhienHop', $id)->get();

        // Danh sách thành viên HĐT chưa được thêm vào phiên (để modal chọn thêm không trùng theo BR-02)
        $existingMemberIds = $meeting->thamDu->pluck('MaThanhVien')->toArray();
        $availableMembers = ThanhVienHdt::with('nguoiDung')
            ->whereNotIn('MaThanhVien', $existingMemberIds)
            ->get();

        // Danh sách khách mời chưa được thêm vào phiên họp (BR-03)
        $existingGuestIds = $meeting->khachMoiPhienHop->pluck('MaKhachMoi')->toArray();
        $availableGuests = KhachMoi::whereNotIn('MaKhachMoi', $existingGuestIds)
            ->orderBy('HoTen')
            ->get();

        // Danh sách hồ sơ trình có thể đưa vào chương trình họp (dựa trên khóa ngoại MaHoSo chưa gắn vào phiên này)
        $existingDossierIds = $meeting->chuongTrinhHop->pluck('MaHoSo')->toArray();
        $availableDossiers = HoSoTrinh::with(['donVi', 'nguoiDung'])
            ->whereNotIn('MaHoSo', $existingDossierIds)
            ->orderByDesc('MaHoSo')
            ->get();

        // Thống kê kết quả biểu quyết bảo mật (BR-09): Tính thống nhất theo số thành viên có quyền biểu quyết và có mặt (BR-07)
        $presentOfficialCount = $meeting->thamDu->filter(function ($att) {
            $hasRight = (bool) ($att->thanhVienHdt?->CoQuyenBieuQuyet ?? true);
            $isPresent = in_array($att->TrangThaiThamDu, ['PRESENT', 'Có mặt']);
            return $hasRight && $isPresent;
        })->count();

        $votingSummaries = [];
        foreach ($meeting->noiDungBieuQuyet as $voteContent) {
            $votingSummaries[$voteContent->MaNoiDung] = $voteContent->calculateStats($presentOfficialCount);
        }

        $role = Auth::user()?->role ?? (session('user_role') ?? 'CHAIR');

        return view('pages.phienhop.chitiet', compact(
            'meeting',
            'resolutions',
            'availableMembers',
            'availableGuests',
            'availableDossiers',
            'votingSummaries',
            'role'
        ));
    }

    /**
     * Biểu mẫu chỉnh sửa phiên họp
     */
    public function edit(int|string $id): View|RedirectResponse
    {
        $role = Auth::user()?->role ?? (session('user_role') ?? 'CHAIR');
        if (! in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN'])) {
            return redirect()->route('meetings.show', $id)
                ->with('error', 'Bạn không có quyền chỉnh sửa phiên họp.');
        }

        return redirect()->route('meetings.show', ['id' => $id, 'edit' => 1]);
    }

    /**
     * Cập nhật thông tin phiên họp
     */
    public function update(Request $request, int|string $id): RedirectResponse
    {
        $role = Auth::user()?->role ?? (session('user_role') ?? 'CHAIR');
        if (! in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN'])) {
            return redirect()->route('meetings.show', $id)->with('error', 'Từ chối quyền thao tác.');
        }

        $meeting = PhienHop::findOrFail($id);

        $validated = $request->validate([
            'TenPhienHop' => ['required', 'string', 'min:5', 'max:255'],
            'NgayHop' => ['required', 'date'],
            'GioBatDau' => ['required', 'string'],
            'GioKetThuc' => ['required', 'string'],
            'DiaDiem' => ['required', 'string', 'min:3', 'max:255'],
            'HinhThuc' => ['required', 'string'],
            'TrangThai' => ['required', 'string'],
            'GhiChu' => ['nullable', 'string', 'max:1000'],
        ], [
            'TenPhienHop.required' => 'Vui lòng nhập tên phiên họp.',
            'TenPhienHop.min' => 'Tên phiên họp phải có ít nhất 5 ký tự.',
            'NgayHop.required' => 'Vui lòng chọn ngày tổ chức họp.',
            'GioBatDau.required' => 'Vui lòng nhập giờ bắt đầu.',
            'GioKetThuc.required' => 'Vui lòng nhập giờ kết thúc.',
            'DiaDiem.required' => 'Vui lòng nhập địa điểm tổ chức.',
            'HinhThuc.required' => 'Vui lòng chọn hình thức tổ chức.',
            'TrangThai.required' => 'Vui lòng chọn trạng thái phiên họp.',
        ]);

        $startTimeStr = strlen($validated['GioBatDau']) === 5 ? "{$validated['GioBatDau']}:00" : $validated['GioBatDau'];
        $endTimeStr = strlen($validated['GioKetThuc']) === 5 ? "{$validated['GioKetThuc']}:00" : $validated['GioKetThuc'];
        $startDateTime = "{$validated['NgayHop']} {$startTimeStr}";
        $endDateTime = "{$validated['NgayHop']} {$endTimeStr}";

        if (strtotime($endDateTime) <= strtotime($startDateTime)) {
            $errMsg = 'Thời gian kết thúc phải diễn ra sau thời gian bắt đầu.';
            return back()
                ->withInput()
                ->with('error', $errMsg)
                ->withErrors(['GioKetThuc' => $errMsg]);
        }

        // [BR-08] Kiểm tra chống xung đột thời gian phiên họp khi cập nhật
        if (! in_array($validated['TrangThai'], ['Hủy', 'Đã hủy', 'CANCELLED', 'Đã ẩn'])) {
            $conflictingMeeting = PhienHop::where('MaPhienHop', '!=', $id)
                ->whereNotIn('TrangThai', ['Hủy', 'Đã hủy', 'CANCELLED', 'Đã ẩn'])
                ->where(function ($query) use ($startDateTime, $endDateTime) {
                    $query->where('ThoiGianBatDau', '<', $endDateTime)
                          ->where('ThoiGianKetThuc', '>', $startDateTime);
                })
                ->first();

            if ($conflictingMeeting) {
                $conflictStart = date('H:i', strtotime($conflictingMeeting->ThoiGianBatDau));
                $conflictEnd = date('H:i', strtotime($conflictingMeeting->ThoiGianKetThuc));
                $conflictDate = date('d/m/Y', strtotime($conflictingMeeting->NgayHop));
                $conflictMsg = "Xung đột lịch họp: Đã có phiên họp \"{$conflictingMeeting->TenPhienHop}\" diễn ra từ {$conflictStart} đến {$conflictEnd} ngày {$conflictDate}.";
                return back()
                    ->withInput()
                    ->with('error', $conflictMsg)
                    ->withErrors([
                        'GioBatDau' => $conflictMsg,
                    ]);
            }
        }

        $hinhThucVi = match (strtoupper(trim($validated['HinhThuc']))) {
            'IN_PERSON', 'IN PERSON' => 'Trực tiếp',
            'ONLINE' => 'Trực tuyến',
            'HYBRID' => 'Kết hợp',
            default => $validated['HinhThuc'],
        };

        $meeting->update([
            'TenPhienHop' => $validated['TenPhienHop'],
            'NgayHop' => $validated['NgayHop'],
            'ThoiGianBatDau' => $startDateTime,
            'ThoiGianKetThuc' => $endDateTime,
            'DiaDiem' => $validated['DiaDiem'],
            'HinhThuc' => $hinhThucVi,
            'TrangThai' => $validated['TrangThai'],
            'GhiChu' => $validated['GhiChu'] ?? null,
        ]);

        return redirect()->route('meetings.show', $meeting->MaPhienHop)
            ->with('success', 'Đã cập nhật lịch họp, thời gian kết thúc và thông tin phiên họp thành công!');
    }

    /**
     * Ẩn phiên họp (Soft Delete - không xóa cứng khỏi CSDL)
     */
    public function destroy(int|string $id): RedirectResponse
    {
        $role = Auth::user()?->role ?? (session('user_role') ?? 'CHAIR');
        if (! in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN'])) {
            return redirect()->route('meetings.index')->with('error', 'Bạn không có thẩm quyền ẩn phiên họp.');
        }

        $meeting = PhienHop::findOrFail($id);
        $meeting->update(['TrangThai' => 'Đã ẩn']);
        $meeting->delete();

        return redirect()->route('meetings.index')
            ->with('success', "Đã ẩn phiên họp #{$meeting->MaPhienHop} khỏi danh sách hiển thị (dữ liệu vẫn được bảo toàn trong CSDL).");
    }

    /**
     * [BR-02] Thêm thành viên vào danh sách tham dự
     */
    public function addMember(Request $request, int|string $id): RedirectResponse
    {
        $role = Auth::user()?->role ?? (session('user_role') ?? 'CHAIR');
        if (! in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN'])) {
            return back()->with('error', 'Từ chối quyền thao tác.');
        }

        $validated = $request->validate([
            'MaThanhVien' => ['required', 'integer', 'exists:thanh_vien_hdt,MaThanhVien'],
            'TrangThaiThamDu' => ['required', 'string'],
            'GhiChu' => ['nullable', 'string'],
        ], [
            'MaThanhVien.required' => 'Vui lòng chọn thành viên Hội đồng trường.',
            'TrangThaiThamDu.required' => 'Vui lòng chọn trạng thái tham dự ban đầu.',
        ]);

        // Chống tạo trùng lặp theo ràng buộc BR-02 (xử lý khôi phục bản ghi nếu đã từng xóa mềm)
        $existing = ThamDu::withTrashed()
            ->where('MaPhienHop', $id)
            ->where('MaThanhVien', $validated['MaThanhVien'])
            ->first();

        if ($existing) {
            if ($existing->trashed()) {
                $existing->restore();
                $existing->update([
                    'TrangThaiThamDu' => $validated['TrangThaiThamDu'],
                    'ThoiDiemXacNhan' => $validated['TrangThaiThamDu'] === 'Có mặt' ? now() : null,
                    'GhiChu' => $validated['GhiChu'] ?? null,
                ]);
                return redirect()->route('meetings.show', ['id' => $id, 'tab' => 'members'])
                    ->with('success', 'Đã thêm lại thành viên vào danh sách tham dự phiên họp thành công.');
            }
            return redirect()->route('meetings.show', ['id' => $id, 'tab' => 'members'])
                ->with('warning', 'Thành viên này đã có trong danh sách mời của phiên họp (Tuân thủ BR-02).');
        }

        ThamDu::create([
            'MaPhienHop' => $id,
            'MaThanhVien' => $validated['MaThanhVien'],
            'TrangThaiThamDu' => $validated['TrangThaiThamDu'],
            'ThoiDiemXacNhan' => $validated['TrangThaiThamDu'] === 'Có mặt' ? now() : null,
            'GhiChu' => $validated['GhiChu'] ?? null,
        ]);

        return redirect()->route('meetings.show', ['id' => $id, 'tab' => 'members'])
            ->with('success', 'Đã thêm thành viên vào danh sách tham dự phiên họp thành công.');
    }

    /**
     * Bớt thành viên khỏi danh sách tham dự phiên họp
     */
    public function removeMember(int|string $id, int|string $attendeeId): RedirectResponse
    {
        $role = Auth::user()?->role ?? (session('user_role') ?? 'CHAIR');
        if (! in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN'])) {
            return back()->with('error', 'Từ chối quyền thao tác.');
        }

        $attendee = ThamDu::with('thanhVienHdt.nguoiDung')
            ->where('MaPhienHop', $id)
            ->where('MaThamDu', $attendeeId)
            ->firstOrFail();

        // 1. Ràng buộc bảo toàn kết quả biểu quyết (BR-09): Không thể bớt thành viên đã biểu quyết trong phiên
        $hasVotes = KetQuaBieuQuyet::where('MaThamDu', $attendeeId)->exists();
        if ($hasVotes) {
            return back()->with('error', 'Không thể bớt thành viên này khỏi phiên họp vì thành viên đã thực hiện biểu quyết (nhằm bảo toàn dữ liệu kết quả bỏ phiếu).');
        }

        // 2. Ràng buộc ủy quyền: Không thể bớt thành viên có giấy ủy quyền liên quan đến phiên họp này
        $hasDelegation = UyQuyenBieuQuyet::where('MaPhienHop', $id)
            ->where(function ($q) use ($attendee) {
                $q->where('MaThanhVienUyQuyen', $attendee->MaThanhVien)
                  ->orWhere('MaThanhVienNhanUyQuyen', $attendee->MaThanhVien);
            })->exists();

        if ($hasDelegation) {
            return back()->with('error', 'Không thể bớt thành viên này khỏi phiên họp vì đang có giấy ủy quyền biểu quyết liên quan trong phiên.');
        }

        $memberName = $attendee->thanhVienHdt?->nguoiDung?->HoTen ?? "Thành viên #{$attendee->MaThanhVien}";
        $attendee->delete();

        return redirect()->route('meetings.show', ['id' => $id, 'tab' => 'members'])
            ->with('success', "Đã bớt thành viên \"{$memberName}\" khỏi danh sách tham dự phiên họp thành công.");
    }

    /**
     * Cập nhật điểm danh thành viên
     */
    public function updateAttendance(Request $request, int|string $id, int|string $attendeeId): RedirectResponse
    {
        $validated = $request->validate([
            'TrangThaiThamDu' => ['required', 'string', 'in:Có mặt,Vắng,Có xin phép,Không xin phép'],
        ]);

        $thamDu = ThamDu::where('MaPhienHop', $id)->where('MaThamDu', $attendeeId)->firstOrFail();
        $thamDu->update([
            'TrangThaiThamDu' => $validated['TrangThaiThamDu'],
            'ThoiDiemXacNhan' => $validated['TrangThaiThamDu'] === 'Có mặt' ? now() : $thamDu->ThoiDiemXacNhan,
        ]);

        return redirect()->route('meetings.show', ['id' => $id, 'tab' => 'members'])
            ->with('success', 'Đã cập nhật trạng thái điểm danh.');
    }

    /**
     * [BR-03] Thêm khách mời vào phiên họp
     */
    public function addGuest(Request $request, int|string $id): RedirectResponse
    {
        $role = Auth::user()?->role ?? (session('user_role') ?? 'CHAIR');
        if (! in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN'])) {
            return back()->with('error', 'Từ chối quyền thao tác.');
        }

        $validated = $request->validate([
            'MaKhachMoi' => ['nullable', 'integer', 'exists:khach_moi,MaKhachMoi'],
            'HoTen' => ['required_without:MaKhachMoi', 'nullable', 'string', 'max:255'],
            'DonViCongTac' => ['nullable', 'string', 'max:255'],
            'ChucVu' => ['nullable', 'string', 'max:255'],
            'Email' => ['nullable', 'email', 'max:191'],
            'NoiDungMoi' => ['required', 'string', 'max:500'],
            'TrangThaiThamDu' => ['required', 'string', 'max:100'],
        ], [
            'HoTen.required_without' => 'Vui lòng chọn khách mời có sẵn hoặc nhập họ tên khách mời mới.',
            'NoiDungMoi.required' => 'Vui lòng nhập nội dung mời tham gia.',
            'TrangThaiThamDu.required' => 'Vui lòng chọn trạng thái tham dự ban đầu.',
        ]);

        if (!empty($validated['MaKhachMoi'])) {
            $guest = KhachMoi::findOrFail($validated['MaKhachMoi']);
        } else {
            $trimmedGuestName = trim((string) $validated['HoTen']);
            $normalizedGuestName = DuplicateNameChecker::normalize($trimmedGuestName);
            $matchedGuest = KhachMoi::all()->first(
                fn ($g) => DuplicateNameChecker::normalize($g->HoTen) === $normalizedGuestName
            );

            if ($matchedGuest) {
                $alreadyInMeeting = KhachMoiPhienHop::where('MaPhienHop', $id)
                    ->where('MaKhachMoi', $matchedGuest->MaKhachMoi)
                    ->exists();

                if ($alreadyInMeeting) {
                    return redirect()->route('meetings.show', ['id' => $id, 'tab' => 'guests'])
                        ->with('warning', 'Khách mời này đã được mời tham gia phiên họp (Tuân thủ BR-03).');
                }

                $errMsg = "Tên khách mời \"{$trimmedGuestName}\" đã tồn tại trong danh bạ. Vui lòng chọn từ danh sách có sẵn hoặc kiểm tra lại.";
                return redirect()->route('meetings.show', ['id' => $id, 'tab' => 'guests', 'add_guest' => 1])
                    ->withInput()
                    ->with('error', $errMsg)
                    ->withErrors(['HoTen' => $errMsg]);
            }

            // Tạo khách mời nếu chưa có trong danh bạ
            $guest = KhachMoi::create([
                'HoTen' => $trimmedGuestName,
                'DonViCongTac' => !empty($validated['DonViCongTac']) ? trim($validated['DonViCongTac']) : null,
                'ChucVu' => !empty($validated['ChucVu']) ? trim($validated['ChucVu']) : null,
                'Email' => !empty($validated['Email']) ? trim($validated['Email']) : null,
            ]);
        }

        // Kiểm tra chống trùng khách mời trong phiên (BR-03, xử lý khôi phục bản ghi nếu đã từng xóa mềm)
        $existingGuest = KhachMoiPhienHop::withTrashed()
            ->where('MaPhienHop', $id)
            ->where('MaKhachMoi', $guest->MaKhachMoi)
            ->first();

        if ($existingGuest) {
            if ($existingGuest->trashed()) {
                $existingGuest->restore();
                $existingGuest->update([
                    'NoiDungMoi' => $validated['NoiDungMoi'],
                    'TrangThaiThamDu' => $validated['TrangThaiThamDu'],
                ]);
                return redirect()->route('meetings.show', ['id' => $id, 'tab' => 'guests'])
                    ->with('success', "Đã thêm lại khách mời \"{$guest->HoTen}\" vào phiên họp thành công.");
            }
            return redirect()->route('meetings.show', ['id' => $id, 'tab' => 'guests'])
                ->with('warning', 'Khách mời này đã được mời tham gia phiên họp (Tuân thủ BR-03).');
        }

        KhachMoiPhienHop::create([
            'MaPhienHop' => $id,
            'MaKhachMoi' => $guest->MaKhachMoi,
            'NoiDungMoi' => $validated['NoiDungMoi'],
            'TrangThaiThamDu' => $validated['TrangThaiThamDu'],
        ]);

        return redirect()->route('meetings.show', ['id' => $id, 'tab' => 'guests'])
            ->with('success', "Đã thêm khách mời \"{$guest->HoTen}\" vào phiên họp thành công.");
    }

    /**
     * Hủy lời mời / Bớt khách mời khỏi phiên họp
     */
    public function removeGuest(int|string $id, int|string $inviteId): RedirectResponse
    {
        $role = Auth::user()?->role ?? (session('user_role') ?? 'CHAIR');
        if (! in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN'])) {
            return back()->with('error', 'Từ chối quyền thao tác.');
        }

        $invite = KhachMoiPhienHop::with('khachMoi')
            ->where('MaPhienHop', $id)
            ->where('MaMoi', $inviteId)
            ->firstOrFail();

        $guestName = $invite->khachMoi?->HoTen ?? 'Khách mời';
        $invite->delete();

        return redirect()->route('meetings.show', ['id' => $id, 'tab' => 'guests'])
            ->with('success', "Đã hủy lời mời và bớt khách mời \"{$guestName}\" khỏi phiên họp thành công.");
    }

    /**
     * [BR-08] Thêm hồ sơ trình vào chương trình họp
     */
    public function addAgendaItem(Request $request, int|string $id): RedirectResponse
    {
        $validated = $request->validate([
            'MaHoSo' => ['required', 'integer', 'exists:ho_so_trinh,MaHoSo'],
            'ThuTu' => ['nullable', 'integer', 'min:1'],
            'GhiChu' => ['nullable', 'string'],
        ], [
            'ThuTu.min' => 'Thứ tự xem xét phải lớn hơn hoặc bằng 1.',
        ]);

        // [Ràng buộc 1] Chống đưa trùng hồ sơ vào phiên họp (BR-08)
        $dossierExists = ChuongTrinhHop::where('MaPhienHop', $id)
            ->where('MaHoSo', $validated['MaHoSo'])
            ->exists();
        if ($dossierExists) {
            $msg = 'Hồ sơ trình này đã có trong chương trình họp của phiên này (Tuân thủ BR-08).';
            return back()
                ->withInput()
                ->with('error', $msg)
                ->withErrors(['MaHoSo' => $msg]);
        }

        // [Ràng buộc 2] Cột Thứ Tự không được trùng khi thêm mới trong cùng một phiên họp
        if (! empty($validated['ThuTu'])) {
            $thuTu = (int) $validated['ThuTu'];
            $orderConflict = ChuongTrinhHop::where('MaPhienHop', $id)
                ->where('ThuTu', $thuTu)
                ->first();
            if ($orderConflict) {
                $conflictName = $orderConflict->hoSoTrinh?->TieuDe ?? ('Hồ sơ #' . $orderConflict->MaHoSo);
                $msg = "Thứ tự xem xét #{$thuTu} đã được sử dụng cho mục: \"{$conflictName}\" trong phiên họp này. Vui lòng chọn số thứ tự khác hoặc để trống để hệ thống tự động tăng.";
                return back()
                    ->withInput()
                    ->with('error', $msg)
                    ->withErrors(['ThuTu' => $msg]);
            }
        } else {
            $maxOrder = ChuongTrinhHop::where('MaPhienHop', $id)->max('ThuTu') ?? 0;
            $thuTu = $maxOrder + 1;
        }

        ChuongTrinhHop::create([
            'MaPhienHop' => $id,
            'MaHoSo' => $validated['MaHoSo'],
            'ThuTu' => $thuTu,
            'TrangThai' => 'Chưa xem xét',
            'GhiChu' => $validated['GhiChu'] ?? null,
            'MaNguoiDung' => Auth::id(),
        ]);

        return redirect()->route('meetings.show', ['id' => $id, 'tab' => 'agenda'])
            ->with('success', "Đã đưa hồ sơ trình vào chương trình họp (Thứ tự: #{$thuTu}).");
    }

    /**
     * [BR-05] Tải lên tài liệu phiên họp (độc lập với hồ sơ trình)
     */
    public function uploadDocument(Request $request, int|string $id): RedirectResponse
    {
        $role = Auth::user()?->role ?? (session('user_role') ?? 'CHAIR');
        if (! in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN'])) {
            return back()->with('error', 'Từ chối quyền thao tác.');
        }

        // Kiểm tra lỗi upload từ phía PHP trước khi validate
        $uploaded = $request->file('file');
        if ($uploaded instanceof \Illuminate\Http\UploadedFile && ! $uploaded->isValid()) {
            $errCode = $uploaded->getError();
            $errMsg = match ($errCode) {
                UPLOAD_ERR_INI_SIZE => 'Tập tin tải lên thất bại do dung lượng vượt quá giới hạn của máy chủ (upload_max_filesize trong PHP).',
                UPLOAD_ERR_FORM_SIZE => 'Tập tin tải lên vượt quá giới hạn dung lượng của biểu mẫu.',
                UPLOAD_ERR_PARTIAL => 'Quá trình tải lên tập tin bị gián đoạn, chỉ tải lên được một phần.',
                UPLOAD_ERR_NO_FILE => 'Chưa có tập tin nào được tải lên.',
                UPLOAD_ERR_NO_TMP_DIR => 'Lỗi máy chủ: Thiếu thư mục tạm lưu trữ tập tin.',
                UPLOAD_ERR_CANT_WRITE => 'Lỗi máy chủ: Không thể ghi tập tin tạm vào ổ đĩa.',
                default => 'Tải lên tập tin thất bại (Mã lỗi: ' . $errCode . '). Vui lòng thử lại.',
            };
            return redirect()->route('meetings.show', ['id' => $id, 'tab' => 'docs', 'upload_doc' => 1])
                ->withInput()
                ->with('error', $errMsg)
                ->withErrors(['file' => $errMsg]);
        }

        $validator = Validator::make($request->all(), [
            'TenTaiLieu' => ['required', 'string', 'max:255'],
            'LoaiTaiLieu' => ['required', 'string', 'max:100'],
            'file' => ['required', 'file', 'max:20480', 'extensions:pdf,doc,docx,xls,xlsx,ppt,pptx'],
        ], [
            'TenTaiLieu.required' => 'Vui lòng nhập tên tài liệu phiên họp.',
            'LoaiTaiLieu.required' => 'Vui lòng chọn phân loại tài liệu.',
            'file.required' => 'Vui lòng chọn tập tin tài liệu đính kèm.',
            'file.file' => 'Tập tin tải lên không hợp lệ.',
            'file.uploaded' => 'Tập tin tải lên thất bại! Dung lượng tệp có thể vượt quá cấu hình của máy chủ hoặc đường truyền bị ngắt quãng.',
            'file.extensions' => 'Định dạng tập tin không được hỗ trợ! Vui lòng chỉ tải lên các tệp tài liệu: PDF (.pdf), Word (.doc, .docx), Excel (.xls, .xlsx) hoặc PowerPoint (.ppt, .pptx).',
            'file.mimes' => 'Định dạng tập tin không được hỗ trợ! Vui lòng chỉ tải lên các tệp tài liệu: PDF (.pdf), Word (.doc, .docx), Excel (.xls, .xlsx) hoặc PowerPoint (.ppt, .pptx).',
            'file.max' => 'Dung lượng tập tin vượt quá giới hạn cho phép (Tối đa 20MB).',
        ]);

        if ($validator->fails()) {
            return redirect()->route('meetings.show', ['id' => $id, 'tab' => 'docs', 'upload_doc' => 1])
                ->withErrors($validator)
                ->withInput();
        }

        $validated = $validator->validated();
        $trimmedDocTitle = trim($validated['TenTaiLieu']);

        $meetingDocQuery = TaiLieu::whereHas('phienHop', fn ($q) => $q->where('phien_hop.MaPhienHop', $id));
        if (DuplicateNameChecker::exists($meetingDocQuery, 'TenTaiLieu', $trimmedDocTitle)) {
            $errMsg = "Tài liệu có tên \"{$trimmedDocTitle}\" đã tồn tại trong phiên họp này. Vui lòng đặt tên khác.";
            return redirect()->route('meetings.show', ['id' => $id, 'tab' => 'docs', 'upload_doc' => 1])
                ->withInput()
                ->with('error', $errMsg)
                ->withErrors(['TenTaiLieu' => $errMsg]);
        }

        $uploadedFile = $request->file('file');
        $fileName = time() . '_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $uploadedFile->getClientOriginalName());
        $path = $uploadedFile->storeAs('documents/meetings', $fileName, 'public');

        $user = Auth::user();

        $taiLieu = TaiLieu::create([
            'TenTaiLieu' => $trimmedDocTitle,
            'LoaiTaiLieu' => $validated['LoaiTaiLieu'],
            'DuongDan' => '/storage/' . $path,
            'DinhDang' => $uploadedFile->getClientOriginalExtension(),
            'DungLuong' => $uploadedFile->getSize(),
            'NgayTaiLen' => now(),
            'MaNguoiDung' => $user?->MaNguoiDung ?? 1,
        ]);

        // Liên kết vào bảng trung gian tai_lieu_phien_hop (Khóa chính kép)
        DB::table('tai_lieu_phien_hop')->insertOrIgnore([
            'MaTaiLieu' => $taiLieu->MaTaiLieu,
            'MaPhienHop' => $id,
        ]);

        return redirect()->route('meetings.show', ['id' => $id, 'tab' => 'docs'])
            ->with('success', "Tải lên tài liệu \"{$taiLieu->TenTaiLieu}\" cho phiên họp thành công (Tuân thủ BR-05).");
    }

    /**
     * Xóa tài liệu khỏi phiên họp
     */
    public function removeDocument(int|string $id, int|string $docId): RedirectResponse
    {
        $role = Auth::user()?->role ?? (session('user_role') ?? 'CHAIR');
        if (! in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN'])) {
            return back()->with('error', 'Từ chối quyền thao tác.');
        }

        DB::table('tai_lieu_phien_hop')
            ->where('MaPhienHop', $id)
            ->where('MaTaiLieu', $docId)
            ->delete();

        $taiLieu = TaiLieu::find($docId);
        if ($taiLieu) {
            $taiLieu->delete();
        }

        return redirect()->route('meetings.show', ['id' => $id, 'tab' => 'docs'])
            ->with('success', 'Đã xóa tài liệu khỏi phiên họp thành công.');
    }
}
