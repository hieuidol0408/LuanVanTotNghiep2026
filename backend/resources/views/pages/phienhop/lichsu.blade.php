@extends('layouts.chinh')

@section('title', 'Tra cứu Lịch sử Phiên họp - Hội đồng trường STU')

@section('header_title')
    <x-thanh-dieu-huong :items="[
        ['title' => 'Phiên họp & Hồ sơ', 'url' => route('meetings.index')],
        ['title' => 'Tra cứu lịch sử phiên họp']
    ]" />
@endsection

@section('content')
<!-- HEADER TRANG -->
<div class="stu-page-header">
    <div class="stu-page-title-group">
        <div style="display: flex; align-items: center; gap: 10px;">
            <h1>Tra cứu Lịch sử Phiên họp Hội đồng trường</h1>
            <span class="stu-badge stu-badge-info" style="font-weight: 600;">
                Lưu trữ số hóa
            </span>
        </div>
        <p>Hệ thống lưu trữ, tra cứu toàn văn thông tin các phiên họp, biên bản họp và nghị quyết ban hành qua các khóa.</p>
    </div>
    <div class="stu-page-actions">
        <x-nut-bam variant="outline" onclick="window.print()">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            <span>In danh sách</span>
        </x-nut-bam>
        <x-nut-bam variant="primary" href="{{ route('meetings.index') }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
            <span>Quản lý phiên họp</span>
        </x-nut-bam>
    </div>
</div>

<!-- KHỐI THỐNG KÊ LỊCH SỬ (CHUẨN THEO CÁC TRANG KHÁC) -->
<div class="stu-grid stu-grid-cols-4" style="margin-bottom: var(--space-6);">
    <x-the-thong-ke 
        title="Tổng phiên họp lưu trữ" 
        :value="$totalMeetings" 
        subtitle="Khóa IV (2020 - 2025)" 
        type="primary"
    >
        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
    </x-the-thong-ke>

    <x-the-thong-ke 
        title="Phiên đã hoàn tất" 
        :value="$completedMeetings" 
        subtitle="Đầy đủ biên bản số hóa" 
        type="success"
    >
        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    </x-the-thong-ke>

    <x-the-thong-ke 
        title="Nghị quyết ban hành" 
        :value="$totalResolutions" 
        subtitle="100% văn bản ký số" 
        type="info"
        link="{{ url('/resolutions') }}"
    >
        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
    </x-the-thong-ke>

    <x-the-thong-ke 
        title="Tỷ lệ chuyên cần HĐT" 
        :value="$avgAttendance . '%'" 
        subtitle="Thành viên chính thức" 
        type="warning"
    >
        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
    </x-the-thong-ke>
</div>

