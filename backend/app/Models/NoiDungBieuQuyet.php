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
}
