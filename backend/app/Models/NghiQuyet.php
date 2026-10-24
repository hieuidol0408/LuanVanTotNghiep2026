<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NghiQuyet extends Model
{
    protected $table = 'nghi_quyet';
    protected $primaryKey = 'MaNghiQuyet';
    public $timestamps = false;

    protected $fillable = [
        'SoNghiQuyet',
        'NgayBanHanh',
        'TrichYeu',
        'NoiDung',
        'TrangThai',
        'MaPhienHop',
    ];

    protected $casts = [
        'NgayBanHanh' => 'date',
    ];

    public function phienHop(): BelongsTo
    {
        return $this->belongsTo(PhienHop::class, 'MaPhienHop', 'MaPhienHop');
    }

    public function hoSoTrinh(): BelongsToMany
    {
        return $this->belongsToMany(HoSoTrinh::class, 'nghi_quyet_ho_so', 'MaNghiQuyet', 'MaHoSo');
    }

    public function noiDungBieuQuyet(): BelongsToMany
    {
        return $this->belongsToMany(NoiDungBieuQuyet::class, 'nghi_quyet_bieu_quyet', 'MaNghiQuyet', 'MaNoiDung');
    }

    public function nhiemVu(): HasMany
    {
        return $this->hasMany(NhiemVu::class, 'MaNghiQuyet', 'MaNghiQuyet');
    }

    public function taiLieu(): BelongsToMany
    {
        return $this->belongsToMany(TaiLieu::class, 'tai_lieu_nghi_quyet', 'MaNghiQuyet', 'MaTaiLieu');
    }
}
