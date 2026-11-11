@extends('layouts.chinh')

@section('title', 'Quản lý Hồ sơ trình Hội đồng trường STU')

@section('content')
<div class="stu-page-container">
    {{-- Breadcrumb --}}
    <x-thanh-dieu-huong :items="['Hồ sơ trình' => null]" />

    {{-- Header --}}
    <div class="stu-page-header stu-flex stu-items-center stu-justify-between stu-mb-6">
        <div>
            <h1 class="stu-page-title" style="font-size: 24px; font-weight: 800; color: var(--color-primary); margin: 0;">
                Hồ sơ trình Hội đồng trường
            </h1>
            <p style="font-size: 13px; color: var(--color-text-muted); margin: 4px 0 0 0;">
                Theo dõi và chuẩn bị hồ sơ trình phục vụ các phiên họp Hội đồng trường STU.
            </p>
        </div>
        <div>
            @if(in_array($role, ['UNIT_REP', 'SECRETARY', 'CHAIR', 'ADMIN']))
                <a href="{{ route('submissions.create') }}" class="stu-btn stu-btn-primary stu-flex stu-items-center" style="gap: 8px;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    <span>Tạo hồ sơ trình mới</span>
                </a>
            @endif
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <x-canh-bao type="success" :dismissible="true" class="stu-mb-4">
            {{ session('success') }}
        </x-canh-bao>
    @endif
    @if(session('error'))
        <x-canh-bao type="danger" :dismissible="true" class="stu-mb-4">
            {{ session('error') }}
        </x-canh-bao>
    @endif

    {{-- Bộ lọc đa tiêu chí (Search & Filter) --}}
    <div class="stu-card stu-mb-6" style="padding: 20px 24px; border-radius: 12px; border: 1px solid var(--color-border); box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05); background: #ffffff;">
        <form method="GET" action="{{ route('submissions.index') }}" class="stu-grid" style="grid-template-columns: 2fr 1.6fr 1.4fr 1.5fr auto auto; gap: 14px; align-items: flex-end;">
            {{-- Từ khóa --}}
            <div class="stu-form-group" style="margin-bottom: 0;">
                <label class="stu-form-label">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <span>Tìm kiếm</span>
                </label>
                <div style="position: relative;">
                    <svg width="15" height="15" fill="none" stroke="#94A3B8" stroke-width="2" viewBox="0 0 24 24" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); pointer-events: none;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="Tiêu đề, nội dung tóm tắt..." 
                        class="stu-form-control"
                        style="padding-left: 36px;"
                    >
                </div>
            </div>

            {{-- Đơn vị trình (nếu không bị khóa theo UNIT_REP) --}}
            <div class="stu-form-group" style="margin-bottom: 0;">
                <label class="stu-form-label">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <span>Đơn vị trình</span>
                </label>
                <select name="department_id" class="stu-form-control" {{ $role === 'UNIT_REP' ? 'disabled' : '' }}>
                    <option value="">Tất cả đơn vị</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->MaDonVi }}" {{ request('department_id') == $dept->MaDonVi || ($role === 'UNIT_REP' && $userDeptId == $dept->MaDonVi) ? 'selected' : '' }}>
                            {{ $dept->TenDonVi }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Lĩnh vực --}}
            <div class="stu-form-group" style="margin-bottom: 0;">
                <label class="stu-form-label">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                    <span>Lĩnh vực</span>
                </label>
                <select name="field" class="stu-form-control">
                    <option value="">Tất cả lĩnh vực</option>
                    @foreach($fields as $f)
                        @if($f)
                            <option value="{{ $f }}" {{ request('field') == $f ? 'selected' : '' }}>{{ $f }}</option>
                        @endif
                    @endforeach
                </select>
            </div>

            {{-- Trạng thái --}}
            <div class="stu-form-group" style="margin-bottom: 0;">
                <label class="stu-form-label">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Trạng thái</span>
                </label>
                <select name="status" class="stu-form-control">
                    <option value="">Tất cả trạng thái</option>
                    <option value="Mới tạo" {{ request('status') === 'Mới tạo' ? 'selected' : '' }}>Mới tạo</option>
                    <option value="Chờ bổ sung" {{ request('status') === 'Chờ bổ sung' ? 'selected' : '' }}>Chờ bổ sung</option>
                    <option value="Đủ điều kiện" {{ request('status') === 'Đủ điều kiện' ? 'selected' : '' }}>Đủ điều kiện</option>
                    <option value="Đầy đủ" {{ request('status') === 'Đầy đủ' ? 'selected' : '' }}>Đầy đủ</option>
                </select>
            </div>

            {{-- Nút bấm Lọc & Reset --}}
            <div>
                <button type="submit" class="stu-btn stu-btn-primary" style="height: 38px; padding: 0 16px; font-weight: 600; box-shadow: 0 1px 2px 0 rgba(0, 58, 140, 0.2);">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span>Lọc</span>
                </button>
            </div>
            <div>
                <a href="{{ route('submissions.index') }}" class="stu-btn stu-btn-ghost" style="height: 38px; padding: 0 14px;" title="Xóa bộ lọc về mặc định">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Đặt lại</span>
                </a>
            </div>
        </form>
    </div>

    {{-- Bảng dữ liệu Hồ sơ trình --}}
    <x-bang-du-lieu 
        cardTitle="Danh sách Hồ sơ trình (Tổng: {{ $submissions->total() }} hồ sơ)"
        :headers="['Mã hồ sơ', 'Tiêu đề hồ sơ', 'Đơn vị trình', 'Lĩnh vực', 'Trạng thái', 'Ngày trình']" 
        :hasActions="true"
        actionTitle="Thao tác"
    >
        @forelse($submissions as $item)
            <tr>
                <td style="font-weight: 700; color: var(--color-primary); white-space: nowrap; width: 105px;">
                    {{ $item->ma_ho_so_hien_thi }}
                </td>
                <td style="max-width: 340px;">
                    <a href="{{ route('submissions.show', $item->MaHoSo) }}" style="font-weight: 600; color: var(--color-text-primary); text-decoration: none; line-height: 1.5; display: block;" class="hover:text-primary">
                        {{ $item->TieuDe }}
                    </a>
                    @if($item->NoiDungTomTat)
                        <div style="font-size: 12px; color: var(--color-text-muted); margin-top: 4px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                            {{ $item->NoiDungTomTat }}
                        </div>
                    @endif
                    @if($item->taiLieu->count() > 0)
                        <div style="font-size: 11px; color: var(--color-accent); margin-top: 4px; display: flex; align-items: center; gap: 4px;">
                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                            <span>{{ $item->taiLieu->count() }} tệp tài liệu đính kèm</span>
                        </div>
                    @endif
                </td>
                <td style="font-size: 13px; max-width: 180px; line-height: 1.4;">
                    <span style="font-weight: 500;">{{ $item->donVi?->TenDonVi ?? 'Chưa xác định' }}</span>
                </td>
                <td style="font-size: 13px; max-width: 180px; line-height: 1.4;">
                    <span class="stu-badge stu-badge-light" style="white-space: normal; text-align: left; line-height: 1.3;">{{ $item->LinhVuc ?? 'Tổng hợp' }}</span>
                </td>
                <td style="white-space: nowrap;">
                    <x-nhan-trang-thai :status="$item->TrangThai" />
                </td>
                <td style="white-space: nowrap; font-size: 12px; color: var(--color-text-muted);">
                    {{ $item->NgayTrinh ? \Carbon\Carbon::parse($item->NgayTrinh)->format('d/m/Y') : 'Chưa gửi' }}
                </td>
                <td class="col-actions">
                    <div class="stu-table-actions-group">
                        <a href="{{ route('submissions.show', $item->MaHoSo) }}" class="stu-btn stu-btn-outline stu-btn-sm" title="Xem chi tiết hồ sơ trình">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <span>Xem</span>
                        </a>
                        @if(in_array($role, ['UNIT_REP', 'SECRETARY', 'CHAIR', 'ADMIN']))
                            <a href="{{ route('submissions.edit', $item->MaHoSo) }}" class="stu-btn stu-btn-secondary stu-btn-sm" title="Chỉnh sửa hồ sơ trình">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                <span>Sửa</span>
                            </a>
                            <form method="POST" action="{{ route('submissions.destroy', $item->MaHoSo) }}" onsubmit="return confirm('Xác nhận ẩn hồ sơ trình {{ $item->ma_ho_so_hien_thi }} khỏi danh sách hiển thị?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="stu-btn stu-btn-danger stu-btn-sm" title="Ẩn hồ sơ trình (Soft Delete)">
                                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    <span>Xóa</span>
                                </button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" style="text-align: center; padding: 48px; color: var(--color-text-muted);">
                    <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="margin: 0 auto 12px; color: var(--color-border);"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <div style="font-weight: 600; font-size: 15px;">Không tìm thấy hồ sơ trình nào</div>
                    <div style="font-size: 13px; margin-top: 4px;">Chưa có dữ liệu phù hợp với điều kiện tìm kiếm hiện tại.</div>
                </td>
            </tr>
        @endforelse
    </x-bang-du-lieu>

    {{-- Phân trang --}}
    @if($submissions->hasPages())
        <div class="stu-mt-6" style="width: 100%;">
            {{ $submissions->links() }}
        </div>
    @endif
</div>
@endsection
