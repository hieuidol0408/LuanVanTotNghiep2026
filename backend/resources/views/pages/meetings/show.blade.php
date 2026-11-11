@extends('layouts.app')

@section('title', $meeting->TenPhienHop . ' - Hội đồng trường STU')

@section('header_title')
    <x-breadcrumb :items="[
        ['title' => 'Quản lý phiên họp', 'url' => route('meetings.index')],
        ['title' => '#' . $meeting->MaPhienHop]
    ]" />
@endsection

@section('content')
<!-- HEADER PHIÊN HỌP -->
<div class="stu-page-header">
    <div class="stu-page-title-group">
        <div style="display: flex; align-items: center; gap: var(--space-3); margin-bottom: var(--space-1);">
            <span class="stu-badge stu-badge-info" style="font-size: 13px;">Phiên #{{ $meeting->MaPhienHop }}</span>
            <x-status-badge :status="$meeting->TrangThai" />
        </div>
        <h1>{{ $meeting->TenPhienHop }}</h1>
        <p style="display: flex; align-items: center; gap: var(--space-4); margin-top: 6px; flex-wrap: wrap;">
            <span>📅 Ngày: <strong>{{ $meeting->NgayHop ? date('d/m/Y', strtotime($meeting->NgayHop)) : '' }}</strong></span>
            <span>⏰ Giờ: <strong>{{ $meeting->ThoiGianBatDau ? date('H:i', strtotime($meeting->ThoiGianBatDau)) : '' }} - {{ $meeting->ThoiGianKetThuc ? date('H:i', strtotime($meeting->ThoiGianKetThuc)) : '' }}</strong></span>
            <span>📍 Địa điểm: <strong>{{ $meeting->DiaDiem }}</strong> ({{ $meeting->HinhThuc }})</span>
            <span>👤 Người tạo: <strong>{{ $meeting->nguoiDung?->HoTen ?? 'ThS. Nguyễn Thị Thư Ký' }}</strong> (ID: #{{ $meeting->MaNguoiDung ?? 3 }})</span>
        </p>
    </div>
    <div class="stu-page-actions">
        @if(in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN']))
            <x-button variant="secondary" href="{{ route('meetings.edit', $meeting->MaPhienHop) }}">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Chỉnh sửa phiên</span>
            </x-button>
        @endif
        <x-button variant="outline" href="{{ route('meetings.index') }}">
            <span>Danh sách phiên</span>
        </x-button>
    </div>
</div>

<!-- HỆ THỐNG 7 TABS NGHIỆP VỤ CHUYÊN SÂU -->
<x-tabs :tabs="[
    ['id' => 'tab-info', 'target' => '#pane-info', 'label' => '1. Thông tin chung', 'active' => true],
    ['id' => 'tab-members', 'target' => '#pane-members', 'label' => '2. Thành viên & Điểm danh', 'badge' => $meeting->thamDu->count()],
    ['id' => 'tab-guests', 'target' => '#pane-guests', 'label' => '3. Khách mời', 'badge' => $meeting->khachMoiPhienHop->count()],
    ['id' => 'tab-agenda', 'target' => '#pane-agenda', 'label' => '4. Chương trình họp', 'badge' => $meeting->chuongTrinhHop->count()],
    ['id' => 'tab-docs', 'target' => '#pane-docs', 'label' => '5. Tài liệu phiên họp', 'badge' => $meeting->taiLieu->count()],
    ['id' => 'tab-voting', 'target' => '#pane-voting', 'label' => '6. Biểu quyết & Kết quả', 'badge' => $meeting->noiDungBieuQuyet->count()],
    ['id' => 'tab-resolutions', 'target' => '#pane-resolutions', 'label' => '7. Nghị quyết ban hành', 'badge' => $resolutions->count()],
]">

    <!-- ==================== TAB 1: THÔNG TIN CHUNG ==================== -->
    <div class="stu-tab-pane active" id="pane-info">
        <div class="stu-grid stu-grid-cols-2">
            <div class="stu-card" style="padding: var(--space-6);">
                <h3 class="stu-card-title" style="margin-bottom: var(--space-4);">Chi tiết tổ chức</h3>
                <table class="stu-table" style="font-size: var(--font-size-sm);">
                    <tr>
                        <td style="width: 160px; color: var(--color-text-muted); font-weight: 500;">Mã phiên họp:</td>
                        <td><strong>#{{ $meeting->MaPhienHop }}</strong></td>
                    </tr>
                    <tr>
                        <td style="color: var(--color-text-muted); font-weight: 500;">Tên phiên họp:</td>
                        <td><strong>{{ $meeting->TenPhienHop }}</strong></td>
                    </tr>
                    <tr>
                        <td style="color: var(--color-text-muted); font-weight: 500;">Ngày diễn ra:</td>
                        <td>{{ $meeting->NgayHop ? date('d/m/Y', strtotime($meeting->NgayHop)) : '' }}</td>
                    </tr>
                    <tr>
                        <td style="color: var(--color-text-muted); font-weight: 500;">Khung thời gian:</td>
                        <td>{{ $meeting->ThoiGianBatDau ? date('H:i', strtotime($meeting->ThoiGianBatDau)) : '' }} - {{ $meeting->ThoiGianKetThuc ? date('H:i', strtotime($meeting->ThoiGianKetThuc)) : '' }}</td>
                    </tr>
                    <tr>
                        <td style="color: var(--color-text-muted); font-weight: 500;">Địa điểm:</td>
                        <td>{{ $meeting->DiaDiem }}</td>
                    </tr>
                    <tr>
                        <td style="color: var(--color-text-muted); font-weight: 500;">Hình thức họp:</td>
                        <td><span class="stu-badge stu-badge-info">{{ $meeting->HinhThuc }}</span></td>
                    </tr>
                    <tr>
                        <td style="color: var(--color-text-muted); font-weight: 500;">Người tạo:</td>
                        <td>
                            <strong>{{ $meeting->nguoiDung?->HoTen ?? 'ThS. Nguyễn Thị Thư Ký' }}</strong>
                            <span style="font-size: 11px; color: var(--color-text-muted);">(ID: #{{ $meeting->MaNguoiDung ?? 3 }})</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="color: var(--color-text-muted); font-weight: 500;">Trạng thái:</td>
                        <td><x-status-badge :status="$meeting->TrangThai" /></td>
                    </tr>
                </table>
            </div>

            <div class="stu-card" style="padding: var(--space-6);">
                <h3 class="stu-card-title" style="margin-bottom: var(--space-4);">Ghi chú & Kịch bản phiên họp</h3>
                <div style="font-size: var(--font-size-sm); color: var(--color-text-body); line-height: var(--line-height-relaxed); background: var(--color-bg-subtle); padding: var(--space-4); border-radius: var(--radius-md); min-height: 140px;">
                    {!! nl2br(e($meeting->GhiChu ?: 'Chưa có ghi chú hoặc kịch bản chi tiết cho phiên họp này.')) !!}
                </div>

                <div style="margin-top: var(--space-6);">
                    <h4 style="font-size: var(--font-size-sm); font-weight: 600; color: var(--color-text-main); margin-bottom: var(--space-2);">
                        Tổng quan thành phần phiên họp
                    </h4>
                    <div style="display: flex; gap: var(--space-3); flex-wrap: wrap;">
                        <span class="stu-badge stu-badge-neutral">
                            👥 {{ $meeting->thamDu->count() }} Thành viên HĐT
                        </span>
                        <span class="stu-badge stu-badge-neutral">
                            🎟️ {{ $meeting->khachMoiPhienHop->count() }} Khách mời
                        </span>
                        <span class="stu-badge stu-badge-neutral">
                            📑 {{ $meeting->chuongTrinhHop->count() }} Hồ sơ trong chương trình
                        </span>
                        <span class="stu-badge stu-badge-neutral">
                            🗳️ {{ $meeting->noiDungBieuQuyet->count() }} Nội dung biểu quyết
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== TAB 2: THÀNH VIÊN THAM DỰ & ĐIỂM DANH (BR-02) ==================== -->
    <div class="stu-tab-pane" id="pane-members">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--space-4);">
            <div>
                <h3 style="font-size: var(--font-size-base); font-weight: 700; color: var(--color-primary); margin-bottom: 2px;">
                    Danh sách Thành viên Hội đồng trường & Điểm danh
                </h3>
                <p style="font-size: var(--font-size-xs); color: var(--color-text-muted);">
                    Tuân thủ <strong>BR-02</strong>: Đảm bảo không trùng lặp thành viên trong cùng một phiên họp.
                </p>
            </div>
            @if(in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN']) && $availableMembers->isNotEmpty())
                <x-button variant="primary" size="sm" data-modal-open="addMemberModal">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    <span>Mời thêm thành viên</span>
                </x-button>
            @endif
        </div>

        <x-table 
            :headers="['Họ và tên', 'Chức danh HĐT', 'Đơn vị', 'Trạng thái điểm danh', 'Thời điểm xác nhận', 'Ghi chú']"
            :hasActions="in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN'])"
            actionTitle="Điểm danh"
            :empty="$meeting->thamDu->isEmpty()"
            emptyTitle="Chưa có thành viên nào"
            emptyMessage="Danh sách mời thành viên đang trống."
        >
            @foreach($meeting->thamDu as $attendee)
                @php
                    $mUser = $attendee->thanhVienHdt?->nguoiDung;
                    $mDept = $mUser?->donVi?->TenDonVi ?: 'Hội đồng trường';
                @endphp
                <tr>
                    <td style="font-weight: 600;">
                        {{ $mUser?->HoTen ?: 'Thành viên #' . $attendee->MaThanhVien }}
                    </td>
                    <td style="font-size: var(--font-size-xs);">
                        <span class="stu-badge stu-badge-info">
                            {{ $attendee->thanhVienHdt?->ChucDanh ?: 'Thành viên HĐT' }}
                        </span>
                    </td>
                    <td style="font-size: var(--font-size-xs);">{{ $mDept }}</td>
                    <td>
                        <x-status-badge :status="$attendee->TrangThaiThamDu" />
                    </td>
                    <td style="font-size: 11px; color: var(--color-text-muted); white-space: nowrap;">
                        {{ $attendee->ThoiDiemXacNhan ? date('H:i d/m/Y', strtotime($attendee->ThoiDiemXacNhan)) : 'Chưa điểm danh' }}
                    </td>
                    <td style="font-size: var(--font-size-xs); color: var(--color-text-muted);">
                        {{ $attendee->GhiChu ?: '-' }}
                    </td>
                    @if(in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN']))
                        <td class="col-actions">
                            <form method="POST" action="{{ route('meetings.attendance', [$meeting->MaPhienHop, $attendee->MaThamDu]) }}" style="display: inline-flex; gap: 4px;">
                                @csrf
                                <select name="TrangThaiThamDu" onchange="this.form.submit()" class="stu-select" style="padding: 3px 8px; font-size: 11px; width: auto;">
                                    <option value="Có mặt" {{ $attendee->TrangThaiThamDu == 'Có mặt' ? 'selected' : '' }}>Có mặt</option>
                                    <option value="Vắng" {{ $attendee->TrangThaiThamDu == 'Vắng' ? 'selected' : '' }}>Vắng</option>
                                    <option value="Có xin phép" {{ $attendee->TrangThaiThamDu == 'Có xin phép' ? 'selected' : '' }}>Có xin phép</option>
                                    <option value="Không xin phép" {{ $attendee->TrangThaiThamDu == 'Không xin phép' ? 'selected' : '' }}>Không xin phép</option>
                                </select>
                            </form>
                        </td>
                    @endif
                </tr>
            @endforeach
        </x-table>
    </div>

    <!-- ==================== TAB 3: KHÁCH MỜI (BR-03) ==================== -->
    <div class="stu-tab-pane" id="pane-guests">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--space-4);">
            <div>
                <h3 style="font-size: var(--font-size-base); font-weight: 700; color: var(--color-primary); margin-bottom: 2px;">
                    Danh sách Khách mời tham gia Phiên họp
                </h3>
                <p style="font-size: var(--font-size-xs); color: var(--color-text-muted);">
                    Tuân thủ <strong>BR-03</strong>: Phân tách rõ ràng giữa khách mời và thành viên chính thức, chống trùng lặp.
                </p>
            </div>
            @if(in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN']))
                <x-button variant="primary" size="sm" data-modal-open="addGuestModal">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    <span>Mời khách mới</span>
                </x-button>
            @endif
        </div>

        <x-table 
            :headers="['Họ và tên khách mời', 'Cơ quan / Đơn vị', 'Chức vụ', 'Nội dung mời tham gia', 'Trạng thái']"
            :hasActions="false"
            :empty="$meeting->khachMoiPhienHop->isEmpty()"
            emptyTitle="Chưa có khách mời nào"
            emptyMessage="Phiên họp này hiện chưa gửi giấy mời cho khách ngoài Hội đồng trường."
        >
            @foreach($meeting->khachMoiPhienHop as $invite)
                @php $guest = $invite->khachMoi; @endphp
                <tr>
                    <td style="font-weight: 600;">
                        <div>{{ $guest?->HoTen ?: 'Khách mời' }}</div>
                        @if($guest?->Email)
                            <div style="font-size: 11px; color: var(--color-text-muted);">{{ $guest->Email }}</div>
                        @endif
                    </td>
                    <td style="font-size: var(--font-size-xs);">{{ $guest?->DonViCongTac ?: '-' }}</td>
                    <td style="font-size: var(--font-size-xs);">{{ $guest?->ChucVu ?: '-' }}</td>
                    <td class="stu-max-w-xs" style="font-size: var(--font-size-xs);">
                        <div class="stu-truncate" title="{{ $invite->NoiDungMoi }}">{{ $invite->NoiDungMoi }}</div>
                    </td>
                    <td>
                        <x-status-badge :status="$invite->TrangThaiThamDu" />
                    </td>
                </tr>
            @endforeach
        </x-table>
    </div>

    <!-- ==================== TAB 4: CHƯƠNG TRÌNH HỌP (BR-08) ==================== -->
    <div class="stu-tab-pane" id="pane-agenda">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--space-4);">
            <div>
                <h3 style="font-size: var(--font-size-base); font-weight: 700; color: var(--color-primary); margin-bottom: 2px;">
                    Chương trình họp & Thứ tự xem xét Hồ sơ trình
                </h3>
                <p style="font-size: var(--font-size-xs); color: var(--color-text-muted);">
                    Tuân thủ <strong>BR-08</strong>: Liên kết hồ sơ trình vào phiên họp theo thứ tự xem xét được sắp xếp rõ ràng.
                </p>
            </div>
            @if(in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN']) && $availableDossiers->isNotEmpty())
                <x-button variant="primary" size="sm" data-modal-open="addAgendaModal">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    <span>Đưa hồ sơ vào chương trình</span>
                </x-button>
            @endif
        </div>

        <x-table 
            :headers="['Thứ tự', 'Hồ sơ trình xem xét', 'Đơn vị trình', 'Lĩnh vực', 'Trạng thái xử lý', 'Ghi chú']"
            :hasActions="true"
            actionTitle="Chi tiết"
            :empty="$meeting->chuongTrinhHop->isEmpty()"
            emptyTitle="Chương trình họp đang trống"
            emptyMessage="Chưa có hồ sơ trình nào được đưa vào chương trình nghị sự của phiên họp này."
        >
            @foreach($meeting->chuongTrinhHop->sortBy('ThuTu') as $agenda)
                @php $dossier = $agenda->hoSoTrinh; @endphp
                <tr>
                    <td style="font-weight: 700; color: var(--color-primary); text-align: center; width: 60px;">
                        #{{ $agenda->ThuTu }}
                    </td>
                    <td class="stu-max-w-xs">
                        <a href="{{ route('submissions.show', $dossier?->MaHoSo) }}" style="font-weight: 600; color: var(--color-primary);" title="{{ $dossier?->TieuDe }}">
                            {{ $dossier?->TieuDe ?: 'Hồ sơ #' . $agenda->MaHoSo }}
                        </a>
                        <div style="font-size: 11px; color: var(--color-text-muted);">Mã: {{ $dossier?->ma_ho_so_hien_thi ?? ('#HS-' . $agenda->MaHoSo) }}</div>
                    </td>
                    <td style="font-size: var(--font-size-xs);">{{ $dossier?->donVi?->TenDonVi ?: '-' }}</td>
                    <td style="font-size: var(--font-size-xs);">
                        <span class="stu-badge stu-badge-info">{{ $dossier?->LinhVuc ?: '-' }}</span>
                    </td>
                    <td>
                        <x-status-badge :status="$agenda->TrangThai" />
                    </td>
                    <td style="font-size: var(--font-size-xs); color: var(--color-text-muted);">
                        {{ $agenda->GhiChu ?: '-' }}
                    </td>
                    <td class="col-actions">
                        <div class="stu-table-actions-group">
                            @if(in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN']))
                                <x-button variant="secondary" size="sm" data-modal-open="editMeetingAgendaModal-{{ $agenda->MaChuongTrinh }}">
                                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    <span>Cập nhật</span>
                                </x-button>
                            @endif
                            <x-button variant="outline" size="sm" href="{{ route('submissions.show', $dossier?->MaHoSo) }}">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span>Xem hồ sơ</span>
                            </x-button>
                            @if(in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN']))
                                <form method="POST" action="{{ route('agendas.destroy', $agenda->MaChuongTrinh) }}" onsubmit="return confirm('Xác nhận ẩn mục chương trình họp này khỏi giao diện?');">
                                    @csrf
                                    @method('DELETE')
                                    <x-button type="submit" variant="danger" size="sm">
                                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        <span>Xóa</span>
                                    </x-button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-table>
    </div>

    <!-- ==================== TAB 5: TÀI LIỆU PHIÊN HỌP (BR-05) ==================== -->
    <div class="stu-tab-pane" id="pane-docs">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--space-4);">
            <div>
                <h3 style="font-size: var(--font-size-base); font-weight: 700; color: var(--color-primary); margin-bottom: 2px;">
                    Kho Tài liệu Phiên họp (Độc lập với Hồ sơ trình)
                </h3>
                <p style="font-size: var(--font-size-xs); color: var(--color-text-muted);">
                    Tuân thủ <strong>BR-05</strong>: Tài liệu phiên họp lưu tại bảng liên kết riêng `tai_lieu_phien_hop`, phục vụ kịch bản, dự thảo kết luận.
                </p>
            </div>
            @if(in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN']))
                <x-button variant="primary" size="sm" data-modal-open="uploadDocModal">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    <span>Tải lên tài liệu họp</span>
                </x-button>
            @endif
        </div>

        <x-table 
            :headers="['Tên tài liệu', 'Phân loại', 'Định dạng / Dung lượng', 'Ngày tải lên']"
            :hasActions="true"
            actionTitle="Tải xuống"
            :empty="$meeting->taiLieu->isEmpty()"
            emptyTitle="Chưa có tài liệu đính kèm"
            emptyMessage="Chưa có văn bản hay kịch bản họp nào được đính kèm vào phiên này."
        >
            @foreach($meeting->taiLieu as $doc)
                <tr>
                    <td style="font-weight: 600;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color: var(--color-primary); flex-shrink: 0;"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            <span>{{ $doc->TenTaiLieu }}</span>
                        </div>
                    </td>
                    <td style="font-size: var(--font-size-xs);">
                        <span class="stu-badge stu-badge-info">{{ $doc->LoaiTaiLieu }}</span>
                    </td>
                    <td style="font-size: var(--font-size-xs); color: var(--color-text-muted);">
                        {{ strtoupper($doc->DinhDang) }} ({{ round(($doc->DungLuong ?? 0) / 1024, 1) }} KB)
                    </td>
                    <td style="font-size: 11px; color: var(--color-text-muted); white-space: nowrap;">
                        {{ $doc->NgayTaiLen ? date('d/m/Y H:i', strtotime($doc->NgayTaiLen)) : '' }}
                    </td>
                    <td class="col-actions">
                        <x-button variant="outline" size="sm" href="{{ asset($doc->DuongDan) }}" target="_blank">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span>Tải về</span>
                        </x-button>
                    </td>
                </tr>
            @endforeach
        </x-table>
    </div>

    <!-- ==================== TAB 6: NỘI DUNG BIỂU QUYẾT (BR-09 BẢO MẬT BỎ PHIẾU KÍN) ==================== -->
    <div class="stu-tab-pane" id="pane-voting">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--space-4);">
            <div>
                <h3 style="font-size: var(--font-size-base); font-weight: 700; color: var(--color-primary); margin-bottom: 2px;">
                    Nội dung Biểu quyết &amp; Kiểm phiếu Bảo mật (BR-09)
                </h3>
                <p style="font-size: var(--font-size-xs); color: var(--color-text-muted);">
                    Bảo vệ tuyệt đối <strong>nguyên tắc bỏ phiếu kín</strong>: Chỉ hiển thị tổng kết quả kiểm phiếu, tỷ lệ % tán thành; không lưu hoặc hiển thị bất kỳ mối liên hệ nào giữa danh tính người bỏ phiếu và lựa chọn cụ thể.
                </p>
            </div>
            @if(in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN']) && $meeting->chuongTrinhHop->isNotEmpty())
                <x-button variant="primary" size="sm" data-modal-open="addVotingModal">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    <span>Tạo nội dung biểu quyết</span>
                </x-button>
            @endif
        </div>

        @if($meeting->noiDungBieuQuyet->isEmpty())
            <div class="stu-card" style="padding: var(--space-10); text-align: center;">
                <p style="color: var(--color-text-muted); font-size: var(--font-size-sm);">
                    Phiên họp này chưa khởi tạo nội dung biểu quyết nào.
                </p>
            </div>
        @else
            <div style="display: flex; flex-direction: column; gap: var(--space-4);">
                @foreach($meeting->noiDungBieuQuyet as $vote)
                    @php $summary = $votingSummaries[$vote->MaNoiDung] ?? ['total' => 0, 'agree' => 0, 'disagree' => 0, 'abstain' => 0, 'agree_percent' => 0]; @endphp
                    <div class="stu-card" style="padding: var(--space-6);">
                        <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: var(--space-4); margin-bottom: var(--space-4);">
                            <div>
                                <div style="display: flex; align-items: center; gap: var(--space-2); margin-bottom: var(--space-1);">
                                    <span style="font-size: 11px; font-weight: 700; color: var(--color-primary);">CÂU HỎI BIỂU QUYẾT #{{ $vote->MaNoiDung }}</span>
                                    <x-status-badge :status="$vote->TrangThai" />
                                </div>
                                <h4 style="font-size: var(--font-size-base); font-weight: 700; color: var(--color-text-main);">
                                    {{ $vote->NoiDung }}
                                </h4>
                            </div>
                            <span class="stu-badge stu-badge-success" style="font-size: var(--font-size-sm); padding: 4px 12px;">
                                {{ $summary['agree_percent'] }}% Tán thành
                            </span>
                        </div>

                        <!-- Tiến độ biểu quyết tổng hợp -->
                        <div style="margin-bottom: var(--space-4);">
                            <div style="height: 10px; width: 100%; display: flex; border-radius: var(--radius-full); overflow: hidden; background: var(--color-bg-subtle);">
                                @if($summary['total'] > 0)
                                    <div style="width: {{ ($summary['agree'] / $summary['total']) * 100 }}%; background-color: var(--color-success);" title="Tán thành: {{ $summary['agree'] }}"></div>
                                    <div style="width: {{ ($summary['disagree'] / $summary['total']) * 100 }}%; background-color: var(--color-danger);" title="Không tán thành: {{ $summary['disagree'] }}"></div>
                                    <div style="width: {{ ($summary['abstain'] / $summary['total']) * 100 }}%; background-color: var(--color-warning);" title="Không ý kiến: {{ $summary['abstain'] }}"></div>
                                @else
                                    <div style="width: 100%; background-color: var(--color-border);"></div>
                                @endif
                            </div>
                        </div>

                        <!-- Kết quả kiểm phiếu 3 phương án bảo mật -->
                        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: var(--space-4); background: var(--color-bg-subtle); padding: var(--space-3); border-radius: var(--radius-md); font-size: var(--font-size-xs); text-align: center;">
                            <div>
                                <span style="color: var(--color-text-muted);">Tổng số phiếu:</span>
                                <div><strong>{{ $summary['total'] }}</strong></div>
                            </div>
                            <div>
                                <span style="color: var(--color-success);">Tán thành:</span>
                                <div><strong>{{ $summary['agree'] }}</strong> ({{ $summary['agree_percent'] }}%)</div>
                            </div>
                            <div>
                                <span style="color: var(--color-danger);">Không tán thành:</span>
                                <div><strong>{{ $summary['disagree'] }}</strong></div>
                            </div>
                            <div>
                                <span style="color: var(--color-warning);">Không ý kiến:</span>
                                <div><strong>{{ $summary['abstain'] }}</strong></div>
                            </div>
                        </div>

                        <div style="margin-top: var(--space-3); display: flex; justify-content: flex-end;">
                            <a href="{{ route('votes.show', $vote->MaNoiDung) }}" class="stu-btn stu-btn-outline stu-btn-sm">
                                <span>Mở phòng bỏ phiếu &amp; Chi tiết kiểm phiếu &rarr;</span>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- ==================== TAB 7: NGHỊ QUYẾT LIÊN QUAN ==================== -->
    <div class="stu-tab-pane" id="pane-resolutions">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--space-4);">
            <h3 style="font-size: var(--font-size-base); font-weight: 700; color: var(--color-primary); margin-bottom: 2px;">
                Nghị quyết được ban hành từ Phiên họp này
            </h3>
        </div>

        <x-table 
            :headers="['Số hiệu nghị quyết', 'Trích yếu nội dung', 'Ngày ban hành', 'Trạng thái hiệu lực']"
            :hasActions="true"
            actionTitle="Thao tác"
            :empty="$resolutions->isEmpty()"
            emptyTitle="Chưa có nghị quyết nào"
            emptyMessage="Phiên họp này chưa ban hành nghị quyết chính thức nào."
        >
            @foreach($resolutions as $res)
                <tr>
                    <td style="font-weight: 700; color: var(--color-primary); white-space: nowrap;">
                        {{ $res->SoNghiQuyet }}
                    </td>
                    <td class="stu-max-w-md">
                        <div style="font-weight: 600;">{{ $res->TrichYeu }}</div>
                    </td>
                    <td style="white-space: nowrap; font-size: var(--font-size-xs);">
                        {{ $res->NgayBanHanh ? $res->NgayBanHanh->format('d/m/Y') : '' }}
                    </td>
                    <td>
                        <x-status-badge :status="$res->TrangThai" />
                    </td>
                    <td class="col-actions">
                        <x-button variant="outline" size="sm" href="{{ url('/resolutions/' . $res->MaNghiQuyet) }}">
                            <span>Xem văn bản</span>
                        </x-button>
                    </td>
                </tr>
            @endforeach
        </x-table>
    </div>
</x-tabs>

<!-- ==================== CÁC MODAL THAO TÁC ==================== -->

<!-- Modal 1: Mời thêm thành viên (BR-02) -->
<x-modal id="addMemberModal" title="Mời Thành viên Hội đồng tham dự" size="md">
    <form method="POST" action="{{ route('meetings.members.add', $meeting->MaPhienHop) }}" id="addMemberForm">
        @csrf
        <x-form.select name="MaThanhVien" label="Chọn thành viên Hội đồng" required hint="Danh sách tự động loại bỏ các thành viên đã có trong phiên (BR-02)">
            @foreach($availableMembers as $m)
                <option value="{{ $m->MaThanhVien }}">
                    {{ $m->nguoiDung?->HoTen }} ({{ $m->ChucDanh }})
                </option>
            @endforeach
        </x-form.select>

        <x-form.select name="TrangThaiThamDu" label="Trạng thái mời ban đầu" required>
            <option value="Có mặt">Có mặt</option>
            <option value="Vắng">Vắng</option>
            <option value="Có xin phép">Có xin phép</option>
        </x-form.select>

        <x-form.textarea name="GhiChu" label="Ghi chú" rows="2" placeholder="Nhập ghi chú (nếu có)..." />

        <div class="stu-flex stu-justify-end stu-gap-3" style="margin-top: var(--space-4);">
            <x-button variant="secondary" data-modal-close>Đóng</x-button>
            <x-button type="submit" variant="primary">Thêm vào phiên họp</x-button>
        </div>
    </form>
</x-modal>

<!-- Modal 2: Mời khách tham gia (BR-03) -->
<x-modal id="addGuestModal" title="Mời Khách tham dự Phiên họp" size="md">
    <form method="POST" action="{{ route('meetings.guests.add', $meeting->MaPhienHop) }}">
        @csrf
        <x-form.input name="HoTen" label="Họ và tên khách mời" placeholder="Ví dụ: GS. TS. Nguyễn Văn Chuyên Gia" required />
        <div class="stu-grid stu-grid-cols-2">
            <x-form.input name="DonViCongTac" label="Cơ quan / Đơn vị" placeholder="Đại học Quốc gia..." />
            <x-form.input name="ChucVu" label="Chức vụ" placeholder="Trưởng ban..." />
        </div>
        <x-form.input type="email" name="Email" label="Email liên hệ" placeholder="expert@domain.com" />
        <x-form.textarea name="NoiDungMoi" label="Nội dung mời tham gia" placeholder="Tham gia đóng góp ý kiến về đề án..." required rows="2" />
        <x-form.select name="TrangThaiThamDu" label="Trạng thái tham dự" required>
            <option value="Xác nhận">Đã gửi giấy mời / Xác nhận</option>
            <option value="Có mặt">Có mặt</option>
            <option value="Vắng">Vắng</option>
        </x-form.select>

        <div class="stu-flex stu-justify-end stu-gap-3" style="margin-top: var(--space-4);">
            <x-button variant="secondary" data-modal-close>Đóng</x-button>
            <x-button type="submit" variant="primary">Xác nhận mời khách</x-button>
        </div>
    </form>
</x-modal>

<!-- Modal 3: Đưa hồ sơ vào chương trình họp (BR-08) -->
<x-modal id="addAgendaModal" title="Đưa Hồ Sơ Trình vào Chương Trình Họp" size="md">
    <form method="POST" action="{{ route('meetings.agenda.add', $meeting->MaPhienHop) }}">
        @csrf
        <x-form.select name="MaHoSo" label="Chọn hồ sơ trình" required hint="Danh sách hiển thị các hồ sơ trình chưa được đưa vào chương trình họp này (BR-08)">
            @foreach($availableDossiers as $d)
                <option value="{{ $d->MaHoSo }}">
                    {{ $d->ma_ho_so_hien_thi }}: {{ $d->TieuDe }} ({{ $d->donVi?->TenDonVi }})
                </option>
            @endforeach
        </x-form.select>

        <x-form.textarea name="GhiChu" label="Ghi chú xem xét" rows="2" placeholder="Ghi chú về nội dung trình bày..." />

        <div class="stu-flex stu-justify-end stu-gap-3" style="margin-top: var(--space-4);">
            <x-button variant="secondary" data-modal-close>Đóng</x-button>
            <x-button type="submit" variant="primary">Đưa vào chương trình</x-button>
        </div>
    </form>
</x-modal>

<!-- Modal 4: Tải lên tài liệu họp (BR-05) -->
<x-modal id="uploadDocModal" title="Tải Lên Tài Liệu Phiên Họp" size="md">
    <form method="POST" action="{{ route('meetings.documents.upload', $meeting->MaPhienHop) }}" enctype="multipart/form-data">
        @csrf
        <x-form.input name="TenTaiLieu" label="Tên tài liệu" placeholder="Ví dụ: Kịch bản điều hành phiên họp thứ 5" required />
        <x-form.select name="LoaiTaiLieu" label="Phân loại tài liệu" required>
            <option value="Kịch bản họp">Kịch bản họp</option>
            <option value="Dự thảo kết luận">Dự thảo kết luận</option>
            <option value="Tài liệu chung">Tài liệu chung</option>
            <option value="Biên bản họp">Biên bản họp</option>
        </x-form.select>
        
        <div class="stu-form-group">
            <x-form.label required>Tập tin đính kèm (PDF, Word, Excel, PPT - Max 20MB)</x-form.label>
            <input type="file" name="file" class="stu-input" required accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx">
        </div>

        <div class="stu-flex stu-justify-end stu-gap-3" style="margin-top: var(--space-4);">
            <x-button variant="secondary" data-modal-close>Đóng</x-button>
            <x-button type="submit" variant="primary">Tải lên</x-button>
        </div>
    </form>
</x-modal>

<!-- Modal 5: Khởi tạo nội dung biểu quyết cho phiên họp -->
<x-modal id="addVotingModal" title="Khởi Tạo Nội Dung Biểu Quyết" size="md">
    <form method="POST" action="{{ route('votes.store') }}">
        @csrf
        <x-form.select name="MaChuongTrinh" label="Mục chương trình họp liên kết" required>
            <option value="">-- Chọn mục chương trình họp --</option>
            @foreach($meeting->chuongTrinhHop->sortBy('ThuTu') as $agendaItem)
                <option value="{{ $agendaItem->MaChuongTrinh }}">
                    Mục {{ $agendaItem->ThuTu }} ({{ $agendaItem->hoSoTrinh?->ma_ho_so_hien_thi ?? ('#HS-' . $agendaItem->MaHoSo) }}): {{ $agendaItem->hoSoTrinh?->TieuDe ?? 'Nội dung thảo luận' }}
                </option>
            @endforeach
        </x-form.select>

        <x-form.input name="NoiDung" label="Câu hỏi / Nội dung biểu quyết" placeholder="Ví dụ: Thông qua Đề án thành lập Viện Nghiên cứu Ứng dụng AI STU" required />

        <div class="stu-flex stu-justify-end stu-gap-3" style="margin-top: var(--space-4);">
            <x-button variant="secondary" data-modal-close>Đóng</x-button>
            <x-button type="submit" variant="primary">Khởi tạo biểu quyết</x-button>
        </div>
    </form>
</x-modal>

<!-- Modal 6: Cập nhật từng mục chương trình họp (Thứ tự, Trạng thái, Ghi chú) -->
@if(in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN']))
    @foreach($meeting->chuongTrinhHop->sortBy('ThuTu') as $agenda)
        <x-modal id="editMeetingAgendaModal-{{ $agenda->MaChuongTrinh }}" title="Cập Nhật Mục Chương Trình Họp #{{ $agenda->ThuTu }}" size="md">
            <form method="POST" action="{{ route('agendas.update', $agenda->MaChuongTrinh) }}">
                @csrf
                @method('PUT')

                <div style="background: var(--color-bg-subtle); padding: 12px 14px; border-radius: var(--radius-md); margin-bottom: var(--space-4); font-size: var(--font-size-xs);">
                    <strong style="color: var(--color-primary);">Hồ sơ trình:</strong> {{ $agenda->hoSoTrinh?->TieuDe ?? ('Hồ sơ #' . $agenda->MaHoSo) }}
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-4);">
                    <x-form.input type="number" name="ThuTu" label="Thứ tự xem xét" :value="$agenda->ThuTu" required />

                    <x-form.select name="TrangThai" label="Trạng thái xử lý" required>
                        @foreach(['Chưa xem xét', 'Đang thảo luận', 'Đã thông qua', 'Tạm hoãn'] as $st)
                            <option value="{{ $st }}" {{ $agenda->TrangThai === $st ? 'selected' : '' }}>{{ $st }}</option>
                        @endforeach
                    </x-form.select>
                </div>

                <x-form.textarea name="GhiChu" label="Ghi chú kịch bản / Kết luận thảo luận" :value="$agenda->GhiChu" rows="3" />

                <div class="stu-flex stu-justify-end stu-gap-3" style="margin-top: var(--space-4);">
                    <x-button variant="secondary" data-modal-close>Đóng</x-button>
                    <x-button type="submit" variant="primary">Lưu cập nhật</x-button>
                </div>
            </form>
        </x-modal>
    @endforeach
@endif
@endsection
