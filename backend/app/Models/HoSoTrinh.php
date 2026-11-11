<?php

namespace App\Models;


use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HoSoTrinh extends Model
{
    use SoftDeletes;

    protected $table = 'ho_so_trinh';
    protected $primaryKey = 'MaHoSo';
    public $timestamps = false;

    protected $fillable = [
        'TieuDe',
        'NoiDungTomTat',
        'NgayTrinh',
        'LinhVuc',
        'TrangThai',
        'GhiChu',
        'MaDonVi',
        'MaNguoiDung',
    ];

    protected $casts = [
        'NgayTrinh' => 'date',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('not_hidden', function ($builder) {
            $builder->where('ho_so_trinh.TrangThai', '!=', 'Đã ẩn');
        });
    }

    public function getTrangThaiAttribute($value): string
    {
        return match (strtoupper(trim((string) $value))) {
            'DRAFT', 'SUBMITTED', 'PENDING' => 'Mới tạo',
            'APPROVED_FOR_MEETING', 'ELIGIBLE' => 'Đủ điều kiện',
            'SCHEDULED' => 'Đã xếp lịch',
            'NEED_MORE_INFO', 'REVISE' => 'Chờ bổ sung',
            'COMPLETE' => 'Đầy đủ',
            'REJECTED' => 'Từ chối',
            default => (string) ($value ?: 'Mới tạo'),
        };
    }

    /**
     * Tự động sinh Mã số hồ sơ theo quy tắc:
     * #HS- + [2 chữ số tháng] + [2 chữ số cuối của năm] + [Số thứ tự tăng dần trong tháng 2 chữ số bắt đầu từ 01]
     * Ví dụ: Tháng 11/2026 -> #HS-112601, #HS-112602... Sang tháng 12/2026 -> #HS-122601.
     */
    public function getMaHoSoHienThiAttribute(): string
    {
        $date = $this->NgayTrinh ? \Carbon\Carbon::parse($this->NgayTrinh) : now();
        $month = $date->format('m');
        $yearShort = $date->format('y');

        if (! $this->MaHoSo) {
            return self::generateNextCode($date);
        }

        // Tính số thứ tự của hồ sơ này trong cùng tháng và năm của NgayTrinh
        $sequence = self::withoutGlobalScopes()
            ->whereYear('NgayTrinh', $date->year)
            ->whereMonth('NgayTrinh', $date->month)
            ->where('MaHoSo', '<=', $this->MaHoSo)
            ->count();

        if ($sequence < 1) {
            $sequence = 1;
        }

        return sprintf('#HS-%s%s%02d', $month, $yearShort, $sequence);
    }

    /**
     * Dự tính mã hồ sơ tiếp theo khi mở form Tạo mới trong tháng hiện hành
     */
    public static function generateNextCode(?\Carbon\Carbon $date = null): string
    {
        $date = $date ?? now();
        $month = $date->format('m');
        $yearShort = $date->format('y');

        $countInMonth = self::withoutGlobalScopes()
            ->whereYear('NgayTrinh', $date->year)
            ->whereMonth('NgayTrinh', $date->month)
            ->count();

        $nextSeq = $countInMonth + 1;

        return sprintf('#HS-%s%s%02d', $month, $yearShort, $nextSeq);
    }

    public function donVi(): BelongsTo
    {
        return $this->belongsTo(DonVi::class, 'MaDonVi', 'MaDonVi');
    }

    public function nguoiDung(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'MaNguoiDung', 'MaNguoiDung');
    }

    public function chuongTrinhHop(): HasMany
    {
        return $this->hasMany(ChuongTrinhHop::class, 'MaHoSo', 'MaHoSo');
    }

    public function nghiQuyet(): BelongsToMany
    {
        return $this->belongsToMany(NghiQuyet::class, 'nghi_quyet_ho_so', 'MaHoSo', 'MaNghiQuyet');
    }

    public function taiLieu(): BelongsToMany
    {
        return $this->belongsToMany(TaiLieu::class, 'tai_lieu_ho_so', 'MaHoSo', 'MaTaiLieu');
    }
}
