@extends('layouts.app')

@section('title', 'Quản lý Tài khoản & Phân quyền - Hội đồng trường STU')

@section('content')
<div class="stu-page-container">
    <div class="stu-page-header stu-flex stu-items-center stu-justify-between stu-mb-6">
        <div>
            <x-breadcrumb :items="['Quản lý người dùng' => null]" />
            <h1 class="stu-page-title" style="font-size: 24px; font-weight: 800; color: var(--color-primary); margin: 0;">Quản trị Người dùng &amp; Phân quyền (RBAC)</h1>
            <p style="font-size: 13px; color: var(--color-text-muted); margin: 4px 0 0 0;">Danh sách tài khoản và vai trò trong hệ thống Quản lý Hội đồng trường STU</p>
        </div>
    </div>

    {{-- Bộ lọc --}}
    <div class="stu-card stu-mb-6" style="padding: 20px 24px; border-radius: 12px; border: 1px solid var(--color-border); box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05); background: #ffffff;">
        <form method="GET" action="{{ route('users.index') }}" class="stu-grid" style="grid-template-columns: 2fr 1.5fr 1.5fr auto auto; gap: 14px; align-items: flex-end;">
            <div class="stu-form-group" style="margin-bottom: 0;">
                <label class="stu-form-label">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <span>Tìm kiếm người dùng</span>
                </label>
                <div style="position: relative;">
                    <svg width="15" height="15" fill="none" stroke="#94A3B8" stroke-width="2" viewBox="0 0 24 24" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); pointer-events: none;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <input type="text" name="search" value="{{ request('search') }}" class="stu-form-control" style="padding-left: 36px;" placeholder="Họ tên hoặc email...">
                </div>
            </div>
            <div class="stu-form-group" style="margin-bottom: 0;">
                <label class="stu-form-label">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span>Vai trò (Role)</span>
                </label>
                <select name="role_id" class="stu-form-control">
                    <option value="">-- Tất cả vai trò --</option>
                    @foreach($roles as $r)
                        <option value="{{ $r->MaVaiTro }}" {{ request('role_id') == $r->MaVaiTro ? 'selected' : '' }}>
                            {{ $r->TenVaiTro }} ({{ $r->MoTa }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="stu-form-group" style="margin-bottom: 0;">
                <label class="stu-form-label">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <span>Đơn vị công tác</span>
                </label>
                <select name="department_id" class="stu-form-control">
                    <option value="">-- Tất cả đơn vị --</option>
                    @foreach($departments as $d)
                        <option value="{{ $d->MaDonVi }}" {{ request('department_id') == $d->MaDonVi ? 'selected' : '' }}>
                            {{ $d->TenDonVi }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <button type="submit" class="stu-btn stu-btn-primary" style="height: 38px; padding: 0 18px; font-weight: 600; box-shadow: 0 1px 2px 0 rgba(0, 58, 140, 0.2);">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span>Lọc</span>
                </button>
            </div>
            <div>
                <a href="{{ route('users.index') }}" class="stu-btn stu-btn-ghost" style="height: 38px; padding: 0 14px;" title="Xóa bộ lọc về mặc định">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Xóa</span>
                </a>
            </div>
        </form>
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
