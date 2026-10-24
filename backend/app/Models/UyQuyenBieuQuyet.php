<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UyQuyenBieuQuyet extends Model
{
    protected $table = 'uy_quyen_bieu_quyet';
    protected $primaryKey = 'MaUyQuyen';
    public $timestamps = false;

    protected $fillable = [
        'NgayUyQuyen',
        'PhamViUyQuyen',
        'TaiLieuUyQuyen',
        'TrangThai',
        'MaPhienHop',
        'MaThanhVienUyQuyen',
        'MaThanhVienNhanUyQuyen',
    ];

    protected $casts = [
        'NgayUyQuyen' => 'date',
    ];

    public function phienHop(): BelongsTo
    {
        return $this->belongsTo(PhienHop::class, 'MaPhienHop', 'MaPhienHop');
    }

    public function thanhVienUyQuyen(): BelongsTo
    {
        return $this->belongsTo(ThanhVienHdt::class, 'MaThanhVienUyQuyen', 'MaThanhVien');
    }

    public function thanhVienNhanUyQuyen(): BelongsTo
    {
        return $this->belongsTo(ThanhVienHdt::class, 'MaThanhVienNhanUyQuyen', 'MaThanhVien');
    }

    public function ketQuaBieuQuyet(): HasMany
    {
        return $this->hasMany(KetQuaBieuQuyet::class, 'MaUyQuyen', 'MaUyQuyen');
    }
}
