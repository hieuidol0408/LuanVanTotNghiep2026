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
<div class="stu-card" style="padding: 18px 24px; margin-bottom: var(--space-6); border-radius: 12px; border: 1px solid var(--color-border); box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05); background: #ffffff;">
    <form method="GET" action="{{ route('meetings.index') }}" style="display: grid; grid-template-columns: 2.2fr 1.2fr 1.2fr 1.2fr auto auto; gap: 12px; align-items: center;">
        <!-- Ô tìm kiếm -->
        <div style="position: relative;">
            <svg width="15" height="15" fill="none" stroke="#94A3B8" stroke-width="2" viewBox="0 0 24 24" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); pointer-events: none;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            <input 
                type="text" 
                name="search" 
                placeholder="Tìm theo tên phiên họp, địa điểm..." 
                value="{{ request('search') }}" 
                class="stu-form-control"
                style="padding-left: 36px; height: 38px;"
            >
        </div>

        <!-- Lọc theo năm -->
        <div>
            <select name="year" class="stu-form-control" style="height: 38px;">
                <option value="">-- Tất cả các năm --</option>
                @foreach($years as $y)
                    <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>Năm {{ $y }}</option>
                @endforeach
            </select>
        </div>

        <!-- Lọc theo hình thức -->
        <div>
            <select name="format" class="stu-form-control" style="height: 38px;">
                <option value="">-- Hình thức họp --</option>
                <option value="Trực tiếp" {{ request('format') == 'Trực tiếp' || request('format') == 'IN_PERSON' ? 'selected' : '' }}>Trực tiếp</option>
                <option value="Trực tuyến" {{ request('format') == 'Trực tuyến' || request('format') == 'ONLINE' ? 'selected' : '' }}>Trực tuyến</option>
                <option value="Kết hợp" {{ request('format') == 'Kết hợp' || request('format') == 'HYBRID' ? 'selected' : '' }}>Kết hợp (Hybrid)</option>
            </select>
        </div>

        <!-- Lọc theo trạng thái -->
        <div>
            <select name="status" class="stu-form-control" style="height: 38px;">
                <option value="">-- Trạng thái họp --</option>
                <option value="Sắp diễn ra" {{ request('status') == 'Sắp diễn ra' ? 'selected' : '' }}>Sắp diễn ra</option>
                <option value="Đang diễn ra" {{ request('status') == 'Đang diễn ra' ? 'selected' : '' }}>Đang diễn ra</option>
                <option value="Đã diễn ra" {{ request('status') == 'Đã diễn ra' ? 'selected' : '' }}>Đã diễn ra</option>
                <option value="Đã kết thúc" {{ request('status') == 'Đã kết thúc' ? 'selected' : '' }}>Đã kết thúc</option>
                <option value="Hủy" {{ request('status') == 'Hủy' ? 'selected' : '' }}>Hủy phiên họp</option>
            </select>
        </div>

        <!-- Nút áp dụng bộ lọc -->
        <div>
            <button type="submit" class="stu-btn stu-btn-primary" style="height: 38px; padding: 0 18px; font-weight: 600; box-shadow: 0 1px 2px 0 rgba(0, 58, 140, 0.2);">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <span>Lọc</span>
            </button>
        </div>
        <div>
            @if(request()->anyFilled(['search', 'year', 'format', 'status']))
                <a href="{{ route('meetings.index') }}" class="stu-btn stu-btn-ghost" style="height: 38px; padding: 0 14px;" title="Xóa bộ lọc">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Xóa</span>
                </a>
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
