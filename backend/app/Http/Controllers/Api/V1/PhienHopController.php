<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ChuongTrinhHop;
use App\Models\HoSoTrinh;
use App\Models\KhachMoi;
use App\Models\KhachMoiPhienHop;
use App\Models\PhienHop;
use App\Models\TaiLieu;
use App\Models\ThamDu;
use App\Models\ThanhVienHdt;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PhienHopController extends Controller
{
    /**
     * Danh sách phiên họp (hỗ trợ tìm kiếm, lọc trạng thái, phân trang)
     * GET /api/v1/meetings
     */
    public function index(Request $request): JsonResponse
    {
        $query = PhienHop::query()
            ->withCount(['chuongTrinhHop as agendas_count', 'thamDu as attendees_count', 'taiLieu as documents_count']);

        // 1. Lọc từ khóa tìm kiếm
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('TenPhienHop', 'like', "%{$search}%")
                    ->orWhere('DiaDiem', 'like', "%{$search}%")
                    ->orWhere('GhiChu', 'like', "%{$search}%");
            });
        }

        // 2. Lọc trạng thái
        if ($status = $request->input('status')) {
            if ($status !== 'ALL') {
                $query->where('TrangThai', $status);
            }
        }

        // 3. Lọc hình thức họp (IN_PERSON, ONLINE, HYBRID)
        if ($format = $request->input('format')) {
            if ($format !== 'ALL') {
                $query->where('HinhThuc', $format);
            }
        }

        $perPage = min((int) $request->input('perPage', 10), 100);
        $paginated = $query->orderBy('NgayHop', 'desc')
            ->orderBy('ThoiGianBatDau', 'desc')
            ->paginate($perPage);

        $items = collect($paginated->items())->map(fn (PhienHop $meeting) => $this->formatMeetingSummary($meeting));

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách phiên họp thành công.',
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
     * Tạo mới phiên họp
     * POST /api/v1/meetings
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'min:5', 'max:255'],
            'meeting_date' => ['required', 'date'],
            'start_time' => ['required', 'string'],
            'end_time' => ['nullable', 'string'],
            'location' => ['required', 'string', 'max:255'],
            'meeting_format' => ['required', 'string', 'in:IN_PERSON,ONLINE,HYBRID'],
            'description' => ['nullable', 'string'],
        ], [
            'title.required' => 'Vui lòng nhập tên phiên họp.',
            'meeting_date.required' => 'Vui lòng chọn ngày họp.',
            'start_time.required' => 'Vui lòng nhập thời gian bắt đầu.',
            'location.required' => 'Vui lòng nhập địa điểm tổ chức.',
        ]);

        $startDateTime = "{$validated['meeting_date']} {$validated['start_time']}:00";
        $endDateTime = ! empty($validated['end_time']) ? "{$validated['meeting_date']} {$validated['end_time']}:00" : null;

        $meeting = DB::transaction(function () use ($validated, $startDateTime, $endDateTime) {
            $phienHop = PhienHop::create([
                'TenPhienHop' => $validated['title'],
                'NgayHop' => $validated['meeting_date'],
                'ThoiGianBatDau' => $startDateTime,
                'ThoiGianKetThuc' => $endDateTime,
                'DiaDiem' => $validated['location'],
                'HinhThuc' => $validated['meeting_format'],
                'TrangThai' => 'PREPARING',
                'GhiChu' => $validated['description'] ?? null,
            ]);

            // [BR-02] Ràng buộc bộ duy nhất (MaPhienHop, MaThanhVien) trong bảng THAM_DU (UNIQUE)
            // [BR-11] Cấu hình ThoiDiemXacNhan là NULLABLE khi mới lên danh sách mời
            $officialMembers = ThanhVienHdt::all();
            foreach ($officialMembers as $member) {
                ThamDu::firstOrCreate([
                    'MaPhienHop' => $phienHop->MaPhienHop,
                    'MaThanhVien' => $member->MaThanhVien,
                ], [
                    'TrangThaiThamDu' => 'INVITED',
                    'ThoiDiemXacNhan' => null,
                    'GhiChu' => null,
                ]);
            }

            return $phienHop;
        });

        return response()->json([
            'success' => true,
            'message' => 'Tạo phiên họp mới thành công.',
            'data' => $this->formatMeetingDetail($meeting->load(['thamDu.thanhVienHdt.nguoiDung'])),
            'errors' => null,
        ], 201);
    }

    /**
     * Chi tiết phiên họp, chương trình họp, thành phần tham dự và tài liệu
     * GET /api/v1/meetings/{id}
     */
    public function show(int|string $id): JsonResponse
    {
        $meeting = PhienHop::with([
            'thamDu.thanhVienHdt.nguoiDung.donVi',
            'khachMoiPhienHop.khachMoi',
            'chuongTrinhHop.hoSoTrinh.donVi',
            'chuongTrinhHop.noiDungBieuQuyet.ketQuaBieuQuyet',
            'taiLieu',
        ])->find($id);

        if (! $meeting) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy phiên họp yêu cầu.',
                'data' => null,
                'errors' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Lấy thông tin chi tiết phiên họp thành công.',
            'data' => $this->formatMeetingDetail($meeting),
            'errors' => null,
        ]);
    }

    /**
     * Cập nhật thông tin phiên họp
     * PUT /api/v1/meetings/{id}
     */
    public function update(Request $request, int|string $id): JsonResponse
    {
        $meeting = PhienHop::find($id);
        if (! $meeting) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy phiên họp yêu cầu.',
                'data' => null,
                'errors' => null,
            ], 404);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'min:5', 'max:255'],
            'meeting_date' => ['required', 'date'],
            'start_time' => ['required', 'string'],
            'end_time' => ['nullable', 'string'],
            'location' => ['required', 'string', 'max:255'],
            'meeting_format' => ['required', 'string', 'in:IN_PERSON,ONLINE,HYBRID'],
            'description' => ['nullable', 'string'],
        ]);

        $startDateTime = "{$validated['meeting_date']} {$validated['start_time']}:00";
        $endDateTime = ! empty($validated['end_time']) ? "{$validated['meeting_date']} {$validated['end_time']}:00" : null;

        $meeting->update([
            'TenPhienHop' => $validated['title'],
            'NgayHop' => $validated['meeting_date'],
            'ThoiGianBatDau' => $startDateTime,
            'ThoiGianKetThuc' => $endDateTime,
            'DiaDiem' => $validated['location'],
            'HinhThuc' => $validated['meeting_format'],
            'GhiChu' => $validated['description'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật phiên họp thành công.',
            'data' => $this->formatMeetingDetail($meeting),
            'errors' => null,
        ]);
    }

    /**
     * Xóa phiên họp (khi chưa bế mạc)
     * DELETE /api/v1/meetings/{id}
     */
    public function destroy(int|string $id): JsonResponse
    {
        $meeting = PhienHop::find($id);
        if (! $meeting) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy phiên họp để xóa.',
                'data' => null,
                'errors' => null,
            ], 404);
        }

        if ($meeting->TrangThai === 'COMPLETED') {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa phiên họp đã bế mạc.',
                'data' => null,
                'errors' => null,
            ], 422);
        }

        $meeting->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa phiên họp thành công.',
            'data' => null,
            'errors' => null,
        ]);
    }

    /**
     * Thêm mục chương trình họp (Agenda)
     * POST /api/v1/meetings/{id}/agendas
     */
    public function addAgenda(Request $request, int|string $id): JsonResponse
    {
        $meeting = PhienHop::find($id);
        if (! $meeting) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy phiên họp.',
                'data' => null,
                'errors' => null,
            ], 404);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'duration_minutes' => ['nullable', 'integer'],
            'presenter' => ['nullable', 'string', 'max:255'],
            'dossier_id' => ['nullable', 'integer', 'exists:ho_so_trinh,MaHoSo'],
            'is_voting_required' => ['nullable', 'boolean'],
        ]);

        $maxThuTu = ChuongTrinhHop::where('MaPhienHop', $id)->max('ThuTu') ?? 0;

        // Nếu có gắn hồ sơ trình thì lấy MaHoSo, nếu không có thì tạo hoặc liên kết mặc định
        $dossierId = $validated['dossier_id'] ?? null;
        if (! $dossierId) {
            // Tạo hồ sơ trình mặc định cho mục chương trình này
            $defaultDossier = HoSoTrinh::create([
                'TieuDe' => $validated['title'],
                'NoiDungTomTat' => $validated['title'],
                'NgayTrinh' => now()->toDateString(),
                'LinhVuc' => 'Chương trình họp',
                'TrangThai' => 'SCHEDULED',
                'MaDonVi' => 1,
                'MaNguoiDung' => auth()->id() ?? 1,
            ]);
            $dossierId = $defaultDossier->MaHoSo;
        }

        // [BR-08] Liên kết Hồ sơ trình vào Phiên họp thông qua Chương trình họp (ThuTu xét duyệt > 0)
        $agenda = ChuongTrinhHop::create([
            'ThuTu' => $maxThuTu + 1,
            'TrangThai' => 'PENDING',
            'GhiChu' => $validated['title'],
            'MaPhienHop' => $id,
            'MaHoSo' => $dossierId,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đã thêm mục vào chương trình họp.',
            'data' => [
                'id' => $agenda->MaChuongTrinh,
                'item_order' => $agenda->ThuTu,
                'title' => $validated['title'],
                'presenter' => $validated['presenter'] ?? 'Đang cập nhật',
                'duration_minutes' => (int) ($validated['duration_minutes'] ?? 30),
                'dossier_id' => $dossierId,
                'is_voting_required' => (bool) ($validated['is_voting_required'] ?? false),
                'status' => 'PENDING',
            ],
            'errors' => null,
        ], 201);
    }

    /**
     * Xóa mục chương trình họp
     * DELETE /api/v1/meetings/{id}/agendas/{agendaId}
     */
    public function deleteAgenda(int|string $id, int|string $agendaId): JsonResponse
    {
        $agenda = ChuongTrinhHop::where('MaPhienHop', $id)->where('MaChuongTrinh', $agendaId)->first();
        if (! $agenda) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy mục chương trình để xóa.',
                'data' => null,
                'errors' => null,
            ], 404);
        }

        $agenda->delete();

        // Tái đánh số thứ tự
        $remaining = ChuongTrinhHop::where('MaPhienHop', $id)->orderBy('ThuTu')->get();
        foreach ($remaining as $idx => $item) {
            $item->update(['ThuTu' => $idx + 1]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã gỡ mục khỏi chương trình họp.',
            'data' => null,
            'errors' => null,
        ]);
    }

    /**
     * Thêm khách mời tham gia phiên họp
     * POST /api/v1/meetings/{id}/attendees
     */
    public function addGuest(Request $request, int|string $id): JsonResponse
    {
        $meeting = PhienHop::find($id);
        if (! $meeting) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy phiên họp.',
                'data' => null,
                'errors' => null,
            ], 404);
        }

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'department' => ['required', 'string', 'max:255'],
            'academic_title' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:191'],
            'phone' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string'],
        ]);

        // [BR-11] Cấu hình các trường NULLABLE cho khách mời (ChucVu, DonViCongTac, Email, SoDienThoai)
        $khachMoi = KhachMoi::firstOrCreate([
            'HoTen' => $validated['full_name'],
            'Email' => $validated['email'] ?? null,
        ], [
            'ChucVu' => $validated['academic_title'] ?? 'Khách mời',
            'DonViCongTac' => $validated['department'],
            'SoDienThoai' => $validated['phone'] ?? null,
        ]);

        // [BR-03] Ràng buộc bộ duy nhất: Cặp (MaKhachMoi, MaPhienHop) bắt buộc phải là UNIQUE
        $existingInvite = KhachMoiPhienHop::where('MaKhachMoi', $khachMoi->MaKhachMoi)
            ->where('MaPhienHop', $id)
            ->first();

        if ($existingInvite) {
            return response()->json([
                'success' => false,
                'message' => 'Khách mời này đã được mời tham gia phiên họp này trước đó (BR-03).',
            ], 422);
        }

        $moi = KhachMoiPhienHop::create([
            'NoiDungMoi' => $validated['notes'] ?? 'Tham dự theo lời mời của HĐT',
            'TrangThaiThamDu' => 'INVITED',
            'MaKhachMoi' => $khachMoi->MaKhachMoi,
            'MaPhienHop' => $id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đã thêm khách mời thành công.',
            'data' => [
                'id' => "att-guest-{$moi->MaMoi}",
                'user_id' => $khachMoi->MaKhachMoi,
                'full_name' => $khachMoi->HoTen,
                'academic_title' => $khachMoi->ChucVu,
                'role_in_council' => 'Khách mời chuyên môn',
                'department' => $khachMoi->DonViCongTac,
                'attendee_type' => 'GUEST',
                'can_vote' => false, // Ràng buộc cứng: Khách mời không được biểu quyết
                'attendance_status' => 'INVITED',
                'attendance_label' => 'Đã mời',
                'checkin_time' => null,
                'notes' => $moi->NoiDungMoi,
            ],
            'errors' => null,
        ], 201);
    }

    /**
     * [BR-05] Quản lý Tài liệu riêng của Phiên họp qua bảng trung gian TAI_LIEU_PHIEN_HOP
     * [BR-06] Khóa chính kép (MaTaiLieu, MaPhienHop)
     * POST /api/v1/meetings/{id}/documents
     */
    public function attachDocument(Request $request, int|string $id): JsonResponse
    {
        $meeting = PhienHop::find($id);
        if (! $meeting) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy phiên họp.',
            ], 404);
        }

        $validated = $request->validate([
            'document_id' => ['required', 'integer', 'exists:tai_lieu,MaTaiLieu'],
        ]);

        // [BR-05 & BR-06] Khóa chính kép (MaTaiLieu, MaPhienHop) cho tài liệu riêng của phiên họp
        $meeting->taiLieu()->syncWithoutDetaching([$validated['document_id']]);

        return response()->json([
            'success' => true,
            'message' => 'Đã liên kết tài liệu vào phiên họp thành công (BR-05).',
        ]);
    }

    /**
     * Cập nhật điểm danh đại biểu
     * POST /api/v1/meetings/{id}/checkin
     */
    public function checkin(Request $request, int|string $id): JsonResponse
    {
        $validated = $request->validate([
            'attendee_id' => ['required'],
            'status' => ['required', 'string', 'in:INVITED,PRESENT,ABSENT,EXCUSED'],
        ]);

        $attendeeId = $validated['attendee_id'];
        $status = $validated['status'];

        if (str_starts_with((string) $attendeeId, 'att-guest-')) {
            $maMoi = (int) str_replace('att-guest-', '', (string) $attendeeId);
            $guestMoi = KhachMoiPhienHop::where('MaPhienHop', $id)->where('MaMoi', $maMoi)->first();
            if ($guestMoi) {
                $guestMoi->update(['TrangThaiThamDu' => $status]);
            }
        } else {
            $thamDu = ThamDu::where('MaPhienHop', $id)->where('MaThamDu', $attendeeId)->first();
            if ($thamDu) {
                $thamDu->update([
                    'TrangThaiThamDu' => $status,
                    'ThoiDiemXacNhan' => now(),
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật điểm danh thành công.',
            'data' => null,
            'errors' => null,
        ]);
    }

    /**
     * Gửi giấy triệu tập phiên họp
     * POST /api/v1/meetings/{id}/send-invitations
     */
    public function sendInvitations(int|string $id): JsonResponse
    {
        $meeting = PhienHop::find($id);
        if (! $meeting) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy phiên họp.',
                'data' => null,
                'errors' => null,
            ], 404);
        }

        $meeting->update(['TrangThai' => 'SCHEDULED']);

        ThamDu::where('MaPhienHop', $id)->update(['TrangThaiThamDu' => 'INVITED']);

        return response()->json([
            'success' => true,
            'message' => 'Đã gửi giấy mời điện tử và mở quyền truy cập tài liệu cho toàn thể đại biểu.',
            'data' => $this->formatMeetingDetail($meeting->fresh()),
            'errors' => null,
        ]);
    }

    /**
     * Chuyển trạng thái phiên họp (PREPARING, SCHEDULED, IN_PROGRESS, COMPLETED, CANCELLED)
     * POST /api/v1/meetings/{id}/status
     */
    public function changeStatus(Request $request, int|string $id): JsonResponse
    {
        $meeting = PhienHop::find($id);
        if (! $meeting) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy phiên họp.',
                'data' => null,
                'errors' => null,
            ], 404);
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:PREPARING,SCHEDULED,IN_PROGRESS,COMPLETED,CANCELLED'],
        ]);

        $meeting->update(['TrangThai' => $validated['status']]);

        return response()->json([
            'success' => true,
            'message' => "Đã chuyển trạng thái phiên họp sang {$validated['status']}.",
            'data' => $this->formatMeetingDetail($meeting->fresh()),
            'errors' => null,
        ]);
    }

    /**
     * Format thông tin tóm tắt phiên họp cho danh sách
     */
    private function formatMeetingSummary(PhienHop $meeting): array
    {
        $statusLabels = [
            'PREPARING' => 'Đang chuẩn bị',
            'SCHEDULED' => 'Đã triệu tập',
            'IN_PROGRESS' => 'Đang diễn ra',
            'COMPLETED' => 'Đã bế mạc',
            'CANCELLED' => 'Đã hủy',
        ];

        $formatLabels = [
            'IN_PERSON' => 'Trực tiếp',
            'ONLINE' => 'Trực tuyến',
            'HYBRID' => 'Kết hợp',
        ];

        $startTimeStr = $meeting->ThoiGianBatDau ? date('H:i', strtotime($meeting->ThoiGianBatDau)) : '08:00';
        $endTimeStr = $meeting->ThoiGianKetThuc ? date('H:i', strtotime($meeting->ThoiGianKetThuc)) : '11:30';

        return [
            'id' => (string) $meeting->MaPhienHop,
            'title' => $meeting->TenPhienHop,
            'term' => 'Khóa IV (2020-2025)',
            'meeting_type' => 'REGULAR',
            'meeting_type_label' => 'Họp định kỳ',
            'meeting_format' => $meeting->HinhThuc,
            'meeting_format_label' => $formatLabels[$meeting->HinhThuc] ?? $meeting->HinhThuc,
            'meeting_date' => $meeting->NgayHop ? date('Y-m-d', strtotime($meeting->NgayHop)) : '',
            'start_time' => $startTimeStr,
            'end_time' => $endTimeStr,
            'location' => $meeting->DiaDiem,
            'chairperson' => 'PGS.TS. Trần Văn Chủ Tịch',
            'secretary' => 'ThS. Nguyễn Thị Thư Ký',
            'status' => $meeting->TrangThai,
            'status_label' => $statusLabels[$meeting->TrangThai] ?? $meeting->TrangThai,
            'description' => $meeting->GhiChu ?? '',
            'agendas_count' => (int) ($meeting->agendas_count ?? 0),
            'attendees_count' => (int) ($meeting->attendees_count ?? 0),
            'documents_count' => (int) ($meeting->documents_count ?? 0),
        ];
    }

    /**
     * Format chi tiết phiên họp đầy đủ
     */
    private function formatMeetingDetail(PhienHop $meeting): array
    {
        $summary = $this->formatMeetingSummary($meeting);

        // Agendas
        $agendas = $meeting->chuongTrinhHop ? $meeting->chuongTrinhHop->map(function ($ch) {
            return [
                'id' => (string) $ch->MaChuongTrinh,
                'item_order' => (int) $ch->ThuTu,
                'title' => $ch->GhiChu ?: ($ch->hoSoTrinh?->TieuDe ?? 'Nội dung thảo luận'),
                'presenter' => 'Đại diện phụ trách',
                'duration_minutes' => 30,
                'dossier_id' => $ch->MaHoSo ? (string) $ch->MaHoSo : null,
                'dossier_title' => $ch->hoSoTrinh?->TieuDe ?? null,
                'is_voting_required' => true,
                'status' => $ch->TrangThai,
            ];
        })->values()->toArray() : [];

        // Attendees (Thành viên chính thức + Khách mời)
        $attendees = [];
        if ($meeting->thamDu) {
            foreach ($meeting->thamDu as $td) {
                $user = $td->thanhVienHdt?->nguoiDung;
                $attendees[] = [
                    'id' => (string) $td->MaThamDu,
                    'user_id' => $user?->MaNguoiDung ?? $td->MaThanhVien,
                    'full_name' => $user?->HoTen ?? 'Thành viên Hội đồng trường',
                    'academic_title' => 'TS',
                    'role_in_council' => $td->thanhVienHdt?->ChucDanhHoiDongTruong ?? 'Thành viên HĐT',
                    'department' => $user?->donVi?->TenDonVi ?? 'Hội đồng trường',
                    'attendee_type' => 'OFFICIAL',
                    'can_vote' => $td->TrangThaiThamDu === 'PRESENT',
                    'attendance_status' => $td->TrangThaiThamDu,
                    'attendance_label' => match ($td->TrangThaiThamDu) {
                        'PRESENT' => 'Có mặt',
                        'ABSENT' => 'Vắng mặt',
                        'EXCUSED' => 'Vắng có lý do',
                        default => 'Đã mời'
                    },
                    'checkin_time' => $td->ThoiDiemXacNhan ? date('Y-m-d H:i', strtotime($td->ThoiDiemXacNhan)) : null,
                    'notes' => $td->GhiChu ?? '',
                ];
            }
        }

        if ($meeting->khachMoiPhienHop) {
            foreach ($meeting->khachMoiPhienHop as $kmph) {
                $km = $kmph->khachMoi;
                $attendees[] = [
                    'id' => "att-guest-{$kmph->MaMoi}",
                    'user_id' => $km?->MaKhachMoi,
                    'full_name' => $km?->HoTen ?? 'Khách mời chuyên môn',
                    'academic_title' => $km?->ChucVu ?? '',
                    'role_in_council' => 'Khách mời chuyên môn',
                    'department' => $km?->DonViCongTac ?? 'Đơn vị ngoài',
                    'attendee_type' => 'GUEST',
                    'can_vote' => false, // Tuyệt đối không có quyền biểu quyết
                    'attendance_status' => $kmph->TrangThaiThamDu,
                    'attendance_label' => match ($kmph->TrangThaiThamDu) {
                        'PRESENT' => 'Có mặt',
                        'ABSENT' => 'Vắng mặt',
                        'EXCUSED' => 'Vắng có lý do',
                        default => 'Đã mời'
                    },
                    'checkin_time' => null,
                    'notes' => $kmph->NoiDungMoi ?? '',
                ];
            }
        }

        // Documents
        $documents = $meeting->taiLieu ? $meeting->taiLieu->map(function ($doc) {
            return [
                'id' => (string) $doc->MaTaiLieu,
                'file_name' => $doc->TenTaiLieu,
                'file_size' => (int) $doc->DungLuong,
                'file_type' => $doc->DinhDang,
                'access_level' => $doc->LoaiTaiLieu,
                'access_level_label' => $doc->LoaiTaiLieu === 'INTERNAL' ? 'Nội bộ HĐT' : 'Công khai cho đại biểu',
                'uploaded_at' => $doc->NgayTaiLen ? date('Y-m-d H:i', strtotime($doc->NgayTaiLen)) : null,
                'uploaded_by' => 'ThS. Nguyễn Thị Thư Ký',
            ];
        })->values()->toArray() : [];

        // Votings
        $votings = [];
        if ($meeting->chuongTrinhHop) {
            foreach ($meeting->chuongTrinhHop as $ch) {
                if ($ch->noiDungBieuQuyet) {
                    foreach ($ch->noiDungBieuQuyet as $nd) {
                        $meta = json_decode($nd->GhiChu ?? '{}', true) ?: [];
                        $votingType = $meta['voting_type'] ?? 'SECRET';
                        $passThreshold = (int) ($meta['pass_threshold'] ?? 50);

                        $votes = $nd->ketQuaBieuQuyet ?? collect([]);
                        $totalCast = $votes->count();
                        $votesFor = $votes->where('LuaChon', 'VOTE_FOR')->count();
                        $votesAgainst = $votes->where('LuaChon', 'VOTE_AGAINST')->count();
                        $votesAbstain = $votes->where('LuaChon', 'VOTE_ABSTAIN')->count();

                        $statusLabels = [
                            'PENDING' => 'Chưa mở',
                            'OPENING' => 'Đang mở biểu quyết',
                            'CLOSED' => 'Đã đóng',
                        ];

                        $votings[] = [
                            'id' => (string) $nd->MaNoiDung,
                            'meeting_id' => (string) $meeting->MaPhienHop,
                            'agenda_id' => (string) $nd->MaChuongTrinh,
                            'title' => $nd->NoiDung,
                            'voting_type' => $votingType,
                            'voting_type_label' => $votingType === 'SECRET' ? 'Bỏ phiếu kín' : 'Biểu quyết công khai',
                            'pass_threshold' => "{$passThreshold}%",
                            'status' => $nd->TrangThai,
                            'status_label' => $statusLabels[$nd->TrangThai] ?? $nd->TrangThai,
                            'result' => $nd->KetQua,
                            'stats' => [
                                'total_votes' => $totalCast,
                                'votes_for' => $votesFor,
                                'votes_against' => $votesAgainst,
                                'votes_abstain' => $votesAbstain,
                            ],
                        ];
                    }
                }
            }
        }

        return array_merge($summary, [
            'agendas' => $agendas,
            'attendees' => $attendees,
            'documents' => $documents,
            'votings' => $votings,
        ]);
    }
}
