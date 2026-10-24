<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DonVi extends Model
{
    protected $table = 'don_vi';
    protected $primaryKey = 'MaDonVi';
    public $timestamps = false;

    protected $fillable = [
        'TenDonVi',
        'TrangThai',
    ];

    public function getNameAttribute(): string
    {
        return (string) $this->TenDonVi;
    }

    public function nguoiDung(): HasMany
    {
        return $this->hasMany(User::class, 'MaDonVi', 'MaDonVi');
    }

    public function hoSoTrinh(): HasMany
    {
        return $this->hasMany(HoSoTrinh::class, 'MaDonVi', 'MaDonVi');
    }

    public function phanCongNhiemVu(): HasMany
    {
        return $this->hasMany(PhanCongNhiemVu::class, 'MaDonVi', 'MaDonVi');
    }
}