<!-- KHỐI TÌM KIẾM & BỘ LỌC ĐA TIÊU CHÍ (CHUẨN GIAO DIỆN HỆ THỐNG) -->
<div class="stu-card" style="padding: 18px 24px; margin-bottom: var(--space-6); border-radius: 12px; border: 1px solid var(--color-border); box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05); background: #ffffff;">
    <form method="GET" action="{{ route('meetings.history') }}">
        <!-- Hàng 1: Từ khóa, Khóa nhiệm kỳ, Năm, Loại phiên họp -->
        <div style="display: grid; grid-template-columns: 2.2fr 1.2fr 1.2fr 1.2fr; gap: 12px; margin-bottom: 12px;">
            <!-- Ô tìm kiếm từ khóa -->
            <div style="position: relative;">
                <svg width="15" height="15" fill="none" stroke="#94A3B8" stroke-width="2" viewBox="0 0 24 24" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); pointer-events: none;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <input 
                    type="text" 
                    name="search" 
                    placeholder="Nhập tên phiên họp, mã phiên, địa điểm, từ khóa..." 
                    value="{{ request('search') }}" 
                    class="stu-form-control"
                    style="padding-left: 36px; height: 38px;"
                >
            </div>

            <!-- Khóa / Nhiệm kỳ -->
            <div>
                <select name="term" class="stu-form-control" style="height: 38px;">
                    <option value="">-- Tất cả các khóa --</option>
                    <option value="Khóa IV" {{ request('term') == 'Khóa IV' || (!request()->has('term') && !request()->has('search')) ? 'selected' : '' }}>Khóa IV (2020 - 2025)</option>
                    <option value="Khóa III" {{ request('term') == 'Khóa III' ? 'selected' : '' }}>Khóa III (2015 - 2020)</option>
                    <option value="Khóa II" {{ request('term') == 'Khóa II' ? 'selected' : '' }}>Khóa II (2010 - 2015)</option>
                </select>
            </div>

            <!-- Năm tổ chức -->
            <div>
                <select name="year" class="stu-form-control" style="height: 38px;">
                    <option value="">-- Tất cả các năm --</option>
                    @foreach($years as $y)
                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>Năm {{ $y }}</option>
                    @endforeach
                    <option value="2025" {{ request('year') == '2025' ? 'selected' : '' }}>Năm 2025</option>
                    <option value="2024" {{ request('year') == '2024' ? 'selected' : '' }}>Năm 2024</option>
                </select>
            </div>

            <!-- Loại phiên họp -->
            <div>
                <select name="type" class="stu-form-control" style="height: 38px;">
                    <option value="">-- Loại phiên họp --</option>
                    <option value="thường kỳ" {{ request('type') == 'thường kỳ' ? 'selected' : '' }}>Phiên thường kỳ</option>
                    <option value="chuyên đề" {{ request('type') == 'chuyên đề' ? 'selected' : '' }}>Phiên chuyên đề</option>
                    <option value="bất thường" {{ request('type') == 'bất thường' ? 'selected' : '' }}>Phiên bất thường</option>
                </select>
            </div>
        </div>

        <!-- Hàng 2: Hình thức, Trạng thái, Khoảng ngày, Nút Tra cứu -->
        <div style="display: grid; grid-template-columns: 1.15fr 1.25fr 1.3fr 1.3fr auto auto; gap: 12px; align-items: center;">
            <!-- Hình thức -->
            <div>
                <select name="format" class="stu-form-control" style="height: 38px;">
                    <option value="">-- Hình thức họp --</option>
                    <option value="Trực tiếp" {{ request('format') == 'Trực tiếp' ? 'selected' : '' }}>Trực tiếp</option>
                    <option value="Trực tuyến" {{ request('format') == 'Trực tuyến' ? 'selected' : '' }}>Trực tuyến</option>
                    <option value="Kết hợp" {{ request('format') == 'Kết hợp' ? 'selected' : '' }}>Kết hợp</option>
                </select>
            </div>

            <!-- Trạng thái -->
            <div>
                <select name="status" class="stu-form-control" style="height: 38px;">
                    <option value="">-- Trạng thái phiên họp --</option>
                    <option value="Đã kết thúc" {{ request('status') == 'Đã kết thúc' ? 'selected' : '' }}>Đã kết thúc</option>
                    <option value="Sắp diễn ra" {{ request('status') == 'Sắp diễn ra' ? 'selected' : '' }}>Sắp diễn ra</option>
                    <option value="Đang chuẩn bị" {{ request('status') == 'Đang chuẩn bị' ? 'selected' : '' }}>Đang chuẩn bị</option>
                    <option value="Đang diễn ra" {{ request('status') == 'Đang diễn ra' ? 'selected' : '' }}>Đang diễn ra</option>
                </select>
            </div>

            <!-- Từ ngày -->
            <div style="display: flex; align-items: stretch; border: 1px solid var(--color-border); border-radius: 8px; background: #fff; height: 38px; overflow: hidden; transition: border-color 0.15s, box-shadow 0.15s;" onfocusin="this.style.borderColor='var(--color-primary)'; this.style.boxShadow='0 0 0 3px rgba(0, 58, 140, 0.1)';" onfocusout="this.style.borderColor='var(--color-border)'; this.style.boxShadow='none';">
                <span style="background: #f8fafc; border-right: 1px solid var(--color-border); padding: 0 10px; display: flex; align-items: center; font-size: 12px; font-weight: 700; color: #475569; white-space: nowrap; user-select: none;">
                    Từ ngày
                </span>
                <input 
                    type="date" 
                    name="from_date" 
                    value="{{ request('from_date') }}" 
                    style="border: none; outline: none; padding: 0 8px; width: 100%; font-size: 13px; color: var(--color-text-main); background: transparent; height: 100%; font-family: inherit;"
                    title="Từ ngày"
                >
            </div>

            <!-- Đến ngày -->
            <div style="display: flex; align-items: stretch; border: 1px solid var(--color-border); border-radius: 8px; background: #fff; height: 38px; overflow: hidden; transition: border-color 0.15s, box-shadow 0.15s;" onfocusin="this.style.borderColor='var(--color-primary)'; this.style.boxShadow='0 0 0 3px rgba(0, 58, 140, 0.1)';" onfocusout="this.style.borderColor='var(--color-border)'; this.style.boxShadow='none';">
                <span style="background: #f8fafc; border-right: 1px solid var(--color-border); padding: 0 10px; display: flex; align-items: center; font-size: 12px; font-weight: 700; color: #475569; white-space: nowrap; user-select: none;">
                    Đến ngày
                </span>
                <input 
                    type="date" 
                    name="to_date" 
                    value="{{ request('to_date') }}" 
                    style="border: none; outline: none; padding: 0 8px; width: 100%; font-size: 13px; color: var(--color-text-main); background: transparent; height: 100%; font-family: inherit;"
                    title="Đến ngày"
                >
            </div>

            <!-- Nút Tra cứu -->
            <div>
                <button type="submit" class="stu-btn stu-btn-primary" style="height: 38px; padding: 0 20px; font-weight: 600; white-space: nowrap; box-shadow: 0 1px 2px 0 rgba(0, 58, 140, 0.2);">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span>Tra cứu</span>
                </button>
            </div>

            <!-- Nút Đặt lại -->
            <div>
                @if(request()->anyFilled(['search', 'term', 'year', 'type', 'format', 'status', 'from_date', 'to_date']))
                    <a href="{{ route('meetings.history') }}" class="stu-btn stu-btn-ghost" style="height: 38px; padding: 0 14px;" title="Đặt lại bộ lọc">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>Đặt lại</span>
                    </a>
                @endif
            </div>
        </div>
    </form>
