@extends('layouts.chinh')

@section('title', 'Quản lý Biểu quyết & Bỏ phiếu - Hội đồng trường STU')

@section('content')
<div class="stu-page-container">
    {{-- Breadcrumb --}}
    <x-thanh-dieu-huong :items="['Biểu quyết & Bỏ phiếu' => null]" />

    {{-- Header --}}
    <div class="stu-page-header stu-flex stu-items-center stu-justify-between stu-mb-6">
        <div>
            <h1 class="stu-page-title" style="font-size: 24px; font-weight: 800; color: var(--color-primary); margin: 0;">
                Biểu quyết &amp; Bỏ phiếu Hội đồng trường
            </h1>
            <p style="font-size: 13px; color: var(--color-text-muted); margin: 4px 0 0 0;">
                Hệ thống kiểm phiếu điện tử bảo mật tuyệt đối (BR-09), hỗ trợ biểu quyết trực tiếp và ủy quyền hợp lệ (BR-04).
            </p>
        </div>
        @if(in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN']))
            <button type="button" class="stu-btn stu-btn-primary" data-modal-open="createVoteModal" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 600; box-shadow: 0 2px 4px rgba(0, 58, 140, 0.2);">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                <span>Tạo nội dung biểu quyết</span>
            </button>
        @endif
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <x-canh-bao type="success" :dismissible="true" class="stu-mb-4">
            {{ session('success') }}
        </x-canh-bao>
    @endif
    @if(session('warning'))
        <x-canh-bao type="warning" :dismissible="true" class="stu-mb-4">
            {{ session('warning') }}
        </x-canh-bao>
    @endif
    @if(session('error'))
        <x-canh-bao type="danger" :dismissible="true" class="stu-mb-4">
            {{ session('error') }}
        </x-canh-bao>
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

            {{-- Nút bấm Lọc --}}
            <div>
                <button type="submit" class="stu-btn stu-btn-primary" style="height: 38px; padding: 0 16px; font-weight: 600; box-shadow: 0 1px 2px 0 rgba(0, 58, 140, 0.2);">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span>Lọc</span>
                </button>
            </div>
        </form>
    </div>

    {{-- Bảng danh sách nội dung biểu quyết --}}
    <x-bang-du-lieu 
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
                <td style="max-width: 340px;">
                    <a href="{{ route('votes.show', $v->MaNoiDung) }}" style="font-weight: 600; color: var(--color-text-primary); text-decoration: none; line-height: 1.5; display: block;" class="hover:text-primary">
                        {{ $v->NoiDung }}
                    </a>
                    @if($v->chuongTrinhHop)
                        <div style="font-size: 11px; color: var(--color-text-muted); margin-top: 4px;">
                            Thuộc mục chương trình họp: <strong>Mục số {{ $v->chuongTrinhHop->ThuTu }}</strong>
                        </div>
                    @endif
                </td>
                <td style="max-width: 200px; font-size: 13px; line-height: 1.4;">
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
                        @if(in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN']))
                            @if(! $isOpening && ! in_array($v->TrangThai, ['CLOSED', 'Đã đóng']))
                                <form method="POST" action="{{ route('votes.open', $v->MaNoiDung) }}" style="display: inline; margin: 0;">
                                    @csrf
                                    <button type="submit" class="stu-btn stu-btn-success stu-btn-sm" title="Mở cổng nhận phiếu biểu quyết">
                                        <span>Mở BQ</span>
                                    </button>
                                </form>
                            @elseif($isOpening)
                                <form method="POST" action="{{ route('votes.close', $v->MaNoiDung) }}" style="display: inline; margin: 0;" onsubmit="return confirm('Bạn có chắc chắn muốn đóng và chốt kết quả biểu quyết này?');">
                                    @csrf
                                    <button type="submit" class="stu-btn stu-btn-danger stu-btn-sm" title="Đóng và chốt kết quả">
                                        <span>Đóng BQ</span>
                                    </button>
                                </form>
                            @endif
                        @endif
                        <a href="{{ route('votes.show', $v->MaNoiDung) }}" class="stu-btn {{ $isOpening ? 'stu-btn-primary' : 'stu-btn-outline' }} stu-btn-sm" title="{{ $isOpening ? 'Vào phòng bỏ phiếu trực tuyến' : 'Xem kết quả biểu quyết' }}">
                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>{{ $isOpening ? 'Bỏ phiếu' : 'Xem kết quả' }}</span>
                        </a>
                        @if(in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN']))
                            <form method="POST" action="{{ route('votes.destroy', $v->MaNoiDung) }}" onsubmit="return confirm('Bạn có chắc chắn muốn ẩn nội dung biểu quyết này khỏi giao diện? (Dữ liệu vẫn được bảo toàn trong hệ thống)');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="stu-btn stu-btn-outline stu-btn-sm" style="color: var(--color-text-secondary);" title="Ẩn nội dung biểu quyết (Bảo toàn dữ liệu)">
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
                    <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="margin: 0 auto 12px; color: var(--color-border);"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div style="font-weight: 600; font-size: 15px;">Chưa có nội dung biểu quyết nào</div>
                    <div style="font-size: 13px; margin-top: 4px;">Các câu hỏi biểu quyết sẽ xuất hiện khi Thư ký khởi tạo trong phiên họp tương ứng.</div>
                </td>
            </tr>
        @endforelse
    </x-bang-du-lieu>

    {{-- Phân trang --}}
    <div class="stu-mt-6" style="width: 100%;">
        {{ $votings->links() }}
    </div>

    {{-- Modal Tạo Nội dung Biểu quyết mới (Thiết kế phân tầng chống tràn) --}}
    @if(in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN']))
        <x-hop-thoai id="createVoteModal" title="Khởi Tạo Nội Dung Biểu Quyết Mới" size="lg">
            <form method="POST" action="{{ route('votes.store') }}" style="max-width: 100%;">
                @csrf

                {{-- Banner hướng dẫn nghiệp vụ --}}
                <div style="background: rgba(0, 43, 91, 0.04); border: 1px solid rgba(0, 43, 91, 0.12); border-radius: 8px; padding: 10px 14px; margin-bottom: 16px; display: flex; align-items: flex-start; gap: 10px;">
                    <svg width="18" height="18" fill="none" stroke="#002b5b" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink: 0; margin-top: 1px;"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div style="font-size: 12.5px; color: #1e293b; line-height: 1.45;">
                        Chọn <strong>Phiên họp</strong> trước để danh sách mục thảo luận/tờ trình tự động thu gọn theo phiên tương ứng, giúp hiển thị gọn gàng và không bị tràn giao diện.
                    </div>
                </div>

                {{-- 2 Cột lựa chọn phân cấp: Phiên họp -> Mục chương trình --}}
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 14px; margin-bottom: 14px;">
                    {{-- Cột 1: Lọc nhanh theo Phiên họp --}}
                    <div class="stu-form-group" style="margin-bottom: 0;">
                        <label class="stu-form-label" style="font-weight: 600; font-size: 13px; margin-bottom: 6px; display: block; color: var(--color-text-main);">
                            1. Phiên họp Hội đồng trường <span style="color: #dc2626;">*</span>
                        </label>
                        <select id="modalFilterMeeting" class="stu-form-control" style="font-size: 13px; border-radius: 6px; width: 100%; max-width: 100%;">
                            <option value="">-- Tất cả phiên họp (Lọc nhanh) --</option>
                            @foreach($meetings as $m)
                                <option value="{{ $m->MaPhienHop }}" {{ (request('meeting_id') == $m->MaPhienHop) ? 'selected' : '' }}>
                                    Phiên #{{ $m->MaPhienHop }}: {{ Str::limit($m->TenPhienHop, 45) }} ({{ $m->NgayHop ? date('d/m/Y', strtotime($m->NgayHop)) : 'Chưa định ngày' }})
                                </option>
                            @endforeach
                        </select>
                        <small style="font-size: 11px; color: var(--color-text-muted); margin-top: 4px; display: block;">
                            Chọn phiên để lọc các mục chương trình họp.
                        </small>
                    </div>

                    {{-- Cột 2: Chọn mục chương trình nghị sự & Tờ trình liên kết --}}
                    <div class="stu-form-group" style="margin-bottom: 0;">
                        <label class="stu-form-label" style="font-weight: 600; font-size: 13px; margin-bottom: 6px; display: block; color: var(--color-text-main);">
                            2. Mục chương trình / Tờ trình <span style="color: #dc2626;">*</span>
                        </label>
                        <select name="MaChuongTrinh" id="modalSelectAgenda" required class="stu-form-control" style="font-size: 13px; border-radius: 6px; width: 100%; max-width: 100%; text-overflow: ellipsis;">
                            <option value="">-- Chọn mục chương trình họp --</option>
                            @php
                                $groupedAgendas = ($agendaItems ?? collect())->groupBy('MaPhienHop');
                            @endphp
                            @foreach($groupedAgendas as $mId => $items)
                                @php
                                    $meetingObj = $items->first()?->phienHop;
                                    $meetingName = $meetingObj?->TenPhienHop ?? ('Phiên họp #' . $mId);
                                @endphp
                                <optgroup label="[Phiên #{{ $mId }}] {{ Str::limit($meetingName, 45) }}" data-meeting="{{ $mId }}">
                                    @foreach($items as $item)
                                        @php
                                            $itemTitle = $item->hoSoTrinh?->TieuDe ?? 'Nội dung thảo luận';
                                            $itemHoSo = $item->hoSoTrinh?->ma_ho_so_hien_thi ?? ($item->MaHoSo ? '#HS-' . $item->MaHoSo : null);
                                        @endphp
                                        <option value="{{ $item->MaChuongTrinh }}"
                                                data-meeting="{{ $mId }}"
                                                data-meeting-name="Phiên #{{ $mId }} - {{ $meetingName }}"
                                                data-order="{{ $item->ThuTu }}"
                                                data-title="{{ $itemTitle }}"
                                                data-hoso="{{ $itemHoSo ?? 'Thảo luận phiên' }}">
                                            Mục {{ $item->ThuTu }}: {{ Str::limit($itemTitle, 55) }} {{ $itemHoSo ? "($itemHoSo)" : '' }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        <small style="font-size: 11px; color: var(--color-text-muted); margin-top: 4px; display: block;">
                            Mỗi câu hỏi biểu quyết gắn liền với một mục chương trình.
                        </small>
                    </div>
                </div>

                {{-- Thẻ xem trước chi tiết nội dung đã chọn --}}
                <div id="modalAgendaPreviewBox" style="display: none; margin-bottom: 16px; padding: 12px 14px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px; flex-wrap: wrap; gap: 6px;">
                        <span style="font-weight: 700; color: #166534; font-size: 11.5px; display: inline-flex; align-items: center; gap: 5px; text-transform: uppercase; letter-spacing: 0.5px;">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            Mục chương trình đã chọn
                        </span>
                        <button type="button" id="btnAutoFillTitle" style="background: none; border: none; color: #0284c7; font-size: 12px; font-weight: 600; cursor: pointer; text-decoration: underline; padding: 0;">
                            Sử dụng tên tờ trình làm câu hỏi biểu quyết ✍️
                        </button>
                    </div>
                    <div id="previewAgendaFullTitle" style="font-weight: 600; color: #0f172a; font-size: 13.5px; line-height: 1.5; margin-bottom: 6px;"></div>
                    <div style="font-size: 12px; color: #475569; display: flex; gap: 16px; flex-wrap: wrap;">
                        <span>🏛️ <span id="previewAgendaMeeting" style="font-weight: 500;"></span></span>
                        <span>📄 Hồ sơ trình: <strong id="previewAgendaDossier" style="color: #0369a1;"></strong></span>
                    </div>
                </div>

                {{-- Ô nhập Câu hỏi / Nội dung biểu quyết --}}
                <div class="stu-form-group" style="margin-bottom: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <label class="stu-form-label" style="font-weight: 600; font-size: 13px; margin: 0; display: block; color: var(--color-text-main);">
                            3. Câu hỏi / Nội dung biểu quyết <span style="color: #dc2626;">*</span>
                        </label>
                        <span style="font-size: 11px; color: var(--color-text-muted);">Tối đa 255 ký tự</span>
                    </div>
                    <textarea name="NoiDung" id="modalVoteContentInput" required minlength="5" maxlength="255" rows="2" class="stu-form-control" style="font-size: 13px; border-radius: 6px; resize: vertical; line-height: 1.5; height: auto;" placeholder="Ví dụ: Thông qua Đề án thành lập Viện Nghiên cứu Ứng dụng AI STU"></textarea>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px; padding-top: 16px; border-top: 1px solid var(--color-border);">
                    <button type="button" class="stu-btn stu-btn-secondary" data-modal-close>Hủy bỏ</button>
                    <button type="submit" class="stu-btn stu-btn-primary" style="box-shadow: 0 1px 3px rgba(0, 58, 140, 0.2);">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <span>Khởi tạo biểu quyết</span>
                    </button>
                </div>
            </form>
        </x-hop-thoai>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const filterMeetingSelect = document.getElementById('modalFilterMeeting');
                const agendaSelect = document.getElementById('modalSelectAgenda');
                const previewBox = document.getElementById('modalAgendaPreviewBox');
                const previewFullTitle = document.getElementById('previewAgendaFullTitle');
                const previewMeeting = document.getElementById('previewAgendaMeeting');
                const previewDossier = document.getElementById('previewAgendaDossier');
                const voteContentInput = document.getElementById('modalVoteContentInput');
                const btnAutoFill = document.getElementById('btnAutoFillTitle');

                if (filterMeetingSelect && agendaSelect) {
                    const allOptgroups = Array.from(agendaSelect.querySelectorAll('optgroup'));

                    function filterAgendasByMeeting(selectedMeetingId) {
                        allOptgroups.forEach(group => {
                            const groupMeetingId = group.getAttribute('data-meeting');
                            if (!selectedMeetingId || groupMeetingId === selectedMeetingId) {
                                group.hidden = false;
                                group.disabled = false;
                                group.style.display = '';
                            } else {
                                group.hidden = true;
                                group.disabled = true;
                                group.style.display = 'none';
                            }
                        });

                        // Nếu option đang chọn thuộc nhóm bị ẩn, reset lựa chọn
                        const currentOption = agendaSelect.options[agendaSelect.selectedIndex];
                        if (currentOption && currentOption.value) {
                            const currentMeeting = currentOption.getAttribute('data-meeting');
                            if (selectedMeetingId && currentMeeting !== selectedMeetingId) {
                                agendaSelect.value = '';
                                updatePreview();
                            }
                        }
                    }

                    function updatePreview() {
                        const selectedOption = agendaSelect.options[agendaSelect.selectedIndex];
                        if (!selectedOption || !selectedOption.value) {
                            if (previewBox) previewBox.style.display = 'none';
                            return;
                        }

                        const title = selectedOption.getAttribute('data-title') || '';
                        const meetingName = selectedOption.getAttribute('data-meeting-name') || '';
                        const hoso = selectedOption.getAttribute('data-hoso') || '';

                        if (previewFullTitle) previewFullTitle.textContent = title;
                        if (previewMeeting) previewMeeting.textContent = meetingName;
                        if (previewDossier) previewDossier.textContent = hoso;
                        if (previewBox) previewBox.style.display = 'block';

                        // Gợi ý tự động điền nếu ô nội dung đang trống hoặc đã được điền tự động
                        if (voteContentInput && (!voteContentInput.value.trim() || voteContentInput.dataset.autoFilled === 'true')) {
                            voteContentInput.value = 'Thông qua: ' + title;
                            voteContentInput.dataset.autoFilled = 'true';
                        }
                    }

                    filterMeetingSelect.addEventListener('change', function () {
                        filterAgendasByMeeting(this.value);
                    });

                    agendaSelect.addEventListener('change', function () {
                        updatePreview();
                    });

                    if (btnAutoFill) {
                        btnAutoFill.addEventListener('click', function () {
                            const selectedOption = agendaSelect.options[agendaSelect.selectedIndex];
                            if (selectedOption && selectedOption.value) {
                                const title = selectedOption.getAttribute('data-title') || '';
                                if (voteContentInput) {
                                    voteContentInput.value = 'Thông qua: ' + title;
                                    voteContentInput.focus();
                                }
                            }
                        });
                    }

                    if (voteContentInput) {
                        voteContentInput.addEventListener('input', function () {
                            this.dataset.autoFilled = 'false';
                        });
                    }

                    // Tự động kích hoạt lọc nếu đã chọn phiên họp từ URL
                    if (filterMeetingSelect.value) {
                        filterAgendasByMeeting(filterMeetingSelect.value);
                    }
                }

                const urlParams = new URLSearchParams(window.location.search);
                if (urlParams.get('create') === '1' @if($errors->any()) || true @endif) {
                    if (window.STUModal) {
                        window.STUModal.open('createVoteModal');
                    }
                }
            });
        </script>
    @endif
</div>
@endsection
