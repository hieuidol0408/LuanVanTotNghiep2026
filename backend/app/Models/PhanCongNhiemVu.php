<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PhanCongNhiemVu extends Model
{
    protected $table = 'phan_cong_nhiem_vu';
    protected $primaryKey = 'MaPhanCong';
    public $timestamps = false;

    protected $fillable = [
        'VaiTroThucHien',
        'TrangThaiThucHien',
        'TenNguoiPhuTrach',
        'MaNhiemVu',
        'MaDonVi',
    ];

    protected static function booted(): void
    {
        static::saving(function ($model) {
            if (empty($model->TenNguoiPhuTrach)) {
                $model->TenNguoiPhuTrach = 'Người phụ trách đơn vị';
            }
        });
    }

    public function nhiemVu(): BelongsTo
    {
        return $this->belongsTo(NhiemVu::class, 'MaNhiemVu', 'MaNhiemVu');
    }

    public function donVi(): BelongsTo
    {
        return $this->belongsTo(DonVi::class, 'MaDonVi', 'MaDonVi');
    }

    public function baoCao(): HasMany
    {
        return $this->hasMany(BaoCao::class, 'MaPhanCong', 'MaPhanCong');
    }
}
