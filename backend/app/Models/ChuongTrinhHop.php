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
