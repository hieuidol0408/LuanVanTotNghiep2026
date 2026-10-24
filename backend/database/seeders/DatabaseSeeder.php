<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\DonVi;
use App\Models\ThanhVienHdt;
use App\Models\User;
use App\Models\VaiTro;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed bảng VAI_TRO
        $roles = [
            'ADMIN' => 'Quản trị viên hệ thống',
            'CHAIR' => 'Chủ tịch Hội đồng trường',
            'SECRETARY' => 'Thư ký Hội đồng trường',
            'MEMBER' => 'Thành viên Hội đồng trường',
            'RECTOR_OFFICE' => 'Văn phòng Hiệu trưởng',
            'UNIT_REP' => 'Đại diện đơn vị trình hồ sơ',
            'EXECUTING_UNIT' => 'Đơn vị thực hiện nghị quyết',
        ];

        $roleMap = [];
        foreach ($roles as $roleName => $moTa) {
            $vt = VaiTro::firstOrCreate(
                ['TenVaiTro' => $roleName],
                ['MoTa' => $moTa]
            );
            $roleMap[$roleName] = $vt->MaVaiTro;
        }

        // 2. Seed bảng DON_VI
        $departmentsData = [
            'VP_HDT' => 'Văn phòng Hội đồng trường',
            'VP_BGH' => 'Văn phòng Hiệu trưởng STU',
            'K_CNTT' => 'Khoa Công nghệ Thông tin',
            'K_DDT' => 'Khoa Điện - Điện tử',
            'P_DT' => 'Phòng Đào tạo',
            'TT_CNTT' => 'Trung tâm CNTT & Truyền thông',
        ];

        $donViMap = [];
        foreach ($departmentsData as $code => $tenDonVi) {
            $dv = DonVi::firstOrCreate(
                ['TenDonVi' => $tenDonVi],
                ['TrangThai' => 'Hoạt động']
            );
            $donViMap[$code] = $dv->MaDonVi;

            // Đồng bộ sang bảng departments legacy nếu có
            if (DB::getSchemaBuilder()->hasTable('departments')) {
                Department::firstOrCreate(
                    ['code' => $code],
                    ['name' => $tenDonVi, 'email' => strtolower($code) . '@stu.edu.vn', 'phone' => '02838505520', 'is_active' => true]
                );
            }
        }

        // 3. Seed bảng NGUOI_DUNG
        $commonPassword = Hash::make('stu@2026');

        $users = [
            [
                'email' => 'admin@stu.edu.vn',
                'HoTen' => 'Kỹ sư Quản Trị Hệ Thống',
                'MatKhau' => $commonPassword,
                'TrangThai' => 'Hoạt động',
                'role' => 'ADMIN',
                'dept_code' => 'TT_CNTT',
            ],
            [
                'email' => 'chutich_hdt@stu.edu.vn',
                'HoTen' => 'PGS.TS. Trần Văn Chủ Tịch',
                'MatKhau' => $commonPassword,
                'TrangThai' => 'Hoạt động',
                'role' => 'CHAIR',
                'dept_code' => 'VP_HDT',
            ],
            [
                'email' => 'thuky_hdt@stu.edu.vn',
                'HoTen' => 'ThS. Nguyễn Thị Thư Ký',
                'MatKhau' => $commonPassword,
                'TrangThai' => 'Hoạt động',
                'role' => 'SECRETARY',
                'dept_code' => 'VP_HDT',
            ],
            [
                'email' => 'thanhvien_hdt@stu.edu.vn',
                'HoTen' => 'TS. Lê Văn Thành Viên',
                'MatKhau' => $commonPassword,
                'TrangThai' => 'Hoạt động',
                'role' => 'MEMBER',
                'dept_code' => 'K_DDT',
            ],
            [
                'email' => 'vphieutruong@stu.edu.vn',
                'HoTen' => 'Văn phòng Hiệu trưởng STU',
                'MatKhau' => $commonPassword,
                'TrangThai' => 'Hoạt động',
                'role' => 'RECTOR_OFFICE',
                'dept_code' => 'VP_BGH',
            ],
            [
                'email' => 'daotao@stu.edu.vn',
                'HoTen' => 'ThS. Hoàng Đơn Vị Trình',
                'MatKhau' => $commonPassword,
                'TrangThai' => 'Hoạt động',
                'role' => 'UNIT_REP',
                'dept_code' => 'P_DT',
            ],
            [
                'email' => 'cntt@stu.edu.vn',
                'HoTen' => 'KS. Phạm Đơn Vị Thi Hành',
                'MatKhau' => $commonPassword,
                'TrangThai' => 'Hoạt động',
                'role' => 'EXECUTING_UNIT',
                'dept_code' => 'K_CNTT',
            ],
        ];

        foreach ($users as $u) {
            $user = User::updateOrCreate(
                ['email' => $u['email']],
                [
                    'HoTen' => $u['HoTen'],
                    'MatKhau' => $u['MatKhau'],
                    'TrangThai' => $u['TrangThai'],
                    'MaVaiTro' => $roleMap[$u['role']],
                    'MaDonVi' => $donViMap[$u['dept_code']],
                ]
            );

            // Đồng bộ sang bảng users legacy nếu bảng users tồn tại
            if (DB::getSchemaBuilder()->hasTable('users')) {
                DB::table('users')->updateOrInsert(
                    ['email' => $u['email']],
                    [
                        'name' => $u['HoTen'],
                        'full_name' => $u['HoTen'],
                        'password' => $u['MatKhau'],
                        'role' => $u['role'],
                        'department_id' => $donViMap[$u['dept_code']],
                        'is_active' => true,
                        'updated_at' => now(),
                    ]
                );
            }

            // 4. Seed bảng THANH_VIEN_HDT cho các thành viên Hội đồng trường
            if (in_array($u['role'], ['CHAIR', 'SECRETARY', 'MEMBER'])) {
                $chucDanh = match ($u['role']) {
                    'CHAIR' => 'Chủ tịch Hội đồng trường',
                    'SECRETARY' => 'Thư ký Hội đồng trường',
                    'MEMBER' => 'Thành viên Hội đồng trường',
                };

                ThanhVienHdt::updateOrCreate(
                    ['MaNguoiDung' => $user->MaNguoiDung],
                    [
                        'ChucDanh' => $chucDanh,
                        'ChucDanhHoiDongTruong' => $chucDanh,
                        'NhiemKy' => '2020-2025',
                        'NgayBatDauThamGia' => '2020-09-01',
                        'NgayBatDau' => '2020-09-01',
                        'NgayKetThuc' => '2025-08-31',
                        'CoQuyenBieuQuyet' => 1,
                    ]
                );
            }
        }

        // 5. Seed phiên họp mẫu PHIEN_HOP
        $phienHop1 = \App\Models\PhienHop::firstOrCreate(
            ['TenPhienHop' => 'Phiên họp lần thứ 5 Hội đồng trường STU (Khóa IV)'],
            [
                'NgayHop' => '2026-11-15',
                'ThoiGianBatDau' => '2026-11-15 08:30:00',
                'ThoiGianKetThuc' => '2026-11-15 11:45:00',
                'DiaDiem' => 'Phòng họp A101, Nhà A - Trụ sở chính STU',
                'HinhThuc' => 'IN_PERSON',
                'TrangThai' => 'SCHEDULED',
                'GhiChu' => 'Phiên họp định kỳ quý IV xem xét đề án mở ngành mới, thảo luận điều chỉnh quy chế chi tiêu và kiện toàn nhân sự Ban Kiểm soát.',
            ]
        );

        // Gắn thành viên tham dự cho phiên họp 1
        $thanhViens = ThanhVienHdt::all();
        foreach ($thanhViens as $tv) {
            \App\Models\ThamDu::firstOrCreate(
                ['MaPhienHop' => $phienHop1->MaPhienHop, 'MaThanhVien' => $tv->MaThanhVien],
                [
                    'TrangThaiThamDu' => 'PRESENT',
                    'ThoiDiemXacNhan' => '2026-11-15 08:15:00',
                    'GhiChu' => 'Có mặt đúng giờ',
                ]
            );
        }

        // Tạo hồ sơ trình mẫu HO_SO_TRINH
        $userDaoTao = User::where('email', 'daotao@stu.edu.vn')->first();
        $hs1 = \App\Models\HoSoTrinh::firstOrCreate(
            ['TieuDe' => 'Đề án mở ngành đào tạo Kỹ thuật Robot & Trí tuệ nhân tạo (AI) bậc Đại học'],
            [
                'NoiDungTomTat' => 'Mở ngành đào tạo mới đón đầu xu hướng công nghiệp 4.0 và nhu cầu nguồn nhân lực công nghệ cao.',
                'NgayTrinh' => '2026-10-15',
                'LinhVuc' => 'Đào tạo & Công nghệ',
                'TrangThai' => 'APPROVED_FOR_MEETING',
                'GhiChu' => 'Đã được Thư ký HĐT thẩm tra đạt yêu cầu',
                'MaDonVi' => $donViMap['P_DT'],
                'MaNguoiDung' => $userDaoTao?->MaNguoiDung ?? 1,
            ]
        );

        // Gắn vào chương trình họp CHUONG_TRINH_HOP
        \App\Models\ChuongTrinhHop::firstOrCreate(
            ['MaPhienHop' => $phienHop1->MaPhienHop, 'ThuTu' => 1],
            [
                'TrangThai' => 'PENDING',
                'GhiChu' => 'Xem xét và thảo luận Đề án mở ngành đào tạo Kỹ thuật Robot & Trí tuệ nhân tạo (AI) bậc Đại học',
                'MaHoSo' => $hs1->MaHoSo,
            ]
        );

        // Thêm khách mời KHACH_MOI & KHACH_MOI_PHIEN_HOP
        $km = \App\Models\KhachMoi::firstOrCreate(
            ['HoTen' => 'GS.TS. Hoàng Khách Mời'],
            [
                'ChucVu' => 'Chuyên gia Cố vấn Trí tuệ Nhân tạo',
                'DonViCongTac' => 'Hội đồng Khoa học & Công nghệ TP.HCM',
                'Email' => 'expert@hcm.edu.vn',
                'SoDienThoai' => '0912345678',
            ]
        );

        \App\Models\KhachMoiPhienHop::firstOrCreate(
            ['MaPhienHop' => $phienHop1->MaPhienHop, 'MaKhachMoi' => $km->MaKhachMoi],
            [
                'NoiDungMoi' => 'Báo cáo tham luận và tư vấn khung chương trình đào tạo ngành Robot & AI',
                'TrangThaiThamDu' => 'INVITED',
            ]
        );

        // 6. Seed nội dung biểu quyết mẫu NOI_DUNG_BIEU_QUYET
        $agenda1 = \App\Models\ChuongTrinhHop::where('MaPhienHop', $phienHop1->MaPhienHop)->first();
        $vote1 = null;
        if ($agenda1) {
            $vote1 = \App\Models\NoiDungBieuQuyet::firstOrCreate(
                ['MaChuongTrinh' => $agenda1->MaChuongTrinh],
                [
                    'NoiDung' => 'Biểu quyết thông qua Đề án mở ngành đào tạo Kỹ thuật Robot & Trí tuệ nhân tạo (AI) bậc Đại học',
                    'ThoiGianMo' => null,
                    'ThoiGianDong' => now()->addHours(2),
                    'TrangThai' => 'CLOSED',
                    'KetQua' => 'PASSED',
                    'GhiChu' => json_encode(['voting_type' => 'SECRET', 'pass_threshold' => 65]),
                ]
            );
        }

        // 7. Seed Nghị quyết mẫu NGHI_QUYET
        $nq1 = \App\Models\NghiQuyet::firstOrCreate(
            ['SoNghiQuyet' => '01/NQ-HĐT-2026'],
            [
                'NgayBanHanh' => '2026-10-20',
                'TrichYeu' => 'Về việc phê duyệt chủ trương mở ngành đào tạo Kỹ thuật Robot & Trí tuệ nhân tạo (AI)',
                'NoiDung' => 'Hội đồng trường Trường Đại học Công nghệ Sài Gòn quyết nghị thông qua chủ trương mở ngành đào tạo trình độ đại học Kỹ thuật Robot & Trí tuệ nhân tạo từ năm học 2026-2027.',
                'TrangThai' => 'PUBLISHED',
                'MaPhienHop' => $phienHop1->MaPhienHop,
            ]
        );

        $nq2 = \App\Models\NghiQuyet::firstOrCreate(
            ['SoNghiQuyet' => '02/NQ-HĐT-2026'],
            [
                'NgayBanHanh' => '2026-10-22',
                'TrichYeu' => 'Về việc ban hành Quy chế chi tiêu nội bộ và định mức tài chính năm 2026',
                'NoiDung' => 'Hội đồng trường thông qua dự thảo Quy chế chi tiêu nội bộ áp dụng thống nhất trong toàn trường từ ngày 01/01/2026.',
                'TrangThai' => 'SIGNED',
                'MaPhienHop' => $phienHop1->MaPhienHop,
            ]
        );

        if ($hs1) {
            $nq1->hoSoTrinh()->syncWithoutDetaching([$hs1->MaHoSo]);
        }
        if ($vote1) {
            $nq1->noiDungBieuQuyet()->syncWithoutDetaching([$vote1->MaNoiDung]);
        }

        // 8. Seed Nhiệm vụ phân rã NHIEM_VU & PHAN_CONG_NHIEM_VU & BAO_CAO
        $userCntt = User::where('email', 'cntt@stu.edu.vn')->first();
        $task1 = \App\Models\NghiQuyet::first()->nhiemVu()->firstOrCreate(
            ['TenNhiemVu' => 'Xây dựng Đề án mở ngành Robot & AI và ban hành chuẩn đầu ra'],
            [
                'NoiDung' => 'Chủ trì phối hợp cùng Phòng Đào tạo hoàn thiện hồ sơ đề án mở ngành đào tạo mới theo Thông tư 02/2022/TT-BGDĐT.',
                'NgayGiaoNhiemVu' => '2026-10-21',
                'NgayGiao' => '2026-10-21',
                'NgayHoanThanhNhiemVu' => '2026-12-31',
                'NgayHoanThanh' => '2026-12-31',
                'TrangThai' => 'IN_PROGRESS',
            ]
        );

        $phanCong1 = \App\Models\PhanCongNhiemVu::firstOrCreate(
            ['MaNhiemVu' => $task1->MaNhiemVu, 'MaDonVi' => $donViMap['K_CNTT']],
            [
                'VaiTroThucHien' => 'CHỦ TRÌ',
                'TrangThaiThucHien' => 'Đang thực hiện',
                'TenNguoiPhuTrach' => 'KS. Phạm Đơn Vị Thi Hành',
            ]
        );

        \App\Models\BaoCao::firstOrCreate(
            ['MaPhanCong' => $phanCong1->MaPhanCong, 'TienDo' => 45],
            [
                'NgayBaoCao' => '2026-10-23',
                'NoiDung' => 'Đã hoàn thành dự thảo khung 132 tín chỉ và lấy ý kiến hội đồng khoa học khoa.',
                'NoiDungTienDo' => 'Đã hoàn thành dự thảo khung 132 tín chỉ và lấy ý kiến hội đồng khoa học khoa.',
                'NoiDungKhoKhan' => 'Cần hỗ trợ kinh phí mời chuyên gia doanh nghiệp phản biện.',
                'TrangThaiXacNhan' => 'APPROVED',
                'NgayXacNhan' => '2026-10-24',
                'GhiChu' => 'Thư ký HĐT đã tiếp nhận và ghi nhận tiến độ.',
                'MaNguoiDung' => $userCntt?->MaNguoiDung ?? 1,
            ]
        );

        // 9. Seed tài liệu mẫu TAI_LIEU & liên kết phiên họp / nghị quyết
        $userThuky = User::where('email', 'thuky_hdt@stu.edu.vn')->first();
        $doc1 = \App\Models\TaiLieu::firstOrCreate(
            ['TenTaiLieu' => 'Quy_che_to_chuc_va_hoat_dong_HDT_STU.pdf'],
            [
                'LoaiTaiLieu' => 'REGULATION',
                'DuongDan' => 'documents/regulations/Quy_che_to_chuc_va_hoat_dong_HDT_STU.pdf',
                'DinhDang' => 'application/pdf',
                'DungLuong' => 1572864, // 1.5 MB
                'NgayTaiLen' => '2026-01-10 09:00:00',
                'MaNguoiDung' => $userThuky?->MaNguoiDung ?? 1,
            ]
        );

        $doc2 = \App\Models\TaiLieu::firstOrCreate(
            ['TenTaiLieu' => 'Chuong_trinh_hop_phien_5_chinh_thuc.pdf'],
            [
                'LoaiTaiLieu' => 'PUBLIC_AGENDA',
                'DuongDan' => 'documents/meetings/Chuong_trinh_hop_phien_5_chinh_thuc.pdf',
                'DinhDang' => 'application/pdf',
                'DungLuong' => 460800, // 450 KB
                'NgayTaiLen' => '2026-11-01 14:30:00',
                'MaNguoiDung' => $userThuky?->MaNguoiDung ?? 1,
            ]
        );

        $doc3 = \App\Models\TaiLieu::firstOrCreate(
            ['TenTaiLieu' => 'To_trinh_kien_toan_nhan_su_mat.pdf'],
            [
                'LoaiTaiLieu' => 'INTERNAL',
                'DuongDan' => 'documents/internal/To_trinh_kien_toan_nhan_su_mat.pdf',
                'DinhDang' => 'application/pdf',
                'DungLuong' => 798720, // 780 KB
                'NgayTaiLen' => '2026-11-05 10:15:00',
                'MaNguoiDung' => $userThuky?->MaNguoiDung ?? 1,
            ]
        );

        $phienHop1->taiLieu()->syncWithoutDetaching([$doc2->MaTaiLieu, $doc3->MaTaiLieu]);
        $nq1->taiLieu()->syncWithoutDetaching([$doc1->MaTaiLieu]);
    }
}
