<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TaiLieu extends Model
{
    protected $table = 'tai_lieu';
    protected $primaryKey = 'MaTaiLieu';
    public $timestamps = false;

    protected $fillable = [
        'TenTaiLieu',
        'LoaiTaiLieu',
        'DuongDan',
        'DinhDang',
        'DungLuong',
        'NgayTaiLen',
        'MaNguoiDung',
    ];

    protected $casts = [
        'NgayTaiLen' => 'datetime',
        'DungLuong' => 'integer',
    ];

    public function nguoiDung(): BelongsTo
    {
        return $this->belongsTo(User::class, 'MaNguoiDung', 'MaNguoiDung');
    }

    public function phienHop(): BelongsToMany
    {
        return $this->belongsToMany(PhienHop::class, 'tai_lieu_phien_hop', 'MaTaiLieu', 'MaPhienHop');
    }

    public function hoSoTrinh(): BelongsToMany
    {
        return $this->belongsToMany(HoSoTrinh::class, 'tai_lieu_ho_so', 'MaTaiLieu', 'MaHoSo');
    }

    public function nghiQuyet(): BelongsToMany
    {
        return $this->belongsToMany(NghiQuyet::class, 'tai_lieu_nghi_quyet', 'MaTaiLieu', 'MaNghiQuyet');
    }

    public function baoCao(): BelongsToMany
    {
        return $this->belongsToMany(BaoCao::class, 'tai_lieu_bao_cao', 'MaTaiLieu', 'MaBaoCao');
    }
}
