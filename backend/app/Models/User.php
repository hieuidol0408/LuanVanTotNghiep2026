<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'nguoi_dung';
    protected $primaryKey = 'MaNguoiDung';

    protected $fillable = [
        'HoTen',
        'email',
        'MatKhau',
        'TrangThai',
        'MaVaiTro',
        'MaDonVi',
    ];

    protected $hidden = [
        'MatKhau',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'MatKhau' => 'hashed',
        ];
    }

    /**
     * Tên cột chứa mật khẩu xác thực của Laravel
     */
    public function getAuthPasswordName(): string
    {
        return 'MatKhau';
    }

    public function getAuthPassword(): string
    {
        return (string) $this->MatKhau;
    }

    public function getPasswordAttribute(): string
    {
        return (string) $this->MatKhau;
    }

    public function getDepartmentIdAttribute(): int
    {
        return (int) $this->MaDonVi;
    }

    /**
     * Intercept where('role', ...) để tự động truy vấn quan hệ vaiTro(TenVaiTro)
     */
    public function newEloquentBuilder($query)
    {
        return new class($query) extends \Illuminate\Database\Eloquent\Builder {
            public function where($column, $operator = null, $value = null, $boolean = 'and')
            {
                if ($column === 'role') {
                    $val = func_num_args() === 2 ? $operator : $value;
                    return $this->whereHas('vaiTro', function ($q) use ($val) {
                        $q->where('TenVaiTro', $val);
                    });
                }
                return parent::where($column, $operator, $value, $boolean);
            }
        };
    }

    /**
     * Accessor id để tương thích Sanctum và JSON API
     */
    public function getIdAttribute(): int
    {
        return (int) $this->MaNguoiDung;
    }

    /**
     * Accessor full_name để tương thích giao diện và controller
     */
    public function getFullNameAttribute(): string
    {
        return (string) $this->HoTen;
    }

    /**
     * Accessor role để tương thích với ma trận RBAC
     */
    public function getRoleAttribute(): string
    {
        return $this->vaiTro?->TenVaiTro ?? 'MEMBER';
    }

    /**
     * Accessor is_active
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->TrangThai === 'Hoạt động' || $this->TrangThai === 'ACTIVE';
    }

    public function vaiTro(): BelongsTo
    {
        return $this->belongsTo(VaiTro::class, 'MaVaiTro', 'MaVaiTro');
    }

    public function donVi(): BelongsTo
    {
        return $this->belongsTo(DonVi::class, 'MaDonVi', 'MaDonVi');
    }

    public function department(): BelongsTo
    {
        return $this->donVi();
    }

    public function thanhVienHdt(): HasOne
    {
        return $this->hasOne(ThanhVienHdt::class, 'MaNguoiDung', 'MaNguoiDung');
    }

    public function hoSoTrinh(): HasMany
    {
        return $this->hasMany(HoSoTrinh::class, 'MaNguoiDung', 'MaNguoiDung');
    }

    public function taiLieu(): HasMany
    {
        return $this->hasMany(TaiLieu::class, 'MaNguoiDung', 'MaNguoiDung');
    }

    public function baoCao(): HasMany
    {
        return $this->hasMany(BaoCao::class, 'MaNguoiDung', 'MaNguoiDung');
    }
}
