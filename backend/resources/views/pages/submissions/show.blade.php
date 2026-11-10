@extends('layouts.app')

@section('title', 'Hồ sơ trình: ' . $dossier->TieuDe)

@section('content')
<div class="stu-page-container">
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="['Hồ sơ trình' => route('submissions.index'), '#HS-' . str_pad($dossier->MaHoSo, 4, '0', STR_PAD_LEFT) => null]" />

    {{-- Header --}}
    <div class="stu-page-header stu-flex stu-items-start stu-justify-between stu-mb-6" style="gap: 16px;">
        <div style="flex: 1;">
            <div class="stu-flex stu-items-center stu-gap-2 stu-mb-2">
                <span style="font-weight: 800; color: var(--color-primary); font-size: 14px; background: rgba(0, 43, 91, 0.08); padding: 3px 8px; border-radius: var(--radius-sm);">
                    #HS-{{ str_pad($dossier->MaHoSo, 4, '0', STR_PAD_LEFT) }}
                </span>
                <span class="stu-badge stu-badge-light">{{ $dossier->LinhVuc ?? 'Tổng hợp' }}</span>
                <x-status-badge :status="$dossier->TrangThai" />
            </div>
            <h1 class="stu-page-title" style="font-size: 22px; font-weight: 800; color: var(--color-primary); margin: 0; line-height: 1.4;">
                {{ $dossier->TieuDe }}
            </h1>
            <div style="font-size: 13px; color: var(--color-text-muted); margin-top: 6px; display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
                <span><strong>Đơn vị trình:</strong> {{ $dossier->donVi?->TenDonVi ?? 'Chưa xác định' }}</span>
                <span><strong>Ngày trình:</strong> {{ $dossier->NgayTrinh ? \Carbon\Carbon::parse($dossier->NgayTrinh)->format('d/m/Y') : 'Chưa gửi' }}</span>
                <span><strong>Người tạo:</strong> {{ $dossier->nguoiDung?->HoTen ?? 'Hệ thống' }}</span>
            </div>
        </div>
        <div style="display: flex; align-items: center; gap: 10px;">
            @if(in_array($role, ['UNIT_REP', 'SECRETARY', 'CHAIR', 'ADMIN']))
                <a href="{{ route('submissions.edit', $dossier->MaHoSo) }}" class="stu-btn stu-btn-primary stu-btn-sm" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 600;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Chỉnh sửa hồ sơ</span>
                </a>
            @endif
            <a href="{{ route('submissions.index') }}" class="stu-btn stu-btn-outline stu-btn-sm">
                Quay lại danh sách
            </a>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <x-alert type="success" :dismissible="true" class="stu-mb-6">
            {{ session('success') }}
        </x-alert>
    @endif
    @if(session('error'))
        <x-alert type="danger" :dismissible="true" class="stu-mb-6">
            {{ session('error') }}
        </x-alert>
    @endif

    {{-- Main Layout 2 Columns --}}
    <div class="stu-grid" style="grid-template-columns: 2fr 1fr; gap: 24px; align-items: flex-start;">
        {{-- Cột trái: Thông tin chi tiết + Tài liệu đính kèm + Phiên họp liên kết --}}
        <div class="stu-flex stu-flex-col stu-gap-6">
            {{-- 1. Tóm tắt nội dung đề xuất --}}
            <div class="stu-card" style="padding: 24px;">
                <h3 style="font-size: 16px; font-weight: 700; color: var(--color-primary); margin: 0 0 14px 0; display: flex; align-items: center; gap: 8px;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Nội dung đề xuất / Căn cứ tờ trình</span>
                </h3>
                <div style="font-size: 14px; line-height: 1.7; color: var(--color-text-primary); white-space: pre-line; background: var(--color-bg-muted); padding: 16px; border-radius: var(--radius-md); border: 1px solid var(--color-border);">
                    {{ $dossier->NoiDungTomTat ?: 'Chưa có nội dung tóm tắt.' }}
                </div>

                @if($dossier->GhiChu)
                    <div style="margin-top: 18px;">
                        <h4 style="font-size: 13px; font-weight: 700; color: var(--color-text-secondary); margin: 0 0 6px 0;">
                            Lịch sử ý kiến thẩm tra & Ghi chú xử lý:
                        </h4>
                        <div style="font-size: 13px; line-height: 1.6; color: var(--color-text-muted); background: #fff8e6; border-left: 3px solid #f59e0b; padding: 10px 14px; border-radius: var(--radius-sm); white-space: pre-line;">
                            {{ $dossier->GhiChu }}
                        </div>
                    </div>
                @endif
            </div>

            {{-- 2. Danh sách tệp tài liệu đính kèm (Lưu trữ an toàn) --}}
            <div class="stu-card" style="padding: 24px;">
                <h3 style="font-size: 16px; font-weight: 700; color: var(--color-primary); margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                    <span>Tài liệu & Bản thuyết minh đính kèm ({{ $dossier->taiLieu->count() }})</span>
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
                                    <th style="text-align: right;">Tải về</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dossier->taiLieu as $doc)
                                    <tr>
                                        <td style="font-weight: 600; color: var(--color-text-primary);">
                                            {{ $doc->TenTaiLieu }}
                                        </td>
                                        <td style="font-size: 12px;">{{ $doc->LoaiTaiLieu ?? 'Tờ trình' }}</td>
                                        <td style="font-size: 12px; text-transform: uppercase;">
                                            <span class="stu-badge stu-badge-light">{{ $doc->DinhDang ?? 'FILE' }}</span>
                                        </td>
                                        <td style="font-size: 12px; color: var(--color-text-muted);">
                                            {{ $doc->DungLuong ? number_format($doc->DungLuong / 1024, 1) . ' KB' : '—' }}
                                        </td>
                                        <td style="font-size: 12px; color: var(--color-text-muted);">
                                            {{ $doc->NgayTaiLen ? \Carbon\Carbon::parse($doc->NgayTaiLen)->format('d/m/Y') : '—' }}
                                        </td>
                                        <td style="text-align: right;">
                                            <a href="{{ $doc->DuongDan }}" target="_blank" download class="stu-btn stu-btn-outline stu-btn-sm" style="padding: 4px 10px;">
                                                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                                <span>Tải</span>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div style="text-align: center; padding: 32px; background: var(--color-bg-muted); border-radius: var(--radius-md); color: var(--color-text-muted); font-size: 13px;">
                        Chưa có tài liệu đính kèm nào trong hồ sơ này.
                    </div>
                @endif
            </div>

            {{-- 3. Phiên họp đã đưa vào chương trình (Ràng buộc BR-08) --}}
            <div class="stu-card" style="padding: 24px;">
                <h3 style="font-size: 16px; font-weight: 700; color: var(--color-primary); margin: 0 0 14px 0; display: flex; align-items: center; gap: 8px;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Kỳ họp Hội đồng trường xem xét</span>
                </h3>
                @if($dossier->chuongTrinhHop->count() > 0)
                    <div class="stu-table-container">
                        <table class="stu-table">
                            <thead>
                                <tr>
                                    <th>Phiên họp</th>
                                    <th>Thứ tự xét</th>
                                    <th>Trạng thái xem xét</th>
                                    <th style="text-align: right;">Chi tiết</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dossier->chuongTrinhHop as $ct)
                                    <tr>
                                        <td style="font-weight: 600;">
                                            {{ $ct->phienHop?->TenPhienHop ?? 'Phiên họp #' . $ct->MaPhienHop }}
                                        </td>
                                        <td>
                                            <span class="stu-badge stu-badge-light">Mục số {{ $ct->ThuTu }}</span>
                                        </td>
                                        <td>
                                            <x-status-badge :status="$ct->TrangThai" />
                                        </td>
                                        <td style="text-align: right;">
                                            @if($ct->phienHop)
                                                <a href="{{ route('meetings.show', $ct->phienHop->MaPhienHop) }}" class="stu-btn stu-btn-ghost stu-btn-sm">
                                                    Xem phiên họp &rarr;
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div style="font-size: 13px; color: var(--color-text-muted); background: var(--color-bg-muted); padding: 16px; border-radius: var(--radius-md);">
                        Hồ sơ này chưa được xếp vào chương trình nghị sự của phiên họp nào.
                        @if($dossier->TrangThai === 'Đủ điều kiện')
                            <div style="margin-top: 6px; color: var(--color-success); font-weight: 600;">
                                ✓ Hồ sơ đã được thẩm định "Đủ điều kiện", Thư ký có thể thêm vào Tab "Chương trình họp" của phiên họp tương ứng (Ràng buộc BR-08).
                            </div>
                        @else
                            <div style="margin-top: 6px; color: var(--color-warning); font-size: 12px;">
                                ⚠ Theo Quy định BR-08: Hồ sơ phải được Thư ký thẩm định đạt trạng thái <strong>"Đủ điều kiện"</strong> trước khi có thể xếp vào chương trình phiên họp.
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        {{-- Cột phải: Khung Thẩm tra hồ sơ (Dành cho Thư ký, Chủ tịch, Quản trị viên) --}}
        <div>
            @if(in_array($role, ['SECRETARY', 'CHAIR', 'ADMIN']))
                <div class="stu-card" style="padding: 24px; border-top: 4px solid var(--color-primary);">
                    <h3 style="font-size: 16px; font-weight: 800; color: var(--color-primary); margin: 0 0 10px 0; display: flex; align-items: center; gap: 8px;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Thẩm tra Hồ sơ tờ trình</span>
                    </h3>
                    <p style="font-size: 12px; color: var(--color-text-muted); margin: 0 0 16px 0;">
                        Thư ký / Chủ tịch Hội đồng trường thẩm định tính hợp lệ của hồ sơ trước khi đưa ra biểu quyết tại phiên họp.
                    </p>

                    <form method="POST" action="{{ route('submissions.review', $dossier->MaHoSo) }}">
                        @csrf
                        <div class="stu-form-group stu-mb-4">
                            <label class="stu-form-label" style="font-size: 12px; font-weight: 700; text-transform: uppercase;">
                                Kết luận thẩm định <span style="color: var(--color-danger);">*</span>
                            </label>
                            <div class="stu-flex stu-flex-col stu-gap-2">
                                <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; cursor: pointer; padding: 8px; border: 1px solid var(--color-border); border-radius: var(--radius-sm);">
                                    <input type="radio" name="decision" value="ELIGIBLE" {{ $dossier->TrangThai === 'Đủ điều kiện' ? 'checked' : '' }} required>
                                    <div>
                                        <strong style="color: var(--color-success);">Đủ điều kiện (ELIGIBLE)</strong>
                                        <div style="font-size: 11px; color: var(--color-text-muted);">Hồ sơ chuẩn chỉ, được phép xếp vào chương trình phiên họp.</div>
                                    </div>
                                </label>
                                <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; cursor: pointer; padding: 8px; border: 1px solid var(--color-border); border-radius: var(--radius-sm);">
                                    <input type="radio" name="decision" value="REVISE" {{ $dossier->TrangThai === 'Chờ bổ sung' ? 'checked' : '' }}>
                                    <div>
                                        <strong style="color: var(--color-warning);">Chờ bổ sung (REVISE)</strong>
                                        <div style="font-size: 11px; color: var(--color-text-muted);">Yêu cầu đơn vị hoàn thiện thêm tài liệu thuyết minh.</div>
                                    </div>
                                </label>
                                <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; cursor: pointer; padding: 8px; border: 1px solid var(--color-border); border-radius: var(--radius-sm);">
                                    <input type="radio" name="decision" value="COMPLETE" {{ $dossier->TrangThai === 'Đầy đủ' ? 'checked' : '' }}>
                                    <div>
                                        <strong>Đầy đủ (COMPLETE)</strong>
                                        <div style="font-size: 11px; color: var(--color-text-muted);">Đã tiếp nhận hồ sơ đầy đủ thành phần.</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="stu-form-group stu-mb-4">
                            <label class="stu-form-label" style="font-size: 12px; font-weight: 700; text-transform: uppercase;">
                                Ý kiến chỉ đạo / Yêu cầu bổ sung
                            </label>
                            <textarea 
                                name="GhiChu" 
                                rows="3" 
                                class="stu-form-control" 
                                placeholder="Ghi rõ nội dung cần chỉnh sửa, bổ sung để đơn vị trình nắm bắt..."
                            ></textarea>
                        </div>

                        <button type="submit" class="stu-btn stu-btn-primary" style="width: 100%; justify-content: center;">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            <span>Lưu kết quả thẩm định</span>
                        </button>
                    </form>
                </div>
            @else
                <div class="stu-card" style="padding: 20px; background: var(--color-bg-muted);">
                    <h4 style="font-size: 14px; font-weight: 700; color: var(--color-primary); margin: 0 0 8px 0;">
                        Quy trình thẩm định hồ sơ
                    </h4>
                    <p style="font-size: 12px; color: var(--color-text-muted); line-height: 1.6; margin: 0;">
                        Hồ sơ sau khi gửi sẽ được Thư ký Hội đồng trường thẩm định tính hợp lệ. Khi đạt trạng thái <strong>"Đủ điều kiện"</strong>, hồ sơ sẽ được đưa vào chương trình nghị sự của phiên họp gần nhất.
                    </p>
                </div>
            @endif

            {{-- Thông tin tra cứu nhanh --}}
            <div class="stu-card stu-mt-4" style="padding: 20px; font-size: 13px;">
                <div style="font-weight: 700; margin-bottom: 12px; color: var(--color-text-primary);">Thông tin tham chiếu</div>
                <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid var(--color-border);">
                    <span style="color: var(--color-text-muted);">Mã đơn vị:</span>
                    <span style="font-weight: 600;">{{ $dossier->MaDonVi }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid var(--color-border);">
                    <span style="color: var(--color-text-muted);">Tài liệu đính kèm:</span>
                    <span style="font-weight: 600;">{{ $dossier->taiLieu->count() }} tệp</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 6px 0;">
                    <span style="color: var(--color-text-muted);">Phiên họp đưa vào:</span>
                    <span style="font-weight: 600;">{{ $dossier->chuongTrinhHop->count() }} kỳ họp</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
