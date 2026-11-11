<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ChuongTrinhHop;
use App\Models\HoSoTrinh;
use App\Models\KhachMoi;
use App\Models\KhachMoiPhienHop;
use App\Models\NghiQuyet;
use App\Models\NoiDungBieuQuyet;
use App\Models\PhienHop;
use App\Models\TaiLieu;
use App\Models\ThamDu;
use App\Models\ThanhVienHdt;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MeetingWebController extends Controller
{
    /**
     * Danh sách phiên họp (Bộ lọc, Tìm kiếm, Phân trang)
     */
    public function index(Request $request): View
    {
        $query = PhienHop::query()
            ->withCount(['chuongTrinhHop', 'thamDu', 'taiLieu']);

        // 1. Tìm kiếm từ khóa
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('TenPhienHop', 'like', "%{$search}%")
                    ->orWhere('DiaDiem', 'like', "%{$search}%")
                    ->orWhere('GhiChu', 'like', "%{$search}%");
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

        $meetings = $query->orderBy('ThoiGianBatDau', 'desc')
            ->paginate(10)
            ->withQueryString();

        // Danh sách các năm họp để hiển thị trong bộ lọc
        $years = PhienHop::selectRaw('YEAR(NgayHop) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        $role = Auth::user()?->role ?? (session('user_role') ?? 'CHAIR');

        return view('pages.meetings.index', compact('meetings', 'years', 'role'));
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

        return view('pages.meetings.create');
    }

    /**
     * Lưu phiên họp mới (kiểm tra BR-02, ràng buộc thời gian)
     */
    public function store(Request $request): RedirectResponse
    {
        $role = Auth::user()?->role ?? (session('user_role') ?? 'CHAIR');
        if (! in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN'])) {
            return redirect()->route('meetings.index')->with('error', 'Từ chối quyền thao tác.');
        }

        $validated = $request->validate([
            'TenPhienHop' => ['required', 'string', 'min:5', 'max:255'],
            'NgayHop' => ['required', 'date'],
            'GioBatDau' => ['required', 'string'],
            'GioKetThuc' => ['required', 'string'],
            'DiaDiem' => ['required', 'string', 'max:255'],
            'HinhThuc' => ['required', 'string'],
            'GhiChu' => ['nullable', 'string'],
        ], [
            'TenPhienHop.required' => 'Vui lòng nhập tên phiên họp.',
            'NgayHop.required' => 'Vui lòng chọn ngày họp.',
            'GioBatDau.required' => 'Vui lòng nhập giờ bắt đầu.',
            'GioKetThuc.required' => 'Vui lòng nhập giờ kết thúc.',
            'DiaDiem.required' => 'Vui lòng nhập địa điểm tổ chức.',
            'HinhThuc.required' => 'Vui lòng chọn hình thức họp.',
        ]);

        $startTimeStr = strlen($validated['GioBatDau']) === 5 ? "{$validated['GioBatDau']}:00" : $validated['GioBatDau'];
        $endTimeStr = strlen($validated['GioKetThuc']) === 5 ? "{$validated['GioKetThuc']}:00" : $validated['GioKetThuc'];
        $startDateTime = "{$validated['NgayHop']} {$startTimeStr}";
        $endDateTime = "{$validated['NgayHop']} {$endTimeStr}";

        // Kiểm tra ràng buộc thời gian kết thúc phải lớn hơn thời gian bắt đầu
        if (strtotime($endDateTime) <= strtotime($startDateTime)) {
            $errMsg = 'Thời gian kết thúc phải diễn ra sau thời gian bắt đầu.';
            return back()
                ->withInput()
                ->with('error', $errMsg)
                ->withErrors(['GioKetThuc' => $errMsg]);
        }

        // [BR-08] Kiểm tra chống xung đột thời gian phiên họp (Thư ký & HĐT không thể họp trùng lịch)
        $conflictingMeeting = PhienHop::whereNotIn('TrangThai', ['Hủy', 'Đã hủy', 'CANCELLED'])
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

        $phienHop = DB::transaction(function () use ($validated, $startDateTime, $endDateTime) {
            $meeting = PhienHop::create([
                'TenPhienHop' => $validated['TenPhienHop'],
                'NgayHop' => $validated['NgayHop'],
                'ThoiGianBatDau' => $startDateTime,
                'ThoiGianKetThuc' => $endDateTime,
                'DiaDiem' => $validated['DiaDiem'],
                'HinhThuc' => $validated['HinhThuc'],
                'TrangThai' => 'Sắp diễn ra',
                'GhiChu' => $validated['GhiChu'] ?? null,
            ]);

            // [BR-02] Tự động mời tất cả thành viên HĐT chính thức vào phiên (chống trùng lặp)
            $officialMembers = ThanhVienHdt::all();
            foreach ($officialMembers as $member) {
                ThamDu::firstOrCreate([
                    'MaPhienHop' => $meeting->MaPhienHop,
                    'MaThanhVien' => $member->MaThanhVien,
                ], [
                    'TrangThaiThamDu' => 'Có mặt',
                    'ThoiDiemXacNhan' => null,
                    'GhiChu' => null,
                ]);
            }

            return $meeting;
        });

        return redirect()->route('meetings.show', $phienHop->MaPhienHop)
            ->with('success', 'Tạo phiên họp mới thành công! Đã tự động tạo danh sách mời thành viên Hội đồng.');
    }

    /**
     * Màn hình chi tiết phiên họp với 7 TAB nghiệp vụ chuyên sâu
     */
    public function show(int|string $id): View
    {
        $meeting = PhienHop::with([
            'thamDu.thanhVienHdt.nguoiDung.donVi',
            'khachMoiPhienHop.khachMoi',
            'chuongTrinhHop.hoSoTrinh.donVi',
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

        // Danh sách hồ sơ trình có thể đưa vào chương trình họp (để modal gán hồ sơ BR-08)
        $existingDossierIds = $meeting->chuongTrinhHop->pluck('MaHoSo')->toArray();
        $availableDossiers = HoSoTrinh::with('donVi')
            ->whereNotIn('MaHoSo', $existingDossierIds)
            ->whereIn('TrangThai', ['Đủ điều kiện', 'Đầy đủ', 'APPROVED_FOR_MEETING', 'SUBMITTED', 'PENDING'])
            ->get();

        // Thống kê kết quả biểu quyết bảo mật (BR-09): Tính tổng số phiếu, tuyệt đối không lộ danh tính
        $votingSummaries = [];
        foreach ($meeting->noiDungBieuQuyet as $voteContent) {
            $votes = $voteContent->ketQuaBieuQuyet;
            $totalVotes = $votes->count();
            $agree = $votes->where('LuaChon', 'Tán thành')->count();
            $disagree = $votes->where('LuaChon', 'Không tán thành')->count();
            $abstain = $votes->where('LuaChon', 'Không có ý kiến')->count();

            $votingSummaries[$voteContent->MaNoiDung] = [
                'total' => $totalVotes,
                'agree' => $agree,
                'disagree' => $disagree,
                'abstain' => $abstain,
                'agree_percent' => $totalVotes > 0 ? round(($agree / $totalVotes) * 100, 1) : 0,
            ];
        }

        $role = Auth::user()?->role ?? (session('user_role') ?? 'CHAIR');

        return view('pages.meetings.show', compact(
            'meeting',
            'resolutions',
            'availableMembers',
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

        $meeting = PhienHop::findOrFail($id);

        return view('pages.meetings.edit', compact('meeting'));
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
            'DiaDiem' => ['required', 'string', 'max:255'],
            'HinhThuc' => ['required', 'string'],
            'TrangThai' => ['required', 'string'],
            'GhiChu' => ['nullable', 'string'],
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
        if (! in_array($validated['TrangThai'], ['Hủy', 'Đã hủy', 'CANCELLED'])) {
            $conflictingMeeting = PhienHop::where('MaPhienHop', '!=', $id)
                ->whereNotIn('TrangThai', ['Hủy', 'Đã hủy', 'CANCELLED'])
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

        $meeting->update([
            'TenPhienHop' => $validated['TenPhienHop'],
            'NgayHop' => $validated['NgayHop'],
            'ThoiGianBatDau' => $startDateTime,
            'ThoiGianKetThuc' => $endDateTime,
            'DiaDiem' => $validated['DiaDiem'],
            'HinhThuc' => $validated['HinhThuc'],
            'TrangThai' => $validated['TrangThai'],
            'GhiChu' => $validated['GhiChu'] ?? null,
        ]);

        return redirect()->route('meetings.show', $meeting->MaPhienHop)
            ->with('success', 'Cập nhật phiên họp thành công!');
    }

    /**
     * [BR-02] Thêm thành viên vào danh sách tham dự
     */
    public function addMember(Request $request, int|string $id): RedirectResponse
    {
        $validated = $request->validate([
            'MaThanhVien' => ['required', 'integer', 'exists:thanh_vien_hdt,MaThanhVien'],
            'TrangThaiThamDu' => ['required', 'string'],
            'GhiChu' => ['nullable', 'string'],
        ]);

        // Chống tạo trùng lặp theo ràng buộc BR-02
        $exists = ThamDu::where('MaPhienHop', $id)
            ->where('MaThanhVien', $validated['MaThanhVien'])
            ->exists();

        if ($exists) {
            return back()->with('warning', 'Thành viên này đã có trong danh sách mời của phiên họp (Tuân thủ BR-02).');
        }

        ThamDu::create([
            'MaPhienHop' => $id,
            'MaThanhVien' => $validated['MaThanhVien'],
            'TrangThaiThamDu' => $validated['TrangThaiThamDu'],
            'ThoiDiemXacNhan' => $validated['TrangThaiThamDu'] === 'Có mặt' ? now() : null,
            'GhiChu' => $validated['GhiChu'] ?? null,
        ]);

        return back()->with('success', 'Đã thêm thành viên vào danh sách tham dự.');
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

        return back()->with('success', 'Đã cập nhật trạng thái điểm danh.');
    }

    /**
     * [BR-03] Thêm khách mời vào phiên họp
     */
    public function addGuest(Request $request, int|string $id): RedirectResponse
    {
        $validated = $request->validate([
            'HoTen' => ['required', 'string', 'max:255'],
            'DonViCongTac' => ['nullable', 'string', 'max:255'],
            'ChucVu' => ['nullable', 'string', 'max:255'],
            'Email' => ['nullable', 'email', 'max:191'],
            'NoiDungMoi' => ['required', 'string'],
            'TrangThaiThamDu' => ['required', 'string'],
        ]);

        // Tạo khách mời nếu chưa có trong danh bạ
        $guest = KhachMoi::firstOrCreate([
            'HoTen' => $validated['HoTen'],
            'DonViCongTac' => $validated['DonViCongTac'] ?? null,
        ], [
            'ChucVu' => $validated['ChucVu'] ?? null,
            'Email' => $validated['Email'] ?? null,
        ]);

        // Kiểm tra chống trùng khách mời trong phiên (BR-03)
        $exists = KhachMoiPhienHop::where('MaPhienHop', $id)
            ->where('MaKhachMoi', $guest->MaKhachMoi)
            ->exists();

        if ($exists) {
            return back()->with('warning', 'Khách mời này đã được mời tham gia phiên họp (Tuân thủ BR-03).');
        }

        KhachMoiPhienHop::create([
            'MaPhienHop' => $id,
            'MaKhachMoi' => $guest->MaKhachMoi,
            'NoiDungMoi' => $validated['NoiDungMoi'],
            'TrangThaiThamDu' => $validated['TrangThaiThamDu'],
        ]);

        return back()->with('success', 'Đã thêm khách mời vào phiên họp.');
    }

    /**
     * [BR-08] Thêm hồ sơ trình vào chương trình họp
     */
    public function addAgendaItem(Request $request, int|string $id): RedirectResponse
    {
        $validated = $request->validate([
            'MaHoSo' => ['required', 'integer', 'exists:ho_so_trinh,MaHoSo'],
            'GhiChu' => ['nullable', 'string'],
        ]);

        $maxOrder = ChuongTrinhHop::where('MaPhienHop', $id)->max('ThuTu') ?? 0;

        ChuongTrinhHop::create([
            'MaPhienHop' => $id,
            'MaHoSo' => $validated['MaHoSo'],
            'ThuTu' => $maxOrder + 1,
            'TrangThai' => 'Chưa xem xét',
            'GhiChu' => $validated['GhiChu'] ?? null,
        ]);

        return back()->with('success', 'Đã đưa hồ sơ trình vào chương trình họp (Thứ tự: ' . ($maxOrder + 1) . ').');
    }

    /**
     * [BR-05] Tải lên tài liệu phiên họp (độc lập với hồ sơ trình)
     */
    public function uploadDocument(Request $request, int|string $id): RedirectResponse
    {
        $validated = $request->validate([
            'TenTaiLieu' => ['required', 'string', 'max:255'],
            'LoaiTaiLieu' => ['required', 'string'],
            'file' => ['required', 'file', 'max:20480', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx'],
        ], [
            'TenTaiLieu.required' => 'Vui lòng nhập tên tài liệu.',
            'file.required' => 'Vui lòng chọn tập tin tài liệu đính kèm.',
            'file.max' => 'Dung lượng tập tin không được vượt quá 20MB.',
        ]);

        $uploadedFile = $request->file('file');
        $fileName = time() . '_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $uploadedFile->getClientOriginalName());
        $path = $uploadedFile->storeAs('documents/meetings', $fileName, 'public');

        $user = Auth::user();

        $taiLieu = TaiLieu::create([
            'TenTaiLieu' => $validated['TenTaiLieu'],
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

        return back()->with('success', 'Tải lên tài liệu phiên họp thành công (Tuân thủ BR-05).');
    }
}
