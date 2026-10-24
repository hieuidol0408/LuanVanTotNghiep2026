<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KetQuaBieuQuyet extends Model
{
    protected $table = 'ket_qua_bieu_quyet';
    protected $primaryKey = 'MaKetQua';
    public $timestamps = false;

    protected $fillable = [
        'LuaChon',
        'ThoiDiem',
        'MaNoiDung',
        'MaThamDu',
        'MaUyQuyen',
    ];

    protected $casts = [
        'ThoiDiem' => 'datetime',
    ];

    public function noiDungBieuQuyet(): BelongsTo
    {
        return $this->belongsTo(NoiDungBieuQuyet::class, 'MaNoiDung', 'MaNoiDung');
    }

    public function thamDu(): BelongsTo
    {
        return $this->belongsTo(ThamDu::class, 'MaThamDu', 'MaThamDu');
    }

    public function uyQuyenBieuQuyet(): BelongsTo
    {
        return $this->belongsTo(UyQuyenBieuQuyet::class, 'MaUyQuyen', 'MaUyQuyen');
    }
}
