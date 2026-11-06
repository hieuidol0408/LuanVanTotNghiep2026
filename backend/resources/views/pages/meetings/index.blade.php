@extends('layouts.app')

@section('title', 'Quản lý Phiên Họp - Hội đồng trường STU')

@section('header_title')
    <x-breadcrumb :items="[
        ['title' => 'Quản lý phiên họp']
    ]" />
@endsection

@section('content')
<div class="stu-page-header">
    <div class="stu-page-title-group">
        <h1>Danh sách Phiên Họp Hội Đồng Trường</h1>
        <p>Quản lý lịch họp, triệu tập thành viên, khách mời và điều hành chương trình họp.</p>
    </div>
    <div class="stu-page-actions">
        @if(in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN']))
            <x-button variant="primary" href="{{ route('meetings.create') }}">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                <span>Tạo phiên họp mới</span>
            </x-button>
        @endif
    </div>
</div>

<!-- THANH TÌM KIẾM & BỘ LỌC ĐA TIÊU CHÍ -->
<div class="stu-card" style="padding: var(--space-4) var(--space-6); margin-bottom: var(--space-6);">
    <form method="GET" action="{{ route('meetings.index') }}" style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr auto; gap: var(--space-3); align-items: end;">
        <!-- Ô tìm kiếm -->
        <x-form.input 
            name="search" 
            placeholder="Tìm theo tên phiên họp, địa điểm..." 
            value="{{ request('search') }}" 
            style="margin-bottom: 0;"
        />

        <!-- Lọc theo năm -->
        <x-form.select name="year" style="margin-bottom: 0;">
            <option value="">-- Tất cả các năm --</option>
            @foreach($years as $y)
                <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>Năm {{ $y }}</option>
            @endforeach
        </x-form.select>

        <!-- Lọc theo hình thức -->
        <x-form.select name="format" style="margin-bottom: 0;">
            <option value="">-- Hình thức họp --</option>
            <option value="Trực tiếp" {{ request('format') == 'Trực tiếp' || request('format') == 'IN_PERSON' ? 'selected' : '' }}>Trực tiếp</option>
            <option value="Trực tuyến" {{ request('format') == 'Trực tuyến' || request('format') == 'ONLINE' ? 'selected' : '' }}>Trực tuyến</option>
            <option value="Kết hợp" {{ request('format') == 'Kết hợp' || request('format') == 'HYBRID' ? 'selected' : '' }}>Kết hợp (Hybrid)</option>
        </x-form.select>

        <!-- Lọc theo trạng thái -->
        <x-form.select name="status" style="margin-bottom: 0;">
            <option value="">-- Trạng thái họp --</option>
            <option value="Sắp diễn ra" {{ request('status') == 'Sắp diễn ra' ? 'selected' : '' }}>Sắp diễn ra</option>
            <option value="Đang diễn ra" {{ request('status') == 'Đang diễn ra' ? 'selected' : '' }}>Đang diễn ra</option>
            <option value="Đã diễn ra" {{ request('status') == 'Đã diễn ra' ? 'selected' : '' }}>Đã diễn ra</option>
            <option value="Đã kết thúc" {{ request('status') == 'Đã kết thúc' ? 'selected' : '' }}>Đã kết thúc</option>
            <option value="Hủy" {{ request('status') == 'Hủy' ? 'selected' : '' }}>Hủy phiên họp</option>
        </x-form.select>

        <!-- Nút áp dụng bộ lọc -->
        <div style="display: flex; gap: var(--space-2);">
            <x-button type="submit" variant="primary">
                <span>Lọc</span>
            </x-button>
            @if(request()->anyFilled(['search', 'year', 'format', 'status']))
                <x-button variant="secondary" href="{{ route('meetings.index') }}" title="Xóa bộ lọc">
                    <span>Xóa</span>
                </x-button>
            @endif
        </div>
    </form>
</div>

<!-- BẢNG DỮ LIỆU PHIÊN HỌP (KHÔNG TRÀN & STICKY ACTION COLUMN) -->
<x-table 
    :headers="['Mã', 'Tên phiên họp', 'Thời gian', 'Địa điểm & Hình thức', 'Trạng thái', 'Hồ sơ / TV', 'Thao tác']"
    :hasActions="true"
    actionTitle="Thao tác"
    :empty="$meetings->isEmpty()"
    emptyTitle="Không tìm thấy phiên họp phù hợp"
    emptyMessage="Vui lòng thử lại với từ khóa tìm kiếm hoặc điều kiện lọc khác."
>
    @foreach($meetings as $meeting)
        <tr>
            <td style="font-weight: 700; color: var(--color-primary); white-space: nowrap;">
                #{{ $meeting->MaPhienHop }}
            </td>
            <td class="stu-max-w-xs">
                <a href="{{ route('meetings.show', $meeting->MaPhienHop) }}" style="font-weight: 600; color: var(--color-primary); display: block;" title="{{ $meeting->TenPhienHop }}">
                    {{ $meeting->TenPhienHop }}
                </a>
                @if($meeting->GhiChu)
                    <div class="stu-truncate" style="font-size: 11px; color: var(--color-text-muted);" title="{{ $meeting->GhiChu }}">
                        {{ $meeting->GhiChu }}
                    </div>
                @endif
            </td>
            <td style="white-space: nowrap;">
                <div style="font-weight: 500;">
                    {{ $meeting->ThoiGianBatDau ? date('d/m/Y', strtotime($meeting->ThoiGianBatDau)) : 'Chưa định ngày' }}
                </div>
                <div style="font-size: 11px; color: var(--color-text-muted);">
                    {{ $meeting->ThoiGianBatDau ? date('H:i', strtotime($meeting->ThoiGianBatDau)) : '' }} - 
                    {{ $meeting->ThoiGianKetThuc ? date('H:i', strtotime($meeting->ThoiGianKetThuc)) : '' }}
                </div>
            </td>
            <td style="font-size: var(--font-size-xs);">
                <div style="font-weight: 500;">{{ $meeting->DiaDiem }}</div>
                <span class="stu-badge stu-badge-info" style="margin-top: 4px; padding: 2px 6px;">
                    {{ $meeting->HinhThuc }}
                </span>
            </td>
            <td>
                <x-status-badge :status="$meeting->TrangThai" />
            </td>
            <td style="font-size: var(--font-size-xs); white-space: nowrap;">
                <div><strong>{{ $meeting->chuong_trinh_hop_count ?? 0 }}</strong> hồ sơ</div>
                <div style="color: var(--color-text-muted);"><strong>{{ $meeting->tham_du_count ?? 0 }}</strong> thành viên</div>
            </td>
            <td class="col-actions">
                <div class="stu-table-actions-group">
                    <x-button variant="outline" size="sm" href="{{ route('meetings.show', $meeting->MaPhienHop) }}">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <span>Chi tiết</span>
                    </x-button>
                    @if(in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN']))
                        <x-button variant="secondary" size="sm" href="{{ route('meetings.edit', $meeting->MaPhienHop) }}">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            <span>Sửa</span>
                        </x-button>
                    @endif
                </div>
            </td>
        </tr>
    @endforeach
</x-table>

<!-- PHÂN TRANG -->
<div style="margin-top: var(--space-4); display: flex; justify-content: flex-end;">
    {{ $meetings->links() }}
</div>
@endsection
