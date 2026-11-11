<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PhienHop extends Model
{
    protected $table = 'phien_hop';
    protected $primaryKey = 'MaPhienHop';
    public $timestamps = false;

    protected $fillable = [
        'TenPhienHop',
        'ThoiGianBatDau',
        'ThoiGianKetThuc',
        'NgayHop',
        'DiaDiem',
        'HinhThuc',
        'TrangThai',
        'GhiChu',
        'MaNguoiDung',
    ];

    protected $casts = [
        'ThoiGianBatDau' => 'datetime',
        'ThoiGianKetThuc' => 'datetime',
        'NgayHop' => 'date',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('not_hidden', function ($builder) {
            $builder->where('phien_hop.TrangThai', '!=', 'Đã ẩn');
        });
    }

    public function getHinhThucAttribute($value): string
    {
        return match (strtoupper(trim((string) $value))) {
            'IN_PERSON', 'IN PERSON' => 'Trực tiếp',
            'ONLINE' => 'Trực tuyến',
            'HYBRID' => 'Kết hợp',
            default => (string) ($value ?: 'Trực tiếp'),
        };
    }

    public function getTrangThaiAttribute($value): string
    {
        return match (strtoupper(trim((string) $value))) {
            'PREPARING' => 'Đang chuẩn bị',
            'SCHEDULED' => 'Sắp diễn ra',
            'ONGOING', 'IN_PROGRESS' => 'Đang diễn ra',
            'COMPLETED' => 'Đã kết thúc',
            'CANCELLED' => 'Hủy',
            default => (string) ($value ?: 'Sắp diễn ra'),
        };
    }

    public function nguoiDung(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'MaNguoiDung', 'MaNguoiDung');
    }

    public function thamDu(): HasMany
    {
        return $this->hasMany(ThamDu::class, 'MaPhienHop', 'MaPhienHop');
    }

    public function khachMoiPhienHop(): HasMany
    {
        return $this->hasMany(KhachMoiPhienHop::class, 'MaPhienHop', 'MaPhienHop');
    }

    public function chuongTrinhHop(): HasMany
    {
        return $this->hasMany(ChuongTrinhHop::class, 'MaPhienHop', 'MaPhienHop');
    }

    public function uyQuyenBieuQuyet(): HasMany
    {
        return $this->hasMany(UyQuyenBieuQuyet::class, 'MaPhienHop', 'MaPhienHop');
    }

    public function nghiQuyet(): HasMany
    {
        return $this->hasMany(NghiQuyet::class, 'MaPhienHop', 'MaPhienHop');
    }

    public function taiLieu(): BelongsToMany
    {
        return $this->belongsToMany(TaiLieu::class, 'tai_lieu_phien_hop', 'MaPhienHop', 'MaTaiLieu');
    }

    public function noiDungBieuQuyet(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(
            NoiDungBieuQuyet::class,
            ChuongTrinhHop::class,
            'MaPhienHop',
            'MaChuongTrinh',
            'MaPhienHop',
            'MaChuongTrinh'
        );
    }
}
