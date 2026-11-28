<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\DonVi;
use App\Models\ThanhVienHdt;
use App\Models\NguoiDung;
use App\Models\VaiTro;
use App\Support\DuplicateNameChecker;
use App\Support\SearchCodeHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class NguoiDungWebController extends Controller
{
    /**
     * Danh sách tài khoản người dùng và phân quyền RBAC
     */
    public function index(Request $request): View|RedirectResponse
    {
        $currentUser = Auth::user();
        if ($currentUser && !in_array($currentUser->role, ['ADMIN', 'CHAIR'])) {
            return redirect()->route('dashboard')->with('error', 'Bạn không có quyền truy cập danh sách quản trị người dùng.');
        }

        $query = NguoiDung::with(['vaiTro', 'donVi', 'thanhVienHdt']);

        if ($search = trim((string) $request->input('search'))) {
            $userId = SearchCodeHelper::extractNumericId($search, ['nd']);

            $query->where(function ($q) use ($search, $userId) {
                $q->where('HoTen', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");

                if ($userId !== null) {
                    $q->orWhere('MaNguoiDung', $userId);
                }
            });
        }

        if ($roleId = $request->input('role_id')) {
            $query->where('MaVaiTro', $roleId);
        }

        if ($deptId = $request->input('department_id')) {
            $query->where('MaDonVi', $deptId);
        }

        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [5, 10, 20, 50, 100])) {
            $perPage = 10;
        }

        $users = $query->orderBy('MaNguoiDung')->paginate($perPage)->withQueryString();
        $roles = VaiTro::all();
        $departments = DonVi::orderBy('TenDonVi')->get();

        return view('pages.nguoidung.danhsach', compact('users', 'roles', 'departments'));
    }

    /**
     * Tạo tài khoản người dùng mới (Dành cho Quản trị viên ADMIN / Chủ tịch HĐT)
     */
    public function store(Request $request): RedirectResponse
    {
        $currentUser = Auth::user();
        if (!$currentUser || !in_array($currentUser->role, ['ADMIN', 'CHAIR'])) {
            return redirect()->route('users.index')->with('error', 'Chỉ Quản trị viên hệ thống (ADMIN) mới có quyền tạo người dùng mới.');
        }

        $validated = $request->validate([
            'HoTen' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:191', 'unique:nguoi_dung,email'],
            'password' => ['required', 'string', 'min:6'],
            'MaVaiTro' => ['required', 'integer', 'exists:vai_tro,MaVaiTro'],
            'MaDonVi' => ['required', 'integer', 'exists:don_vi,MaDonVi'],
            'TrangThai' => ['nullable', 'string', 'in:Hoạt động,Khóa,Cho phép'],
        ], [
            'HoTen.required' => 'Vui lòng nhập họ và tên người dùng.',
            'email.required' => 'Vui lòng nhập địa chỉ email STU.',
            'email.email' => 'Địa chỉ email không đúng định dạng.',
            'email.unique' => 'Địa chỉ email này đã được sử dụng trong hệ thống.',
            'password.required' => 'Vui lòng nhập mật khẩu khởi tạo.',
            'password.min' => 'Mật khẩu phải có tối thiểu 6 ký tự.',
            'MaVaiTro.required' => 'Vui lòng chọn vai trò (Role) cho người dùng.',
            'MaVaiTro.exists' => 'Vai trò được chọn không hợp lệ.',
            'MaDonVi.required' => 'Vui lòng chọn đơn vị công tác.',
            'MaDonVi.exists' => 'Đơn vị công tác không hợp lệ.',
        ]);

        $trimmedFullName = trim($validated['HoTen']);
        if (DuplicateNameChecker::exists(NguoiDung::query(), 'HoTen', $trimmedFullName)) {
            return DuplicateNameChecker::failWeb(
                'HoTen',
                "Tên người dùng \"{$trimmedFullName}\" đã tồn tại trong hệ thống. Vui lòng kiểm tra lại."
            );
        }

        $user = NguoiDung::create([
            'HoTen' => $trimmedFullName,
            'email' => $validated['email'],
            'MatKhau' => Hash::make($validated['password']),
            'MaVaiTro' => $validated['MaVaiTro'],
            'MaDonVi' => $validated['MaDonVi'],
            'TrangThai' => $validated['TrangThai'] ?? 'Hoạt động',
        ]);

        $vaiTro = VaiTro::find($validated['MaVaiTro']);

        // Đồng bộ sang bảng users legacy nếu bảng users tồn tại
        if (DB::getSchemaBuilder()->hasTable('users')) {
            DB::table('users')->updateOrInsert(
                ['email' => $user->email],
                [
                    'name' => $user->HoTen,
                    'full_name' => $user->HoTen,
                    'password' => $user->MatKhau,
                    'role' => $vaiTro?->TenVaiTro ?? 'MEMBER',
                    'department_id' => $user->MaDonVi,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // Tự động phân nhiệm kỳ và liên kết Thành viên Hội đồng trường nếu vai trò tương ứng
        if ($vaiTro && in_array($vaiTro->TenVaiTro, ['CHAIR', 'SECRETARY', 'MEMBER'])) {
            $chucDanh = match ($vaiTro->TenVaiTro) {
                'CHAIR' => 'Chủ tịch Hội đồng trường',
                'SECRETARY' => 'Thư ký Hội đồng trường',
                'MEMBER' => 'Thành viên Hội đồng trường',
                default => 'Thành viên Hội đồng trường',
            };
            ThanhVienHdt::firstOrCreate(
                ['MaNguoiDung' => $user->MaNguoiDung],
                [
                    'ChucDanh' => $chucDanh,
                    'ChucDanhHoiDongTruong' => $chucDanh,
                    'NhiemKy' => '2020-2025',
                    'NgayBatDauThamGia' => now(),
                    'NgayBatDau' => now(),
                    'CoQuyenBieuQuyet' => 1,
                ]
            );
        }

        return redirect()->route('users.index')->with('success', "Đã tạo tài khoản '{$user->HoTen}' ({$user->email}) thành công.");
    }

    /**
     * Cập nhật thông tin và vai trò người dùng
     */
    public function update(Request $request, int|string $id): RedirectResponse
    {
        $currentUser = Auth::user();
        if (!$currentUser || !in_array($currentUser->role, ['ADMIN', 'CHAIR'])) {
            return redirect()->route('users.index')->with('error', 'Chỉ Quản trị viên hệ thống (ADMIN) mới có quyền cập nhật người dùng.');
        }

        $user = NguoiDung::findOrFail($id);

        $validated = $request->validate([
            'HoTen' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:191', 'unique:nguoi_dung,email,' . $user->MaNguoiDung . ',MaNguoiDung'],
            'password' => ['nullable', 'string', 'min:6'],
            'MaVaiTro' => ['required', 'integer', 'exists:vai_tro,MaVaiTro'],
            'MaDonVi' => ['required', 'integer', 'exists:don_vi,MaDonVi'],
            'TrangThai' => ['nullable', 'string', 'in:Hoạt động,Khóa,Cho phép'],
        ]);

        $updateData = [
            'HoTen' => $validated['HoTen'],
            'email' => $validated['email'],
            'MaVaiTro' => $validated['MaVaiTro'],
            'MaDonVi' => $validated['MaDonVi'],
            'TrangThai' => $validated['TrangThai'] ?? 'Hoạt động',
        ];

        if (!empty($validated['password'])) {
            $updateData['MatKhau'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        $vaiTro = VaiTro::find($validated['MaVaiTro']);

        if (DB::getSchemaBuilder()->hasTable('users')) {
            $userSync = [
                'name' => $user->HoTen,
                'full_name' => $user->HoTen,
                'role' => $vaiTro?->TenVaiTro ?? 'MEMBER',
                'department_id' => $user->MaDonVi,
                'is_active' => $user->TrangThai === 'Hoạt động',
                'updated_at' => now(),
            ];
            if (!empty($validated['password'])) {
                $userSync['password'] = Hash::make($validated['password']);
            }
            DB::table('users')->where('email', $user->email)->update($userSync);
        }

        return redirect()->route('users.index')->with('success', "Đã cập nhật thông tin tài khoản '{$user->HoTen}' thành công.");
    }

    /**
     * Ẩn tài khoản người dùng khỏi danh sách hiển thị (Soft Delete)
     */
    public function destroy(int|string $id): RedirectResponse
    {
        $currentUser = Auth::user();
        $role = $currentUser?->role ?? (session('user_role') ?? 'CHAIR');
        if (!in_array($role, ['ADMIN', 'CHAIR'])) {
            return back()->with('error', 'Chỉ Quản trị viên hệ thống (ADMIN) hoặc Chủ tịch HĐT mới có quyền này.');
        }

        $user = NguoiDung::findOrFail($id);
        if ($currentUser && $currentUser->MaNguoiDung == $user->MaNguoiDung) {
            return back()->with('error', 'Không thể tự ẩn tài khoản đang đăng nhập.');
        }

        $user->update(['TrangThai' => 'Đã ẩn']);
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', "Đã ẩn tài khoản \"{$user->HoTen}\" khỏi danh sách hiển thị (dữ liệu được bảo toàn).");
    }
}