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
                <button type="button" class="stu-btn stu-btn-primary stu-flex stu-items-center" style="gap: 8px;" data-modal-open="createSubmissionModal">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    <span>Tạo hồ sơ trình mới</span>
                </button>
            @endif
        </div>
    </div>

    {{-- Bộ lọc đa tiêu chí (Search & Filter) --}}
    <div class="stu-card stu-mb-6" style="padding: 20px 24px; border-radius: 12px; border: 1px solid var(--color-border); box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05); background: #ffffff;">
        <form method="GET" action="{{ route('submissions.index') }}" class="stu-grid" style="grid-template-columns: minmax(160px, 1.6fr) minmax(155px, 1.5fr) minmax(145px, 1.35fr) minmax(145px, 1.3fr) auto auto; gap: 10px; align-items: flex-end;">
            <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
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
                        placeholder="Mã hồ sơ (#HS-...), tiêu đề, nội dung..." 
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
                <a href="{{ route('submissions.index') }}" class="stu-btn stu-btn-ghost" style="height: 38px; padding: 0 14px;" title="Đặt lại bộ lọc về mặc định">
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
                    @include('pages.nghiquyet._linked_resolutions_pills', ['resolutions' => $item->nghiQuyet])
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
                    <div class="stu-actions-2x2">
                        {{-- Hàng 1 (Trên): Xem & Kiểm tra --}}
                        <a href="{{ route('submissions.show', $item->MaHoSo) }}" class="stu-btn-2x2 btn-view" title="Xem chi tiết hồ sơ trình">
                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <span>Xem</span>
                        </a>
                        @if(in_array($role, ['SECRETARY', 'CHAIR', 'ADMIN']))
                            <a href="{{ route('submissions.show', [$item->MaHoSo, 'review' => 1]) }}" class="stu-btn-2x2 btn-success" title="Kiểm tra & thẩm định hồ sơ vào phiên họp">
                                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Kiểm tra</span>
                            </a>
                        @endif

                        {{-- Hàng 2 (Dưới): Sửa & Ẩn --}}
                        @if(in_array($role, ['UNIT_REP', 'SECRETARY', 'CHAIR', 'ADMIN']))
                            <a href="{{ route('submissions.edit', $item->MaHoSo) }}" class="stu-btn-2x2 btn-update" title="Chỉnh sửa hồ sơ trình">
                                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                <span>Sửa</span>
                            </a>
                            <form method="POST" action="{{ route('submissions.destroy', $item->MaHoSo) }}" onsubmit="return confirm('Bạn có chắc chắn muốn ẩn hồ sơ trình {{ $item->ma_ho_so_hien_thi }} khỏi danh sách hiển thị? (Dữ liệu vẫn được bảo toàn trong hệ thống)');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="stu-btn-2x2 btn-delete" title="Ẩn hồ sơ trình (Bảo toàn dữ liệu)">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                    <span>Ẩn</span>
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
    <div class="stu-mt-6" style="width: 100%;">
        {{ $submissions->links() }}
    </div>
</div>

