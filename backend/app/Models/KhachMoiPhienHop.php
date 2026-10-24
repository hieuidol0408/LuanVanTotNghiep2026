<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KhachMoiPhienHop extends Model
{
    protected $table = 'khach_moi_phien_hop';
    protected $primaryKey = 'MaMoi';
    public $timestamps = false;

    protected $fillable = [
        'NoiDungMoi',
        'TrangThaiThamDu',
        'MaKhachMoi',
        'MaPhienHop',
    ];

    public function khachMoi(): BelongsTo
    {
        return $this->belongsTo(KhachMoi::class, 'MaKhachMoi', 'MaKhachMoi');
    }

    public function phienHop(): BelongsTo
    {
        return $this->belongsTo(PhienHop::class, 'MaPhienHop', 'MaPhienHop');
    }
}
