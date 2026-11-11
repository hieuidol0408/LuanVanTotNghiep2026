@extends('layouts.app')

@section('title', 'Chi tiết Hồ sơ trình ' . $dossier->ma_ho_so_hien_thi)

@section('content')
<div class="stu-page-container">
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="['Hồ sơ trình' => route('submissions.index'), $dossier->ma_ho_so_hien_thi => null]" />

    {{-- Header --}}
    <div class="stu-page-header stu-flex stu-items-start stu-justify-between stu-mb-6" style="gap: 16px;">
        <div style="flex: 1;">
            <div class="stu-flex stu-items-center stu-gap-2 stu-mb-2">
                <span style="font-weight: 800; color: var(--color-primary); font-size: 14px; background: rgba(0, 43, 91, 0.08); padding: 3px 8px; border-radius: var(--radius-sm);">
                    {{ $dossier->ma_ho_so_hien_thi }}
                </span>
                <span class="stu-badge stu-badge-light">{{ $dossier->LinhVuc ?? 'Tổng hợp' }}</span>
                <x-status-badge :status="$dossier->TrangThai" />
            </div>
            <h1 class="stu-page-title" style="font-size: 22px; font-weight: 800; color: var(--color-primary); margin: 0; line-height: 1.4;">
                {{ $dossier->TieuDe }}
            </h1>
        </div>
        <div style="display: flex; align-items: center; gap: 10px;">
            @if(in_array($role, ['UNIT_REP', 'SECRETARY', 'CHAIR', 'ADMIN']))
                <a href="{{ route('submissions.edit', $dossier->MaHoSo) }}" class="stu-btn stu-btn-primary stu-btn-sm" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 600;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Chỉnh sửa hồ sơ</span>
                </a>
                <form method="POST" action="{{ route('submissions.destroy', $dossier->MaHoSo) }}" style="display: inline-flex; margin: 0;" onsubmit="return confirm('Xác nhận ẩn hồ sơ trình {{ $dossier->ma_ho_so_hien_thi }} khỏi danh sách hiển thị?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="stu-btn stu-btn-danger stu-btn-sm">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        <span>Xóa</span>
                    </button>
                </form>
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
                    <td><x-status-badge :status="$dossier->TrangThai" /></td>
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
                                        <a href="{{ asset($doc->DuongDan) }}" target="_blank" style="color: var(--color-primary); text-decoration: none;">
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
                                            <a href="{{ asset($doc->DuongDan) }}" target="_blank" class="stu-btn stu-btn-outline stu-btn-sm">
                                                <span>Xem file</span>
                                            </a>
                                            <a href="{{ asset($doc->DuongDan) }}" target="_blank" download class="stu-btn stu-btn-secondary stu-btn-sm">
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
    </div>
</div>
@endsection
