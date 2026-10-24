<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NhiemVu extends Model
{
    protected $table = 'nhiem_vu';
    protected $primaryKey = 'MaNhiemVu';
    public $timestamps = false;

    protected $fillable = [
        'TenNhiemVu',
        'NoiDung',
        'NgayGiao',
        'NgayHoanThanh',
        'TrangThai',
        'NgayXacNhanHoanThanh',
        'MaNghiQuyet',
    ];

    protected $casts = [
        'NgayGiao' => 'date',
        'NgayHoanThanh' => 'date',
        'NgayXacNhanHoanThanh' => 'date',
    ];

    public function nghiQuyet(): BelongsTo
    {
        return $this->belongsTo(NghiQuyet::class, 'MaNghiQuyet', 'MaNghiQuyet');
    }

    public function phanCongNhiemVu(): HasMany
    {
        return $this->hasMany(PhanCongNhiemVu::class, 'MaNhiemVu', 'MaNhiemVu');
    }
}
