<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class DangNhapController extends Controller
{
    /**
     * Map vai trò sang danh sách thẩm quyền và nhãn hiển thị
     */
    private function getRoleDetails(string $role): array
    {
        $roleMap = [
            'ADMIN' => [
                'name' => 'Quản trị viên hệ thống',
                'avatar_initials' => 'AD',
                'duties_badge' => 'Quản trị tài khoản, phân quyền & hệ thống',
                'can_vote' => false,
                'allowed_routes' => ['/', '/components', '/users', '/councils', '/documents', '/resolutions'],
            ],
            'CHAIR' => [
                'name' => 'Chủ tịch Hội đồng trường',
                'avatar_initials' => 'CT',
                'duties_badge' => 'Chủ trì & Ký ban hành Nghị quyết',
                'can_vote' => true,
                'allowed_routes' => ['/', '/components', '/councils', '/dossiers', '/meetings', '/voting', '/resolutions', '/tasks', '/documents'],
            ],
            'SECRETARY' => [
                'name' => 'Thư ký Hội đồng trường',
                'avatar_initials' => 'TK',
                'duties_badge' => 'Thẩm tra & Điều hành phiên họp',
                'can_vote' => true,
                'allowed_routes' => ['/', '/components', '/councils', '/dossiers', '/meetings', '/voting', '/resolutions', '/tasks', '/documents'],
            ],
            'MEMBER' => [
                'name' => 'Thành viên Hội đồng trường',
                'avatar_initials' => 'TV',
                'duties_badge' => 'Tham dự họp & Thực hiện biểu quyết',
                'can_vote' => true,
                'allowed_routes' => ['/', '/components', '/councils', '/meetings', '/voting', '/resolutions', '/tasks', '/documents'],
            ],
            'RECTOR_OFFICE' => [
                'name' => 'Văn phòng Hiệu trưởng',
                'avatar_initials' => 'VP',
                'duties_badge' => 'Tham mưu Ban Giám hiệu & Giám sát thực thi',
                'can_vote' => false,
                'allowed_routes' => ['/', '/components', '/resolutions', '/tasks', '/documents'],
            ],
            'UNIT_REP' => [
                'name' => 'Đại diện Đơn vị trình hồ sơ',
                'avatar_initials' => 'ĐT',
                'duties_badge' => 'Nộp tờ trình & Theo dõi thẩm tra',
                'can_vote' => false,
                'allowed_routes' => ['/', '/components', '/dossiers', '/resolutions', '/documents'],
            ],
            'EXECUTING_UNIT' => [
                'name' => 'Đơn vị thực hiện Nghị quyết',
                'avatar_initials' => 'TH',
                'duties_badge' => 'Thực hiện nhiệm vụ & Nộp minh chứng',
                'can_vote' => false,
                'allowed_routes' => ['/', '/components', '/tasks', '/resolutions', '/documents'],
            ],
        ];

        return $roleMap[$role] ?? [
            'name' => 'Thành viên Hội đồng trường',
            'avatar_initials' => 'TV',
            'duties_badge' => 'Tham dự họp',
            'can_vote' => false,
            'allowed_routes' => ['/', '/components'],
        ];
    }

    /**
     * Chuẩn hóa đối tượng User trả về cho Frontend
     */
    private function formatUserResponse(User $user): array
    {
        $roleInfo = $this->getRoleDetails($user->role);

        return [
            'id' => $user->id,
            'key' => $user->role,
            'name' => $roleInfo['name'],
            'role_name' => $roleInfo['name'],
            'user_name' => $user->full_name ?? $user->name,
            'full_name' => $user->full_name ?? $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'academic_title' => $user->academic_title,
            'avatar_initials' => $roleInfo['avatar_initials'],
            'duties_badge' => $roleInfo['duties_badge'],
            'department' => $user->department?->name ?? 'Đang cập nhật',
            'department_id' => $user->department_id,
            'roles' => [$user->role],
            'role' => $user->role,
            'can_vote' => $roleInfo['can_vote'],
            'allowed_routes' => $roleInfo['allowed_routes'],
        ];
    }

    /**
     * Đăng nhập: POST /api/v1/auth/login
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Vui lòng nhập địa chỉ email.',
            'email.email' => 'Địa chỉ email không đúng định dạng.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
        ]);

        $user = User::with('department')->where('email', strtolower(trim($validated['email'])))->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Thông tin email hoặc mật khẩu không chính xác.',
                'data' => null,
                'errors' => [
                    'email' => ['Thông tin email hoặc mật khẩu không chính xác.'],
                ],
            ], 422);
        }

        if (! $user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Tài khoản đã bị tạm khóa. Vui lòng liên hệ Quản trị viên.',
                'data' => null,
                'errors' => null,
            ], 403);
        }

        // Tạo Bearer Token Sanctum
        $token = $user->createToken('stu_access_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Đăng nhập thành công.',
            'data' => [
                'token' => $token,
                'user' => $this->formatUserResponse($user),
            ],
            'errors' => null,
            'meta' => null,
        ], 200);
    }

    /**
     * Thông tin người dùng hiện tại: GET /api/v1/auth/me
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Chưa xác thực.',
                'data' => null,
            ], 401);
        }

        $user->load('department');

        return response()->json([
            'success' => true,
            'message' => 'Lấy thông tin người dùng thành công.',
            'data' => [
                'user' => $this->formatUserResponse($user),
            ],
            'errors' => null,
            'meta' => null,
        ], 200);
    }

    /**
     * Đăng xuất: POST /api/v1/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user) {
            $user->currentAccessToken()?->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Đăng xuất thành công.',
            'data' => null,
            'errors' => null,
            'meta' => null,
        ], 200);
    }
}
