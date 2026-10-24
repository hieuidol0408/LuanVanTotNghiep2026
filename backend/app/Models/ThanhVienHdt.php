<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ThanhVienHdt extends Model
{
    protected $table = 'thanh_vien_hdt';
    protected $primaryKey = 'MaThanhVien';
    public $timestamps = false;

    protected $fillable = [
        'ChucDanh',
        'ChucDanhHoiDongTruong',
        'NhiemKy',
        'NgayBatDauThamGia',
        'NgayBatDau',
        'NgayKetThuc',
        'CoQuyenBieuQuyet',
        'MaNguoiDung',
    ];

    protected $casts = [
        'NgayBatDauThamGia' => 'date',
        'NgayBatDau' => 'date',
        'NgayKetThuc' => 'date',
        'CoQuyenBieuQuyet' => 'integer',
    ];

    public function getChucDanhAttribute($value)
    {
        return $value ?? $this->attributes['ChucDanhHoiDongTruong'] ?? null;
    }

    public function getNgayBatDauThamGiaAttribute($value)
    {
        return $value ?? $this->attributes['NgayBatDau'] ?? null;
    }

    public function nguoiDung(): BelongsTo
    {
        return $this->belongsTo(User::class, 'MaNguoiDung', 'MaNguoiDung');
    }

    public function thamDu(): HasMany
    {
        return $this->hasMany(ThamDu::class, 'MaThanhVien', 'MaThanhVien');
    }

    public function uyQuyenChoNguoiKhac(): HasMany
    {
        return $this->hasMany(UyQuyenBieuQuyet::class, 'MaThanhVienUyQuyen', 'MaThanhVien');
    }

    public function nhanUyQuyen(): HasMany
    {
        return $this->hasMany(UyQuyenBieuQuyet::class, 'MaThanhVienNhanUyQuyen', 'MaThanhVien');
    }
}
