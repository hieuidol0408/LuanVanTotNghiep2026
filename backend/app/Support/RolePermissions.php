<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;

/**
 * Ma trận phân quyền tập trung theo 7 vai trò của Hội đồng trường STU.
 * Mỗi vai trò chỉ nhìn thấy menu, khối dashboard và nút thao tác mà mình được phép thực hiện.
 */
class RolePermissions
{
    public const MODULE_ROLES = [
        'dashboard' => ['ADMIN', 'CHAIR', 'SECRETARY', 'MEMBER', 'RECTOR_OFFICE', 'UNIT_REP', 'EXECUTING_UNIT'],
        'meetings' => ['ADMIN', 'CHAIR', 'SECRETARY', 'MEMBER', 'RECTOR_OFFICE'],
        'meetings.history' => ['ADMIN', 'CHAIR', 'SECRETARY', 'MEMBER', 'RECTOR_OFFICE'],
        'submissions' => ['ADMIN', 'CHAIR', 'SECRETARY', 'MEMBER', 'UNIT_REP'],
        'agendas' => ['ADMIN', 'CHAIR', 'SECRETARY', 'MEMBER'],
        'votes' => ['ADMIN', 'CHAIR', 'SECRETARY', 'MEMBER'],
        'resolutions' => ['ADMIN', 'CHAIR', 'SECRETARY', 'MEMBER', 'RECTOR_OFFICE', 'UNIT_REP', 'EXECUTING_UNIT'],
        'tasks' => ['ADMIN', 'CHAIR', 'SECRETARY', 'RECTOR_OFFICE', 'EXECUTING_UNIT'],
        'task-reports' => ['ADMIN', 'CHAIR', 'SECRETARY', 'RECTOR_OFFICE', 'EXECUTING_UNIT'],
        'documents' => ['ADMIN', 'CHAIR', 'SECRETARY', 'MEMBER', 'RECTOR_OFFICE', 'UNIT_REP', 'EXECUTING_UNIT'],
        'members' => ['ADMIN', 'CHAIR', 'SECRETARY', 'MEMBER', 'RECTOR_OFFICE'],
        'users' => ['ADMIN', 'CHAIR'],
    ];

    public static function currentRole(): string
    {
        $raw = Auth::user()?->role ?? (session('user_role') ?? 'CHAIR');

        return strtoupper(trim((string) $raw)) ?: 'CHAIR';
    }

    public static function canAccessModule(?string $role, string $module): bool
    {
        $normalized = strtoupper(trim((string) ($role ?: 'CHAIR')));

        return in_array($normalized, self::MODULE_ROLES[$module] ?? [], true);
    }

    public static function canAnyModule(?string $role, array $modules): bool
    {
        foreach ($modules as $module) {
            if (self::canAccessModule($role, $module)) {
                return true;
            }
        }

        return false;
    }

    public static function label(?string $role): string
    {
        return match (strtoupper(trim((string) $role))) {
            'ADMIN' => 'Quản trị hệ thống',
            'CHAIR' => 'Chủ tịch HĐT',
            'SECRETARY' => 'Thư ký HĐT',
            'MEMBER' => 'Thành viên HĐT',
            'RECTOR_OFFICE' => 'VP Hiệu trưởng',
            'UNIT_REP' => 'Đơn vị trình hồ sơ',
            'EXECUTING_UNIT' => 'Đơn vị thực hiện',
            default => (string) ($role ?: 'Thành viên HĐT'),
        };
    }
}