<!-- MODAL FORM LƠ LỬNG: TẠO MỚI HỒ SƠ TRÌNH -->
<x-hop-thoai id="createSubmissionModal" title="Tạo Mới Hồ Sơ Trình Hội Đồng Trường" size="lg">
    <form method="POST" action="{{ route('submissions.store') }}" enctype="multipart/form-data" id="modalCreateSubmissionForm" novalidate>
        @csrf

        {{-- 0. Mã số hồ sơ tự động sinh --}}
        <div style="background: var(--color-bg-subtle); border: 1px solid var(--color-border); border-radius: 8px; padding: 10px 14px; margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between; font-size: 12.5px;">
            <div>
                <span style="color: var(--color-text-muted);">Mã số hồ sơ (Tự động sinh):</span>
                <strong style="color: var(--color-primary); margin-left: 6px;">#HS-{{ now()->format('my') }}...</strong>
            </div>
            <span class="stu-badge stu-badge-info" style="font-size: 11px;">Chuẩn BR-01</span>
        </div>

        {{-- 1. Tiêu đề hồ sơ trình --}}
        <div class="stu-form-group" style="margin-bottom: 14px;">
            <label class="stu-form-label" style="font-size: 12.5px; font-weight: 700;">
                Tiêu đề hồ sơ trình <span style="color: var(--color-danger);">*</span>
            </label>
            <input 
                type="text" 
                name="TieuDe" 
                id="modalSubmissionTitle"
                value="{{ old('TieuDe') }}" 
                class="stu-form-control" 
                placeholder="Ví dụ: Tờ trình về việc phê duyệt chủ trương đầu tư cơ sở vật chất năm 2026" 
                required
                minlength="5"
                maxlength="255"
                style="height: 38px; font-size: 13px;"
            >
            <div id="modalSubmissionTitleErr" style="display: none; color: var(--color-danger); font-size: 11.5px; margin-top: 4px;">Vui lòng nhập tiêu đề hồ sơ tối thiểu 5 ký tự.</div>
        </div>

        {{-- 2. Đơn vị trình & Lĩnh vực (2 cột) --}}
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
            <div class="stu-form-group">
                <label class="stu-form-label" style="font-size: 12.5px; font-weight: 700;">
                    Đơn vị trình <span style="color: var(--color-danger);">*</span>
                </label>
                <select name="MaDonVi" class="stu-form-control" required style="height: 38px; font-size: 13px;">
                    <option value="">-- Chọn đơn vị --</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->MaDonVi }}" {{ (old('MaDonVi') == $dept->MaDonVi || ($role === 'UNIT_REP' && $userDeptId == $dept->MaDonVi)) ? 'selected' : '' }}>
                            {{ $dept->TenDonVi }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="stu-form-group">
                <label class="stu-form-label" style="font-size: 12.5px; font-weight: 700;">
                    Lĩnh vực <span style="color: var(--color-danger);">*</span>
                </label>
                <select name="LinhVuc" class="stu-form-control" required style="height: 38px; font-size: 13px;">
                    <option value="">-- Chọn lĩnh vực --</option>
                    <option value="Đào tạo & Đảm bảo chất lượng">Đào tạo & Đảm bảo chất lượng</option>
                    <option value="Khoa học công nghệ & Dự án">Khoa học công nghệ & Dự án</option>
                    <option value="Cơ sở vật chất & Đầu tư xây dựng">Cơ sở vật chất & Đầu tư xây dựng</option>
                    <option value="Tài chính & Kế hoạch ngân sách">Tài chính & Kế hoạch ngân sách</option>
                    <option value="Tổ chức cán bộ & Nhân sự">Tổ chức cán bộ & Nhân sự</option>
                    <option value="Hợp tác quốc tế & Doanh nghiệp">Hợp tác quốc tế & Doanh nghiệp</option>
                    <option value="Khác">Khác</option>
                </select>
            </div>
        </div>

        {{-- 3. Nội dung tóm tắt --}}
        <div class="stu-form-group" style="margin-bottom: 14px;">
            <label class="stu-form-label" style="font-size: 12.5px; font-weight: 700;">
                Nội dung tóm tắt <span style="color: var(--color-danger);">*</span>
            </label>
            <textarea 
                name="NoiDungTomTat" 
                id="modalSubmissionContent"
                rows="3" 
                class="stu-form-control" 
                placeholder="Nhập nội dung tóm tắt của hồ sơ trình gửi Hội đồng trường..."
                required
                style="font-size: 13px;"
            >{{ old('NoiDungTomTat') }}</textarea>
            <div id="modalSubmissionContentErr" style="display: none; color: var(--color-danger); font-size: 11.5px; margin-top: 4px;">Vui lòng nhập nội dung tóm tắt hồ sơ.</div>
        </div>

        {{-- 4. Tài liệu đính kèm --}}
        <div class="stu-form-group" style="margin-bottom: 14px;">
            <label class="stu-form-label" style="font-size: 12.5px; font-weight: 700;">
                Tài liệu tờ trình đính kèm (PDF, Word, Excel, PPT - Max 20MB)
            </label>
            <input 
                type="file" 
                name="file_dinh_kem" 
                id="modalSubmissionDocFile"
                class="stu-form-control" 
                accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx"
                style="height: 38px; font-size: 12px; padding: 6px 12px;"
            >
            <div id="modalSubmissionDocErr" class="stu-form-error" style="display: none; color: var(--color-danger, #ef4444); font-size: 11.5px; margin-top: 4px; font-weight: 500;"></div>
            <div style="font-size: 11px; color: var(--color-text-muted); margin-top: 4px;">
                * Định dạng được phép: <strong>.pdf, .doc, .docx, .xls, .xlsx, .ppt, .pptx</strong> (Tối đa 20MB). Hệ thống sẽ báo lỗi nếu tải lên tệp khác định dạng.
            </div>
        </div>

        <!-- Footer buttons -->
        <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 18px; padding-top: 14px; border-top: 1px solid var(--color-border);">
            <button type="button" class="stu-btn stu-btn-secondary" data-modal-close>
                <span>Hủy bỏ</span>
            </button>
            <button type="submit" class="stu-btn stu-btn-primary" style="font-weight: 600;">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                <span>Gửi nộp hồ sơ trình</span>
            </button>
        </div>
    </form>
</x-hop-thoai>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        @if(request('create') || $errors->any())
            window.STUModal?.open('createSubmissionModal');
        @endif

        const form = document.getElementById('modalCreateSubmissionForm');
        const docFile = document.getElementById('modalSubmissionDocFile');
        const docErr = document.getElementById('modalSubmissionDocErr');
        const allowedExts = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'];

        if (docFile) {
            docFile.addEventListener('change', function () {
                if (docErr) {
                    docErr.textContent = '';
                    docErr.style.display = 'none';
                }
                if (!this.files || this.files.length === 0) return;
                const file = this.files[0];
                const fileName = file.name;
                const ext = fileName.split('.').pop().toLowerCase();

                if (!allowedExts.includes(ext)) {
                    const msg = `Định dạng tập tin ".${ext}" không được hỗ trợ! Vui lòng chỉ tải lên các tệp tài liệu: PDF (.pdf), Word (.doc, .docx), Excel (.xls, .xlsx) hoặc PowerPoint (.ppt, .pptx).`;
                    if (docErr) {
                        docErr.textContent = msg;
                        docErr.style.display = 'block';
                    }
                    alert(msg);
                    this.value = '';
                    return;
                }

                if (file.size > 20 * 1024 * 1024) {
                    const msg = 'Dung lượng tập tin vượt quá 20MB! Vui lòng chọn tệp nhỏ hơn.';
                    if (docErr) {
                        docErr.textContent = msg;
                        docErr.style.display = 'block';
                    }
                    alert(msg);
                    this.value = '';
                    return;
                }
            });
        }

        if (form) {
            form.addEventListener('submit', function (e) {
                const title = document.getElementById('modalSubmissionTitle');
                const content = document.getElementById('modalSubmissionContent');
                const titleErr = document.getElementById('modalSubmissionTitleErr');
                const contentErr = document.getElementById('modalSubmissionContentErr');

                if (titleErr) titleErr.style.display = 'none';
                if (contentErr) contentErr.style.display = 'none';

                let hasErr = false;
                if (!title || !title.value.trim() || title.value.trim().length < 5) {
                    if (titleErr) titleErr.style.display = 'block';
                    hasErr = true;
                }
                if (!content || !content.value.trim() || content.value.trim().length < 5) {
                    if (contentErr) contentErr.style.display = 'block';
                    hasErr = true;
                }

                if (docFile && docFile.files && docFile.files.length > 0) {
                    const file = docFile.files[0];
                    const ext = file.name.split('.').pop().toLowerCase();
                    if (!allowedExts.includes(ext)) {
                        if (docErr) {
                            docErr.textContent = `Định dạng tập tin ".${ext}" không được hỗ trợ!`;
                            docErr.style.display = 'block';
                        }
                        hasErr = true;
                    }
                    if (file.size > 20 * 1024 * 1024) {
                        if (docErr) {
                            docErr.textContent = 'Dung lượng tập tin vượt quá 20MB!';
                            docErr.style.display = 'block';
                        }
                        hasErr = true;
                    }
                }

                if (hasErr) {
                    e.preventDefault();
                }
            });
        }
    });
</script>
@endsection
