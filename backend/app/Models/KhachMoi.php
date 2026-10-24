<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KhachMoi extends Model
{
    protected $table = 'khach_moi';
    protected $primaryKey = 'MaKhachMoi';
    public $timestamps = false;

    protected $fillable = [
        'HoTen',
        'ChucVu',
        'DonViCongTac',
        'Email',
        'SoDienThoai',
    ];

    public function khachMoiPhienHop(): HasMany
    {
        return $this->hasMany(KhachMoiPhienHop::class, 'MaKhachMoi', 'MaKhachMoi');
    }
}
