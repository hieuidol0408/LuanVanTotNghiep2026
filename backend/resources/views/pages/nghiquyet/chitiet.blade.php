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
    <div class="stu-page-header stu-flex stu-items-start stu-justify-between stu-mb-6" style="gap: 16px;">
        <div style="flex: 1;">
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
                <span><strong>Phiên họp:</strong> {{ $resolution->phienHop?->TenPhienHop ?? 'Phiên họp #' . $resolution->MaPhienHop }}</span>
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
                <a href="{{ route('resolutions.edit', $resolution->MaNghiQuyet) }}" class="stu-btn stu-btn-ghost" style="height: 38px;">
                    Sửa dự thảo
                </a>
            @endif

            <a href="{{ route('resolutions.index') }}" class="stu-btn stu-btn-ghost" style="height: 38px;">
                Quay lại
            </a>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <x-canh-bao type="success" :dismissible="true" class="stu-mb-6">
            {{ session('success') }}
        </x-canh-bao>
    @endif
    @if(session('error'))
        <x-canh-bao type="danger" :dismissible="true" class="stu-mb-6">
            {{ session('error') }}
        </x-canh-bao>
    @endif

    {{-- Bố cục 2 Cột --}}
    <div class="stu-grid" style="grid-template-columns: 2fr 1fr; gap: 24px; align-items: flex-start;">
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
            <div class="stu-card" style="padding: 24px;">
                <h3 style="font-size: 16px; font-weight: 700; color: var(--color-primary); margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                    <span>Văn bản Nghị quyết bản scan có chữ ký & đóng dấu ({{ $resolution->taiLieu->count() }})</span>
                </h3>

                @if($resolution->taiLieu->count() > 0)
                    <div class="stu-table-container">
                        <table class="stu-table">
                            <thead>
                                <tr>
                                    <th>Tên tài liệu</th>
                                    <th>Định dạng</th>
                                    <th>Dung lượng</th>
                                    <th>Ngày tải lên</th>
                                    <th style="text-align: right;">Tải về</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($resolution->taiLieu as $doc)
                                    <tr>
                                        <td style="font-weight: 600; color: var(--color-text-primary);">
                                            {{ $doc->TenTaiLieu }}
                                        </td>
                                        <td style="font-size: 12px; text-transform: uppercase;">
                                            <span class="stu-badge stu-badge-light">{{ $doc->DinhDang ?? 'PDF' }}</span>
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
                                                <span>Tải file</span>
                                            </a>
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
                    <div class="stu-table-container">
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
            <div class="stu-card" style="padding: 22px;">
                <h4 style="font-size: 14px; font-weight: 800; color: var(--color-primary); margin: 0 0 12px 0;">
                    Căn cứ Hồ sơ tờ trình ({{ $resolution->hoSoTrinh->count() }})
                </h4>
                @forelse($resolution->hoSoTrinh as $hs)
                    <div style="padding: 8px 0; border-bottom: 1px solid var(--color-border); font-size: 13px;">
                        <a href="{{ route('submissions.show', $hs->MaHoSo) }}" style="font-weight: 600; color: var(--color-text-primary); text-decoration: none;">
                            #HS-{{ str_pad($hs->MaHoSo, 3, '0', STR_PAD_LEFT) }}: {{ $hs->TieuDe }}
                        </a>
                        <div style="font-size: 11px; color: var(--color-text-muted); margin-top: 2px;">
                            Đơn vị trình: {{ $hs->donVi?->TenDonVi }}
                        </div>
                    </div>
                @empty
                    <div style="font-size: 12px; color: var(--color-text-muted);">Không liên kết trực tiếp hồ sơ tờ trình.</div>
                @endforelse
            </div>

            {{-- Căn cứ Kết quả Biểu quyết --}}
            <div class="stu-card" style="padding: 22px;">
                <h4 style="font-size: 14px; font-weight: 800; color: var(--color-primary); margin: 0 0 12px 0;">
                    Căn cứ Kết quả Biểu quyết ({{ $resolution->noiDungBieuQuyet->count() }})
                </h4>
                @forelse($resolution->noiDungBieuQuyet as $bq)
                    <div style="padding: 8px 0; border-bottom: 1px solid var(--color-border); font-size: 13px;">
                        <a href="{{ route('votes.show', $bq->MaNoiDung) }}" style="font-weight: 600; color: var(--color-text-primary); text-decoration: none;">
                            #BQ-{{ str_pad($bq->MaNoiDung, 3, '0', STR_PAD_LEFT) }}: {{ $bq->NoiDung }}
                        </a>
                        <div style="margin-top: 4px;">
                            <span class="stu-badge stu-badge-success">Đã thông qua</span>
                        </div>
                    </div>
                @empty
                    <div style="font-size: 12px; color: var(--color-text-muted);">Không liên kết biểu quyết kín riêng.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
