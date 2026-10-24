<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NoiDungBieuQuyet extends Model
{
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
