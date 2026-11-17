@extends('layouts.chinh')

@section('title', 'Quản lý Nghị quyết Hội đồng trường STU')

@section('content')
<div class="stu-page-container">
    {{-- Breadcrumb --}}
    <x-thanh-dieu-huong :items="['Nghị quyết HĐT' => null]" />

    {{-- Header --}}
    <div class="stu-page-header stu-flex stu-items-center stu-justify-between stu-mb-6">
        <div>
            <h1 class="stu-page-title" style="font-size: 24px; font-weight: 800; color: var(--color-primary); margin: 0;">
                Nghị quyết Hội đồng trường STU
            </h1>
            <p style="font-size: 13px; color: var(--color-text-muted); margin: 4px 0 0 0;">
                Quản lý các Nghị quyết đã thông qua, theo dõi quy trình ký ban hành, công bố và phân rã nhiệm vụ thi hành.
            </p>
        </div>
        <div>
            @if(in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN']))
                <button type="button" class="stu-btn stu-btn-primary stu-flex stu-items-center" style="gap: 8px;" data-modal-open="createResolutionModal">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    <span>Ghi nhận số ngày ban hành nghị quyết</span>
                </button>
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

    {{-- Bộ lọc tìm kiếm đa tiêu chí --}}
    <div class="stu-card stu-mb-6" style="padding: 20px 24px; border-radius: 12px; border: 1px solid var(--color-border); box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05); background: #ffffff;">
        <form method="GET" action="{{ route('resolutions.index') }}" class="stu-grid" style="grid-template-columns: 2fr 1.5fr 1fr 1fr auto auto; gap: 14px; align-items: flex-end;">
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
                        placeholder="Số hiệu, trích yếu, nội dung..." 
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
                    <option value="DRAFT" {{ request('status') === 'DRAFT' ? 'selected' : '' }}>Dự thảo</option>
                    <option value="SIGNED" {{ request('status') === 'SIGNED' ? 'selected' : '' }}>Đã ký ban hành</option>
                    <option value="PUBLISHED" {{ request('status') === 'PUBLISHED' ? 'selected' : '' }}>Đã công bố hiệu lực</option>
                </select>
            </div>

            {{-- Năm --}}
            <div class="stu-form-group" style="margin-bottom: 0;">
                <label class="stu-form-label">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    <span>Năm ban hành</span>
                </label>
                <select name="year" class="stu-form-control">
                    <option value="">Tất cả các năm</option>
                    @foreach($years as $y)
                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>Năm {{ $y }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Nút bấm Lọc & Reset --}}
            <div>
                <button type="submit" class="stu-btn stu-btn-primary" style="height: 38px; padding: 0 16px; font-weight: 600; box-shadow: 0 1px 2px 0 rgba(0, 58, 140, 0.2);">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span>Lọc</span>
                </button>
            </div>
            @if(request()->anyFilled(['search', 'meeting_id', 'status', 'year']))
                <div>
                    <a href="{{ route('resolutions.index') }}" class="stu-btn stu-btn-ghost" style="height: 38px; padding: 0 14px;" title="Đặt lại bộ lọc về mặc định">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>Đặt lại</span>
                    </a>
                </div>
            @endif
        </form>
    </div>

    {{-- Bảng danh sách Nghị quyết (Áp dụng layout chống tràn) --}}
    <x-bang-du-lieu 
        cardTitle="Danh sách Nghị quyết Hội đồng trường (Tổng: {{ $resolutions->total() }})"
        :headers="['Số hiệu', 'Trích yếu nội dung', 'Phiên họp và Ngày', 'Trạng thái', 'Tiến độ thực hiện']" 
        :hasActions="true"
        actionTitle="Thao tác"
    >
        @forelse($resolutions as $res)
            @php
                $tasks = $res->nhiemVu;
                $totalTasks = $tasks->count();
                $completedTasks = $tasks->filter(fn ($t) => in_array($t->TrangThai, ['COMPLETED', 'Hoàn thành']))->count();
                $progress = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0;
                $isDraft = in_array($res->TrangThai, ['DRAFT', 'Dự thảo']);
                $isSigned = in_array($res->TrangThai, ['SIGNED', 'Đã ký ban hành', 'Đã ký']);
                $isPublished = in_array($res->TrangThai, ['PUBLISHED', 'Đã công bố hiệu lực', 'Đã công bố']);
            @endphp
            <tr>
                <td style="font-weight: 700; color: var(--color-primary); white-space: nowrap;">
                    <a href="{{ route('resolutions.show', $res->MaNghiQuyet) }}" style="color: var(--color-primary); text-decoration: none;">
                        {{ $res->SoNghiQuyet }}
                    </a>
                </td>
                <td style="max-width: 340px;">
                    <a href="{{ route('resolutions.show', $res->MaNghiQuyet) }}" style="font-weight: 600; color: var(--color-text-primary); text-decoration: none; line-height: 1.5; display: block;" class="hover:text-primary">
                        {{ $res->TrichYeu }}
                    </a>
                    @if($res->taiLieu->count() > 0)
                        <div style="font-size: 11px; color: var(--color-accent); margin-top: 3px; display: flex; align-items: center; gap: 4px;">
                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                            <span>Có {{ $res->taiLieu->count() }} văn bản chính thức</span>
                        </div>
                    @endif
                </td>
                <td style="max-width: 210px;">
                    <div style="font-weight: 500; font-size: 13px; line-height: 1.4;">
                        {{ $res->phienHop?->TenPhienHop ?? 'Phiên họp #' . $res->MaPhienHop }}
                    </div>
                    <div style="font-size: 11px; color: var(--color-text-muted); margin-top: 2px;">
                        {{ $res->NgayBanHanh ? \Carbon\Carbon::parse($res->NgayBanHanh)->format('d/m/Y') : 'Chưa ấn định' }}
                    </div>
                </td>
                <td style="text-align: center; white-space: nowrap;">
                    <x-nhan-trang-thai :status="$res->TrangThai" />
                </td>
                <td style="text-align: center; min-width: 120px;">
                    @if($totalTasks > 0)
                        <div style="font-size: 12px; font-weight: 700; color: var(--color-primary); margin-bottom: 4px;">
                            {{ $progress }}% ({{ $completedTasks }}/{{ $totalTasks }} NV)
                        </div>
                        <div style="height: 6px; background: var(--color-bg-muted); border-radius: var(--radius-full); overflow: hidden;">
                            <div style="height: 100%; width: {{ $progress }}%; background: {{ $progress == 100 ? 'var(--color-success)' : 'var(--color-primary)' }};"></div>
                        </div>
                    @else
                        <a href="{{ route('tasks.create', ['resolution_id' => $res->MaNghiQuyet]) }}" style="font-size: 12px; color: var(--color-text-muted); text-decoration: none;" class="hover:text-primary" title="Bấm để phân rã nhiệm vụ">
                            Chưa phân rã NV ↗
                        </a>
                    @endif
                </td>
                <td class="col-actions" style="text-align: right; white-space: nowrap;">
                    <div class="stu-actions-2x2">
                        {{-- Hàng 1 (Trên): Xem & Sửa --}}
                        <a href="{{ route('resolutions.show', $res->MaNghiQuyet) }}" class="stu-btn-2x2 btn-view" title="Xem chi tiết toàn văn">
                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <span>Xem</span>
                        </a>

                        @if($isDraft && in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN']))
                            <a href="{{ route('resolutions.edit', $res->MaNghiQuyet) }}" class="stu-btn-2x2 btn-update" title="Chỉnh sửa dự thảo">
                                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                <span>Sửa</span>
                            </a>
                        @endif

                        {{-- Hàng 2 (Dưới): Ký / Công bố & Ẩn --}}
                        @if($isDraft && in_array($role, ['CHAIR', 'ADMIN']))
                            <form method="POST" action="{{ route('resolutions.sign', $res->MaNghiQuyet) }}" style="display: inline-block;">
                                @csrf
                                <button type="submit" class="stu-btn-2x2 btn-success" title="Chủ tịch ký ban hành">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    <span>Ký</span>
                                </button>
                            </form>
                        @elseif($isSigned && in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN']))
                            <form method="POST" action="{{ route('resolutions.publish', $res->MaNghiQuyet) }}" style="display: inline-block;">
                                @csrf
                                <button type="submit" class="stu-btn-2x2 btn-primary" title="Công bố hiệu lực toàn trường">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                    <span>Công bố</span>
                                </button>
                            </form>
                        @endif

                        @if(in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN']))
                            <form method="POST" action="{{ route('resolutions.destroy', $res->MaNghiQuyet) }}" onsubmit="return confirm('Bạn có chắc chắn muốn ẩn Nghị quyết {{ $res->SoNghiQuyet }} khỏi giao diện? (Dữ liệu vẫn được bảo toàn trong hệ thống)');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="stu-btn-2x2 btn-delete" title="Ẩn Nghị quyết (Bảo toàn dữ liệu)">
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
                <td colspan="6" style="text-align: center; padding: 48px; color: var(--color-text-muted);">
                    <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="margin: 0 auto 12px; color: var(--color-border);"><path stroke-linecap="round" stroke-linejoin="round" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                    <div style="font-weight: 600; font-size: 15px;">Chưa có Nghị quyết nào</div>
                    <div style="font-size: 13px; margin-top: 4px;">Các Nghị quyết được ban hành từ phiên họp sẽ xuất hiện tại đây.</div>
                </td>
            </tr>
        @endforelse
    </x-bang-du-lieu>

    {{-- Phân trang --}}
    <div class="stu-mt-6" style="width: 100%;">
        {{ $resolutions->links() }}
    </div>
</div>

<!-- MODAL FORM LƠ LỬNG: GHI NHẬN SỐ NGÀY BAN HÀNH NGHỊ QUYẾT -->
<x-hop-thoai id="createResolutionModal" title="Ghi Nhận Số Ngày Ban Hành Nghị Quyết" size="lg">
    <form method="POST" action="{{ route('resolutions.store') }}" enctype="multipart/form-data" id="modalCreateResolutionForm">
        @csrf

        <div style="display: grid; grid-template-columns: 1.2fr 1.5fr 1fr; gap: 12px; margin-bottom: 14px;">
            <div class="stu-form-group">
                <label class="stu-form-label" style="font-size: 12.5px; font-weight: 700;">
                    Số hiệu Nghị quyết <span style="color: var(--color-danger);">*</span>
                </label>
                <input 
                    type="text" 
                    name="SoNghiQuyet" 
                    id="modalSoNghiQuyet"
                    value="{{ old('SoNghiQuyet', '0' . ($resolutions->total() + 1) . '/NQ-HĐT-' . date('Y')) }}" 
                    class="stu-form-control" 
                    placeholder="Ví dụ: 08/NQ-HĐT-2026" 
                    required
                    style="height: 38px; font-size: 13px;"
                >
            </div>

            <div class="stu-form-group">
                <label class="stu-form-label" style="font-size: 12.5px; font-weight: 700;">
                    Kỳ họp ban hành <span style="color: var(--color-danger);">*</span>
                </label>
                <select name="MaPhienHop" class="stu-form-control" required style="height: 38px; font-size: 13px;">
                    <option value="">-- Chọn phiên họp --</option>
                    @foreach($meetings as $m)
                        <option value="{{ $m->MaPhienHop }}">
                            #{{ $m->MaPhienHop }} - {{ $m->TenPhienHop }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="stu-form-group">
                <label class="stu-form-label" style="font-size: 12.5px; font-weight: 700;">
                    Ngày ban hành <span style="color: var(--color-danger);">*</span>
                </label>
                <input 
                    type="date" 
                    name="NgayBanHanh" 
                    value="{{ old('NgayBanHanh', date('Y-m-d')) }}" 
                    class="stu-form-control" 
                    required
                    style="height: 38px; font-size: 13px;"
                >
            </div>
        </div>

        {{-- 2. Trích yếu nội dung --}}
        <div class="stu-form-group" style="margin-bottom: 14px;">
            <label class="stu-form-label" style="font-size: 12.5px; font-weight: 700;">
                Trích yếu nội dung Nghị quyết <span style="color: var(--color-danger);">*</span>
            </label>
            <textarea 
                name="TrichYeu" 
                id="modalTrichYeu"
                rows="2" 
                class="stu-form-control" 
                placeholder="Ví dụ: Về việc thông qua đề án thành lập Viện Nghiên cứu Ứng dụng Trí tuệ Nhân tạo STU..." 
                required
                style="font-size: 13px;"
            >{{ old('TrichYeu') }}</textarea>
        </div>

        {{-- 3. Toàn văn nghị quyết --}}
        <div class="stu-form-group" style="margin-bottom: 14px;">
            <label class="stu-form-label" style="font-size: 12.5px; font-weight: 700;">
                Nội dung toàn văn nghị quyết <span style="color: var(--color-danger);">*</span>
            </label>
            <textarea 
                name="NoiDung" 
                rows="4" 
                class="stu-form-control" 
                placeholder="Điều 1: Hội đồng trường quyết nghị thông qua đề án... Điều 2: Giao Hiệu trưởng ban hành quyết định thành lập và bổ nhiệm nhân sự..." 
                required
                style="font-size: 13px;"
            >{{ old('NoiDung') }}</textarea>
        </div>

        {{-- 4. Tập tin văn bản đính kèm --}}
        <div class="stu-form-group" style="margin-bottom: 14px;">
            <label class="stu-form-label" style="font-size: 12.5px; font-weight: 700;">
                Tập tin văn bản ký số / scan (.pdf, .docx)
            </label>
            <input 
                type="file" 
                name="file" 
                class="stu-form-control" 
                accept=".pdf,.doc,.docx"
                style="height: 38px; font-size: 12px; padding: 6px 12px;"
            >
        </div>

        {{-- 5. Quy tắc nghiệp vụ tự động --}}
        <div style="padding: 12px 14px; background: #f0f9ff; border: 1px dashed #bae6fd; border-radius: 8px; font-size: 12px; color: #0369a1; line-height: 1.5; margin-bottom: 16px;">
            <strong>Quy tắc nghiệp vụ tự động (Yêu cầu chức năng 15 & BR-10):</strong> Hệ thống tự động ghi nhận số hiệu và ngày ban hành chính thức của Nghị quyết gắn liền với phiên họp Hội đồng trường, tạo căn cứ pháp lý để kích hoạt quy trình phân rã nhiệm vụ thi hành.
        </div>

        <!-- Footer buttons -->
        <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 18px; padding-top: 14px; border-top: 1px solid var(--color-border);">
            <button type="button" class="stu-btn stu-btn-secondary" data-modal-close>
                <span>Hủy bỏ</span>
            </button>
            <button type="submit" class="stu-btn stu-btn-primary" style="font-weight: 600;">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                <span>Xác nhận ghi nhận và Ban hành</span>
            </button>
        </div>
    </form>
</x-hop-thoai>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('create') === '1' || urlParams.get('modal') === '1' || {{ $errors->any() ? 'true' : 'false' }}) {
            if (window.STUModal) {
                window.STUModal.open('createResolutionModal');
            }
        }
    });
</script>
@endsection
