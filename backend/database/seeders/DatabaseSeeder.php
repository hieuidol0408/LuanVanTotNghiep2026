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
                        'ChucDanhHoiDongTruong' => $chucDanh,
                        'NhiemKy' => '2020-2025',
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
        if ($agenda1) {
            \App\Models\NoiDungBieuQuyet::firstOrCreate(
                ['MaChuongTrinh' => $agenda1->MaChuongTrinh],
                [
                    'NoiDung' => 'Biểu quyết thông qua Đề án mở ngành đào tạo Kỹ thuật Robot & Trí tuệ nhân tạo (AI) bậc Đại học',
                    'ThoiGianMo' => null,
                    'ThoiGianDong' => now()->addHours(2),
                    'TrangThai' => 'PENDING',
                    'KetQua' => 'PENDING',
                    'GhiChu' => json_encode(['voting_type' => 'SECRET', 'pass_threshold' => 65]),
                ]
            );
        }
    }
}
