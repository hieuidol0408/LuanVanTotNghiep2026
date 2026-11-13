<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ChuongTrinhHop;
use App\Models\KetQuaBieuQuyet;
use App\Models\NoiDungBieuQuyet;
use App\Models\PhienHop;
use App\Models\ThamDu;
use App\Models\ThanhVienHdt;
use App\Models\UyQuyenBieuQuyet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BieuQuyetWebController extends Controller
{
    /**
     * Danh sách các nội dung biểu quyết (Lọc theo phiên họp, trạng thái, tìm kiếm)
     */
    public function index(Request $request): View
    {
        $query = NoiDungBieuQuyet::with([
            'chuongTrinhHop.phienHop',
            'ketQuaBieuQuyet',
        ]);

        // 1. Tìm kiếm theo nội dung
        if ($search = $request->input('search')) {
            $query->where('NoiDung', 'like', "%{$search}%");
        }

        // 2. Lọc theo phiên họp
        if ($meetingId = $request->input('meeting_id')) {
            if ($meetingId !== 'ALL') {
                $query->whereHas('chuongTrinhHop', function ($q) use ($meetingId) {
                    $q->where('MaPhienHop', $meetingId);
                });
            }
        }

        // 3. Lọc theo trạng thái
        if ($status = $request->input('status')) {
            if ($status !== 'ALL') {
                if ($status === 'OPENING') {
                    $query->whereIn('TrangThai', ['OPENING', 'Đang mở biểu quyết', 'Đang mở']);
                } elseif ($status === 'CLOSED') {
                    $query->whereIn('TrangThai', ['CLOSED', 'Đã đóng', 'Đã kết thúc']);
                } elseif ($status === 'PENDING') {
                    $query->whereIn('TrangThai', ['PENDING', 'Chưa mở', 'Dự thảo']);
                } else {
                    $query->where('TrangThai', $status);
                }
            }
        }

        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [5, 10, 20, 50, 100])) {
            $perPage = 10;
        }

        $votings = $query->orderBy('MaNoiDung', 'asc')
            ->paginate($perPage)
            ->withQueryString();

        $meetings = PhienHop::orderBy('MaPhienHop', 'asc')->get();
        $agendaItems = ChuongTrinhHop::with(['phienHop', 'hoSoTrinh'])
            ->orderBy('MaPhienHop', 'asc')
            ->orderBy('ThuTu')
            ->get();
        $role = Auth::user()?->role ?? (session('user_role') ?? 'CHAIR');

        // Thống kê nhanh
        $allVotes = NoiDungBieuQuyet::with('ketQuaBieuQuyet')->get();
        $passedCount = $allVotes->filter(function ($v) {
            $total = $v->ketQuaBieuQuyet->count();
            if ($total === 0) {
                return false;
            }
            $agree = $v->ketQuaBieuQuyet->filter(fn ($k) => in_array($k->LuaChon, ['VOTE_FOR', 'Tán thành']))->count();
            return ($agree / $total) >= 0.5;
        })->count();

        $stats = [
            'total' => $allVotes->count(),
            'opening' => NoiDungBieuQuyet::whereIn('TrangThai', ['OPENING', 'Đang mở biểu quyết', 'Đang mở'])->count(),
            'closed' => NoiDungBieuQuyet::whereIn('TrangThai', ['CLOSED', 'Đã đóng'])->count(),
            'passed' => $passedCount,
        ];

        return view('pages.bieuquyet.danhsach', compact('votings', 'meetings', 'agendaItems', 'role', 'stats'));
    }

    /**
     * Khởi tạo nội dung biểu quyết mới (Dành cho Thư ký, Chủ tịch, Quản trị viên)
     */
    public function store(Request $request): RedirectResponse
    {
        $role = Auth::user()?->role ?? (session('user_role') ?? 'CHAIR');
        if (! in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN'])) {
            return back()->with('error', 'Bạn không có thẩm quyền tạo nội dung biểu quyết.');
        }

        $validated = $request->validate([
            'MaChuongTrinh' => ['required', 'integer', 'exists:chuong_trinh_hop,MaChuongTrinh'],
            'NoiDung' => ['required', 'string', 'min:5', 'max:255'],
        ], [
            'MaChuongTrinh.required' => 'Vui lòng chọn mục chương trình họp liên kết.',
            'MaChuongTrinh.exists' => 'Mục chương trình họp không hợp lệ.',
            'NoiDung.required' => 'Vui lòng nhập câu hỏi / nội dung biểu quyết.',
            'NoiDung.min' => 'Nội dung biểu quyết phải có ít nhất 5 ký tự.',
            'NoiDung.max' => 'Nội dung biểu quyết không được vượt quá 255 ký tự.',
        ]);

        $vote = NoiDungBieuQuyet::create([
            'NoiDung' => trim($validated['NoiDung']),
            'ThoiGianMo' => null,
            'ThoiGianDong' => now()->addMinutes(30),
            'TrangThai' => 'Chưa mở',
            'MaChuongTrinh' => $validated['MaChuongTrinh'],
        ]);

        return redirect()->route('votes.show', $vote->MaNoiDung)
            ->with('success', 'Đã khởi tạo nội dung biểu quyết mới thành công!');
    }

    /**
     * Chi tiết nội dung biểu quyết & Phòng bỏ phiếu bảo mật (BR-09, BR-04)
     */
    public function show(Request $request, int|string $id): View|RedirectResponse
    {
        $vote = NoiDungBieuQuyet::with([
            'chuongTrinhHop.phienHop.thamDu.thanhVienHdt.nguoiDung',
            'ketQuaBieuQuyet',
        ])->findOrFail($id);

        $user = Auth::user();
        $role = $user?->role ?? (session('user_role') ?? 'CHAIR');
        $meeting = $vote->chuongTrinhHop?->phienHop;

        // Đọc cấu hình ngưỡng thông qua & hình thức biểu quyết từ GhiChu
        $meta = json_decode($vote->GhiChu ?? '{}', true) ?: [];
        $votingType = $meta['voting_type'] ?? 'SECRET'; // 'SECRET' (bỏ phiếu kín) | 'OPEN' (công khai)
        $passThreshold = (int) ($meta['pass_threshold'] ?? 50);

        // 1. Tổng hợp kết quả bỏ phiếu (Tuyệt đối không lộ danh tính cá nhân - BR-09)
        $votes = $vote->ketQuaBieuQuyet;
        $totalVotes = $votes->count();
        $agreeVotes = $votes->filter(fn ($v) => in_array($v->LuaChon, ['VOTE_FOR', 'Tán thành']))->count();
        $disagreeVotes = $votes->filter(fn ($v) => in_array($v->LuaChon, ['VOTE_AGAINST', 'Không tán thành']))->count();
        $abstainVotes = $votes->filter(fn ($v) => in_array($v->LuaChon, ['VOTE_ABSTAIN', 'Không có ý kiến', 'Không ý kiến']))->count();

        // Tính mẫu số theo số thành viên có mặt tại phiên họp
        $presentOfficialCount = 0;
        if ($meeting) {
            $presentOfficialCount = ThamDu::where('MaPhienHop', $meeting->MaPhienHop)
                ->whereIn('TrangThaiThamDu', ['PRESENT', 'Có mặt'])
                ->count();
        }
        $denominator = $presentOfficialCount > 0 ? $presentOfficialCount : max($totalVotes, 1);
        $agreePercent = round(($agreeVotes / $denominator) * 100, 1);

        $voteStats = [
            'total' => $totalVotes,
            'present_count' => $presentOfficialCount,
            'agree' => $agreeVotes,
            'agree_percent' => $agreePercent,
            'disagree' => $disagreeVotes,
            'disagree_percent' => round(($disagreeVotes / max($totalVotes, 1)) * 100, 1),
            'abstain' => $abstainVotes,
            'abstain_percent' => round(($abstainVotes / max($totalVotes, 1)) * 100, 1),
            'pass_threshold' => $passThreshold,
            'is_passed' => $agreePercent >= $passThreshold,
        ];

        // 2. Kiểm tra điều kiện bỏ phiếu của người dùng hiện tại
        $canVoteDirect = false;
        $hasVotedDirect = false;
        $directChoice = null;
        $validDelegations = [];

        if ($user && $meeting) {
            $member = ThanhVienHdt::where('MaNguoiDung', $user->MaNguoiDung)->first();
            if ($member && ($member->CoQuyenBieuQuyet ?? true)) {
                $attendance = ThamDu::where('MaPhienHop', $meeting->MaPhienHop)
                    ->where('MaThanhVien', $member->MaThanhVien)
                    ->first();

                // Phải có mặt (PRESENT) tại phiên họp
                if ($attendance && in_array($attendance->TrangThaiThamDu, ['PRESENT', 'Có mặt'])) {
                    $canVoteDirect = true;

                    // Kiểm tra phiếu trực tiếp đã bỏ
                    $myVote = KetQuaBieuQuyet::where('MaNoiDung', $vote->MaNoiDung)
                        ->where('MaThamDu', $attendance->MaThamDu)
                        ->whereNull('MaUyQuyen')
                        ->first();

                    if ($myVote) {
                        $hasVotedDirect = true;
                        $directChoice = $myVote->LuaChon;
                    }

                    // [BR-04] Kiểm tra các quyền ủy quyền hợp lệ mà thành viên này được nhận
                    $authList = UyQuyenBieuQuyet::with('thanhVienUyQuyen.nguoiDung')
                        ->where('MaPhienHop', $meeting->MaPhienHop)
                        ->where('MaThanhVienNhanUyQuyen', $member->MaThanhVien)
                        ->whereIn('TrangThai', ['Hợp lệ', 'ACTIVE'])
                        ->get();

                    foreach ($authList as $auth) {
                        $proxyVote = KetQuaBieuQuyet::where('MaNoiDung', $vote->MaNoiDung)
                            ->where('MaUyQuyen', $auth->MaUyQuyen)
                            ->first();

                        $validDelegations[] = [
                            'auth_id' => $auth->MaUyQuyen,
                            'authorizer_name' => $auth->thanhVienUyQuyen?->nguoiDung?->HoTen ?? 'Thành viên ủy quyền',
                            'scope' => $auth->PhamViUyQuyen,
                            'document' => $auth->TaiLieuUyQuyen,
                            'has_voted' => (bool) $proxyVote,
                            'choice' => $proxyVote?->LuaChon,
                        ];
                    }
                }
            }
        }

        // Danh sách thành viên phục vụ Modal tạo ủy quyền mới (BR-04)
        $officialMembers = $meeting ? ThanhVienHdt::with('nguoiDung')->get() : collect([]);

        return view('pages.bieuquyet.chitiet', compact(
            'vote',
            'meeting',
            'votingType',
            'passThreshold',
            'voteStats',
            'canVoteDirect',
            'hasVotedDirect',
            'directChoice',
            'validDelegations',
            'officialMembers',
            'role'
        ));
    }

    /**
     * Mở cổng biểu quyết (Dành cho Thư ký, Chủ tịch, Quản trị viên)
     */
    public function open(int|string $id): RedirectResponse
    {
        $role = Auth::user()?->role ?? (session('user_role') ?? 'CHAIR');
        if (! in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN'])) {
            return back()->with('error', 'Bạn không có thẩm quyền mở biểu quyết.');
        }

        $vote = NoiDungBieuQuyet::findOrFail($id);
        $vote->update([
            'TrangThai' => 'Đang mở biểu quyết',
            'ThoiGianMo' => now(),
        ]);

        return back()->with('success', 'Đã mở cổng biểu quyết thành công. Các thành viên có mặt có thể tiến hành bỏ phiếu.');
    }

    /**
     * Đóng cổng biểu quyết và tính toán kết quả tự động (BR-09)
     */
    public function close(int|string $id): RedirectResponse
    {
        $role = Auth::user()?->role ?? (session('user_role') ?? 'CHAIR');
        if (! in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN'])) {
            return back()->with('error', 'Bạn không có thẩm quyền đóng biểu quyết.');
        }

        $vote = NoiDungBieuQuyet::with(['chuongTrinhHop.phienHop', 'ketQuaBieuQuyet'])->findOrFail($id);
        $passThreshold = 50;

        $votes = $vote->ketQuaBieuQuyet;
        $totalCast = $votes->count();
        $votesFor = $votes->filter(fn ($v) => in_array($v->LuaChon, ['VOTE_FOR', 'Tán thành']))->count();

        // Lấy số thành viên HĐT có mặt tại phiên họp
        $phienHop = $vote->chuongTrinhHop?->phienHop;
        $presentOfficialCount = 0;
        if ($phienHop) {
            $presentOfficialCount = ThamDu::where('MaPhienHop', $phienHop->MaPhienHop)
                ->whereIn('TrangThaiThamDu', ['PRESENT', 'Có mặt'])
                ->count();
        }

        $denominator = $presentOfficialCount > 0 ? $presentOfficialCount : max($totalCast, 1);
        $agreePercent = round(($votesFor / $denominator) * 100, 1);

        $isPassed = $agreePercent >= $passThreshold;
        $resultVietnamese = $isPassed ? 'Đã thông qua' : 'Không thông qua';

        $vote->update([
            'TrangThai' => 'Đã đóng',
            'ThoiGianDong' => now(),
        ]);

        return back()->with('success', "Đã kết thúc biểu quyết! Kết quả: {$resultVietnamese} (Tỷ lệ tán thành: {$agreePercent}% - Ngưỡng yêu cầu: {$passThreshold}%).");
    }

    /**
     * Ẩn nội dung biểu quyết (Soft Delete - Không xóa cứng khỏi CSDL)
     */
    public function destroy(int|string $id): RedirectResponse
    {
        $role = Auth::user()?->role ?? (session('user_role') ?? 'CHAIR');
        if (! in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN'])) {
            return back()->with('error', 'Bạn không có thẩm quyền ẩn nội dung biểu quyết.');
        }

        $vote = NoiDungBieuQuyet::findOrFail($id);
        $vote->update(['TrangThai' => 'Đã ẩn']);
        $vote->delete();

        return redirect()->route('votes.index')
            ->with('success', 'Đã ẩn nội dung biểu quyết #' . $id . ' khỏi giao diện (Soft Delete).');
    }

    /**
     * Gửi phiếu biểu quyết (Hỗ trợ bỏ phiếu trực tiếp và bỏ phiếu theo ủy quyền BR-04, BR-09, BR-11)
     */
    public function vote(Request $request, int|string $id): RedirectResponse
    {
        $user = Auth::user();
        $vote = NoiDungBieuQuyet::with('chuongTrinhHop.phienHop')->findOrFail($id);

        // 1. Kiểm tra trạng thái mở
        if (! in_array($vote->TrangThai, ['OPENING', 'Đang mở', 'Đang mở biểu quyết'])) {
            return back()->with('error', 'Biểu quyết này hiện không ở trạng thái mở để nhận phiếu.');
        }

        $validated = $request->validate([
            'choice' => ['required', 'string', 'in:VOTE_FOR,VOTE_AGAINST,VOTE_ABSTAIN,Tán thành,Không tán thành,Không có ý kiến'],
            'authorization_id' => ['nullable', 'integer', 'exists:uy_quyen_bieu_quyet,MaUyQuyen'],
        ], [
            'choice.required' => 'Vui lòng chọn phương án biểu quyết.',
        ]);

        // Chuẩn hóa lựa chọn tiếng Việt thống nhất
        $choiceNormalized = match ($validated['choice']) {
            'VOTE_FOR', 'Tán thành' => 'Tán thành',
            'VOTE_AGAINST', 'Không tán thành' => 'Không tán thành',
            default => 'Không có ý kiến',
        };

        // 2. Kiểm tra thành viên HĐT chính thức
        $member = ThanhVienHdt::where('MaNguoiDung', $user?->MaNguoiDung)->first();
        if (! $member) {
            return back()->with('error', 'Chỉ thành viên chính thức của Hội đồng trường mới có quyền bỏ phiếu.');
        }

        if (isset($member->CoQuyenBieuQuyet) && ! $member->CoQuyenBieuQuyet) {
            return back()->with('error', 'Thành viên này không có quyền biểu quyết theo quy chế hoạt động (BR-07).');
        }

        $meeting = $vote->chuongTrinhHop?->phienHop;
        if (! $meeting) {
            return back()->with('error', 'Không tìm thấy phiên họp liên kết.');
        }

        $attendance = ThamDu::where('MaPhienHop', $meeting->MaPhienHop)
            ->where('MaThanhVien', $member->MaThanhVien)
            ->first();

        if (! $attendance || ! in_array($attendance->TrangThaiThamDu, ['PRESENT', 'Có mặt'])) {
            return back()->with('error', 'Bạn chưa được xác nhận có mặt tại phiên họp nên không thể thực hiện biểu quyết.');
        }

        $authId = $validated['authorization_id'] ?? null;

        // 3. Phân nhánh xử lý: Bỏ phiếu theo ủy quyền hay Bỏ phiếu trực tiếp
        if ($authId) {
            // [BR-04] Bỏ phiếu theo ủy quyền
            $auth = UyQuyenBieuQuyet::with('thanhVienUyQuyen.nguoiDung')->findOrFail($authId);

            if ($auth->MaPhienHop != $meeting->MaPhienHop || $auth->MaThanhVienNhanUyQuyen != $member->MaThanhVien) {
                return back()->with('error', 'Giấy ủy quyền này không hợp lệ hoặc bạn không phải người nhận ủy quyền.');
            }

            if (! in_array($auth->TrangThai, ['Hợp lệ', 'ACTIVE'])) {
                return back()->with('error', 'Giấy ủy quyền này chưa được duyệt hoặc đã hết hiệu lực.');
            }

            // Chống bỏ phiếu 2 lần cho cùng một giấy ủy quyền
            $existingProxyVote = KetQuaBieuQuyet::where('MaNoiDung', $id)
                ->where('MaUyQuyen', $authId)
                ->first();

            if ($existingProxyVote) {
                return back()->with('warning', 'Văn bản ủy quyền này đã được thực hiện bỏ phiếu trước đó.');
            }

            KetQuaBieuQuyet::create([
                'LuaChon' => $choiceNormalized,
                'ThoiDiem' => now(),
                'MaNoiDung' => $id,
                'MaThamDu' => $attendance->MaThamDu,
                'MaUyQuyen' => $authId, // [BR-11] Ghi nhận khi bỏ phiếu theo ủy quyền
            ]);

            $authorizerName = $auth->thanhVienUyQuyen?->nguoiDung?->HoTen ?? 'Thành viên ủy quyền';
            return back()->with('success', "Đã ghi nhận phiếu biểu quyết theo ủy quyền của [{$authorizerName}] thành công.");
        } else {
            // Bỏ phiếu trực tiếp cho chính mình (Single Vote Constraint)
            $existingDirectVote = KetQuaBieuQuyet::where('MaNoiDung', $id)
                ->where('MaThamDu', $attendance->MaThamDu)
                ->whereNull('MaUyQuyen')
                ->first();

            if ($existingDirectVote) {
                return back()->with('warning', 'Bạn đã bỏ phiếu trực tiếp cho nội dung này rồi (Mỗi thành viên chỉ được bỏ phiếu 1 lần).');
            }

            KetQuaBieuQuyet::create([
                'LuaChon' => $choiceNormalized,
                'ThoiDiem' => now(),
                'MaNoiDung' => $id,
                'MaThamDu' => $attendance->MaThamDu,
                'MaUyQuyen' => null, // [BR-11] Bắt buộc NULL khi bỏ phiếu trực tiếp
            ]);

            return back()->with('success', 'Đã ghi nhận phiếu biểu quyết của bạn thành công (Bảo mật tuyệt đối danh tính theo BR-09).');
        }
    }

    /**
     * Đăng ký văn bản ủy quyền biểu quyết mới (BR-04)
     */
    public function storeDelegation(Request $request): RedirectResponse
    {
        $role = Auth::user()?->role ?? (session('user_role') ?? 'CHAIR');
        if (! in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN', 'MEMBER'])) {
            return back()->with('error', 'Bạn không có quyền thực hiện ủy quyền biểu quyết.');
        }

        $validated = $request->validate([
            'MaPhienHop' => ['required', 'integer', 'exists:phien_hop,MaPhienHop'],
            'MaThanhVienUyQuyen' => ['required', 'integer', 'exists:thanh_vien_hdt,MaThanhVien'],
            'MaThanhVienNhanUyQuyen' => ['required', 'integer', 'exists:thanh_vien_hdt,MaThanhVien', 'different:MaThanhVienUyQuyen'],
            'PhamViUyQuyen' => ['required', 'string', 'max:255'],
            'NgayUyQuyen' => ['required', 'date'],
            'file' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx'],
        ], [
            'MaThanhVienNhanUyQuyen.different' => 'Người nhận ủy quyền không được trùng với người ủy quyền.',
            'PhamViUyQuyen.required' => 'Vui lòng nhập phạm vi ủy quyền.',
            'NgayUyQuyen.required' => 'Vui lòng chọn ngày lập văn bản ủy quyền.',
        ]);

        $taiLieuPath = null;
        if ($request->hasFile('file')) {
            $uploaded = $request->file('file');
            $fileName = 'uy_quyen_' . time() . '.' . $uploaded->getClientOriginalExtension();
            $path = $uploaded->storeAs('documents/delegations', $fileName, 'public');
            $taiLieuPath = '/storage/' . $path;
        }

        UyQuyenBieuQuyet::create([
            'NgayUyQuyen' => $validated['NgayUyQuyen'],
            'PhamViUyQuyen' => $validated['PhamViUyQuyen'],
            'TaiLieuUyQuyen' => $taiLieuPath ?? 'Văn bản ủy quyền tại chỗ',
            'TrangThai' => 'Hợp lệ',
            'MaPhienHop' => $validated['MaPhienHop'],
            'MaThanhVienUyQuyen' => $validated['MaThanhVienUyQuyen'],
            'MaThanhVienNhanUyQuyen' => $validated['MaThanhVienNhanUyQuyen'],
        ]);

        return back()->with('success', 'Tiếp nhận văn bản ủy quyền biểu quyết thành công (Tuân thủ BR-04).');
    }
}
