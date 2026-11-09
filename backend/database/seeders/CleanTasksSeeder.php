<?php

namespace Database\Seeders;

use App\Models\BaoCao;
use App\Models\NghiQuyet;
use App\Models\NhiemVu;
use App\Models\PhanCongNhiemVu;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CleanTasksSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Lấy mã nghị quyết thật
        $nq1 = NghiQuyet::where('SoNghiQuyet', '01/NQ-HĐT-2026')->first();
        $nq2 = NghiQuyet::where('SoNghiQuyet', '02/NQ-HĐT-2026')->first();
        $nq3 = NghiQuyet::where('SoNghiQuyet', '03/NQ-HĐT-2026')->first() ?? $nq1;
        $nq4 = NghiQuyet::where('SoNghiQuyet', '04/NQ-HĐT-2026')->first() ?? $nq2;

        // 2. Dọn dẹp bảng Nhiệm vụ & Phân công
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        BaoCao::truncate();
        PhanCongNhiemVu::truncate();
        NhiemVu::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $tasksData = [
            [
                'TenNhiemVu' => 'Xây dựng Đề án mở ngành Trí tuệ nhân tạo (AI) và Robot',
                'NoiDung' => 'Chủ trì phối hợp cùng Phòng Đào tạo hoàn thiện hồ sơ đề án mở ngành đào tạo mới theo Thông tư 02/2022/TT-BGDĐT.',
                'NgayGiaoNhiemVu' => '2026-10-21',
                'NgayGiao' => '2026-10-21',
                'NgayHoanThanhNhiemVu' => '2026-12-31',
                'NgayHoanThanh' => '2026-12-31',
                'TrangThai' => 'IN_PROGRESS',
                'MaNghiQuyet' => $nq1->MaNghiQuyet,
                'MaDonVi' => 3, // Khoa CNTT
                'NguoiPhuTrach' => 'TS. Nguyễn Hoàng Nam',
                'VaiTro' => 'Đơn vị chủ trì',
            ],
            [
                'TenNhiemVu' => 'Nâng cấp phòng thí nghiệm Viễn thông và Mạng máy tính cơ sở 2',
                'NoiDung' => 'Khảo sát hiện trạng, lập dự toán mua sắm trang thiết bị phòng thực hành đáp ứng chuẩn AUN-QA.',
                'NgayGiaoNhiemVu' => '2026-10-22',
                'NgayGiao' => '2026-10-22',
                'NgayHoanThanhNhiemVu' => '2026-11-25',
                'NgayHoanThanh' => '2026-11-25',
                'TrangThai' => 'IN_PROGRESS',
                'MaNghiQuyet' => $nq3->MaNghiQuyet,
                'MaDonVi' => 4, // Khoa Điện - Điện tử
                'NguoiPhuTrach' => 'ThS. Lê Hoàng Long',
                'VaiTro' => 'Đơn vị chủ trì',
            ],
            [
                'TenNhiemVu' => 'Thẩm định định mức học phí khối ngành Kỹ thuật - Công nghệ năm 2026',
                'NoiDung' => 'Xây dựng đề xuất khung học phí mới theo Nghị định 97/2023/NĐ-CP trình Hội đồng trường xem xét.',
                'NgayGiaoNhiemVu' => '2026-10-25',
                'NgayGiao' => '2026-10-25',
                'NgayHoanThanhNhiemVu' => '2026-11-30',
                'NgayHoanThanh' => '2026-11-30',
                'TrangThai' => 'IN_PROGRESS',
                'MaNghiQuyet' => $nq2->MaNghiQuyet,
                'MaDonVi' => 2, // VP Hiệu trưởng
                'NguoiPhuTrach' => 'ThS. Phan Đình Trí',
                'VaiTro' => 'Đơn vị chủ trì',
            ],
            [
                'TenNhiemVu' => 'Rà soát và ban hành chuẩn đầu ra theo Khung trình độ quốc gia',
                'NoiDung' => 'Cập nhật đề cương chi tiết học phần, lấy ý kiến khảo sát từ doanh nghiệp và cựu sinh viên.',
                'NgayGiaoNhiemVu' => '2026-09-15',
                'NgayGiao' => '2026-09-15',
                'NgayHoanThanhNhiemVu' => '2026-10-25',
                'NgayHoanThanh' => '2026-10-25',
                'TrangThai' => 'IN_PROGRESS', // Quá hạn
                'MaNghiQuyet' => $nq4->MaNghiQuyet,
                'MaDonVi' => 5, // Phòng Đào tạo
                'NguoiPhuTrach' => 'TS. Vũ Hải Đăng',
                'VaiTro' => 'Đơn vị chủ trì',
            ],
            [
                'TenNhiemVu' => 'Triển khai hệ thống chữ ký số văn bản và lưu trữ điện tử',
                'NoiDung' => 'Cung cấp USB Token và chứng thư số chuyên dùng cho các đơn vị trực thuộc trường.',
                'NgayGiaoNhiemVu' => '2026-09-01',
                'NgayGiao' => '2026-09-01',
                'NgayHoanThanhNhiemVu' => '2026-10-15',
                'NgayHoanThanh' => '2026-10-15',
                'TrangThai' => 'COMPLETED',
                'NgayXacNhanHoanThanh' => '2026-10-14',
                'MaNghiQuyet' => $nq3->MaNghiQuyet,
                'MaDonVi' => 6, // TT CNTT & Truyền thông
                'NguoiPhuTrach' => 'ThS. Phạm Quốc Tuấn',
                'VaiTro' => 'Đơn vị chủ trì',
            ],
            [
                'TenNhiemVu' => 'Hoàn thiện Quy chế chi tiêu nội bộ năm học 2026 - 2027',
                'NoiDung' => 'Tổng hợp ý kiến đóng góp từ các Khoa/Phòng và xin ý kiến Thường trực Hội đồng trường.',
                'NgayGiaoNhiemVu' => '2026-09-10',
                'NgayGiao' => '2026-09-10',
                'NgayHoanThanhNhiemVu' => '2026-10-20',
                'NgayHoanThanh' => '2026-10-20',
                'TrangThai' => 'COMPLETED',
                'NgayXacNhanHoanThanh' => '2026-10-19',
                'MaNghiQuyet' => $nq2->MaNghiQuyet,
                'MaDonVi' => 1, // VP Hội đồng trường
                'NguoiPhuTrach' => 'ThS. Trần Văn Thư Ký',
                'VaiTro' => 'Đơn vị chủ trì',
            ],
        ];

        foreach ($tasksData as $t) {
            $nhiemVu = NhiemVu::create([
                'TenNhiemVu' => $t['TenNhiemVu'],
                'NoiDung' => $t['NoiDung'],
                'NgayGiaoNhiemVu' => $t['NgayGiaoNhiemVu'],
                'NgayGiao' => $t['NgayGiao'],
                'NgayHoanThanhNhiemVu' => $t['NgayHoanThanhNhiemVu'],
                'NgayHoanThanh' => $t['NgayHoanThanh'],
                'TrangThai' => $t['TrangThai'],
                'NgayXacNhanHoanThanh' => $t['NgayXacNhanHoanThanh'] ?? null,
                'MaNghiQuyet' => $t['MaNghiQuyet'],
            ]);

            PhanCongNhiemVu::create([
                'MaNhiemVu' => $nhiemVu->MaNhiemVu,
                'MaDonVi' => $t['MaDonVi'],
                'VaiTroThucHien' => $t['VaiTro'],
                'TrangThaiThucHien' => $t['TrangThai'] === 'COMPLETED' ? 'COMPLETED' : 'IN_PROGRESS',
                'TenNguoiPhuTrach' => $t['NguoiPhuTrach'],
            ]);
        }
    }
}
