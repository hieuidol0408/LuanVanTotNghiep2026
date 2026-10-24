<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HoSoTrinh extends Model
{
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

    public function donVi(): BelongsTo
    {
        return $this->belongsTo(DonVi::class, 'MaDonVi', 'MaDonVi');
    }

    public function nguoiDung(): BelongsTo
    {
        return $this->belongsTo(User::class, 'MaNguoiDung', 'MaNguoiDung');
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
