<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Tạo các Đơn vị STU chuẩn
        $deptHDT = Department::firstOrCreate(['code' => 'VP_HDT'], [
            'name' => 'Văn phòng Hội đồng trường',
            'email' => 'hdt@stu.edu.vn',
            'phone' => '02838505520',
            'is_active' => true,
        ]);

        $deptBGH = Department::firstOrCreate(['code' => 'VP_BGH'], [
            'name' => 'Văn phòng Hiệu trưởng STU',
            'email' => 'bgh@stu.edu.vn',
            'phone' => '02838505521',
            'is_active' => true,
        ]);

        $deptCNTT = Department::firstOrCreate(['code' => 'K_CNTT'], [
            'name' => 'Khoa Công nghệ Thông tin',
            'email' => 'fit@stu.edu.vn',
            'phone' => '02838505522',
            'is_active' => true,
        ]);

        $deptDDT = Department::firstOrCreate(['code' => 'K_DDT'], [
            'name' => 'Khoa Điện - Điện tử',
            'email' => 'fee@stu.edu.vn',
            'phone' => '02838505523',
            'is_active' => true,
        ]);

        $deptDT = Department::firstOrCreate(['code' => 'P_DT'], [
            'name' => 'Phòng Đào tạo',
            'email' => 'daotao@stu.edu.vn',
            'phone' => '02838505524',
            'is_active' => true,
        ]);

        $deptTTCNTT = Department::firstOrCreate(['code' => 'TT_CNTT'], [
            'name' => 'Trung tâm CNTT & Truyền thông',
            'email' => 'itcenter@stu.edu.vn',
            'phone' => '02838505525',
            'is_active' => true,
        ]);

        // 2. Tạo 7 tài khoản đại diện cho 7 vai trò
        $commonPassword = Hash::make('stu@2026');

        $users = [
            [
                'username' => 'admin_hdt',
                'name' => 'Kỹ sư Quản Trị Hệ Thống',
                'full_name' => 'Kỹ sư Quản Trị Hệ Thống',
                'email' => 'admin@stu.edu.vn',
                'password' => $commonPassword,
                'role' => 'ADMIN',
                'department_id' => $deptTTCNTT->id,
                'academic_title' => 'KS',
                'phone' => '0901234567',
                'is_active' => true,
            ],
            [
                'username' => 'chutich_hdt',
                'name' => 'PGS.TS. Nguyễn Văn Chủ Tịch',
                'full_name' => 'PGS.TS. Nguyễn Văn Chủ Tịch',
                'email' => 'chutich_hdt@stu.edu.vn',
                'password' => $commonPassword,
                'role' => 'CHAIR',
                'department_id' => $deptHDT->id,
                'academic_title' => 'PGS.TS',
                'phone' => '0902345678',
                'is_active' => true,
            ],
            [
                'username' => 'thuky_hdt',
                'name' => 'TS. Trần Văn Thư Ký',
                'full_name' => 'TS. Trần Văn Thư Ký',
                'email' => 'thuky_hdt@stu.edu.vn',
                'password' => $commonPassword,
                'role' => 'SECRETARY',
                'department_id' => $deptHDT->id,
                'academic_title' => 'TS',
                'phone' => '0903456789',
                'is_active' => true,
            ],
            [
                'username' => 'thanhvien_hdt',
                'name' => 'TS. Lê Hoàng Thành Viên',
                'full_name' => 'TS. Lê Hoàng Thành Viên',
                'email' => 'thanhvien_hdt@stu.edu.vn',
                'password' => $commonPassword,
                'role' => 'MEMBER',
                'department_id' => $deptDDT->id,
                'academic_title' => 'TS',
                'phone' => '0904567890',
                'is_active' => true,
            ],
            [
                'username' => 'vphieutruong',
                'name' => 'ThS. Hoàng Thị Văn Phòng',
                'full_name' => 'ThS. Hoàng Thị Văn Phòng',
                'email' => 'vphieutruong@stu.edu.vn',
                'password' => $commonPassword,
                'role' => 'RECTOR_OFFICE',
                'department_id' => $deptBGH->id,
                'academic_title' => 'ThS',
                'phone' => '0905678901',
                'is_active' => true,
            ],
            [
                'username' => 'daotao_stu',
                'name' => 'ThS. Đỗ Thị Đại Diện',
                'full_name' => 'ThS. Đỗ Thị Đại Diện',
                'email' => 'daotao@stu.edu.vn',
                'password' => $commonPassword,
                'role' => 'UNIT_REP',
                'department_id' => $deptDT->id,
                'academic_title' => 'ThS',
                'phone' => '0906789012',
                'is_active' => true,
            ],
            [
                'username' => 'cntt_stu',
                'name' => 'TS. Vũ Minh Thực Hiện',
                'full_name' => 'TS. Vũ Minh Thực Hiện',
                'email' => 'cntt@stu.edu.vn',
                'password' => $commonPassword,
                'role' => 'EXECUTING_UNIT',
                'department_id' => $deptCNTT->id,
                'academic_title' => 'TS',
                'phone' => '0907890123',
                'is_active' => true,
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }
}
