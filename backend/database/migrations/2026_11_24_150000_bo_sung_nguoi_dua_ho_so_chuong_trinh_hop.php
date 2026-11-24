<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('chuong_trinh_hop') && !Schema::hasColumn('chuong_trinh_hop', 'MaNguoiDung')) {
            Schema::table('chuong_trinh_hop', function (Blueprint $table) {
                $table->unsignedInteger('MaNguoiDung')->nullable()->after('MaHoSo');
                $table->foreign('MaNguoiDung')->references('MaNguoiDung')->on('nguoi_dung')->nullOnDelete();
            });

            // Gán người đưa hồ sơ vào phiên ban đầu là người tạo phiên họp
            DB::statement('
                UPDATE chuong_trinh_hop c 
                JOIN phien_hop p ON c.MaPhienHop = p.MaPhienHop 
                SET c.MaNguoiDung = p.MaNguoiDung 
                WHERE c.MaNguoiDung IS NULL AND p.MaNguoiDung IS NOT NULL
            ');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('chuong_trinh_hop') && Schema::hasColumn('chuong_trinh_hop', 'MaNguoiDung')) {
            Schema::table('chuong_trinh_hop', function (Blueprint $table) {
                $table->dropForeign(['MaNguoiDung']);
                $table->dropColumn('MaNguoiDung');
            });
        }
    }
};
