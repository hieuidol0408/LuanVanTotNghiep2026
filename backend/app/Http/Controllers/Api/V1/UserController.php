<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DonVi;
use App\Models\User;
use App\Models\VaiTro;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Danh sách vai trò trong hệ thống
     * GET /api/v1/roles
     */
    public function roles(): JsonResponse
    {
        $roles = VaiTro::orderBy('MaVaiTro', 'asc')->get();

        $items = $roles->map(function (VaiTro $role) {
            return [
                'id' => (int) $role->MaVaiTro,
                'code' => $role->TenVaiTro,
                'name' => $role->MoTa ?: $role->TenVaiTro,
                'description' => $role->MoTa,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }

    /**
     * Danh sách tài khoản người dùng
     * GET /api/v1/users
     */
    public function index(Request $request): JsonResponse
    {
        $currentUser = $request->user();

        // Kiểm tra quyền: Admin hoặc Thư ký được tra cứu danh sách
        if ($currentUser && !in_array($currentUser->role, ['ADMIN', 'SECRETARY', 'CHAIR'])) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có thẩm quyền truy cập danh sách người dùng.',
            ], 403);
        }

        $query = User::with(['vaiTro', 'donVi']);

        // 1. Tìm kiếm từ khóa (Họ tên, email)
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('HoTen', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // 2. Lọc theo vai trò (MaVaiTro hoặc code vai trò)
        if ($role = $request->input('role')) {
            if ($role !== 'ALL') {
                if (is_numeric($role)) {
                    $query->where('MaVaiTro', $role);
                } else {
                    $query->whereHas('vaiTro', fn($q) => $q->where('TenVaiTro', $role));
                }
            }
        }

        // 3. Lọc theo đơn vị (MaDonVi)
        if ($deptId = $request->input('department_id')) {
            if ($deptId !== 'ALL') {
                $query->where('MaDonVi', $deptId);
            }
        }

        // 4. Lọc theo trạng thái
        if ($status = $request->input('status')) {
            if ($status !== 'ALL') {
                if ($status === 'ACTIVE') {
                    $query->whereIn('TrangThai', ['Hoạt động', 'ACTIVE']);
                } elseif ($status === 'INACTIVE' || $status === 'LOCKED') {
                    $query->whereIn('TrangThai', ['Khóa', 'INACTIVE', 'LOCKED']);
                } else {
                    $query->where('TrangThai', $status);
                }
            }
        }

        $perPage = min((int) $request->input('perPage', 15), 100);
        $paginated = $query->orderBy('MaNguoiDung', 'asc')->paginate($perPage);

        $items = collect($paginated->items())->map(function (User $user) {
            return $this->formatUser($user);
        });

        return response()->json([
            'success' => true,
            'data' => $items,
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ],
        ]);
    }

    /**
     * Tạo tài khoản người dùng mới
     * POST /api/v1/users
     */
    public function store(Request $request): JsonResponse
    {
        $currentUser = $request->user();

        if ($currentUser && !in_array($currentUser->role, ['ADMIN', 'SECRETARY'])) {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ Quản trị viên hoặc Thư ký được tạo tài khoản người dùng.',
            ], 403);
        }

        $validated = $request->validate([
            'HoTen' => 'required|string|max:100',
            'email' => 'required|email|max:100|unique:nguoi_dung,email',
            'MatKhau' => 'nullable|string|min:6',
            'MaVaiTro' => 'required|exists:vai_tro,MaVaiTro',
            'MaDonVi' => 'nullable|exists:don_vi,MaDonVi',
            'TrangThai' => 'nullable|string|in:Hoạt động,Khóa,ACTIVE,INACTIVE',
        ], [
            'HoTen.required' => 'Họ và tên người dùng là bắt buộc.',
            'email.required' => 'Địa chỉ email là bắt buộc.',
            'email.unique' => 'Địa chỉ email này đã tồn tại trong hệ thống.',
            'MaVaiTro.required' => 'Vui lòng chọn vai trò cho tài khoản.',
            'MaVaiTro.exists' => 'Vai trò được chọn không hợp lệ.',
        ]);

        $defaultPassword = $validated['MatKhau'] ?? 'STU@123456';
        $status = $validated['TrangThai'] ?? 'Hoạt động';

        $user = User::create([
            'HoTen' => $validated['HoTen'],
            'email' => $validated['email'],
            'MatKhau' => Hash::make($defaultPassword),
            'MaVaiTro' => $validated['MaVaiTro'],
            'MaDonVi' => $validated['MaDonVi'] ?? null,
            'TrangThai' => $status,
        ]);

        $user->load(['vaiTro', 'donVi']);

        return response()->json([
            'success' => true,
            'message' => 'Tạo tài khoản người dùng thành công.',
            'data' => $this->formatUser($user),
        ], 201);
    }

    /**
     * Chi tiết tài khoản người dùng
     * GET /api/v1/users/{id}
     */
    public function show(Request $request, $id): JsonResponse
    {
        $currentUser = $request->user();
        $user = User::with(['vaiTro', 'donVi'])->find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy người dùng với mã yêu cầu.',
            ], 404);
        }

        // Cho phép user xem chính mình, hoặc Admin/Secretary/Chair xem
        if ($currentUser && $currentUser->MaNguoiDung != $user->MaNguoiDung && !in_array($currentUser->role, ['ADMIN', 'SECRETARY', 'CHAIR'])) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền xem thông tin tài khoản này.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $this->formatUser($user),
        ]);
    }

    /**
     * Cập nhật thông tin tài khoản người dùng
     * PUT /api/v1/users/{id}
     */
    public function update(Request $request, $id): JsonResponse
    {
        $currentUser = $request->user();

        if ($currentUser && !in_array($currentUser->role, ['ADMIN', 'SECRETARY'])) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền chỉnh sửa tài khoản người dùng.',
            ], 403);
        }

        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy người dùng cần cập nhật.',
            ], 404);
        }

        $validated = $request->validate([
            'HoTen' => 'sometimes|required|string|max:100',
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:100',
                Rule::unique('nguoi_dung', 'email')->ignore($user->MaNguoiDung, 'MaNguoiDung'),
            ],
            'MatKhau' => 'nullable|string|min:6',
            'MaVaiTro' => 'sometimes|required|exists:vai_tro,MaVaiTro',
            'MaDonVi' => 'nullable|exists:don_vi,MaDonVi',
            'TrangThai' => 'sometimes|string|in:Hoạt động,Khóa,ACTIVE,INACTIVE',
        ]);

        if (isset($validated['HoTen'])) {
            $user->HoTen = $validated['HoTen'];
        }
        if (isset($validated['email'])) {
            $user->email = $validated['email'];
        }
        if (!empty($validated['MatKhau'])) {
            $user->MatKhau = Hash::make($validated['MatKhau']);
        }
        if (isset($validated['MaVaiTro'])) {
            $user->MaVaiTro = $validated['MaVaiTro'];
        }
        if (array_key_exists('MaDonVi', $validated)) {
            $user->MaDonVi = $validated['MaDonVi'];
        }
        if (isset($validated['TrangThai'])) {
            $user->TrangThai = $validated['TrangThai'];
        }

        $user->save();
        $user->load(['vaiTro', 'donVi']);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật tài khoản người dùng thành công.',
            'data' => $this->formatUser($user),
        ]);
    }

    /**
     * Khóa hoặc mở khóa tài khoản người dùng
     * POST /api/v1/users/{id}/toggle-status
     */
    public function toggleStatus(Request $request, $id): JsonResponse
    {
        $currentUser = $request->user();

        if ($currentUser && !in_array($currentUser->role, ['ADMIN', 'SECRETARY'])) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền thay đổi trạng thái tài khoản.',
            ], 403);
        }

        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy người dùng.',
            ], 404);
        }

        // Không cho phép tự khóa tài khoản của chính mình
        if ($currentUser && $currentUser->MaNguoiDung == $user->MaNguoiDung) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể tự khóa tài khoản đang đăng nhập của chính bạn.',
            ], 422);
        }

        $isCurrentlyActive = in_array($user->TrangThai, ['Hoạt động', 'ACTIVE']);
        $newStatus = $isCurrentlyActive ? 'Khóa' : 'Hoạt động';

        $user->TrangThai = $newStatus;
        $user->save();

        $actionText = $newStatus === 'Hoạt động' ? 'Mở khóa kích hoạt' : 'Khóa';

        return response()->json([
            'success' => true,
            'message' => "Đã {$actionText} tài khoản thành công.",
            'data' => [
                'id' => (int) $user->MaNguoiDung,
                'status' => $user->TrangThai,
                'is_active' => $user->is_active,
            ],
        ]);
    }

    /**
     * Đặt lại mật khẩu mặc định cho người dùng
     * POST /api/v1/users/{id}/reset-password
     */
    public function resetPassword(Request $request, $id): JsonResponse
    {
        $currentUser = $request->user();

        if ($currentUser && !in_array($currentUser->role, ['ADMIN', 'SECRETARY'])) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền đặt lại mật khẩu người dùng.',
            ], 403);
        }

        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy người dùng.',
            ], 404);
        }

        $newPassword = $request->input('MatKhau', 'STU@123456');
        $user->MatKhau = Hash::make($newPassword);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Đặt lại mật khẩu thành công. Mật khẩu mới là: ' . $newPassword,
        ]);
    }

    /**
     * Xóa tài khoản người dùng
     * DELETE /api/v1/users/{id}
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        $currentUser = $request->user();

        if ($currentUser && $currentUser->role !== 'ADMIN') {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ Quản trị viên hệ thống (Admin) mới có quyền xóa tài khoản.',
            ], 403);
        }

        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy người dùng.',
            ], 404);
        }

        if ($currentUser && $currentUser->MaNguoiDung == $user->MaNguoiDung) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể tự xóa tài khoản của chính mình.',
            ], 422);
        }

        // Chuyển sang trạng thái Khóa thay vì xóa vật lý nếu có dữ liệu liên quan
        $user->TrangThai = 'Khóa';
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Đã vô hiệu hóa và khóa tài khoản thành công.',
        ]);
    }

    /**
     * Chuẩn hóa cấu trúc User trả về API
     */
    private function formatUser(User $user): array
    {
        $roleName = $user->vaiTro?->MoTa ?: ($user->vaiTro?->TenVaiTro ?: 'Thành viên');
        $roleCode = $user->vaiTro?->TenVaiTro ?: 'MEMBER';

        return [
            'id' => (int) $user->MaNguoiDung,
            'full_name' => (string) $user->HoTen,
            'email' => (string) $user->email,
            'role' => $roleCode,
            'role_id' => $user->MaVaiTro ? (int) $user->MaVaiTro : null,
            'role_name' => $roleName,
            'department_id' => $user->MaDonVi ? (int) $user->MaDonVi : null,
            'department_name' => $user->donVi?->TenDonVi ?: 'Chưa phân bổ',
            'status' => in_array($user->TrangThai, ['Hoạt động', 'ACTIVE']) ? 'ACTIVE' : 'LOCKED',
            'status_label' => in_array($user->TrangThai, ['Hoạt động', 'ACTIVE']) ? 'Hoạt động' : 'Đã khóa',
            'is_active' => $user->is_active,
            'created_at' => $user->created_at ? $user->created_at->format('Y-m-d H:i:s') : null,
        ];
    }
}
