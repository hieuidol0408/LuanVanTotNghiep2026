<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DonVi;
use App\Models\ThanhVienHdt;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CouncilController extends Controller
{
    /**
     * Danh sách Thành viên Hội đồng trường
     * GET /api/v1/councils/members
     */
    public function members(Request $request): JsonResponse
    {
        $query = ThanhVienHdt::with(['nguoiDung.donVi', 'nguoiDung.vaiTro']);

        if ($term = $request->query('term')) {
            if ($term !== 'ALL') {
                $query->where('NhiemKy', $term);
            }
        }

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('ChucDanhHoiDongTruong', 'like', "%{$search}%")
                    ->orWhereHas('nguoiDung', function ($uQ) use ($search) {
                        $uQ->where('HoTen', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $members = $query->orderBy('MaThanhVien', 'asc')->get();

        $items = $members->map(function (ThanhVienHdt $tv) {
            $user = $tv->nguoiDung;

            return [
                'id' => (string) $tv->MaThanhVien,
                'user_id' => $user?->MaNguoiDung,
                'full_name' => $user?->HoTen ?? 'Thành viên HĐT',
                'email' => $user?->email,
                'academic_title' => 'TS',
                'council_role' => $tv->ChucDanhHoiDongTruong,
                'term' => $tv->NhiemKy,
                'department' => $user?->donVi?->TenDonVi ?? 'Hội đồng trường',
                'start_date' => $tv->NgayBatDau ? date('Y-m-d', strtotime($tv->NgayBatDau)) : null,
                'end_date' => $tv->NgayKetThuc ? date('Y-m-d', strtotime($tv->NgayKetThuc)) : null,
                'can_vote' => true,
                'status' => 'ACTIVE',
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách Thành viên Hội đồng trường thành công.',
            'data' => $items,
            'errors' => null,
        ]);
    }

    /**
     * Danh sách các Khóa nhiệm kỳ
     * GET /api/v1/councils/terms
     */
    public function terms(): JsonResponse
    {
        $terms = ThanhVienHdt::distinct()->pluck('NhiemKy')->filter()->values();

        if ($terms->isEmpty()) {
            $terms = collect(['Khóa IV (2020-2025)', 'Khóa V (2025-2030)']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách Khóa nhiệm kỳ thành công.',
            'data' => $terms,
            'errors' => null,
        ]);
    }

    /**
     * Danh sách Đơn vị / Phòng ban trong trường
     * GET /api/v1/departments
     */
    public function departments(): JsonResponse
    {
        $departments = DonVi::orderBy('MaDonVi', 'asc')->get();

        $items = $departments->map(fn (DonVi $d) => [
            'id' => $d->MaDonVi,
            'name' => $d->TenDonVi,
            'status' => $d->TrangThai,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách Đơn vị thành công.',
            'data' => $items,
            'errors' => null,
        ]);
    }
}
