@extends('layouts.app')

@section('title', 'Quản lý Hồ sơ trình Hội đồng trường STU')

@section('content')
<div class="stu-page-container">
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="['Hồ sơ trình' => null]" />

    {{-- Header --}}
    <div class="stu-page-header stu-flex stu-items-center stu-justify-between stu-mb-6">
        <div>
            <h1 class="stu-page-title" style="font-size: 24px; font-weight: 800; color: var(--color-primary); margin: 0;">
                Hồ sơ trình Hội đồng trường
            </h1>
            <p style="font-size: 13px; color: var(--color-text-muted); margin: 4px 0 0 0;">
                Theo dõi, thẩm định và chuẩn bị tờ trình, đề án phục vụ các kỳ họp Hội đồng trường STU.
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
        <x-alert type="success" :dismissible="true" class="stu-mb-4">
            {{ session('success') }}
        </x-alert>
    @endif
    @if(session('error'))
        <x-alert type="danger" :dismissible="true" class="stu-mb-4">
            {{ session('error') }}
        </x-alert>
    @endif

    {{-- Bộ lọc đa tiêu chí (Search & Filter) --}}
    <div class="stu-card stu-mb-6" style="padding: 18px 24px;">
        <form method="GET" action="{{ route('submissions.index') }}" class="stu-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)) 120px 90px; gap: 14px; align-items: flex-end;">
            {{-- Từ khóa --}}
            <div class="stu-form-group" style="margin-bottom: 0;">
                <label class="stu-form-label" style="font-size: 12px; font-weight: 600; text-transform: uppercase;">Tìm kiếm</label>
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Tiêu đề, nội dung tóm tắt..." 
                    class="stu-form-control"
                    style="font-size: 13px;"
                >
            </div>

            {{-- Đơn vị trình (nếu không bị khóa theo UNIT_REP) --}}
            <div class="stu-form-group" style="margin-bottom: 0;">
                <label class="stu-form-label" style="font-size: 12px; font-weight: 600; text-transform: uppercase;">Đơn vị trình</label>
                <select name="department_id" class="stu-form-control" style="font-size: 13px;" {{ $role === 'UNIT_REP' ? 'disabled' : '' }}>
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
                <label class="stu-form-label" style="font-size: 12px; font-weight: 600; text-transform: uppercase;">Lĩnh vực</label>
                <select name="field" class="stu-form-control" style="font-size: 13px;">
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
                <label class="stu-form-label" style="font-size: 12px; font-weight: 600; text-transform: uppercase;">Trạng thái thẩm định</label>
                <select name="status" class="stu-form-control" style="font-size: 13px;">
                    <option value="">Tất cả trạng thái</option>
                    <option value="Mới tạo" {{ request('status') === 'Mới tạo' ? 'selected' : '' }}>Mới tạo</option>
                    <option value="Chờ bổ sung" {{ request('status') === 'Chờ bổ sung' ? 'selected' : '' }}>Chờ bổ sung</option>
                    <option value="Đủ điều kiện" {{ request('status') === 'Đủ điều kiện' ? 'selected' : '' }}>Đủ điều kiện</option>
                    <option value="Đầy đủ" {{ request('status') === 'Đầy đủ' ? 'selected' : '' }}>Đầy đủ</option>
                </select>
            </div>

            {{-- Nút bấm Lọc & Reset --}}
            <div>
                <button type="submit" class="stu-btn stu-btn-secondary" style="width: 100%; justify-content: center; height: 38px;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span>Lọc</span>
                </button>
            </div>
            <div>
                <a href="{{ route('submissions.index') }}" class="stu-btn stu-btn-ghost" style="width: 100%; justify-content: center; height: 38px;">
                    Xóa
                </a>
            </div>
        </form>
    </div>

    {{-- Bảng dữ liệu Hồ sơ trình --}}
    <x-table 
        cardTitle="Danh sách Hồ sơ tờ trình (Tổng: {{ $submissions->total() }} hồ sơ)"
        :headers="['Mã hồ sơ', 'Tiêu đề tờ trình', 'Đơn vị trình', 'Lĩnh vực', 'Trạng thái', 'Ngày trình']" 
        :hasActions="true"
        actionTitle="Thao tác"
    >
        @forelse($submissions as $item)
            <tr>
                <td style="font-weight: 700; color: var(--color-primary); white-space: nowrap;">
                    #HS-{{ str_pad($item->MaHoSo, 4, '0', STR_PAD_LEFT) }}
                </td>
                <td style="min-width: 280px; max-width: 420px;">
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
                <td style="white-space: nowrap; font-size: 13px;">
                    <span style="font-weight: 500;">{{ $item->donVi?->TenDonVi ?? 'Chưa xác định' }}</span>
                </td>
                <td style="white-space: nowrap; font-size: 13px;">
                    <span class="stu-badge stu-badge-light">{{ $item->LinhVuc ?? 'Tổng hợp' }}</span>
                </td>
                <td style="text-align: center; white-space: nowrap;">
                    <x-status-badge :status="$item->TrangThai" />
                </td>
                <td style="white-space: nowrap; font-size: 12px; color: var(--color-text-muted);">
                    {{ $item->NgayTrinh ? \Carbon\Carbon::parse($item->NgayTrinh)->format('d/m/Y') : 'Chưa gửi' }}
                </td>
                <td class="col-actions">
                    <div class="stu-table-actions-group">
                        <a href="{{ route('submissions.show', $item->MaHoSo) }}" class="stu-btn stu-btn-outline stu-btn-sm" title="Xem chi tiết và quy trình thẩm tra">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <span>Xem hồ sơ</span>
                        </a>
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
    </x-table>

    {{-- Phân trang --}}
    @if($submissions->hasPages())
        <div class="stu-mt-6" style="display: flex; justify-content: flex-end;">
            {{ $submissions->links() }}
        </div>
    @endif
</div>
@endsection
