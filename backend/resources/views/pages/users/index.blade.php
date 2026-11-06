@extends('layouts.app')

@section('title', 'Quản lý Tài khoản & Phân quyền - Hội đồng trường STU')

@section('content')
<div class="page-container">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <x-breadcrumb :items="[
                ['label' => 'Bảng điều hành', 'url' => route('dashboard')],
                ['label' => 'Quản lý người dùng', 'url' => null]
            ]" />
            <h1 class="page-title mt-2">Quản trị Người dùng &amp; Phân quyền (RBAC)</h1>
            <p class="text-muted">Danh sách tài khoản và vai trò trong hệ thống Quản lý Hội đồng trường STU</p>
        </div>
    </div>

    {{-- Bộ lọc --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('users.index') }}" class="row g-3">
                <div class="col-md-5">
                    <label class="form-label small text-muted">Tìm kiếm người dùng</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Họ tên hoặc email...">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Vai trò (Role)</label>
                    <select name="role_id" class="form-select">
                        <option value="">-- Tất cả vai trò --</option>
                        @foreach($roles as $r)
                            <option value="{{ $r->MaVaiTro }}" {{ request('role_id') == $r->MaVaiTro ? 'selected' : '' }}>
                                {{ $r->TenVaiTro }} ({{ $r->MoTa }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Đơn vị công tác</label>
                    <select name="department_id" class="form-select">
                        <option value="">-- Tất cả đơn vị --</option>
                        @foreach($departments as $d)
                            <option value="{{ $d->MaDonVi }}" {{ request('department_id') == $d->MaDonVi ? 'selected' : '' }}>
                                {{ $d->TenDonVi }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Bảng danh sách người dùng --}}
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <x-table>
                <x-slot:thead>
                    <tr>
                        <th style="width: 70px;">Mã</th>
                        <th>Họ và tên</th>
                        <th>Email đăng nhập</th>
                        <th>Vai trò RBAC</th>
                        <th>Đơn vị</th>
                        <th style="width: 130px;">Trạng thái</th>
                    </tr>
                </x-slot:thead>

                @forelse($users as $u)
                    <tr>
                        <td class="text-muted fw-semibold">#{{ $u->MaNguoiDung }}</td>
                        <td>
                            <div class="fw-bold text-dark">{{ $u->HoTen }}</div>
                            @if($u->thanhVienHdt)
                                <span class="badge bg-light text-primary border small">Thành viên HĐT</span>
                            @endif
                        </td>
                        <td>
                            <div class="text-dark">{{ $u->email }}</div>
                        </td>
                        <td>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                {{ $u->vaiTro?->TenVaiTro ?? 'MEMBER' }}
                            </span>
                        </td>
                        <td>
                            <div class="text-muted small">{{ $u->donVi?->TenDonVi ?? '--' }}</div>
                        </td>
                        <td>
                            @if($u->is_active)
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    <i class="bi bi-check-circle me-1"></i> Hoạt động
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                    <i class="bi bi-lock-fill me-1"></i> Khóa
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            Không tìm thấy tài khoản người dùng nào.
                        </td>
                    </tr>
                @endforelse
            </x-table>
        </div>

        @if($users->hasPages())
            <div class="card-footer bg-white border-top-0 py-3">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
