<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'vai_tro', 'don_vi', 'nguoi_dung', 'thanh_vien_hdt',
        'phien_hop', 'chuong_trinh_hop', 'tham_du', 'khach_moi', 'khach_moi_phien_hop',
        'ho_so_trinh', 'noi_dung_bieu_quyet', 'ket_qua_bieu_quyet', 'uy_quyen_bieu_quyet',
        'nghi_quyet', 'nhiem_vu', 'phan_cong_nhiem_vu', 'bao_cao', 'tai_lieu',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'deleted_at')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->softDeletes();
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'deleted_at')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropSoftDeletes();
                });
            }
        }
    }
};