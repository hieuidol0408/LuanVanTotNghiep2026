<?php

namespace App\Models;


use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NhiemVu extends Model
{
    use SoftDeletes;

    protected $table = 'nhiem_vu';
    protected $primaryKey = 'MaNhiemVu';
    public $timestamps = false;

    protected $fillable = [
        'TenNhiemVu',
        'NoiDung',
        'NgayGiaoNhiemVu',
        'NgayGiao',
        'NgayHoanThanhNhiemVu',
        'NgayHoanThanh',
        'TrangThai',
        'NgayXacNhanHoanThanh',
        'MaNghiQuyet',
    ];

    protected $casts = [
        'NgayGiaoNhiemVu' => 'date',
        'NgayGiao' => 'date',
        'NgayHoanThanhNhiemVu' => 'date',
        'NgayHoanThanh' => 'date',
        'NgayXacNhanHoanThanh' => 'date',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('not_hidden', function ($builder) {
            $builder->where('nhiem_vu.TrangThai', '!=', 'Đã ẩn');
        });

        static::saving(function ($model) {
            if (empty($model->NgayGiaoNhiemVu) && !empty($model->NgayGiao)) {
                $model->NgayGiaoNhiemVu = $model->NgayGiao;
            }
            if (empty($model->NgayGiao) && !empty($model->NgayGiaoNhiemVu)) {
                $model->NgayGiao = $model->NgayGiaoNhiemVu;
            }
            if (empty($model->NgayHoanThanhNhiemVu) && !empty($model->NgayHoanThanh)) {
                $model->NgayHoanThanhNhiemVu = $model->NgayHoanThanh;
            }
            if (empty($model->NgayHoanThanh) && !empty($model->NgayHoanThanhNhiemVu)) {
                $model->NgayHoanThanh = $model->NgayHoanThanhNhiemVu;
            }
        });
    }

    public function getNgayGiaoNhiemVuAttribute($value)
    {
        $raw = $value ?? $this->attributes['NgayGiao'] ?? null;

        return $raw ? \Carbon\Carbon::parse($raw) : null;
    }

    public function getNgayHoanThanhNhiemVuAttribute($value)
    {
        $raw = $value ?? $this->attributes['NgayHoanThanh'] ?? null;

        return $raw ? \Carbon\Carbon::parse($raw) : null;
    }

    public function nghiQuyet(): BelongsTo
    {
        return $this->belongsTo(NghiQuyet::class, 'MaNghiQuyet', 'MaNghiQuyet');
    }

    public function phanCongNhiemVu(): HasMany
    {
        return $this->hasMany(PhanCongNhiemVu::class, 'MaNhiemVu', 'MaNhiemVu');
    }
}