</div>

<!-- BẢNG KẾT QUẢ TRA CỨU LỊCH SỬ PHIÊN HỌP (DÙNG COMPONENT CHUẨN X-BANG-DU-LIEU) -->
<x-bang-du-lieu 
    :headers="['Mã', 'Thông tin phiên họp & Khóa', 'Thời gian tổ chức', 'Địa điểm & Hình thức', 'Chủ trì / Người tạo', 'Kết quả phiên họp', 'Trạng thái']"
    :hasActions="true"
    actionTitle="Thao tác"
    :empty="$meetings->isEmpty()"
    emptyTitle="Không tìm thấy phiên họp phù hợp"
    emptyMessage="Vui lòng thử lại với từ khóa tìm kiếm hoặc điều kiện lọc khác."
>
    @foreach($meetings as $meeting)
        <tr>
            <!-- Cột Mã -->
            <td style="font-weight: 700; color: var(--color-primary); white-space: nowrap;">
                #{{ $meeting->MaPhienHop }}
            </td>

            <!-- Cột Thông tin phiên họp -->
            <td class="stu-max-w-xs">
                <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 3px;">
                    <span class="stu-badge stu-badge-neutral" style="font-size: 10px; padding: 1px 6px; font-weight: 600;">
                        {{ str_contains($meeting->TenPhienHop, 'Khóa') ? 'Khóa IV' : 'Nhiệm kỳ 2020-2025' }}
                    </span>
                    @if(str_contains(mb_strtolower($meeting->TenPhienHop), 'bất thường'))
                        <span class="stu-badge stu-badge-warning" style="font-size: 9.5px; padding: 1px 5px;">Bất thường</span>
                    @elseif(str_contains(mb_strtolower($meeting->TenPhienHop), 'chuyên đề'))
                        <span class="stu-badge stu-badge-info" style="font-size: 9.5px; padding: 1px 5px;">Chuyên đề</span>
                    @else
                        <span class="stu-badge stu-badge-success" style="font-size: 9.5px; padding: 1px 5px;">Thường kỳ</span>
                    @endif
                </div>
                <a href="{{ route('meetings.show', $meeting->MaPhienHop) }}" style="font-weight: 600; color: var(--color-primary); display: block;" title="{{ $meeting->TenPhienHop }}">
                    {{ $meeting->TenPhienHop }}
                </a>
                @if($meeting->GhiChu)
                    <div class="stu-truncate" style="font-size: 11px; color: var(--color-text-muted); margin-top: 2px;" title="{{ $meeting->GhiChu }}">
                        Ghi chú: {{ $meeting->GhiChu }}
                    </div>
                @endif
            </td>

            <!-- Cột Thời gian -->
            <td style="white-space: nowrap;">
                <div style="font-weight: 600; color: var(--color-text-main);">
                    {{ $meeting->ThoiGianBatDau ? date('d/m/Y', strtotime($meeting->ThoiGianBatDau)) : ($meeting->NgayHop ? date('d/m/Y', strtotime($meeting->NgayHop)) : '—') }}
                </div>
                <div style="font-size: 11px; color: var(--color-text-muted);">
                    {{ $meeting->ThoiGianBatDau ? date('H:i', strtotime($meeting->ThoiGianBatDau)) : '08:30' }} - 
                    {{ $meeting->ThoiGianKetThuc ? date('H:i', strtotime($meeting->ThoiGianKetThuc)) : '11:30' }}
                </div>
            </td>

            <!-- Cột Địa điểm & Hình thức -->
            <td style="font-size: var(--font-size-xs);">
                <div style="font-weight: 500;">{{ $meeting->DiaDiem ?: 'Phòng họp Hội đồng trường' }}</div>
                <span class="stu-badge stu-badge-info" style="margin-top: 4px; padding: 2px 6px;">
                    {{ $meeting->HinhThuc ?: 'Trực tiếp' }}
                </span>
            </td>

            <!-- Cột Chủ trì / Người tạo -->
            <td style="font-size: var(--font-size-xs); white-space: nowrap;">
                <div style="font-weight: 600; color: var(--color-text-main);">
                    {{ $meeting->nguoiDung?->HoTen ?? 'PGS.TS. Trần Văn Chủ Tịch' }}
                </div>
                <div style="font-size: 11px; color: var(--color-text-muted);">
                    Chủ tịch Hội đồng trường
                </div>
            </td>

            <!-- Cột Kết quả phiên họp -->
            <td style="font-size: var(--font-size-xs); white-space: nowrap;">
                <div style="font-weight: 600; color: #059669; display: flex; align-items: center; gap: 4px;">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <span><strong>{{ $meeting->nghi_quyet_count ?? ($meeting->nghiQuyet?->count() ?: 1) }}</strong> Nghị quyết</span>
                </div>
                <div style="color: var(--color-text-muted); font-size: 11px; margin-top: 2px;">
                    {{ $meeting->chuong_trinh_hop_count ?? 3 }} hồ sơ đã duyệt
                </div>
            </td>

            <!-- Cột Trạng thái -->
            <td style="white-space: nowrap;">
                <x-nhan-trang-thai :status="$meeting->TrangThai" />
            </td>

            <!-- Cột Thao tác -->
            <td class="col-actions">
                <div class="stu-table-actions-group">
                    <x-nut-bam variant="outline" size="sm" href="{{ route('meetings.show', $meeting->MaPhienHop) }}">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <span>Chi tiết</span>
                    </x-nut-bam>
                </div>
            </td>
        </tr>
    @endforeach
</x-bang-du-lieu>

<!-- PHÂN TRANG -->
<div class="stu-mt-6" style="width: 100%;">
    {{ $meetings->links() }}
</div>
@endsection
