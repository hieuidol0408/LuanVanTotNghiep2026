<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VaiTro extends Model
{
    protected $table = 'vai_tro';
    protected $primaryKey = 'MaVaiTro';
    public $timestamps = false;

    protected $fillable = [
        'TenVaiTro',
        'MoTa',
    ];

    public function nguoiDung(): HasMany
    {
        return $this->hasMany(User::class, 'MaVaiTro', 'MaVaiTro');
    }
}
