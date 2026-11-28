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
use App\Support\DuplicateNameChecker;
use App\Support\SearchCodeHelper;
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
            'nghiQuyet',
        ]);

        // 1. Tìm kiếm theo nội dung hoặc mã biểu quyết (#BQ-001, #1, 1)
        if ($search = trim((string) $request->input('search'))) {
            $votingId = SearchCodeHelper::extractNumericId($search, ['bq']);

            $query->where(function ($q) use ($search, $votingId) {
                $q->where('NoiDung', 'like', "%{$search}%");

                if ($votingId !== null) {
                    $q->orWhere('MaNoiDung', $votingId);
                }
            });
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
        $allVotes = NoiDungBieuQuyet::with([
            'chuongTrinhHop.phienHop.thamDu.thanhVienHdt',
            'ketQuaBieuQuyet',
        ])->get();
        $passedCount = $allVotes->filter(function ($v) {
            $stats = $v->calculateStats();
            return $stats['total'] > 0 && $stats['is_passed'];
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

        $trimmedContent = trim($validated['NoiDung']);
        if (DuplicateNameChecker::exists(NoiDungBieuQuyet::query(), 'NoiDung', $trimmedContent)) {
            return DuplicateNameChecker::failWeb(
                'NoiDung',
                "Nội dung biểu quyết \"{$trimmedContent}\" đã tồn tại trong hệ thống. Vui lòng nhập tên / nội dung khác."
            );
        }

        $vote = NoiDungBieuQuyet::create([
            'NoiDung' => $trimmedContent,
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
            'nghiQuyet.phienHop',
        ])->findOrFail($id);

        $user = Auth::user();
        $role = $user?->role ?? (session('user_role') ?? 'CHAIR');
        $meeting = $vote->chuongTrinhHop?->phienHop;

        // Đọc cấu hình ngưỡng thông qua & hình thức biểu quyết từ GhiChu và tổng hợp kết quả thống nhất (BR-07, BR-09)
        $voteStats = $vote->calculateStats();
        $votingType = $voteStats['voting_type'];
        $passThreshold = $voteStats['pass_threshold'];

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

        // Kiểm tra quyền quyết định khi hòa phiếu: chỉ người đã mở cổng biểu quyết (hoặc người điều hành nếu dữ liệu cũ chưa lưu người mở)
        $openedByUserId = $voteStats['opened_by_user_id'] ?? null;
        $canDecideTie = $user && (
            ($openedByUserId && (int) $user->MaNguoiDung === (int) $openedByUserId)
            || (! $openedByUserId && in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN']))
        );

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
            'canDecideTie',
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

        $vote = NoiDungBieuQuyet::with('chuongTrinhHop')->findOrFail($id);
        $user = Auth::user();
        $meta = $vote->decodeMeta();
        $meta['opened_by_user_id'] = $user?->MaNguoiDung;
        $meta['opened_by_name'] = $user?->HoTen ?? 'Người mở cổng biểu quyết';
        unset($meta['tie_decision'], $meta['decided_by_user_id'], $meta['decided_by_name'], $meta['decided_at']);

        $vote->update([
            'TrangThai' => 'Đang mở biểu quyết',
            'ThoiGianMo' => now(),
            'KetQua' => null,
            'GhiChu' => json_encode($meta, JSON_UNESCAPED_UNICODE),
        ]);

        $msg = 'Đã mở cổng biểu quyết thành công. Các thành viên có mặt có thể tiến hành bỏ phiếu.';
        if (str_contains((string) url()->previous(), '/meetings/') && $vote->chuongTrinhHop?->MaPhienHop) {
            return redirect()->route('meetings.show', ['id' => $vote->chuongTrinhHop->MaPhienHop, 'tab' => 'voting'])->with('success', $msg);
        }

        return back()->with('success', $msg);
    }

    /**
     * Đóng cổng biểu quyết và tính toán kết quả tự động (BR-09)
     * Nếu hòa phiếu (Tán thành == Không tán thành > 0), chuyển sang chờ người mở cổng quyết định.
     */
    public function close(int|string $id): RedirectResponse
    {
        $role = Auth::user()?->role ?? (session('user_role') ?? 'CHAIR');
        if (! in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN'])) {
            return back()->with('error', 'Bạn không có thẩm quyền đóng biểu quyết.');
        }

        $vote = NoiDungBieuQuyet::with(['chuongTrinhHop.phienHop.thamDu.thanhVienHdt', 'ketQuaBieuQuyet'])->findOrFail($id);
        $stats = $vote->calculateStats();
        $passThreshold = $stats['pass_threshold'];
        $agreePercent = $stats['agree_percent'];

        if ($stats['is_tied'] && $stats['tie_decision'] === null) {
            $vote->update([
                'TrangThai' => 'Đã đóng',
                'ThoiGianDong' => now(),
                'KetQua' => 'Chờ quyết định',
            ]);

            $openerName = $stats['opened_by_name'] ?? 'người mở cổng biểu quyết';
            $tieMsg = "Đã đóng cổng biểu quyết! Số phiếu Tán thành ({$stats['agree']}) và Không tán thành ({$stats['disagree']}) đang bằng nhau. Kết quả cuối cùng sẽ do {$openerName} (người mở cổng biểu quyết) quyết định.";

            if (str_contains((string) url()->previous(), '/meetings/') && $vote->chuongTrinhHop?->MaPhienHop) {
                return redirect()->route('meetings.show', ['id' => $vote->chuongTrinhHop->MaPhienHop, 'tab' => 'voting'])->with('warning', $tieMsg);
            }

            return back()->with('warning', $tieMsg);
        }

        $resultVietnamese = $stats['is_passed'] ? 'Đã thông qua' : 'Không thông qua';

        $vote->update([
            'TrangThai' => 'Đã đóng',
            'ThoiGianDong' => now(),
            'KetQua' => $resultVietnamese,
        ]);

        $closeMsg = "Đã kết thúc biểu quyết! Kết quả: {$resultVietnamese} (Tỷ lệ tán thành: {$agreePercent}% - Ngưỡng yêu cầu: {$passThreshold}%).";
        if (str_contains((string) url()->previous(), '/meetings/') && $vote->chuongTrinhHop?->MaPhienHop) {
            return redirect()->route('meetings.show', ['id' => $vote->chuongTrinhHop->MaPhienHop, 'tab' => 'voting'])->with('success', $closeMsg);
        }

        return back()->with('success', $closeMsg);
    }

    /**
     * Chốt kết quả cuối cùng trong trường hợp hòa phiếu (tỷ lệ Tán thành == Không tán thành)
     * Quyền thuộc về người đã mở cổng biểu quyết.
     */
    public function decideTie(Request $request, int|string $id): RedirectResponse
    {
        $validated = $request->validate([
            'decision' => ['required', 'string', 'in:PASSED,REJECTED'],
        ]);

        $vote = NoiDungBieuQuyet::with(['chuongTrinhHop.phienHop.thamDu.thanhVienHdt', 'ketQuaBieuQuyet'])->findOrFail($id);
        $rawStatus = strtoupper(trim((string) ($vote->getRawOriginal('TrangThai') ?: $vote->TrangThai)));
        $isClosed = in_array($vote->TrangThai, ['CLOSED', 'Đã đóng']) || in_array($rawStatus, ['CLOSED', 'ĐÃ ĐÓNG']);

        if (! $isClosed) {
            return back()->with('error', 'Vui lòng đóng cổng biểu quyết trước khi đưa ra quyết định hòa phiếu.');
        }

        $stats = $vote->calculateStats();
        if (! $stats['is_tied']) {
            return back()->with('error', 'Nội dung biểu quyết này không ở trạng thái hòa phiếu.');
        }

        $user = Auth::user();
        $role = $user?->role ?? (session('user_role') ?? 'CHAIR');
        $openedByUserId = $stats['opened_by_user_id'] ?? null;

        if ($openedByUserId && (int) $user?->MaNguoiDung !== (int) $openedByUserId) {
            $openerName = $stats['opened_by_name'] ?? 'người mở cổng biểu quyết';
            return back()->with('error', "Chỉ người đã mở cổng biểu quyết ({$openerName}) mới có quyền quyết định kết quả khi tỷ lệ phiếu bằng nhau.");
        }

        if (! $openedByUserId && ! in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN'])) {
            return back()->with('error', 'Bạn không có thẩm quyền quyết định kết quả biểu quyết.');
        }

        $meta = $vote->decodeMeta();
        $meta['tie_decision'] = $validated['decision'];
        $meta['decided_by_user_id'] = $user?->MaNguoiDung;
        $meta['decided_by_name'] = $user?->HoTen ?? ($stats['opened_by_name'] ?? 'Người mở cổng biểu quyết');
        $meta['decided_at'] = now()->format('H:i d/m/Y');

        $resultVietnamese = $validated['decision'] === 'PASSED' ? 'Đã thông qua' : 'Không thông qua';

        $vote->update([
            'KetQua' => $resultVietnamese,
            'GhiChu' => json_encode($meta, JSON_UNESCAPED_UNICODE),
        ]);

        return back()->with('success', "Đã ghi nhận quyết định của người mở cổng biểu quyết: {$resultVietnamese} (Hòa phiếu {$stats['agree']} - {$stats['disagree']}).");
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
        $rawStatus = strtoupper(trim((string) ($vote->getRawOriginal('TrangThai') ?: $vote->TrangThai)));
        if (! in_array($rawStatus, ['OPENING', 'ĐANG MỞ', 'ĐANG MỞ BIỂU QUYẾT']) && ! in_array($vote->TrangThai, ['OPENING', 'Đang mở', 'Đang mở biểu quyết'])) {
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
