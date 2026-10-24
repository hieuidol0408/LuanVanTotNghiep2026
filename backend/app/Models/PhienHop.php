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
    ];

    protected $casts = [
        'ThoiGianBatDau' => 'datetime',
        'ThoiGianKetThuc' => 'datetime',
        'NgayHop' => 'date',
    ];

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
}
