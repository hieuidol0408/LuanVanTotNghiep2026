@extends('layouts.chinh')

@section('title', 'Nghị quyết: ' . $resolution->SoNghiQuyet)

@section('content')
<div class="stu-page-container">
    {{-- Breadcrumb --}}
    <x-thanh-dieu-huong :items="['Nghị quyết HĐT' => route('resolutions.index'), $resolution->SoNghiQuyet => null]" />

    @php
        $isDraft = in_array($resolution->TrangThai, ['DRAFT', 'Dự thảo']);
        $isSigned = in_array($resolution->TrangThai, ['SIGNED', 'Đã ký ban hành', 'Đã ký']);
        $isPublished = in_array($resolution->TrangThai, ['PUBLISHED', 'Đã công bố hiệu lực', 'Đã công bố']);
    @endphp

    {{-- Header --}}
    <div class="stu-page-header stu-flex stu-items-start stu-justify-between stu-mb-6" style="gap: 16px; flex-wrap: wrap;">
        <div style="flex: 1 1 420px; min-width: 0;">
            <div class="stu-flex stu-items-center stu-gap-2 stu-mb-2">
                <span style="font-weight: 800; color: var(--color-primary); font-size: 14px; background: rgba(0, 43, 91, 0.08); padding: 3px 8px; border-radius: var(--radius-sm);">
                    {{ $resolution->SoNghiQuyet }}
                </span>
                <x-nhan-trang-thai :status="$resolution->TrangThai" />
            </div>

            <h1 class="stu-page-title" style="font-size: 22px; font-weight: 800; color: var(--color-primary); margin: 0; line-height: 1.4;">
                {{ $resolution->TrichYeu }}
            </h1>

            <div style="font-size: 13px; color: var(--color-text-muted); margin-top: 6px; display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
                <span>
                    <strong>Phiên họp:</strong>
                    @if($resolution->phienHop)
                        <a href="{{ route('meetings.show', $resolution->MaPhienHop) }}" style="color: var(--color-primary); font-weight: 600; text-decoration: none;">
                            #{{ $resolution->MaPhienHop }} - {{ $resolution->phienHop->TenPhienHop }}
                        </a>
                    @else
                        Phiên họp #{{ $resolution->MaPhienHop }}
                    @endif
                </span>
                <span><strong>Ngày ban hành:</strong> {{ $resolution->NgayBanHanh ? \Carbon\Carbon::parse($resolution->NgayBanHanh)->format('d/m/Y') : 'Chưa ấn định' }}</span>
                <span><strong>Tiến độ thực hiện:</strong> {{ $overallProgress }}% ({{ $completedTasks }}/{{ $totalTasks }} nhiệm vụ hoàn thành)</span>
            </div>
        </div>

        {{-- Thanh thao tác nghiệp vụ theo vai trò --}}
        <div class="stu-flex stu-items-center stu-gap-2" style="flex-wrap: wrap;">
            {{-- Ký ban hành (Dành riêng cho Chủ tịch HĐT) --}}
            @if($isDraft && in_array($role, ['CHAIR', 'ADMIN']))
                <form method="POST" action="{{ route('resolutions.sign', $resolution->MaNghiQuyet) }}">
                    @csrf
                    <button type="submit" class="stu-btn stu-btn-secondary" style="height: 38px;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        <span>Chủ tịch ký ban hành</span>
                    </button>
                </form>
            @endif

            {{-- Công bố hiệu lực (Thư ký / Chủ tịch) --}}
            @if($isSigned && in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN']))
                <form method="POST" action="{{ route('resolutions.publish', $resolution->MaNghiQuyet) }}">
                    @csrf
                    <button type="submit" class="stu-btn stu-btn-primary" style="height: 38px;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        <span>Công bố hiệu lực toàn trường</span>
                    </button>
                </form>
            @endif

            {{-- Phân rã nhiệm vụ thi hành (Thư ký / Chủ tịch) --}}
            @if(in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN']))
                <a href="{{ route('tasks.create', ['resolution_id' => $resolution->MaNghiQuyet]) }}" class="stu-btn stu-btn-outline" style="height: 38px;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    <span>Phân rã nhiệm vụ (BR-10)</span>
                </a>
            @endif

            @if($isDraft && in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN']))
                <button type="button" class="stu-btn stu-btn-outline" style="height: 38px;" data-modal-open="editResolutionModal">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Sửa dự thảo</span>
                </button>
            @endif

            <a href="{{ route('resolutions.index') }}" class="stu-btn stu-btn-ghost" style="height: 38px;">
                Quay lại
            </a>
        </div>
    </div>

    {{-- Bố cục 2 Cột --}}
    <div class="stu-grid" style="grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); gap: 24px; align-items: flex-start;">
        {{-- Cột trái: Toàn văn quyết nghị + Tài liệu văn bản + Danh sách nhiệm vụ phân rã --}}
        <div class="stu-flex stu-flex-col stu-gap-6">
            {{-- 1. Toàn văn Nghị quyết --}}
            <div class="stu-card" style="padding: 26px; border-top: 4px solid var(--color-primary);">
                <div style="text-align: center; margin-bottom: 24px; border-bottom: 1px solid var(--color-border); padding-bottom: 16px;">
                    <div style="font-size: 13px; font-weight: 700; color: var(--color-text-secondary); text-transform: uppercase;">
                        HỘI ĐỒNG TRƯỜNG TRƯỜNG ĐẠI HỌC CÔNG NGHỆ SÀI GÒN
                    </div>
                    <div style="font-size: 16px; font-weight: 800; color: var(--color-primary); margin-top: 4px;">
                        NGHỊ QUYẾT {{ $resolution->SoNghiQuyet }}
                    </div>
                    <div style="font-size: 13px; font-style: italic; color: var(--color-text-muted); margin-top: 4px;">
                        {{ $resolution->TrichYeu }}
                    </div>
                </div>

                <div style="font-size: 14px; line-height: 1.8; color: var(--color-text-primary); white-space: pre-line; background: var(--color-bg-muted); padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--color-border); font-family: inherit;">
                    {{ $resolution->NoiDung }}
                </div>
            </div>

            {{-- 2. Tài liệu văn bản đính kèm --}}
            <div class="stu-card" style="padding: 22px;">
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 14px;">
                    <h3 style="font-size: 16px; font-weight: 700; color: var(--color-primary); margin: 0; display: flex; align-items: center; gap: 8px;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                        <span>Văn bản Nghị quyết bản scan có chữ ký &amp; đóng dấu ({{ $resolution->taiLieu->count() }})</span>
                    </h3>
                </div>

                @if(in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN']))
                    <form method="POST" action="{{ route('resolutions.documents.store', $resolution->MaNghiQuyet) }}" enctype="multipart/form-data" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap; padding: 12px 14px; background: #F8FAFC; border: 1px dashed #CBD5E1; border-radius: 10px; margin-bottom: 16px;">
                        @csrf
                        <div style="flex: 1 1 240px; min-width: 0;">
                            <input
                                type="file"
                                name="files[]"
                                multiple
                                accept=".pdf,.doc,.docx"
                                required
                                class="stu-form-control"
                                style="height: 36px; font-size: 12px; padding: 5px 10px; background: #fff;"
                            >
                        </div>
                        <button type="submit" class="stu-btn stu-btn-primary" style="height: 36px; padding: 0 14px; font-size: 12.5px; white-space: nowrap;">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            <span>Đính kèm văn bản</span>
                        </button>
                    </form>
                @endif

                @if($resolution->taiLieu->count() > 0)
                    <div class="stu-table-wrapper">
                        <table class="stu-table" style="table-layout: auto;">
                            <thead>
                                <tr>
                                    <th style="padding: 10px 10px;">Tên tài liệu</th>
                                    <th style="padding: 10px 8px; white-space: nowrap;">Định dạng</th>
                                    <th style="padding: 10px 8px; white-space: nowrap;">Ngày tải lên</th>
                                    <th style="padding: 10px 10px; text-align: right; white-space: nowrap;">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($resolution->taiLieu as $doc)
                                    <tr>
                                        <td style="padding: 10px 10px; font-weight: 600; color: var(--color-text-primary); word-break: break-word;">
                                            <div>{{ $doc->TenTaiLieu }}</div>
                                            <div style="font-size: 11px; font-weight: 400; color: var(--color-text-muted); margin-top: 2px;">
                                                {{ $doc->DungLuong ? number_format($doc->DungLuong / 1024, 1) . ' KB' : '—' }}
                                            </div>
                                        </td>
                                        <td style="padding: 10px 8px; font-size: 12px; text-transform: uppercase; white-space: nowrap;">
                                            <span class="stu-badge stu-badge-light">{{ $doc->DinhDang ?? 'PDF' }}</span>
                                        </td>
                                        <td style="padding: 10px 8px; font-size: 12px; color: var(--color-text-muted); white-space: nowrap;">
                                            {{ $doc->NgayTaiLen ? \Carbon\Carbon::parse($doc->NgayTaiLen)->format('d/m/Y') : '—' }}
                                        </td>
                                        <td style="padding: 10px 10px; text-align: right; white-space: nowrap;">
                                            <div style="display: inline-flex; align-items: center; gap: 6px; flex-wrap: nowrap;">
                                                <a href="{{ route('documents.preview', $doc->MaTaiLieu) }}" target="_blank" class="stu-btn stu-btn-ghost stu-btn-sm" style="padding: 4px 8px; font-size: 12px;" title="Xem trước tài liệu">
                                                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                    <span>Xem</span>
                                                </a>
                                                <a href="{{ route('documents.download', $doc->MaTaiLieu) }}" class="stu-btn stu-btn-outline stu-btn-sm" style="padding: 4px 8px; font-size: 12px;" title="Tải về máy">
                                                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                                    <span>Tải file</span>
                                                </a>
                                                @if(in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN']))
                                                    <form method="POST" action="{{ route('resolutions.documents.destroy', [$resolution->MaNghiQuyet, $doc->MaTaiLieu]) }}" style="display: inline-block; margin: 0;" onsubmit="return confirm('Bạn có chắc muốn gỡ tài liệu {{ $doc->TenTaiLieu }} khỏi nghị quyết này?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="stu-btn stu-btn-ghost stu-btn-sm" style="padding: 4px 8px; font-size: 12px; color: var(--color-danger);" title="Gỡ tài liệu đính kèm">
                                                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                            <span>Gỡ</span>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div style="text-align: center; padding: 24px; background: var(--color-bg-muted); border-radius: var(--radius-md); color: var(--color-text-muted); font-size: 13px;">
                        Chưa có bản scan văn bản đính kèm cho nghị quyết này.
                    </div>
                @endif
            </div>

            {{-- 3. Danh sách các Nhiệm vụ được phân rã từ Nghị quyết này (BR-10) --}}
            <div class="stu-card" style="padding: 24px;">
                <div class="stu-flex stu-items-center stu-justify-between stu-mb-4">
                    <h3 style="font-size: 16px; font-weight: 800; color: var(--color-primary); margin: 0; display: flex; align-items: center; gap: 8px;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        <span>Các Nhiệm vụ phân rã thi hành ({{ $totalTasks }})</span>
                    </h3>
                    @if(in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN']))
                        <a href="{{ route('tasks.create', ['resolution_id' => $resolution->MaNghiQuyet]) }}" class="stu-btn stu-btn-secondary stu-btn-sm">
                            + Giao nhiệm vụ mới
                        </a>
                    @endif
                </div>

                @if($totalTasks > 0)
                    <div class="stu-table-wrapper">
                        <table class="stu-table">
                            <thead>
                                <tr>
                                    <th>Mã NV</th>
                                    <th>Tên đầu việc</th>
                                    <th>Đơn vị chủ trì</th>
                                    <th>Hạn hoàn thành</th>
                                    <th>Trạng thái</th>
                                    <th style="text-align: right;">Chi tiết</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($resolution->nhiemVu as $task)
                                    @php
                                        $lead = $task->phanCongNhiemVu->firstWhere('VaiTroThucHien', 'CHỦ TRÌ') ?: $task->phanCongNhiemVu->first();
                                    @endphp
                                    <tr>
                                        <td style="font-weight: 700; color: var(--color-primary);">
                                            #NV-{{ str_pad($task->MaNhiemVu, 3, '0', STR_PAD_LEFT) }}
                                        </td>
                                        <td style="font-weight: 600;">
                                            <a href="{{ route('tasks.show', $task->MaNhiemVu) }}" style="color: var(--color-text-primary); text-decoration: none;">
                                                {{ $task->TenNhiemVu }}
                                            </a>
                                        </td>
                                        <td style="font-size: 13px;">
                                            <span class="stu-badge stu-badge-light">{{ $lead?->donVi?->TenDonVi ?? 'Chưa phân công' }}</span>
                                        </td>
                                        <td style="font-size: 12px; color: var(--color-text-muted);">
                                            {{ $task->NgayHoanThanh ? \Carbon\Carbon::parse($task->NgayHoanThanh)->format('d/m/Y') : '—' }}
                                        </td>
                                        <td>
                                            <x-nhan-trang-thai :status="$task->TrangThai" />
                                        </td>
                                        <td style="text-align: right;">
                                            <a href="{{ route('tasks.show', $task->MaNhiemVu) }}" class="stu-btn stu-btn-ghost stu-btn-sm">
                                                Xem &rarr;
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div style="text-align: center; padding: 32px; background: var(--color-bg-muted); border-radius: var(--radius-md); color: var(--color-text-muted); font-size: 13px;">
                        Nghị quyết này chưa được Thư ký phân rã thành các đầu việc nhiệm vụ cụ thể theo BR-10.
                    </div>
                @endif
            </div>
        </div>

        {{-- Cột phải: Thông tin tham chiếu & Căn cứ pháp lý --}}
        <div class="stu-flex stu-flex-col stu-gap-6">
            {{-- Thẻ Tiến độ tổng thể --}}
            <div class="stu-card" style="padding: 22px;">
                <h4 style="font-size: 14px; font-weight: 800; color: var(--color-primary); margin: 0 0 12px 0;">
                    Tiến độ Thực hiện Nghị quyết
                </h4>
                <div style="font-size: 28px; font-weight: 800; color: {{ $overallProgress == 100 ? 'var(--color-success)' : 'var(--color-primary)' }};">
                    {{ $overallProgress }}%
                </div>
                <div style="height: 8px; background: var(--color-bg-muted); border-radius: var(--radius-full); overflow: hidden; margin: 8px 0 12px 0;">
                    <div style="height: 100%; width: {{ $overallProgress }}%; background: {{ $overallProgress == 100 ? 'var(--color-success)' : 'var(--color-primary)' }}; border-radius: var(--radius-full);"></div>
                </div>
                <div style="font-size: 12px; color: var(--color-text-muted);">
                    Đã hoàn thành <strong>{{ $completedTasks }}</strong> trên tổng số <strong>{{ $totalTasks }}</strong> nhiệm vụ được phân công.
                </div>
            </div>

            {{-- Căn cứ Hồ sơ tờ trình xem xét --}}
            <div class="stu-card" style="padding: 20px;">
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 12px;">
                    <h4 style="font-size: 14px; font-weight: 800; color: var(--color-primary); margin: 0;">
                        Căn cứ Hồ sơ tờ trình ({{ $resolution->hoSoTrinh->count() }})
                    </h4>
                    @if(in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN']))
                        <button type="button" class="stu-btn stu-btn-ghost stu-btn-sm" style="padding: 3px 9px; font-size: 11.5px;" data-modal-open="linkEvidenceModal">
                            + Liên kết
                        </button>
                    @endif
                </div>
                @forelse($resolution->hoSoTrinh as $hs)
                    <div style="padding: 8px 0; border-bottom: 1px solid var(--color-border); font-size: 13px;">
                        <a href="{{ route('submissions.show', $hs->MaHoSo) }}" style="font-weight: 600; color: var(--color-text-primary); text-decoration: none;">
                            {{ $hs->ma_ho_so_hien_thi }}: {{ $hs->TieuDe }}
                        </a>
                        <div style="font-size: 11px; color: var(--color-text-muted); margin-top: 2px;">
                            Đơn vị trình: {{ $hs->donVi?->TenDonVi ?? 'Chưa cập nhật' }}
                        </div>
                    </div>
                @empty
                    <div style="font-size: 12px; color: var(--color-text-muted);">Chưa liên kết hồ sơ tờ trình nào.</div>
                @endforelse
            </div>

            {{-- Căn cứ Kết quả Biểu quyết --}}
            <div class="stu-card" style="padding: 20px;">
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 12px;">
                    <h4 style="font-size: 14px; font-weight: 800; color: var(--color-primary); margin: 0;">
                        Căn cứ Kết quả Biểu quyết ({{ $resolution->noiDungBieuQuyet->count() }})
                    </h4>
                    @if(in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN']))
                        <button type="button" class="stu-btn stu-btn-ghost stu-btn-sm" style="padding: 3px 9px; font-size: 11.5px;" data-modal-open="linkEvidenceModal">
                            + Liên kết
                        </button>
                    @endif
                </div>
                @forelse($resolution->noiDungBieuQuyet as $bq)
                    <div style="padding: 8px 0; border-bottom: 1px solid var(--color-border); font-size: 13px;">
                        <a href="{{ route('votes.show', $bq->MaNoiDung) }}" style="font-weight: 600; color: var(--color-text-primary); text-decoration: none;">
                            #BQ-{{ str_pad($bq->MaNoiDung, 3, '0', STR_PAD_LEFT) }}: {{ $bq->NoiDung }}
                        </a>
                        <div style="margin-top: 4px; display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                            <x-nhan-trang-thai :status="$bq->TrangThai" />
                            @if($bq->chuongTrinhHop?->phienHop)
                                <span style="font-size: 11px; color: var(--color-text-muted);">Phiên #{{ $bq->chuongTrinhHop->MaPhienHop }}</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div style="font-size: 12px; color: var(--color-text-muted);">Chưa liên kết nội dung biểu quyết nào.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@if(in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN']))
    @php
        $linkedDossierIds = old('dossier_ids', $resolution->hoSoTrinh->pluck('MaHoSo')->all());
        $linkedVotingIds = old('voting_ids', $resolution->noiDungBieuQuyet->pluck('MaNoiDung')->all());
    @endphp

    {{-- Modal Chỉnh sửa / Ghi nhận Nghị quyết --}}
    <x-hop-thoai id="editResolutionModal" title="Chỉnh Sửa Dự Thảo Nghị Quyết: {{ $resolution->SoNghiQuyet }}" size="lg">
        <form method="POST" action="{{ route('resolutions.update', $resolution->MaNghiQuyet) }}" enctype="multipart/form-data" id="editResolutionForm">
            @csrf
            @method('PUT')
            <input type="hidden" name="sync_links" value="1">

            {{-- 1. Hàng 1: Số hiệu & Ngày ban hành --}}
            <div class="stu-grid" style="grid-template-columns: 1.2fr 1fr; gap: 16px; width: 100%; max-width: 100%; box-sizing: border-box;">
                <div class="stu-form-group" style="min-width: 0; width: 100%; max-width: 100%; box-sizing: border-box; margin-bottom: 0;">
                    <label class="stu-form-label" style="font-size: 13px; font-weight: 700;">Số hiệu Nghị quyết <span style="color: var(--color-danger);">*</span></label>
                    <input type="text" name="SoNghiQuyet" value="{{ old('SoNghiQuyet', $resolution->SoNghiQuyet) }}" class="stu-form-control" required>
                </div>

                <div class="stu-form-group" style="min-width: 0; width: 100%; max-width: 100%; box-sizing: border-box; margin-bottom: 0;">
                    <label class="stu-form-label" style="font-size: 13px; font-weight: 700;">Ngày ban hành <span style="color: var(--color-danger);">*</span></label>
                    <input type="date" name="NgayBanHanh" value="{{ old('NgayBanHanh', $resolution->NgayBanHanh ? \Carbon\Carbon::parse($resolution->NgayBanHanh)->format('Y-m-d') : '') }}" class="stu-form-control" required>
                </div>
            </div>

            {{-- 2. Hàng 2: Phiên họp ban hành --}}
            <div class="stu-form-group stu-mb-4" style="margin-top: 12px; width: 100%; max-width: 100%; box-sizing: border-box;">
                <label class="stu-form-label" style="font-size: 13px; font-weight: 700;">Phiên họp Hội đồng trường ban hành <span style="color: var(--color-danger);">*</span></label>
                <div style="position: relative; width: 100%; max-width: 100%; box-sizing: border-box;">
                    <select name="MaPhienHop" class="stu-form-control" required style="width: 100%; max-width: 100%; box-sizing: border-box; height: 40px; font-size: 13.5px; border-radius: 8px; border: 1.5px solid #CBD5E1; background-color: #F8FAFC;">
                        @php
                            $groupedMeetings = collect($meetings ?? [])->groupBy(function($m) {
                                $date = $m->NgayHop ?: $m->ThoiGianBatDau;
                                return $date ? date('Y', strtotime($date)) : 'Khác';
                            });
                        @endphp
                        @foreach($groupedMeetings as $yearGroup => $mList)
                            <optgroup label="📅 Các phiên họp năm {{ $yearGroup }} ({{ $mList->count() }} phiên)">
                                @foreach($mList as $m)
                                    @php
                                        $meetingDate = $m->NgayHop ?: $m->ThoiGianBatDau;
                                        $dateFormatted = $meetingDate ? \Carbon\Carbon::parse($meetingDate)->format('d/m/Y') : 'Chưa xếp ngày';
                                    @endphp
                                    <option value="{{ $m->MaPhienHop }}" {{ old('MaPhienHop', $resolution->MaPhienHop) == $m->MaPhienHop ? 'selected' : '' }}>
                                        #{{ $m->MaPhienHop }} - {{ Str::limit($m->TenPhienHop, 52) }} ({{ $dateFormatted }})
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="stu-grid stu-grid-cols-2 stu-gap-4 stu-mb-4" style="margin-top: 12px;">
                <div class="stu-form-group" style="margin-bottom: 0;">
                    <label class="stu-form-label" style="font-size: 13px; font-weight: 700;">Trích yếu <span style="color: var(--color-danger);">*</span></label>
                    <input type="text" name="TrichYeu" value="{{ old('TrichYeu', $resolution->TrichYeu) }}" class="stu-form-control" required>
                </div>

                <div class="stu-form-group" style="margin-bottom: 0;">
                    <label class="stu-form-label" style="font-size: 13px; font-weight: 700;">Trạng thái xử lý <span style="color: var(--color-danger);">*</span></label>
                    <select name="TrangThai" class="stu-form-control" required>
                        <option value="Dự thảo" {{ old('TrangThai', $resolution->TrangThai) === 'Dự thảo' ? 'selected' : '' }}>Dự thảo (DRAFT)</option>
                        <option value="Đã ký ban hành" {{ old('TrangThai', $resolution->TrangThai) === 'Đã ký ban hành' ? 'selected' : '' }}>Đã ký ban hành (SIGNED)</option>
                        <option value="Đã công bố hiệu lực" {{ old('TrangThai', $resolution->TrangThai) === 'Đã công bố hiệu lực' ? 'selected' : '' }}>Đã công bố hiệu lực (PUBLISHED)</option>
                    </select>
                </div>
            </div>

            <div class="stu-form-group stu-mb-4" style="margin-top: 12px;">
                <label class="stu-form-label" style="font-size: 13px; font-weight: 700;">Nội dung nghị quyết <span style="color: var(--color-danger);">*</span></label>
                <textarea name="NoiDung" rows="4" class="stu-form-control" required>{{ old('NoiDung', $resolution->NoiDung) }}</textarea>
            </div>

            {{-- Liên kết Hồ sơ trình & Nội dung biểu quyết --}}
            @include('pages.nghiquyet._link_evidence_fields', [
                'containerId' => 'editLinkEvidenceBox',
                'dossiers' => $dossiers,
                'votings' => $votings,
                'selectedDossierIds' => $linkedDossierIds,
                'selectedVotingIds' => $linkedVotingIds,
                'currentMeetingId' => old('MaPhienHop', $resolution->MaPhienHop),
            ])

            {{-- Đính kèm thêm văn bản / bản scan --}}
            <div class="stu-form-group" style="margin-bottom: 0;">
                <label class="stu-form-label" style="font-size: 12.5px; font-weight: 700;">
                    Đính kèm thêm văn bản (.pdf, .doc, .docx)
                </label>
                <input type="file" name="files[]" multiple class="stu-form-control" accept=".pdf,.doc,.docx" style="height: 38px; font-size: 12px; padding: 6px 12px;">
            </div>
        </form>

        <x-slot:footer>
            <button type="button" class="stu-btn stu-btn-ghost" data-modal-close>Hủy bỏ</button>
            <button type="submit" form="editResolutionForm" class="stu-btn stu-btn-primary" style="padding: 9px 22px; font-weight: 600;">
                Lưu cập nhật
            </button>
        </x-slot:footer>
    </x-hop-thoai>

    {{-- Modal Liên kết nhanh Hồ sơ trình & Biểu quyết (dùng cho mọi trạng thái) --}}
    <x-hop-thoai id="linkEvidenceModal" title="Liên kết Căn cứ Hồ sơ & Biểu quyết: {{ $resolution->SoNghiQuyet }}" size="lg">
        <form method="POST" action="{{ route('resolutions.links.update', $resolution->MaNghiQuyet) }}" id="linkEvidenceForm">
            @csrf
            @method('PUT')
            @include('pages.nghiquyet._link_evidence_fields', [
                'containerId' => 'quickLinkEvidenceBox',
                'dossiers' => $dossiers,
                'votings' => $votings,
                'selectedDossierIds' => $resolution->hoSoTrinh->pluck('MaHoSo')->all(),
                'selectedVotingIds' => $resolution->noiDungBieuQuyet->pluck('MaNoiDung')->all(),
                'currentMeetingId' => $resolution->MaPhienHop,
            ])
        </form>

        <x-slot:footer>
            <button type="button" class="stu-btn stu-btn-ghost" data-modal-close>Đóng</button>
            <button type="submit" form="linkEvidenceForm" class="stu-btn stu-btn-primary" style="padding: 9px 22px; font-weight: 600;">
                Lưu liên kết
            </button>
        </x-slot:footer>
    </x-hop-thoai>
@endif

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('edit') === '1' || urlParams.get('chinh_sua') === '1') {
            if (window.STUModal) {
                window.STUModal.open('editResolutionModal');
            }
        }
    });
</script>
@endsection
