@extends('layouts.chinh')

@section('title', 'Chi tiết Hồ sơ trình ' . $dossier->ma_ho_so_hien_thi)

@section('content')
<div class="stu-page-container">
    {{-- Breadcrumb --}}
    <x-thanh-dieu-huong :items="['Hồ sơ trình' => route('submissions.index'), $dossier->ma_ho_so_hien_thi => null]" />

    {{-- Header --}}
    <div class="stu-page-header stu-flex stu-items-start stu-justify-between stu-mb-6" style="gap: 16px;">
        <div style="flex: 1;">
            <div class="stu-flex stu-items-center stu-gap-2 stu-mb-2">
                <span style="font-weight: 800; color: var(--color-primary); font-size: 14px; background: rgba(0, 43, 91, 0.08); padding: 3px 8px; border-radius: var(--radius-sm);">
                    {{ $dossier->ma_ho_so_hien_thi }}
                </span>
                <span class="stu-badge stu-badge-light">{{ $dossier->LinhVuc ?? 'Tổng hợp' }}</span>
                <x-nhan-trang-thai :status="$dossier->TrangThai" />
            </div>
            <h1 class="stu-page-title" style="font-size: 22px; font-weight: 800; color: var(--color-primary); margin: 0; line-height: 1.4;">
                {{ $dossier->TieuDe }}
            </h1>
        </div>
        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            @if(in_array($role, ['SECRETARY', 'CHAIR', 'ADMIN']))
                <button type="button" class="stu-btn stu-btn-success stu-btn-sm" data-modal-open="reviewSubmissionModal" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 600;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Kiểm tra hồ sơ vào phiên họp</span>
                </button>
                <button type="button" class="stu-btn stu-btn-primary stu-btn-sm" data-modal-open="addToMeetingModal" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 600;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Đưa vào phiên họp</span>
                </button>
            @endif
            @if(in_array($role, ['UNIT_REP', 'SECRETARY', 'CHAIR', 'ADMIN']))
                <button type="button" class="stu-btn stu-btn-outline stu-btn-sm" data-modal-open="editSubmissionModal" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 600;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Chỉnh sửa hồ sơ</span>
                </button>
                <form method="POST" action="{{ route('submissions.destroy', $dossier->MaHoSo) }}" style="display: inline-flex; margin: 0;" onsubmit="return confirm('Bạn có chắc chắn muốn ẩn hồ sơ trình {{ $dossier->ma_ho_so_hien_thi }} khỏi danh sách hiển thị? (Dữ liệu vẫn được bảo toàn trong hệ thống)');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="stu-btn stu-btn-outline stu-btn-sm" style="color: var(--color-text-secondary);" title="Ẩn hồ sơ trình (Bảo toàn dữ liệu)">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                        <span>Ẩn</span>
                    </button>
                </form>
            @endif
            <a href="{{ route('submissions.index') }}" class="stu-btn stu-btn-outline stu-btn-sm">
                Quay lại danh sách
            </a>
        </div>
    </div>

    <div class="stu-flex stu-flex-col stu-gap-6">
        {{-- 1. Thông tin thuộc tính trong bảng ho_so_trinh --}}
        <div class="stu-card" style="padding: 24px;">
            <h3 style="font-size: 16px; font-weight: 700; color: var(--color-primary); margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px;">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Thông tin chi tiết Hồ sơ trình</span>
            </h3>

            <table class="stu-table" style="font-size: var(--font-size-sm);">
                <tr>
                    <td style="width: 180px; color: var(--color-text-muted); font-weight: 600;">Mã hồ sơ:</td>
                    <td><strong style="color: var(--color-primary);">{{ $dossier->ma_ho_so_hien_thi }}</strong></td>
                </tr>
                <tr>
                    <td style="color: var(--color-text-muted); font-weight: 600;">Tên hồ sơ trình:</td>
                    <td><strong>{{ $dossier->TieuDe }}</strong></td>
                </tr>
                <tr>
                    <td style="color: var(--color-text-muted); font-weight: 600;">Đơn vị trình:</td>
                    <td>{{ $dossier->donVi?->TenDonVi ?? 'Chưa xác định' }}</td>
                </tr>
                <tr>
                    <td style="color: var(--color-text-muted); font-weight: 600;">Lĩnh vực:</td>
                    <td><span class="stu-badge stu-badge-info">{{ $dossier->LinhVuc ?? 'Tổng hợp' }}</span></td>
                </tr>
                <tr>
                    <td style="color: var(--color-text-muted); font-weight: 600;">Ngày trình:</td>
                    <td>{{ $dossier->NgayTrinh ? \Carbon\Carbon::parse($dossier->NgayTrinh)->format('d/m/Y') : '—' }}</td>
                </tr>
                <tr>
                    <td style="color: var(--color-text-muted); font-weight: 600;">Người tạo:</td>
                    <td>{{ $dossier->nguoiDung?->HoTen ?? 'Hệ thống' }} <span style="font-size: 11px; color: var(--color-text-muted);">(ID: #{{ $dossier->MaNguoiDung }})</span></td>
                </tr>
                <tr>
                    <td style="color: var(--color-text-muted); font-weight: 600;">Trạng thái:</td>
                    <td><x-nhan-trang-thai :status="$dossier->TrangThai" /></td>
                </tr>
                <tr>
                    <td style="color: var(--color-text-muted); font-weight: 600;">Nội dung tóm tắt:</td>
                    <td style="line-height: 1.6; white-space: pre-line;">{{ $dossier->NoiDungTomTat ?: '—' }}</td>
                </tr>
                <tr>
                    <td style="color: var(--color-text-muted); font-weight: 600;">Ghi chú:</td>
                    <td style="line-height: 1.6; white-space: pre-line;">{{ $dossier->GhiChu ?: '—' }}</td>
                </tr>
            </table>
        </div>

        {{-- 2. File hồ sơ đính kèm --}}
        <div class="stu-card" style="padding: 24px;">
            <h3 style="font-size: 16px; font-weight: 700; color: var(--color-primary); margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px;">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                <span>File hồ sơ đính kèm ({{ $dossier->taiLieu->count() }})</span>
            </h3>

            @if($dossier->taiLieu->count() > 0)
                <div class="stu-table-container">
                    <table class="stu-table">
                        <thead>
                            <tr>
                                <th>Tên tài liệu</th>
                                <th>Loại tài liệu</th>
                                <th>Định dạng</th>
                                <th>Dung lượng</th>
                                <th>Ngày tải lên</th>
                                <th class="col-actions">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dossier->taiLieu as $doc)
                                <tr>
                                    <td style="font-weight: 600; color: var(--color-text-primary);">
                                        <a href="javascript:void(0)" 
                                           class="btn-view-doc" 
                                           data-doc-id="{{ $doc->MaTaiLieu }}" 
                                           data-doc-title="{{ $doc->TenTaiLieu }}" 
                                           data-doc-ext="{{ strtolower($doc->DinhDang ?? pathinfo($doc->DuongDan, PATHINFO_EXTENSION)) }}" 
                                           data-doc-url="{{ asset($doc->DuongDan) }}" 
                                           data-view-url="{{ route('documents.view', $doc->MaTaiLieu) }}"
                                           data-preview-url="{{ route('documents.preview', $doc->MaTaiLieu) }}"
                                           data-download-url="{{ route('documents.download', $doc->MaTaiLieu) }}"
                                           style="color: var(--color-primary); text-decoration: none; cursor: pointer;">
                                            {{ $doc->TenTaiLieu }}
                                        </a>
                                    </td>
                                    <td style="font-size: 12px;">{{ $doc->LoaiTaiLieu ?? 'Tờ trình đính kèm' }}</td>
                                    <td style="font-size: 12px; text-transform: uppercase;">
                                        <span class="stu-badge stu-badge-light">{{ $doc->DinhDang ?? 'FILE' }}</span>
                                    </td>
                                    <td style="font-size: 12px; color: var(--color-text-muted);">
                                        {{ $doc->DungLuong ? number_format($doc->DungLuong / 1024, 1) . ' KB' : '—' }}
                                    </td>
                                    <td style="font-size: 12px; color: var(--color-text-muted);">
                                        {{ $doc->NgayTaiLen ? \Carbon\Carbon::parse($doc->NgayTaiLen)->format('d/m/Y H:i') : '—' }}
                                    </td>
                                    <td class="col-actions">
                                        <div class="stu-table-actions-group">
                                            <button type="button" 
                                                    class="stu-btn stu-btn-outline stu-btn-sm btn-view-doc" 
                                                    data-doc-id="{{ $doc->MaTaiLieu }}" 
                                                    data-doc-title="{{ $doc->TenTaiLieu }}" 
                                                    data-doc-ext="{{ strtolower($doc->DinhDang ?? pathinfo($doc->DuongDan, PATHINFO_EXTENSION)) }}" 
                                                    data-doc-url="{{ asset($doc->DuongDan) }}" 
                                                    data-view-url="{{ route('documents.view', $doc->MaTaiLieu) }}"
                                                    data-preview-url="{{ route('documents.preview', $doc->MaTaiLieu) }}"
                                                    data-download-url="{{ route('documents.download', $doc->MaTaiLieu) }}"
                                                    title="Xem trực tiếp trong trình duyệt không cần tải về">
                                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                <span>Xem file</span>
                                            </button>
                                            <a href="{{ route('documents.download', $doc->MaTaiLieu) }}" class="stu-btn stu-btn-secondary stu-btn-sm" title="Tải về máy tính">
                                                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                                <span>Tải về</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div style="text-align: center; padding: 32px; background: var(--color-bg-muted); border-radius: var(--radius-md); color: var(--color-text-muted); font-size: 13px;">
                    Chưa có file tài liệu đính kèm nào trong hồ sơ này.
                </div>
            @endif
        </div>

        {{-- 3. Thông tin phiên họp & Chương trình nghị sự xem xét hồ sơ (BR-08) --}}
        <div class="stu-card" style="padding: 24px;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
                <h3 style="font-size: 16px; font-weight: 700; color: var(--color-primary); margin: 0; display: flex; align-items: center; gap: 8px;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Phiên họp &amp; Chương trình nghị sự xem xét hồ sơ (BR-08)</span>
                </h3>
                @if(in_array($role, ['SECRETARY', 'CHAIR', 'ADMIN']))
                    <button type="button" class="stu-btn stu-btn-primary stu-btn-sm" data-modal-open="addToMeetingModal" style="display: inline-flex; align-items: center; gap: 6px;">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        <span>Đưa hồ sơ vào phiên họp</span>
                    </button>
                @endif
            </div>

            @if($dossier->chuongTrinhHop->isNotEmpty())
                <div class="stu-table-container">
                    <table class="stu-table">
                        <thead>
                            <tr>
                                <th style="width: 80px; text-align: center;">Thứ tự</th>
                                <th>Phiên họp Hội đồng trường</th>
                                <th>Thời gian họp</th>
                                <th>Địa điểm / Hình thức</th>
                                <th>Trạng thái xem xét</th>
                                <th>Ghi chú</th>
                                <th class="col-actions">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dossier->chuongTrinhHop as $agenda)
                                <tr>
                                    <td style="font-weight: 700; color: var(--color-primary); text-align: center;">
                                        #{{ $agenda->ThuTu }}
                                    </td>
                                    <td>
                                        <a href="{{ route('meetings.show', $agenda->MaPhienHop) }}" style="font-weight: 700; color: var(--color-primary); text-decoration: none;">
                                            {{ $agenda->phienHop?->TenPhienHop ?: ('Phiên họp #' . $agenda->MaPhienHop) }}
                                        </a>
                                    </td>
                                    <td style="font-size: 13px;">
                                        {{ $agenda->phienHop?->NgayHop ? \Carbon\Carbon::parse($agenda->phienHop->NgayHop)->format('d/m/Y') : '—' }}
                                        @if($agenda->phienHop?->ThoiGianBatDau)
                                            <div style="font-size: 11px; color: var(--color-text-muted);">
                                                {{ date('H:i', strtotime($agenda->phienHop->ThoiGianBatDau)) }}
                                                @if($agenda->phienHop?->ThoiGianKetThuc)
                                                    - {{ date('H:i', strtotime($agenda->phienHop->ThoiGianKetThuc)) }}
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td style="font-size: 13px;">
                                        <div>{{ $agenda->phienHop?->DiaDiem ?: 'Hội trường HĐT' }}</div>
                                        <span class="stu-badge stu-badge-light" style="font-size: 11px;">{{ $agenda->phienHop?->HinhThuc ?: 'Trực tiếp' }}</span>
                                    </td>
                                    <td>
                                        <x-nhan-trang-thai :status="$agenda->TrangThai" />
                                    </td>
                                    <td style="font-size: 12px; color: var(--color-text-secondary); max-width: 200px;">
                                        {{ $agenda->GhiChu ?: '—' }}
                                    </td>
                                    <td class="col-actions">
                                        <a href="{{ route('meetings.show', $agenda->MaPhienHop) }}#pane-agenda" class="stu-btn stu-btn-outline stu-btn-sm">
                                            Xem phiên họp
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div style="background: #f8fafc; border: 1.5px dashed #cbd5e1; border-radius: 8px; padding: 20px; text-align: center;">
                    <div style="font-size: 14px; font-weight: 600; color: #475569; margin-bottom: 6px;">
                        Hồ sơ này chưa được đưa vào chương trình nghị sự của phiên họp nào.
                    </div>
                    @if(in_array($dossier->TrangThai, ['Đủ điều kiện', 'Đầy đủ']))
                        <p style="font-size: 12.5px; color: var(--color-success); margin: 0 0 14px 0; font-weight: 500;">
                            ✓ Hồ sơ đã đạt thẩm định <strong>ĐỦ ĐIỀU KIỆN</strong>. Thư ký hoặc Chủ tịch có thể xếp lịch đưa vào phiên họp ngay bên dưới:
                        </p>
                        @if(in_array($role, ['SECRETARY', 'CHAIR', 'ADMIN']))
                            <button type="button" class="stu-btn stu-btn-primary stu-btn-sm" data-modal-open="addToMeetingModal" style="display: inline-flex; align-items: center; gap: 6px;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                <span>Đưa hồ sơ vào phiên họp ngay</span>
                            </button>
                        @endif
                    @else
                        <p style="font-size: 12px; color: var(--color-text-muted); margin: 0;">
                            Quy trình: Thư ký thẩm định hồ sơ đạt <strong>Đủ điều kiện</strong> qua nút "Kiểm tra hồ sơ vào phiên họp", sau đó xếp lịch nghị sự vào phiên họp Hội đồng trường.
                        </p>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

