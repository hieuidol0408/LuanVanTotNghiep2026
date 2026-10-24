<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BaoCao extends Model
{
    protected $table = 'bao_cao';
    protected $primaryKey = 'MaBaoCao';
    public $timestamps = false;

    protected $fillable = [
        'NgayBaoCao',
        'TienDo',
        'NoiDungTienDo',
        'NoiDungKhoKhan',
        'TrangThaiXacNhan',
        'NgayXacNhan',
        'GhiChu',
        'MaPhanCong',
        'MaNguoiDung',
    ];

    protected $casts = [
        'NgayBaoCao' => 'date',
        'NgayXacNhan' => 'date',
        'TienDo' => 'integer',
    ];

    public function phanCongNhiemVu(): BelongsTo
    {
        return $this->belongsTo(PhanCongNhiemVu::class, 'MaPhanCong', 'MaPhanCong');
    }

    public function nguoiDung(): BelongsTo
    {
        return $this->belongsTo(User::class, 'MaNguoiDung', 'MaNguoiDung');
    }

    public function taiLieu(): BelongsToMany
    {
        return $this->belongsToMany(TaiLieu::class, 'tai_lieu_bao_cao', 'MaBaoCao', 'MaTaiLieu');
    }
}
