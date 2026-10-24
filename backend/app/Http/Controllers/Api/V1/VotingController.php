<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ChuongTrinhHop;
use App\Models\KetQuaBieuQuyet;
use App\Models\NoiDungBieuQuyet;
use App\Models\PhienHop;
use App\Models\ThamDu;
use App\Models\ThanhVienHdt;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VotingController extends Controller
{
    /**
     * Danh sách các nội dung biểu quyết của phiên họp
     * GET /api/v1/meetings/{meetingId}/votings
     */
    public function index(int|string $meetingId): JsonResponse
    {
        $meeting = PhienHop::find($meetingId);
        if (! $meeting) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy phiên họp.',
                'data' => null,
                'errors' => null,
            ], 404);
        }

        $agendaIds = ChuongTrinhHop::where('MaPhienHop', $meetingId)->pluck('MaChuongTrinh');

        $votings = NoiDungBieuQuyet::with(['chuongTrinhHop', 'ketQuaBieuQuyet'])
            ->whereIn('MaChuongTrinh', $agendaIds)
            ->orderBy('MaNoiDung', 'asc')
            ->get();

        $items = $votings->map(fn (NoiDungBieuQuyet $vote) => $this->formatVotingSummary($vote, $meetingId));

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách nội dung biểu quyết thành công.',
            'data' => $items,
            'errors' => null,
        ]);
    }

    /**
     * Tạo nội dung biểu quyết mới cho mục chương trình
     * POST /api/v1/meetings/{meetingId}/votings
     */
    public function store(Request $request, int|string $meetingId): JsonResponse
    {
        $user = $request->user();
        if (! in_array($user?->role, ['CHAIR', 'SECRETARY', 'ADMIN'])) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có thẩm quyền tạo nội dung biểu quyết.',
                'data' => null,
                'errors' => null,
            ], 403);
        }

        $validated = $request->validate([
            'agenda_id' => ['required', 'integer', 'exists:chuong_trinh_hop,MaChuongTrinh'],
            'title' => ['required', 'string', 'min:5', 'max:255'],
            'voting_type' => ['nullable', 'string', 'in:OPEN,SECRET'],
            'pass_threshold' => ['nullable', 'integer', 'min:50', 'max:100'],
            'notes' => ['nullable', 'string'],
        ], [
            'agenda_id.required' => 'Vui lòng chọn mục chương trình họp liên kết.',
            'title.required' => 'Vui lòng nhập nội dung biểu quyết.',
        ]);

        $votingType = $validated['voting_type'] ?? 'SECRET';
        $passThreshold = $validated['pass_threshold'] ?? 50;

        $vote = NoiDungBieuQuyet::create([
            'NoiDung' => $validated['title'],
            'ThoiGianMo' => null,
            'ThoiGianDong' => now()->addMinutes(30),
            'TrangThai' => 'PENDING',
            'KetQua' => 'PENDING',
            'GhiChu' => json_encode([
                'voting_type' => $votingType,
                'pass_threshold' => $passThreshold,
                'user_notes' => $validated['notes'] ?? null,
            ]),
            'MaChuongTrinh' => $validated['agenda_id'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tạo nội dung biểu quyết thành công.',
            'data' => $this->formatVotingSummary($vote, $meetingId),
            'errors' => null,
        ], 201);
    }

    /**
     * Chi tiết nội dung biểu quyết và trạng thái bỏ phiếu của cử tri
     * GET /api/v1/votings/{id}
     */
    public function show(Request $request, int|string $id): JsonResponse
    {
        $user = $request->user();
        $vote = NoiDungBieuQuyet::with(['chuongTrinhHop.phienHop', 'ketQuaBieuQuyet.thamDu.thanhVienHdt.nguoiDung'])->find($id);

        if (! $vote) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy nội dung biểu quyết.',
                'data' => null,
                'errors' => null,
            ], 404);
        }

        $phienHop = $vote->chuongTrinhHop?->phienHop;
        $meetingId = $phienHop?->MaPhienHop ?? 1;

        // Kiểm tra quyền biểu quyết của user hiện tại
        $canVote = false;
        $hasVoted = false;
        $userChoice = null;

        $thanhVien = ThanhVienHdt::where('MaNguoiDung', $user->MaNguoiDung)->first();
        if ($thanhVien && $phienHop) {
            $thamDu = ThamDu::where('MaPhienHop', $phienHop->MaPhienHop)
                ->where('MaThanhVien', $thanhVien->MaThanhVien)
                ->first();

            if ($thamDu && $thamDu->TrangThaiThamDu === 'PRESENT') {
                $canVote = true;

                $daBoPhieu = KetQuaBieuQuyet::where('MaNoiDung', $id)
                    ->where('MaThamDu', $thamDu->MaThamDu)
                    ->first();

                if ($daBoPhieu) {
                    $hasVoted = true;
                    $userChoice = $daBoPhieu->LuaChon;
                }
            }
        }

        $formatted = $this->formatVotingSummary($vote, $meetingId);
        $formatted['can_vote'] = $canVote;
        $formatted['has_voted'] = $hasVoted;
        $formatted['user_choice'] = $userChoice;

        return response()->json([
            'success' => true,
            'message' => 'Lấy chi tiết biểu quyết thành công.',
            'data' => $formatted,
            'errors' => null,
        ]);
    }

    /**
     * Mở biểu quyết
     * POST /api/v1/votings/{id}/open
     */
    public function open(Request $request, int|string $id): JsonResponse
    {
        $user = $request->user();
        if (! in_array($user?->role, ['CHAIR', 'SECRETARY', 'ADMIN'])) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có thẩm quyền mở biểu quyết.',
                'data' => null,
                'errors' => null,
            ], 403);
        }

        $vote = NoiDungBieuQuyet::find($id);
        if (! $vote) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy nội dung biểu quyết.',
                'data' => null,
                'errors' => null,
            ], 404);
        }

        $vote->update([
            'TrangThai' => 'OPENING',
            'ThoiGianMo' => now(),
            'KetQua' => 'PENDING',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đã mở cổng biểu quyết thành công. Các thành viên có thể tiến hành bỏ phiếu.',
            'data' => $this->formatVotingSummary($vote->fresh()),
            'errors' => null,
        ]);
    }

    /**
     * Gửi phiếu biểu quyết (Chống bỏ phiếu 2 lần, chặn khách mời)
     * POST /api/v1/votings/{id}/vote
     */
    public function vote(Request $request, int|string $id): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'choice' => ['required', 'string', 'in:VOTE_FOR,VOTE_AGAINST,VOTE_ABSTAIN'],
        ], [
            'choice.required' => 'Vui lòng chọn phương án biểu quyết (Tán thành, Không tán thành, Không ý kiến).',
        ]);

        $vote = NoiDungBieuQuyet::with('chuongTrinhHop.phienHop')->find($id);
        if (! $vote) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy nội dung biểu quyết.',
                'data' => null,
                'errors' => null,
            ], 404);
        }

        // 1. Kiểm tra trạng thái: Biểu quyết phải đang mở
        if ($vote->TrangThai !== 'OPENING') {
            return response()->json([
                'success' => false,
                'message' => 'Biểu quyết này hiện không ở trạng thái mở để nhận phiếu.',
                'data' => null,
                'errors' => null,
            ], 422);
        }

        // 2. RÀNG BUỘC CỨNG BR-VOT-01: Chỉ thành viên HĐT đã điểm danh có mặt (PRESENT) mới có quyền biểu quyết
        $thanhVien = ThanhVienHdt::where('MaNguoiDung', $user->MaNguoiDung)->first();
        if (! $thanhVien) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không phải là Thành viên chính thức của Hội đồng trường và không có quyền biểu quyết.',
                'data' => null,
                'errors' => null,
            ], 403);
        }

        $phienHop = $vote->chuongTrinhHop?->phienHop;
        if (! $phienHop) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thông tin phiên họp liên kết.',
                'data' => null,
                'errors' => null,
            ], 422);
        }

        $thamDu = ThamDu::where('MaPhienHop', $phienHop->MaPhienHop)
            ->where('MaThanhVien', $thanhVien->MaThanhVien)
            ->first();

        if (! $thamDu || $thamDu->TrangThaiThamDu !== 'PRESENT') {
            return response()->json([
                'success' => false,
                'message' => 'Bạn chưa được xác nhận có mặt (PRESENT) tại phiên họp nên không thể thực hiện biểu quyết.',
                'data' => null,
                'errors' => null,
            ], 403);
        }

        // 3. RÀNG BUỘC CỨNG BR-VOT-02: Chống bỏ phiếu 2 lần (Single Vote Constraint)
        $existingVote = KetQuaBieuQuyet::where('MaNoiDung', $id)
            ->where('MaThamDu', $thamDu->MaThamDu)
            ->first();

        if ($existingVote) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn đã gửi phiếu cho nội dung này. Mỗi thành viên chỉ được bỏ phiếu một lần.',
                'data' => null,
                'errors' => null,
            ], 422);
        }

        // 4. Lưu phiếu biểu quyết
        KetQuaBieuQuyet::create([
            'LuaChon' => $validated['choice'],
            'ThoiDiem' => now(),
            'MaNoiDung' => $id,
            'MaThamDu' => $thamDu->MaThamDu,
            'MaUyQuyen' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ghi nhận phiếu biểu quyết thành công.',
            'data' => [
                'choice' => $validated['choice'],
                'has_voted' => true,
            ],
            'errors' => null,
        ]);
    }

    /**
     * Đóng biểu quyết và tự động tổng hợp kết quả (PASSED / REJECTED)
     * POST /api/v1/votings/{id}/close
     */
    public function close(Request $request, int|string $id): JsonResponse
    {
        $user = $request->user();
        if (! in_array($user?->role, ['CHAIR', 'SECRETARY', 'ADMIN'])) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có thẩm quyền đóng biểu quyết.',
                'data' => null,
                'errors' => null,
            ], 403);
        }

        $vote = NoiDungBieuQuyet::with(['chuongTrinhHop.phienHop', 'ketQuaBieuQuyet'])->find($id);
        if (! $vote) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy nội dung biểu quyết.',
                'data' => null,
                'errors' => null,
            ], 404);
        }

        // Đọc cấu hình ngưỡng thông qua từ GhiChu
        $meta = json_decode($vote->GhiChu ?? '{}', true) ?: [];
        $passThreshold = (int) ($meta['pass_threshold'] ?? 50);

        // Tổng hợp phiếu
        $votes = $vote->ketQuaBieuQuyet;
        $totalCast = $votes->count();
        $votesFor = $votes->where('LuaChon', 'VOTE_FOR')->count();

        // Lấy số thành viên HĐT có mặt tại phiên họp
        $phienHop = $vote->chuongTrinhHop?->phienHop;
        $presentOfficialCount = 0;
        if ($phienHop) {
            $presentOfficialCount = ThamDu::where('MaPhienHop', $phienHop->MaPhienHop)
                ->where('TrangThaiThamDu', 'PRESENT')
                ->count();
        }

        // Mẫu số: tính theo số thành viên có mặt, nếu không có thì tính trên tổng số phiếu đã bỏ
        $denominator = $presentOfficialCount > 0 ? $presentOfficialCount : max($totalCast, 1);
        $percentageFor = ($votesFor / $denominator) * 100;

        // Xác định kết quả: Đạt ngưỡng thì PASSED, không đạt thì REJECTED
        $finalResult = $percentageFor >= $passThreshold ? 'PASSED' : 'REJECTED';

        $vote->update([
            'TrangThai' => 'CLOSED',
            'ThoiGianDong' => now(),
            'KetQua' => $finalResult,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Đã đóng biểu quyết. Kết quả: " . ($finalResult === 'PASSED' ? 'THÔNG QUA' : 'KHÔNG THÔNG QUA'),
            'data' => $this->formatVotingSummary($vote->fresh(), $phienHop?->MaPhienHop ?? 1),
            'errors' => null,
        ]);
    }

    /**
     * Chuẩn hóa đối tượng thống kê Biểu quyết
     */
    private function formatVotingSummary(NoiDungBieuQuyet $vote, ?int $meetingId = null): array
    {
        $meta = json_decode($vote->GhiChu ?? '{}', true) ?: [];
        $votingType = $meta['voting_type'] ?? 'SECRET';
        $passThreshold = (int) ($meta['pass_threshold'] ?? 50);

        $votes = $vote->ketQuaBieuQuyet ?? collect([]);
        $totalCast = $votes->count();
        $votesFor = $votes->where('LuaChon', 'VOTE_FOR')->count();
        $votesAgainst = $votes->where('LuaChon', 'VOTE_AGAINST')->count();
        $votesAbstain = $votes->where('LuaChon', 'VOTE_ABSTAIN')->count();

        $percentageFor = $totalCast > 0 ? round(($votesFor / $totalCast) * 100, 1) : 0;
        $percentageAgainst = $totalCast > 0 ? round(($votesAgainst / $totalCast) * 100, 1) : 0;
        $percentageAbstain = $totalCast > 0 ? round(($votesAbstain / $totalCast) * 100, 1) : 0;

        $statusLabels = [
            'PENDING' => 'Chưa mở',
            'OPENING' => 'Đang mở biểu quyết',
            'CLOSED' => 'Đã đóng',
        ];

        $resultLabels = [
            'PASSED' => 'Đã thông qua',
            'REJECTED' => 'Không thông qua',
            'PENDING' => 'Chưa có kết quả',
        ];

        return [
            'id' => (string) $vote->MaNoiDung,
            'meeting_id' => (string) ($meetingId ?: $vote->chuongTrinhHop?->MaPhienHop ?? 1),
            'agenda_id' => (string) $vote->MaChuongTrinh,
            'title' => $vote->NoiDung,
            'voting_type' => $votingType,
            'voting_type_label' => $votingType === 'SECRET' ? 'Bỏ phiếu kín' : 'Biểu quyết công khai',
            'pass_threshold' => $passThreshold,
            'status' => $vote->TrangThai,
            'status_label' => $statusLabels[$vote->TrangThai] ?? $vote->TrangThai,
            'result' => $vote->KetQua,
            'result_label' => $resultLabels[$vote->KetQua] ?? $vote->KetQua,
            'opened_at' => $vote->ThoiGianMo ? date('Y-m-d H:i', strtotime($vote->ThoiGianMo)) : null,
            'closed_at' => $vote->ThoiGianDong ? date('Y-m-d H:i', strtotime($vote->ThoiGianDong)) : null,
            'stats' => [
                'total_votes' => $totalCast,
                'votes_for' => $votesFor,
                'percentage_for' => $percentageFor,
                'votes_against' => $votesAgainst,
                'percentage_against' => $percentageAgainst,
                'votes_abstain' => $votesAbstain,
                'percentage_abstain' => $percentageAbstain,
            ],
        ];
    }
}
