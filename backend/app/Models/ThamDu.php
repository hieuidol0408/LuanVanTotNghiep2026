<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ThamDu extends Model
{
    protected $table = 'tham_du';
    protected $primaryKey = 'MaThamDu';
    public $timestamps = false;

    protected $fillable = [
        'TrangThaiThamDu',
        'ThoiDiemXacNhan',
        'GhiChu',
        'MaPhienHop',
        'MaThanhVien',
    ];

    protected $casts = [
        'ThoiDiemXacNhan' => 'datetime',
    ];

    public function phienHop(): BelongsTo
    {
        return $this->belongsTo(PhienHop::class, 'MaPhienHop', 'MaPhienHop');
    }

    public function thanhVienHdt(): BelongsTo
    {
        return $this->belongsTo(ThanhVienHdt::class, 'MaThanhVien', 'MaThanhVien');
    }

    public function ketQuaBieuQuyet(): HasMany
    {
        return $this->hasMany(KetQuaBieuQuyet::class, 'MaThamDu', 'MaThamDu');
    }
}
