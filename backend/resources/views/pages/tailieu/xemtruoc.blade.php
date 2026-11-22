@extends('layouts.chinh')

@section('title', 'Xem tài liệu: ' . $doc->TenTaiLieu)

@push('styles')
<style>
    .doc-preview-container {
        display: flex;
        flex-direction: column;
        height: calc(100vh - 120px);
        background: #f8fafc;
        border-radius: var(--radius-lg, 12px);
        border: 1px solid var(--color-border);
        overflow: hidden;
    }
    .doc-preview-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 20px;
        background: #ffffff;
        border-bottom: 1px solid var(--color-border);
        flex-wrap: wrap;
        gap: 12px;
    }
    .doc-preview-body {
        flex: 1;
        overflow-y: auto;
        padding: 24px;
        display: flex;
        justify-content: center;
        background: #e2e8f0;
    }
    .doc-a4-sheet {
        background: #ffffff;
        width: 100%;
        max-width: 900px;
        min-height: 1000px;
        padding: 48px 56px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        border-radius: 4px;
        color: #1e293b;
        font-family: 'Times New Roman', 'Inter', serif;
        font-size: 15px;
        line-height: 1.65;
    }
    .docx-preview-content p {
        margin-bottom: 8px;
    }
    .docx-table {
        width: 100%;
        border-collapse: collapse;
        margin: 16px 0;
    }
    .docx-table th, .docx-table td {
        border: 1px solid #cbd5e1;
        padding: 8px 12px;
        font-size: 13.5px;
    }
    .docx-table th {
        background-color: #f1f5f9;
        font-weight: 700;
    }
    .xlsx-sheet-tab {
        padding: 8px 16px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        border: 1px solid #cbd5e1;
        background: #f8fafc;
        border-radius: 6px 6px 0 0;
        margin-right: 4px;
    }
    .xlsx-sheet-tab.active {
        background: #ffffff;
        border-bottom-color: #ffffff;
        color: var(--color-primary);
    }
    .pptx-slide-card {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 16px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    @media print {
        .stu-sidebar, .stu-header, .doc-preview-header, .stu-footer {
            display: none !important;
        }
        .doc-preview-container {
            border: none;
            height: auto;
            background: transparent;
        }
        .doc-preview-body {
            background: transparent;
            padding: 0;
        }
        .doc-a4-sheet {
            box-shadow: none;
            padding: 0;
            max-width: 100%;
        }
    }
</style>
@endpush

@section('content')
<div class="stu-mb-4" style="display: flex; align-items: center; justify-content: space-between;">
    <div style="display: flex; align-items: center; gap: 10px;">
        <a href="{{ $backUrl }}" onclick="handleDocBack(event)" class="stu-btn stu-btn-secondary stu-btn-sm" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 600;" title="Quay lại">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Quay lại</span>
        </a>
        <h1 style="font-size: 18px; font-weight: 700; color: var(--color-text-main); margin: 0;">
            Trình xem văn bản trực tiếp
        </h1>
    </div>
</div>

<div class="doc-preview-container">
    <!-- Header của trình xem -->
    <div class="doc-preview-header">
        <div style="display: flex; align-items: center; gap: 12px; min-width: 0;">
            @php
                $extUpper = strtoupper($ext);
                $badgeBg = match($ext) {
                    'docx', 'doc' => '#2563eb',
                    'xlsx', 'xls' => '#16a34a',
                    'pptx', 'ppt' => '#ea580c',
                    'pdf' => '#dc2626',
                    default => '#64748b'
                };
            @endphp
            <span style="background: {{ $badgeBg }}; color: #ffffff; font-size: 11px; font-weight: 800; padding: 4px 8px; border-radius: 4px;">
                {{ $extUpper }}
            </span>
            <div style="min-width: 0;">
                <div style="font-weight: 700; font-size: 14px; color: var(--color-text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 500px;" title="{{ $doc->TenTaiLieu }}">
                    {{ $doc->TenTaiLieu }}
                </div>
                <div style="font-size: 11.5px; color: var(--color-text-muted);">
                    Phân loại: <strong>{{ $doc->LoaiTaiLieu ?? 'Văn bản' }}</strong> &bull;
                    Dung lượng: {{ $doc->DungLuong ? number_format($doc->DungLuong / 1024, 1) . ' KB' : '—' }} &bull;
                    Ngày tải lên: {{ $doc->NgayTaiLen ? \Carbon\Carbon::parse($doc->NgayTaiLen)->format('d/m/Y H:i') : '—' }}
                </div>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 8px;">
            <button type="button" onclick="window.print()" class="stu-btn stu-btn-outline stu-btn-sm" title="In văn bản">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>In / Xuất PDF</span>
            </button>
            <a href="{{ route('documents.download', $doc->MaTaiLieu) }}" class="stu-btn stu-btn-primary stu-btn-sm">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Tải về máy</span>
            </a>
            <a href="{{ $backUrl }}" onclick="handleDocBack(event)" class="stu-btn stu-btn-secondary stu-btn-sm" style="display: inline-flex; align-items: center; gap: 4px;" title="Đóng trình xem">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                <span>Đóng</span>
            </a>
        </div>
    </div>

    <!-- Body của trình xem theo định dạng -->
    <div class="doc-preview-body">
        @if($ext === 'pdf')
            {{-- Định dạng PDF: Dùng iframe stream inline --}}
            <iframe 
                src="{{ route('documents.view', $doc->MaTaiLieu) }}" 
                style="width: 100%; height: 100%; border: none; border-radius: 4px; background: #ffffff;"
                title="{{ $doc->TenTaiLieu }}"
            ></iframe>
        @elseif(in_array($ext, ['docx', 'doc']))
            {{-- Định dạng Word (.docx): Render trang A4 chuẩn --}}
            <div class="doc-a4-sheet">
                {!! $previewHtml !!}
            </div>
        @elseif(in_array($ext, ['xlsx', 'xls']))
            {{-- Định dạng Excel (.xlsx): Render bảng đa sheets --}}
            <div style="width: 100%; max-width: 1100px; background: #ffffff; padding: 24px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.06);">
                @if(!empty($sheets))
                    <div style="display: flex; border-bottom: 2px solid #cbd5e1; margin-bottom: 16px;">
                        @foreach($sheets as $idx => $s)
                            <div class="xlsx-sheet-tab {{ $idx === 0 ? 'active' : '' }}" onclick="switchSheet({{ $idx }})">
                                {{ $s['name'] }}
                            </div>
                        @endforeach
                    </div>
                    @foreach($sheets as $idx => $s)
                        <div id="sheet_pane_{{ $idx }}" class="xlsx-sheet-pane" style="{{ $idx !== 0 ? 'display: none;' : '' }}">
                            {!! $s['html'] !!}
                        </div>
                    @endforeach
                @else
                    <div style="text-align: center; padding: 32px; color: #64748b;">Không có dữ liệu bảng tính trong tệp này.</div>
                @endif
            </div>
        @elseif(in_array($ext, ['pptx', 'ppt']))
            {{-- Định dạng PowerPoint (.pptx): Render các Slide --}}
            <div style="width: 100%; max-width: 900px;">
                @if(!empty($slides))
                    @foreach($slides as $sl)
                        <div class="pptx-slide-card">
                            <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; margin-bottom: 12px;">
                                <span style="background: #ea580c; color: #fff; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 4px;">Slide {{ $sl['index'] }}</span>
                                <strong style="font-size: 15px; color: #0f172a;">{{ $sl['title'] }}</strong>
                            </div>
                            <ul style="margin: 0; padding-left: 20px; line-height: 1.6; color: #334155;">
                                @foreach($sl['content'] as $line)
                                    <li>{{ $line }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                @else
                    <div style="text-align: center; padding: 32px; background: #fff; border-radius: 8px; color: #64748b;">Không trích xuất được nội dung slide.</div>
                @endif
            </div>
        @elseif(in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg']))
            {{-- Định dạng hình ảnh --}}
            <div style="display: flex; align-items: center; justify-content: center; width: 100%;">
                <img src="{{ route('documents.view', $doc->MaTaiLieu) }}" alt="{{ $doc->TenTaiLieu }}" style="max-width: 100%; max-height: 80vh; object-fit: contain; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
            </div>
        @else
            {{-- Fallback --}}
            <div class="doc-a4-sheet">
                {!! $previewHtml !!}
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    function handleDocBack(e) {
        // 1. Nếu mở trong tab/cửa sổ popup có opener
        if (window.opener && !window.opener.closed) {
            e.preventDefault();
            window.close();
            return;
        }
        // 2. Nếu trong tab có lịch sử duyệt trang trước đó thực sự
        if (window.history.length > 1 && document.referrer && document.referrer !== window.location.href) {
            e.preventDefault();
            window.history.back();
            return;
        }
        // 3. Nếu mở trực tiếp trong tab mới mà window.close bị chặn bởi trình duyệt
        // Trình duyệt sẽ tự động điều hướng theo liên kết href="{{ $backUrl }}"
    }

    function switchSheet(index) {
        document.querySelectorAll('.xlsx-sheet-tab').forEach((tab, i) => {
            tab.classList.toggle('active', i === index);
        });
        document.querySelectorAll('.xlsx-sheet-pane').forEach((pane, i) => {
            pane.style.display = (i === index) ? 'block' : 'none';
        });
    }
</script>
@endpush
@endsection
