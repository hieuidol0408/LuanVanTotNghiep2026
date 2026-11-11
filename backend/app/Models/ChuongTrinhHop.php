<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChuongTrinhHop extends Model
{
    protected $table = 'chuong_trinh_hop';
    protected $primaryKey = 'MaChuongTrinh';
    public $timestamps = false;

    protected $fillable = [
        'ThuTu',
        'TrangThai',
        'GhiChu',
        'MaPhienHop',
        'MaHoSo',
    ];

    protected $casts = [
        'ThuTu' => 'integer',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('not_hidden', function ($builder) {
            $builder->where('chuong_trinh_hop.TrangThai', '!=', 'Đã ẩn');
        });
    }

    public function getTrangThaiAttribute($value): string
    {
        return match (strtoupper(trim((string) $value))) {
            'PENDING' => 'Chưa xem xét',
            'DISCUSSING' => 'Đang thảo luận',
            'COMPLETED', 'PASSED' => 'Đã thông qua',
            default => (string) ($value ?: 'Chưa xem xét'),
        };
    }

    public function phienHop(): BelongsTo
    {
        return $this->belongsTo(PhienHop::class, 'MaPhienHop', 'MaPhienHop');
    }

    public function hoSoTrinh(): BelongsTo
    {
        return $this->belongsTo(HoSoTrinh::class, 'MaHoSo', 'MaHoSo');
    }

    public function noiDungBieuQuyet(): HasMany
    {
        return $this->hasMany(NoiDungBieuQuyet::class, 'MaChuongTrinh', 'MaChuongTrinh');
    }
}
