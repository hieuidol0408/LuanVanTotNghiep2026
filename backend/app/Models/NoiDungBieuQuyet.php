<?php

namespace App\Models;


use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NoiDungBieuQuyet extends Model
{
    use SoftDeletes;

    protected $table = 'noi_dung_bieu_quyet';
    protected $primaryKey = 'MaNoiDung';
    public $timestamps = false;

    protected $fillable = [
        'NoiDung',
        'ThoiGianMo',
        'ThoiGianDong',
        'TrangThai',
        'KetQuaBieuQuyet',
        'KetQua',
        'GhiChu',
        'MaChuongTrinh',
    ];

    protected $casts = [
        'ThoiGianMo' => 'datetime',
        'ThoiGianDong' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('not_hidden', function ($builder) {
            $builder->where('noi_dung_bieu_quyet.TrangThai', '!=', 'Đã ẩn');
        });
    }

    public function getTrangThaiAttribute($value): string
    {
        return match (strtoupper(trim((string) $value))) {
            'PENDING' => 'Chưa mở',
            'OPENING' => 'Đang mở',
            'CLOSED' => 'Đã đóng',
            default => (string) ($value ?: 'Chưa mở'),
        };
    }

    public function chuongTrinhHop(): BelongsTo
    {
        return $this->belongsTo(ChuongTrinhHop::class, 'MaChuongTrinh', 'MaChuongTrinh');
    }

    public function ketQuaBieuQuyet(): HasMany
    {
        return $this->hasMany(KetQuaBieuQuyet::class, 'MaNoiDung', 'MaNoiDung');
    }

    public function nghiQuyet(): BelongsToMany
    {
        return $this->belongsToMany(NghiQuyet::class, 'nghi_quyet_bieu_quyet', 'MaNoiDung', 'MaNghiQuyet');
    }

    /**
     * Đọc an toàn metadata JSON trong cột GhiChu (giữ nguyên ghi chú văn bản thuần nếu có).
     */
    public function decodeMeta(): array
    {
        $raw = trim((string) ($this->GhiChu ?? ''));
        if ($raw === '') {
            return [];
        }
        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : ['notes' => $raw];
    }

    /**
     * Tính toán thống kê kiểm phiếu thống nhất trên toàn hệ thống theo Quy chế (BR-07, BR-09):
     * - Tỷ lệ % luôn tính trên tổng số thành viên chính thức có quyền biểu quyết và có mặt tại phiên họp
     *   (fallback về tổng phiếu đã bỏ nếu phiên họp chưa điểm danh ai có mặt).
     * - Khi số phiếu Tán thành == Không tán thành (> 0) là trường hợp hòa phiếu (is_tied),
     *   kết quả cuối cùng do người mở cổng biểu quyết quyết định (tie_decision: PASSED | REJECTED).
     */
    public function calculateStats(?int $presentOfficialCount = null): array
    {
        $meta = $this->decodeMeta();
        $votingType = $meta['voting_type'] ?? 'SECRET';
        $passThreshold = (int) ($meta['pass_threshold'] ?? 50);

        $votes = $this->relationLoaded('ketQuaBieuQuyet')
            ? $this->ketQuaBieuQuyet
            : $this->ketQuaBieuQuyet()->get();

        $totalVotes = $votes->count();
        $agreeVotes = $votes->filter(fn ($v) => in_array($v->LuaChon, ['VOTE_FOR', 'Tán thành']))->count();
        $disagreeVotes = $votes->filter(fn ($v) => in_array($v->LuaChon, ['VOTE_AGAINST', 'Không tán thành']))->count();
        $abstainVotes = $votes->filter(fn ($v) => in_array($v->LuaChon, ['VOTE_ABSTAIN', 'Không có ý kiến', 'Không ý kiến']))->count();

        if ($presentOfficialCount === null) {
            $phienHop = $this->chuongTrinhHop?->phienHop;
            if ($phienHop && $phienHop->relationLoaded('thamDu')) {
                $presentOfficialCount = $phienHop->thamDu->filter(function ($att) {
                    $hasRight = (bool) ($att->thanhVienHdt?->CoQuyenBieuQuyet ?? true);
                    $isPresent = in_array($att->TrangThaiThamDu, ['PRESENT', 'Có mặt']);
                    return $hasRight && $isPresent;
                })->count();
            } elseif ($phienHop) {
                $presentOfficialCount = ThamDu::where('MaPhienHop', $phienHop->MaPhienHop)
                    ->whereIn('TrangThaiThamDu', ['PRESENT', 'Có mặt'])
                    ->whereHas('thanhVienHdt', function ($q) {
                        $q->where(function ($sub) {
                            $sub->whereNull('CoQuyenBieuQuyet')
                                ->orWhere('CoQuyenBieuQuyet', 1);
                        });
                    })
                    ->count();
            } else {
                $presentOfficialCount = 0;
            }
        }

        $denominator = $presentOfficialCount > 0 ? $presentOfficialCount : max($totalVotes, 1);
        $agreePercent = $totalVotes > 0 ? round(($agreeVotes / $denominator) * 100, 1) : 0;
        $disagreePercent = $totalVotes > 0 ? round(($disagreeVotes / $denominator) * 100, 1) : 0;
        $abstainPercent = $totalVotes > 0 ? round(($abstainVotes / $denominator) * 100, 1) : 0;

        $isTied = $agreeVotes > 0 && $agreeVotes === $disagreeVotes;
        $tieDecision = $meta['tie_decision'] ?? null; // 'PASSED' | 'REJECTED' | null

        if ($isTied && $tieDecision !== null) {
            $isPassed = $tieDecision === 'PASSED';
        } elseif ($isTied) {
            $isPassed = false;
        } else {
            $isPassed = $agreePercent >= $passThreshold;
        }

        return [
            'total' => $totalVotes,
            'present_count' => $presentOfficialCount,
            'agree' => $agreeVotes,
            'agree_percent' => $agreePercent,
            'disagree' => $disagreeVotes,
            'disagree_percent' => $disagreePercent,
            'abstain' => $abstainVotes,
            'abstain_percent' => $abstainPercent,
            'pass_threshold' => $passThreshold,
            'voting_type' => $votingType,
            'is_tied' => $isTied,
            'tie_decision' => $tieDecision,
            'needs_tie_decision' => $isTied && $tieDecision === null,
            'opened_by_user_id' => $meta['opened_by_user_id'] ?? null,
            'opened_by_name' => $meta['opened_by_name'] ?? null,
            'decided_by_name' => $meta['decided_by_name'] ?? null,
            'decided_at' => $meta['decided_at'] ?? null,
            'is_passed' => $isPassed,
        ];
    }
}