@if(in_array($role, ['UNIT_REP', 'SECRETARY', 'CHAIR', 'ADMIN']))
    {{-- Modal Chỉnh sửa Hồ sơ trình --}}
    <x-hop-thoai id="editSubmissionModal" title="Cập nhật Hồ sơ trình: {{ $dossier->ma_ho_so_hien_thi }}" size="lg">
        <form method="POST" action="{{ route('submissions.update', $dossier->MaHoSo) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="stu-grid stu-mb-4" style="grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="stu-form-group" style="margin-bottom: 0;">
                    <label class="stu-form-label" style="font-size: 13px; font-weight: 700;">Mã số hồ sơ</label>
                    <input type="text" value="{{ $dossier->ma_ho_so_hien_thi }}" class="stu-form-control" style="background-color: var(--color-bg-subtle); font-weight: 700; color: var(--color-primary);" readonly>
                </div>

                @if(in_array($role, ['SECRETARY', 'CHAIR', 'ADMIN']))
                    <div class="stu-form-group" style="margin-bottom: 0;">
                        <label class="stu-form-label" style="font-size: 13px; font-weight: 700;">Trạng thái hồ sơ</label>
                        @php $curStatus = old('TrangThai', $dossier->TrangThai); @endphp
                        <select name="TrangThai" class="stu-form-control">
                            @foreach(['Mới tạo', 'Đầy đủ', 'Chờ bổ sung', 'Đủ điều kiện'] as $st)
                                <option value="{{ $st }}" {{ $curStatus === $st ? 'selected' : '' }}>{{ $st }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>

            <div class="stu-form-group stu-mb-4">
                <label class="stu-form-label" style="font-size: 13px; font-weight: 700;">Tiêu đề hồ sơ trình <span style="color: var(--color-danger);">*</span></label>
                <input type="text" name="TieuDe" value="{{ old('TieuDe', $dossier->TieuDe) }}" class="stu-form-control" required minlength="5" maxlength="255">
            </div>

            <div class="stu-grid stu-mb-4" style="grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="stu-form-group" style="margin-bottom: 0;">
                    <label class="stu-form-label" style="font-size: 13px; font-weight: 700;">Đơn vị trình <span style="color: var(--color-danger);">*</span></label>
                    <select name="MaDonVi" class="stu-form-control" required {{ $role === 'UNIT_REP' ? 'readonly' : '' }}>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->MaDonVi }}" {{ old('MaDonVi', $dossier->MaDonVi) == $dept->MaDonVi ? 'selected' : '' }}>{{ $dept->TenDonVi }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="stu-form-group" style="margin-bottom: 0;">
                    <label class="stu-form-label" style="font-size: 13px; font-weight: 700;">Lĩnh vực <span style="color: var(--color-danger);">*</span></label>
                    @php
                        $currentField = old('LinhVuc', $dossier->LinhVuc);
                        $standardFields = [
                            'Đào tạo & Đảm bảo chất lượng',
                            'Khoa học công nghệ & Dự án',
                            'Cơ sở vật chất & Đầu tư xây dựng',
                            'Tài chính & Kế hoạch ngân sách',
                            'Tổ chức cán bộ & Nhân sự',
                            'Hợp tác quốc tế & Doanh nghiệp',
                            'Khác',
                        ];
                        if ($currentField && !in_array($currentField, $standardFields)) {
                            array_unshift($standardFields, $currentField);
                        }
                    @endphp
                    <select name="LinhVuc" class="stu-form-control" required>
                        @foreach($standardFields as $fieldItem)
                            <option value="{{ $fieldItem }}" {{ $currentField === $fieldItem ? 'selected' : '' }}>{{ $fieldItem }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="stu-form-group stu-mb-4">
                <label class="stu-form-label" style="font-size: 13px; font-weight: 700;">Nội dung tóm tắt <span style="color: var(--color-danger);">*</span></label>
                <textarea name="NoiDungTomTat" rows="4" class="stu-form-control" required minlength="5">{{ old('NoiDungTomTat', $dossier->NoiDungTomTat) }}</textarea>
            </div>

            <div class="stu-form-group stu-mb-4" style="background: var(--color-bg-muted); padding: 14px 16px; border-radius: var(--radius-md); border: 1px dashed var(--color-border);">
                <label class="stu-form-label" style="font-size: 12px; font-weight: 700;">Tải lên tài liệu đính kèm bổ sung (Tùy chọn)</label>
                <input 
                    type="file" 
                    name="file" 
                    id="submissionEditDocFile"
                    class="stu-form-control @error('file') is-invalid @enderror" 
                    accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx"
                >
                <div id="submissionEditDocError" class="stu-form-error" style="display: none; color: var(--color-danger, #ef4444); font-size: 12px; margin-top: 6px; font-weight: 500;"></div>
                <div style="font-size: 11.5px; color: var(--color-text-muted); margin-top: 4px;">
                    * Định dạng được phép: <strong>.pdf, .doc, .docx, .xls, .xlsx, .ppt, .pptx</strong> (Tối đa 20MB). Hệ thống sẽ cảnh báo lỗi nếu tải lên tệp khác định dạng.
                </div>
            </div>

            <div class="stu-form-group stu-mb-5">
                <label class="stu-form-label" style="font-size: 13px; font-weight: 700;">Ghi chú</label>
                <textarea name="GhiChu" rows="2" class="stu-form-control" placeholder="Ghi chú thêm...">{{ old('GhiChu', $dossier->GhiChu) }}</textarea>
            </div>

            <div class="stu-flex stu-items-center stu-justify-between" style="border-top: 1px solid var(--color-border); padding-top: 16px;">
                <button type="button" class="stu-btn stu-btn-ghost" data-modal-close>Hủy bỏ</button>
                <button type="submit" class="stu-btn stu-btn-primary" style="padding: 10px 24px;">Lưu cập nhật hồ sơ</button>
            </div>
        </form>
    </x-hop-thoai>
@endif

@if(in_array($role, ['SECRETARY', 'CHAIR', 'ADMIN']))
    {{-- Modal Kiểm tra và Thẩm định hồ sơ vào phiên họp --}}
    <x-hop-thoai id="reviewSubmissionModal" title="Kiểm Tra & Thẩm Định Hồ Sơ Vào Phiên Họp" size="md">
        <form method="POST" action="{{ route('submissions.review', $dossier->MaHoSo) }}">
            @csrf

            <div style="background: var(--color-bg-subtle); border: 1px solid var(--color-border); border-radius: 8px; padding: 12px 14px; margin-bottom: 16px; font-size: 13px;">
                <div style="margin-bottom: 4px;">
                    <span style="color: var(--color-text-muted);">Mã hồ sơ:</span>
                    <strong style="color: var(--color-primary); margin-left: 4px;">{{ $dossier->ma_ho_so_hien_thi }}</strong>
                </div>
                <div style="margin-bottom: 4px;">
                    <span style="color: var(--color-text-muted);">Tên hồ sơ:</span>
                    <strong>{{ $dossier->TieuDe }}</strong>
                </div>
                <div style="margin-bottom: 4px;">
                    <span style="color: var(--color-text-muted);">Đơn vị trình:</span>
                    <span>{{ $dossier->donVi?->TenDonVi ?? 'Chưa xác định' }}</span>
                </div>
                <div>
                    <span style="color: var(--color-text-muted);">Số lượng tài liệu đính kèm:</span>
                    <strong style="color: var(--color-accent);">{{ $dossier->taiLieu->count() }} tệp văn bản</strong>
                </div>
            </div>

            <div class="stu-form-group stu-mb-4">
                <label class="stu-form-label" style="font-size: 13px; font-weight: 700;">
                    Kết luận kiểm tra / Thẩm định hồ sơ <span style="color: var(--color-danger);">*</span>
                </label>
                <div class="stu-flex stu-flex-col stu-gap-2" style="margin-top: 6px;">
                    <label style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; border: 1.5px solid #bbf7d0; background: #f0fdf4; border-radius: 8px; cursor: pointer;">
                        <input type="radio" name="decision" value="ELIGIBLE" required {{ in_array($dossier->TrangThai, ['Đủ điều kiện', 'Đầy đủ']) ? 'checked' : '' }} style="accent-color: var(--color-success); width: 16px; height: 16px;">
                        <div>
                            <strong style="color: #166534; font-size: 13px;">✓ ĐỦ ĐIỀU KIỆN VÀO PHIÊN HỌP (ELIGIBLE)</strong>
                            <div style="font-size: 11.5px; color: #15803d;">Hồ sơ đầy đủ tính pháp lý, hợp lệ để xếp lịch nghị sự trong phiên họp HĐT.</div>
                        </div>
                    </label>

                    <label style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; border: 1.5px solid #fde68a; background: #fffbeb; border-radius: 8px; cursor: pointer;">
                        <input type="radio" name="decision" value="REVISE" {{ $dossier->TrangThai === 'Chờ bổ sung' ? 'checked' : '' }} style="accent-color: #d97706; width: 16px; height: 16px;">
                        <div>
                            <strong style="color: #92400e; font-size: 13px;">⚠️ YÊU CẦU BỔ SUNG HỒ SƠ (REVISE)</strong>
                            <div style="font-size: 11.5px; color: #b45309;">Hồ sơ thiếu tờ trình, dự thảo quy chế hoặc tài liệu minh chứng cần hoàn thiện lại.</div>
                        </div>
                    </label>

                    <label style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; border: 1.5px solid var(--color-border); background: #ffffff; border-radius: 8px; cursor: pointer;">
                        <input type="radio" name="decision" value="COMPLETE" {{ $dossier->TrangThai === 'Mới tạo' ? 'checked' : '' }} style="accent-color: var(--color-primary); width: 16px; height: 16px;">
                        <div>
                            <strong style="color: var(--color-primary); font-size: 13px;">📄 HỒ SƠ ĐÃ TIẾP NHẬN ĐẦY ĐỦ (COMPLETE)</strong>
                            <div style="font-size: 11.5px; color: var(--color-text-muted);">Đã tiếp nhận hồ sơ từ đơn vị, chuyển giao thường trực thẩm tra nội dung.</div>
                        </div>
                    </label>
                </div>
            </div>

            <div class="stu-form-group stu-mb-4">
                <label class="stu-form-label" style="font-size: 13px; font-weight: 700;">
                    Ý kiến kiểm tra &amp; Thẩm định của Thư ký / HĐT
                </label>
                <textarea name="GhiChu" rows="3" class="stu-form-control" placeholder="Ghi nhận xét đánh giá tính pháp lý, nội dung cần bổ sung hoặc ý kiến chỉ đạo của Thường trực HĐT...">{{ old('GhiChu', $dossier->GhiChu) }}</textarea>
            </div>

            <div class="stu-flex stu-items-center stu-justify-between" style="border-top: 1px solid var(--color-border); padding-top: 16px;">
                <button type="button" class="stu-btn stu-btn-ghost" data-modal-close>Đóng</button>
                <button type="submit" class="stu-btn stu-btn-primary" style="padding: 10px 24px;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <span>Lưu kết quả kiểm tra</span>
                </button>
            </div>
        </form>
    </x-hop-thoai>

    {{-- Modal Đưa hồ sơ vào phiên họp (BR-08) --}}
    <x-hop-thoai id="addToMeetingModal" title="Đưa Hồ Sơ Vào Phiên Họp Hội Đồng Trường (BR-08)" size="md">
        <form method="POST" action="{{ route('agendas.store') }}">
            @csrf
            <input type="hidden" name="MaHoSo" value="{{ $dossier->MaHoSo }}">

            <div style="background: var(--color-bg-subtle); border: 1px solid var(--color-border); border-radius: 8px; padding: 12px 14px; margin-bottom: 16px; font-size: 13px;">
                <div style="margin-bottom: 4px;">
                    <span style="color: var(--color-text-muted);">Hồ sơ trình:</span>
                    <strong style="color: var(--color-primary); margin-left: 4px;">{{ $dossier->ma_ho_so_hien_thi }}</strong> - {{ $dossier->TieuDe }}
                </div>
                <div>
                    <span style="color: var(--color-text-muted);">Đơn vị trình:</span>
                    <span>{{ $dossier->donVi?->TenDonVi ?? 'Chưa xác định' }}</span>
                </div>
            </div>

            <div class="stu-form-group stu-mb-4">
                <label class="stu-form-label" style="font-size: 13px; font-weight: 700;">
                    Chọn phiên họp Hội đồng trường tiếp nhận <span style="color: var(--color-danger);">*</span>
                </label>
                <select name="MaPhienHop" class="stu-form-control" required style="width: 100%; max-width: 100%; box-sizing: border-box; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; height: 38px; font-size: 13px; border-radius: 8px; border: 1.5px solid #CBD5E1; padding-right: 32px; background-color: #F8FAFC;">
                    <option value="">-- Chọn phiên họp nhận hồ sơ --</option>
                    @foreach($upcomingMeetings as $m)
                        @php
                            $alreadyInMeeting = $dossier->chuongTrinhHop->contains('MaPhienHop', $m->MaPhienHop);
                            $meetingDate = $m->NgayHop ?: $m->ThoiGianBatDau;
                            $dateFormatted = $meetingDate ? \Carbon\Carbon::parse($meetingDate)->format('d/m/Y') : 'Chưa xếp ngày';
                        @endphp
                        <option value="{{ $m->MaPhienHop }}" {{ $alreadyInMeeting ? 'disabled' : '' }}>
                            #{{ $m->MaPhienHop }} - {{ Str::limit($m->TenPhienHop, 48) }} ({{ $dateFormatted }}) {{ $alreadyInMeeting ? '— [Đã có trong phiên]' : '' }}
                        </option>
                    @endforeach
                </select>
                <div style="font-size: 11.5px; color: var(--color-text-muted); margin-top: 4px;">
                    Tuân thủ BR-08: Hồ sơ sẽ được đưa vào chương trình nghị sự và chuẩn bị cho biểu quyết.
                </div>
            </div>

            <div class="stu-form-group stu-mb-4">
                <label class="stu-form-label" style="font-size: 13px; font-weight: 700;">
                    Thứ tự xem xét trong phiên (Tùy chọn)
                </label>
                <input type="number" name="ThuTu" class="stu-form-control" placeholder="Để trống hệ thống sẽ tự động xếp thứ tự tiếp theo" min="1">
                <div style="font-size: 11.5px; color: var(--color-text-muted); margin-top: 4px;">
                    * Ràng buộc: Không được trùng thứ tự với các mục đã có trong phiên họp này
                </div>
            </div>

            <div class="stu-form-group stu-mb-4">
                <label class="stu-form-label" style="font-size: 13px; font-weight: 700;">
                    Ghi chú / Phân công trình bày
                </label>
                <textarea name="GhiChu" rows="2" class="stu-form-control" placeholder="Ghi chú người báo cáo, thời lượng trình bày hoặc định hướng xem xét...">{{ old('GhiChu') }}</textarea>
            </div>

            <div class="stu-flex stu-items-center stu-justify-between" style="border-top: 1px solid var(--color-border); padding-top: 16px;">
                <button type="button" class="stu-btn stu-btn-ghost" data-modal-close>Đóng</button>
                <button type="submit" class="stu-btn stu-btn-primary" style="padding: 10px 24px;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    <span>Xác nhận đưa vào phiên họp</span>
                </button>
            </div>
        </form>
    </x-hop-thoai>
@endif

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Kiểm tra và bẫy lỗi định dạng file đính kèm tài liệu bổ sung
        const editDocFile = document.getElementById('submissionEditDocFile');
        const editDocErr = document.getElementById('submissionEditDocError');
        const allowedExts = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'];

        if (editDocFile) {
            editDocFile.addEventListener('change', function () {
                if (editDocErr) {
                    editDocErr.textContent = '';
                    editDocErr.style.display = 'none';
                }
                if (!this.files || this.files.length === 0) return;
                const file = this.files[0];
                const fileName = file.name;
                const ext = fileName.split('.').pop().toLowerCase();

                if (!allowedExts.includes(ext)) {
                    const msg = `Định dạng tập tin ".${ext}" không được hỗ trợ! Vui lòng chỉ tải lên các tệp tài liệu: PDF (.pdf), Word (.doc, .docx), Excel (.xls, .xlsx) hoặc PowerPoint (.ppt, .pptx).`;
                    if (editDocErr) {
                        editDocErr.textContent = msg;
                        editDocErr.style.display = 'block';
                    }
                    alert(msg);
                    this.value = '';
                    return;
                }

                if (file.size > 20 * 1024 * 1024) {
                    const msg = 'Dung lượng tập tin vượt quá 20MB! Vui lòng chọn tệp nhỏ hơn.';
                    if (editDocErr) {
                        editDocErr.textContent = msg;
                        editDocErr.style.display = 'block';
                    }
                    alert(msg);
                    this.value = '';
                    return;
                }
            });
        }

        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('edit') === '1' || urlParams.get('chinh_sua') === '1') {
            if (window.STUModal) {
                window.STUModal.open('editSubmissionModal');
            }
        } else if (urlParams.get('review') === '1' || urlParams.get('kiem_tra') === '1') {
            if (window.STUModal) {
                window.STUModal.open('reviewSubmissionModal');
            }
        }
    });
</script>
@endsection
