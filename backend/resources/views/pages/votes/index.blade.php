@extends('layouts.app')

@section('title', 'Quản lý Biểu quyết & Bỏ phiếu - Hội đồng trường STU')

@section('content')
<div class="stu-page-container">
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="['Biểu quyết & Bỏ phiếu' => null]" />

    {{-- Header --}}
    <div class="stu-page-header stu-flex stu-items-center stu-justify-between stu-mb-6">
        <div>
            <h1 class="stu-page-title" style="font-size: 24px; font-weight: 800; color: var(--color-primary); margin: 0;">
                Biểu quyết & Bỏ phiếu Hội đồng trường
            </h1>
            <p style="font-size: 13px; color: var(--color-text-muted); margin: 4px 0 0 0;">
                Hệ thống kiểm phiếu điện tử bảo mật tuyệt đối (BR-09), hỗ trợ biểu quyết trực tiếp và ủy quyền hợp lệ (BR-04).
            </p>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <x-alert type="success" :dismissible="true" class="stu-mb-4">
            {{ session('success') }}
        </x-alert>
    @endif
    @if(session('warning'))
        <x-alert type="warning" :dismissible="true" class="stu-mb-4">
            {{ session('warning') }}
        </x-alert>
    @endif
    @if(session('error'))
        <x-alert type="danger" :dismissible="true" class="stu-mb-4">
            {{ session('error') }}
        </x-alert>
    @endif

    {{-- Thống kê nhanh 4 thẻ KPI --}}
    <div class="stu-grid stu-grid-cols-4 stu-mb-6" style="gap: 16px;">
        <div class="stu-card" style="padding: 18px; border-left: 4px solid var(--color-primary);">
            <div style="font-size: 12px; font-weight: 600; color: var(--color-text-muted); text-transform: uppercase;">Tổng nội dung BQ</div>
            <div style="font-size: 26px; font-weight: 800; color: var(--color-primary); margin-top: 4px;">{{ $stats['total'] }}</div>
        </div>
        <div class="stu-card" style="padding: 18px; border-left: 4px solid var(--color-success);">
            <div style="font-size: 12px; font-weight: 600; color: var(--color-text-muted); text-transform: uppercase;">Đang mở cổng bỏ phiếu</div>
            <div style="font-size: 26px; font-weight: 800; color: var(--color-success); margin-top: 4px;">{{ $stats['opening'] }}</div>
        </div>
        <div class="stu-card" style="padding: 18px; border-left: 4px solid var(--color-neutral);">
            <div style="font-size: 12px; font-weight: 600; color: var(--color-text-muted); text-transform: uppercase;">Đã kết thúc kiểm phiếu</div>
            <div style="font-size: 26px; font-weight: 800; color: var(--color-text-primary); margin-top: 4px;">{{ $stats['closed'] }}</div>
        </div>
        <div class="stu-card" style="padding: 18px; border-left: 4px solid var(--color-info);">
            <div style="font-size: 12px; font-weight: 600; color: var(--color-text-muted); text-transform: uppercase;">Nghị quyết thông qua</div>
            <div style="font-size: 26px; font-weight: 800; color: var(--color-info); margin-top: 4px;">{{ $stats['passed'] }}</div>
        </div>
    </div>

    {{-- Bộ lọc tìm kiếm --}}
    <div class="stu-card stu-mb-6" style="padding: 20px 24px; border-radius: 12px; border: 1px solid var(--color-border); box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05); background: #ffffff;">
        <form method="GET" action="{{ route('votes.index') }}" class="stu-grid" style="grid-template-columns: 2fr 1.5fr 1fr auto auto; gap: 14px; align-items: flex-end;">
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
                        placeholder="Nội dung câu hỏi biểu quyết..." 
                        class="stu-form-control"
                        style="padding-left: 36px;"
                    >
                </div>
            </div>

            {{-- Phiên họp --}}
            <div class="stu-form-group" style="margin-bottom: 0;">
                <label class="stu-form-label">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Phiên họp</span>
                </label>
                <select name="meeting_id" class="stu-form-control">
                    <option value="">Tất cả phiên họp</option>
                    @foreach($meetings as $m)
                        <option value="{{ $m->MaPhienHop }}" {{ request('meeting_id') == $m->MaPhienHop ? 'selected' : '' }}>
                            {{ $m->TenPhienHop }}
                        </option>
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
                    <option value="OPENING" {{ request('status') === 'OPENING' ? 'selected' : '' }}>Đang mở biểu quyết</option>
                    <option value="PENDING" {{ request('status') === 'PENDING' ? 'selected' : '' }}>Chưa mở</option>
                    <option value="CLOSED" {{ request('status') === 'CLOSED' ? 'selected' : '' }}>Đã đóng</option>
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
                <a href="{{ route('votes.index') }}" class="stu-btn stu-btn-ghost" style="height: 38px; padding: 0 14px;" title="Xóa bộ lọc về mặc định">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Xóa</span>
                </a>
            </div>
        </form>
    </div>

    {{-- Bảng danh sách nội dung biểu quyết --}}
    <x-table 
        cardTitle="Danh sách Nội dung biểu quyết (Tổng: {{ $votings->total() }})"
        :headers="['Mã BQ', 'Nội dung câu hỏi biểu quyết', 'Phiên họp liên quan', 'Hình thức', 'Trạng thái', 'Kết quả tổng hợp']" 
        :hasActions="true"
        actionTitle="Thao tác"
    >
        @forelse($votings as $v)
            @php
                $meta = json_decode($v->GhiChu ?? '{}', true) ?: [];
                $vType = $meta['voting_type'] ?? 'SECRET';
                $isOpening = in_array($v->TrangThai, ['OPENING', 'Đang mở biểu quyết', 'Đang mở']);
            @endphp
            <tr>
                <td style="font-weight: 700; color: var(--color-primary); white-space: nowrap;">
                    #BQ-{{ str_pad($v->MaNoiDung, 3, '0', STR_PAD_LEFT) }}
                </td>
                <td style="min-width: 280px; max-width: 440px;">
                    <a href="{{ route('votes.show', $v->MaNoiDung) }}" style="font-weight: 600; color: var(--color-text-primary); text-decoration: none; line-height: 1.5; display: block;" class="hover:text-primary">
                        {{ $v->NoiDung }}
                    </a>
                    @if($v->chuongTrinhHop)
                        <div style="font-size: 11px; color: var(--color-text-muted); margin-top: 4px;">
                            Thuộc mục chương trình họp: <strong>Mục số {{ $v->chuongTrinhHop->ThuTu }}</strong>
                        </div>
                    @endif
                </td>
                <td style="white-space: nowrap; font-size: 13px;">
                    <div style="font-weight: 500;">
                        {{ $v->chuongTrinhHop?->phienHop?->TenPhienHop ?? 'Phiên họp #' . ($v->chuongTrinhHop?->MaPhienHop ?? '—') }}
                    </div>
                    <div style="font-size: 11px; color: var(--color-text-muted);">
                        {{ $v->chuongTrinhHop?->phienHop?->NgayHop ? \Carbon\Carbon::parse($v->chuongTrinhHop->phienHop->NgayHop)->format('d/m/Y') : '' }}
                    </div>
                </td>
                <td style="white-space: nowrap; font-size: 12px;">
                    @if($vType === 'SECRET')
                        <span class="stu-badge stu-badge-light" style="color: #0369a1; border-color: #bae6fd; background: #f0f9ff;">
                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-right: 3px;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            Bỏ phiếu kín (BR-09)
                        </span>
                    @else
                        <span class="stu-badge stu-badge-light">Công khai</span>
                    @endif
                </td>
                <td style="text-align: center; white-space: nowrap;">
                    @if($isOpening)
                        <span class="stu-badge stu-badge-success" style="font-weight: 700; animation: pulse 2s infinite;">
                            ● Đang mở biểu quyết
                        </span>
                    @elseif(in_array($v->TrangThai, ['CLOSED', 'Đã đóng']))
                        <span class="stu-badge stu-badge-neutral">Đã đóng</span>
                    @else
                        <span class="stu-badge stu-badge-light">Chưa mở</span>
                    @endif
                </td>
                <td style="text-align: center; white-space: nowrap;">
                    @if(in_array($v->KetQua, ['PASSED', 'Đã thông qua', 'THÔNG QUA']))
                        <span class="stu-badge stu-badge-success">Đã thông qua</span>
                    @elseif(in_array($v->KetQua, ['REJECTED', 'Không thông qua']))
                        <span class="stu-badge stu-badge-danger">Không thông qua</span>
                    @else
                        <span style="font-size: 12px; color: var(--color-text-muted);">{{ $v->ketQuaBieuQuyet->count() }} phiếu đã gửi</span>
                    @endif
                </td>
                <td class="col-actions">
                    <div class="stu-table-actions-group">
                        <a href="{{ route('votes.show', $v->MaNoiDung) }}" class="stu-btn {{ $isOpening ? 'stu-btn-primary' : 'stu-btn-outline' }} stu-btn-sm">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>{{ $isOpening ? 'Vào phòng bỏ phiếu' : 'Xem kết quả' }}</span>
                        </a>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" style="text-align: center; padding: 48px; color: var(--color-text-muted);">
                    <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="margin: 0 auto 12px; color: var(--color-border);"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div style="font-weight: 600; font-size: 15px;">Chưa có nội dung biểu quyết nào</div>
                    <div style="font-size: 13px; margin-top: 4px;">Các câu hỏi biểu quyết sẽ xuất hiện khi Thư ký khởi tạo trong phiên họp tương ứng.</div>
                </td>
            </tr>
        @endforelse
    </x-table>

    {{-- Phân trang --}}
    @if($votings->hasPages())
        <div class="stu-mt-6" style="display: flex; justify-content: flex-end;">
            {{ $votings->links() }}
        </div>
    @endif
</div>
@endsection
