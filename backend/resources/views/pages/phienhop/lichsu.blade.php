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
<div class="stu-page-header" style="margin-bottom: var(--space-4);">
    <div class="stu-page-title-group">
        <div style="display: flex; align-items: center; gap: 10px;">
            <h1 style="font-size: 20px; font-weight: 700; color: var(--color-text-main); margin: 0;">
                Tra cứu Lịch sử Phiên họp Hội đồng trường
            </h1>
            <span class="stu-badge stu-badge-info" style="font-size: 11px; font-weight: 600;">
                Lưu trữ số hóa
            </span>
        </div>
        <p style="font-size: 12.5px; color: var(--color-text-muted); margin-top: 4px;">
            Hệ thống lưu trữ, tra cứu toàn văn thông tin các phiên họp, biên bản họp và nghị quyết ban hành qua các nhiệm kỳ Hội đồng trường STU.
        </p>
    </div>
    <div class="stu-page-actions" style="display: flex; gap: 8px;">
        <button type="button" class="stu-btn stu-btn-outline stu-btn-sm" onclick="window.print()" style="font-size: 12px; height: 34px;">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            <span>In danh sách</span>
        </button>
        <x-nut-bam variant="primary" size="sm" href="{{ route('meetings.index') }}" style="font-size: 12px; height: 34px;">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
            <span>Quản lý phiên họp</span>
        </x-nut-bam>
    </div>
</div>

<!-- KHỐI THỐNG KÊ LỊCH SỬ (KPI CHIPS) -->
<div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: var(--space-4);">
    <div class="stu-card" style="padding: 14px 18px; border-radius: 10px; background: #ffffff; border: 1px solid var(--color-border); display: flex; align-items: center; gap: 14px;">
        <div style="width: 42px; height: 42px; border-radius: 8px; background: rgba(0, 58, 140, 0.08); display: flex; align-items: center; justify-content: center; color: var(--color-primary); flex-shrink: 0;">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        </div>
        <div>
            <div style="font-size: 11px; font-weight: 600; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.03em;">Tổng phiên họp lưu trữ</div>
            <div style="font-size: 20px; font-weight: 700; color: var(--color-primary); line-height: 1.2;">{{ $totalMeetings }} <span style="font-size: 12px; font-weight: 500; color: var(--color-text-muted);">phiên</span></div>
            <div style="font-size: 10.5px; color: var(--color-text-muted);">Khóa IV (2020 - 2025)</div>
        </div>
    </div>

    <div class="stu-card" style="padding: 14px 18px; border-radius: 10px; background: #ffffff; border: 1px solid var(--color-border); display: flex; align-items: center; gap: 14px;">
        <div style="width: 42px; height: 42px; border-radius: 8px; background: rgba(16, 185, 129, 0.1); display: flex; align-items: center; justify-content: center; color: #059669; flex-shrink: 0;">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <div style="font-size: 11px; font-weight: 600; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.03em;">Phiên đã hoàn tất</div>
            <div style="font-size: 20px; font-weight: 700; color: #059669; line-height: 1.2;">{{ $completedMeetings }} <span style="font-size: 12px; font-weight: 500; color: var(--color-text-muted);">phiên</span></div>
            <div style="font-size: 10.5px; color: var(--color-text-muted);">Đầy đủ biên bản số hóa</div>
        </div>
    </div>

    <div class="stu-card" style="padding: 14px 18px; border-radius: 10px; background: #ffffff; border: 1px solid var(--color-border); display: flex; align-items: center; gap: 14px;">
        <div style="width: 42px; height: 42px; border-radius: 8px; background: rgba(59, 130, 246, 0.1); display: flex; align-items: center; justify-content: center; color: #2563eb; flex-shrink: 0;">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        </div>
        <div>
            <div style="font-size: 11px; font-weight: 600; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.03em;">Nghị quyết ban hành</div>
            <div style="font-size: 20px; font-weight: 700; color: #2563eb; line-height: 1.2;">{{ $totalResolutions }} <span style="font-size: 12px; font-weight: 500; color: var(--color-text-muted);">văn bản</span></div>
            <div style="font-size: 10.5px; color: var(--color-text-muted);">100% ký số điện tử</div>
        </div>
    </div>

    <div class="stu-card" style="padding: 14px 18px; border-radius: 10px; background: #ffffff; border: 1px solid var(--color-border); display: flex; align-items: center; gap: 14px;">
        <div style="width: 42px; height: 42px; border-radius: 8px; background: rgba(245, 158, 11, 0.1); display: flex; align-items: center; justify-content: center; color: #d97706; flex-shrink: 0;">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
        </div>
        <div>
            <div style="font-size: 11px; font-weight: 600; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.03em;">Tỷ lệ chuyên cần HĐT</div>
            <div style="font-size: 20px; font-weight: 700; color: #d97706; line-height: 1.2;">{{ $avgAttendance }}%</div>
            <div style="font-size: 10.5px; color: var(--color-text-muted);">Thành viên chính thức</div>
        </div>
    </div>
</div>

<!-- KHỐI TÌM KIẾM & BỘ LỌC NÂNG CAO (TRA CỨU TOÀN DIỆN) -->
<div class="stu-card" style="padding: 16px 20px; margin-bottom: var(--space-4); border-radius: 10px; border: 1px solid var(--color-border); background: #ffffff;">
    <form method="GET" action="{{ route('meetings.history') }}">
        <!-- Hàng 1: Từ khóa, Khóa nhiệm kỳ, Năm, Loại phiên họp -->
        <div style="display: grid; grid-template-columns: 2.5fr 1.3fr 1fr 1.2fr; gap: 10px; margin-bottom: 10px;">
            <!-- Ô tìm kiếm từ khóa -->
            <div style="position: relative;">
                <svg width="15" height="15" fill="none" stroke="#94A3B8" stroke-width="2" viewBox="0 0 24 24" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); pointer-events: none;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <input 
                    type="text" 
                    name="search" 
                    placeholder="Nhập tên phiên họp, mã phiên, địa điểm, từ khóa nội dung..." 
                    value="{{ request('search') }}" 
                    class="stu-form-control"
                    style="padding-left: 36px; height: 36px; font-size: 12px;"
                >
            </div>

            <!-- Khóa / Nhiệm kỳ -->
            <div>
                <select name="term" class="stu-form-control" style="height: 36px; font-size: 12px;">
                    <option value="">-- Tất cả khóa --</option>
                    <option value="Khóa IV" {{ request('term') == 'Khóa IV' || (!request()->has('term') && !request()->has('search')) ? 'selected' : '' }}>Khóa IV (2020 - 2025)</option>
                    <option value="Khóa III" {{ request('term') == 'Khóa III' ? 'selected' : '' }}>Khóa III (2015 - 2020)</option>
                    <option value="Khóa II" {{ request('term') == 'Khóa II' ? 'selected' : '' }}>Khóa II (2010 - 2015)</option>
                </select>
            </div>

            <!-- Năm tổ chức -->
            <div>
                <select name="year" class="stu-form-control" style="height: 36px; font-size: 12px;">
                    <option value="">-- Tất cả năm --</option>
                    @foreach($years as $y)
                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>Năm {{ $y }}</option>
                    @endforeach
                    <option value="2025" {{ request('year') == '2025' ? 'selected' : '' }}>Năm 2025</option>
                    <option value="2024" {{ request('year') == '2024' ? 'selected' : '' }}>Năm 2024</option>
                </select>
            </div>

            <!-- Loại phiên họp -->
            <div>
                <select name="type" class="stu-form-control" style="height: 36px; font-size: 12px;">
                    <option value="">-- Loại phiên họp --</option>
                    <option value="thường kỳ" {{ request('type') == 'thường kỳ' ? 'selected' : '' }}>Phiên thường kỳ</option>
                    <option value="chuyên đề" {{ request('type') == 'chuyên đề' ? 'selected' : '' }}>Phiên chuyên đề</option>
                    <option value="bất thường" {{ request('type') == 'bất thường' ? 'selected' : '' }}>Phiên bất thường</option>
                </select>
            </div>
        </div>

        <!-- Hàng 2: Hình thức, Trạng thái, Khoảng ngày, Nút Tra cứu -->
        <div style="display: grid; grid-template-columns: 1.2fr 1.3fr 1.2fr 1.2fr auto auto; gap: 10px; align-items: center;">
            <!-- Hình thức -->
            <div>
                <select name="format" class="stu-form-control" style="height: 36px; font-size: 12px;">
                    <option value="">-- Hình thức họp --</option>
                    <option value="Trực tiếp" {{ request('format') == 'Trực tiếp' ? 'selected' : '' }}>Trực tiếp</option>
                    <option value="Trực tuyến" {{ request('format') == 'Trực tuyến' ? 'selected' : '' }}>Trực tuyến</option>
                    <option value="Kết hợp" {{ request('format') == 'Kết hợp' ? 'selected' : '' }}>Kết hợp</option>
                </select>
            </div>

            <!-- Trạng thái -->
            <div>
                <select name="status" class="stu-form-control" style="height: 36px; font-size: 12px;">
                    <option value="">-- Trạng thái phiên họp --</option>
                    <option value="Đã kết thúc" {{ request('status') == 'Đã kết thúc' ? 'selected' : '' }}>Đã kết thúc</option>
                    <option value="Sắp diễn ra" {{ request('status') == 'Sắp diễn ra' ? 'selected' : '' }}>Sắp diễn ra</option>
                    <option value="Đang chuẩn bị" {{ request('status') == 'Đang chuẩn bị' ? 'selected' : '' }}>Đang chuẩn bị</option>
                    <option value="Đang diễn ra" {{ request('status') == 'Đang diễn ra' ? 'selected' : '' }}>Đang diễn ra</option>
                </select>
            </div>

            <!-- Từ ngày -->
            <div>
                <input 
                    type="date" 
                    name="from_date" 
                    value="{{ request('from_date') }}" 
                    class="stu-form-control"
                    style="height: 36px; font-size: 12px;"
                    title="Từ ngày"
                >
            </div>

            <!-- Đến ngày -->
            <div>
                <input 
                    type="date" 
                    name="to_date" 
                    value="{{ request('to_date') }}" 
                    class="stu-form-control"
                    style="height: 36px; font-size: 12px;"
                    title="Đến ngày"
                >
            </div>

            <!-- Nút Tra cứu -->
            <div>
                <button type="submit" class="stu-btn stu-btn-primary" style="height: 36px; padding: 0 16px; font-size: 12px; font-weight: 600; white-space: nowrap;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span>Tra cứu</span>
                </button>
            </div>

            <!-- Nút Đặt lại -->
            <div>
                @if(request()->anyFilled(['search', 'term', 'year', 'type', 'format', 'status', 'from_date', 'to_date']))
                    <a href="{{ route('meetings.history') }}" class="stu-btn stu-btn-ghost" style="height: 36px; padding: 0 12px; font-size: 12px;" title="Đặt lại bộ lọc">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>Đặt lại</span>
                    </a>
                @endif
            </div>
        </div>
    </form>
</div>

<!-- BẢNG KẾT QUẢ TRA CỨU LỊCH SỬ PHIÊN HỌP -->
<div class="stu-card" style="border-radius: 10px; border: 1px solid var(--color-border); overflow: hidden; background: #ffffff;">
    <div style="padding: 12px 18px; border-bottom: 1px solid var(--color-border); display: flex; justify-content: space-between; align-items: center; background: #fafbfc;">
        <div style="font-size: 12.5px; font-weight: 600; color: var(--color-text-main);">
            Kết quả tra cứu: <span style="color: var(--color-primary); font-weight: 700;">{{ $meetings->total() }}</span> phiên họp phù hợp
        </div>
        <div style="font-size: 11.5px; color: var(--color-text-muted);">
            Sắp xếp theo: <span style="font-weight: 600; color: var(--color-text-main);">Mới nhất trước</span>
        </div>
    </div>

    @if($meetings->isEmpty())
        <div class="stu-empty-state" style="padding: 36px 20px;">
            <svg width="48" height="48" fill="none" stroke="#94A3B8" stroke-width="1.5" viewBox="0 0 24 24" style="margin: 0 auto 12px auto; display: block;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <div class="stu-empty-title" style="font-size: 13.5px; font-weight: 600;">Không tìm thấy phiên họp phù hợp</div>
            <div class="stu-empty-desc" style="font-size: 12px; color: var(--color-text-muted); margin-top: 4px;">Vui lòng thử lại với từ khóa tìm kiếm hoặc điều kiện lọc khác.</div>
        </div>
    @else
        <div style="overflow-x: auto;">
            <table class="dashboard-table" style="width: 100%; border-collapse: separate; border-spacing: 0;">
                <thead>
                    <tr style="background: #f8fafc;">
                        <th style="width: 55px; text-align: center; font-size: 11px; padding: 10px 8px;">Mã</th>
                        <th style="font-size: 11px; padding: 10px 12px;">Thông tin phiên họp & Khóa</th>
                        <th style="width: 140px; font-size: 11px; padding: 10px 10px;">Thời gian tổ chức</th>
                        <th style="width: 180px; font-size: 11px; padding: 10px 10px;">Địa điểm & Hình thức</th>
                        <th style="width: 150px; font-size: 11px; padding: 10px 10px;">Chủ trì / Người tạo</th>
                        <th style="width: 130px; font-size: 11px; padding: 10px 10px;">Kết quả phiên họp</th>
                        <th style="width: 130px; font-size: 11px; padding: 10px 10px; white-space: nowrap;">Trạng thái</th>
                        <th style="width: 105px; text-align: center; font-size: 11px; padding: 10px 8px; white-space: nowrap;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($meetings as $meeting)
                        @php
                            $isCompleted = in_array(strtoupper(trim((string)$meeting->TrangThai)), ['ĐÃ KẾT THÚC', 'ĐÃ DIỄN RA', 'COMPLETED', 'HOÀN THÀNH']);
                        @endphp
                        <tr style="border-bottom: 1px solid var(--color-border);">
                            <!-- Cột Mã -->
                            <td style="text-align: center; font-weight: 700; color: var(--color-primary); font-size: 12px; padding: 10px 8px; white-space: nowrap;">
                                #{{ $meeting->MaPhienHop }}
                            </td>

                            <!-- Cột Thông tin phiên họp -->
                            <td style="padding: 10px 12px;">
                                <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 3px;">
                                    <span class="stu-badge stu-badge-neutral" style="font-size: 9.5px; padding: 1px 5px; font-weight: 600;">
                                        {{ str_contains($meeting->TenPhienHop, 'Khóa') ? 'Khóa IV' : 'Nhiệm kỳ 2020-2025' }}
                                    </span>
                                    @if(str_contains(mb_strtolower($meeting->TenPhienHop), 'bất thường'))
                                        <span class="stu-badge stu-badge-warning" style="font-size: 9px; padding: 1px 5px;">Bất thường</span>
                                    @elseif(str_contains(mb_strtolower($meeting->TenPhienHop), 'chuyên đề'))
                                        <span class="stu-badge stu-badge-info" style="font-size: 9px; padding: 1px 5px;">Chuyên đề</span>
                                    @else
                                        <span class="stu-badge stu-badge-success" style="font-size: 9px; padding: 1px 5px;">Thường kỳ</span>
                                    @endif
                                </div>
                                <a href="{{ route('meetings.show', $meeting->MaPhienHop) }}" style="font-weight: 600; font-size: 12.5px; color: var(--color-primary); text-decoration: none; line-height: 1.3; display: block;" title="{{ $meeting->TenPhienHop }}">
                                    {{ $meeting->TenPhienHop }}
                                </a>
                                @if($meeting->GhiChu)
                                    <div style="font-size: 11px; color: var(--color-text-muted); margin-top: 2px;">
                                        Ghi chú: {{ $meeting->GhiChu }}
                                    </div>
                                @endif
                            </td>

                            <!-- Cột Thời gian -->
                            <td style="padding: 10px 10px; white-space: nowrap;">
                                <div style="font-weight: 600; font-size: 12px; color: var(--color-text-main);">
                                    {{ $meeting->ThoiGianBatDau ? date('d/m/Y', strtotime($meeting->ThoiGianBatDau)) : ($meeting->NgayHop ? date('d/m/Y', strtotime($meeting->NgayHop)) : '—') }}
                                </div>
                                <div style="font-size: 10.5px; color: var(--color-text-muted);">
                                    {{ $meeting->ThoiGianBatDau ? date('H:i', strtotime($meeting->ThoiGianBatDau)) : '08:30' }} - 
                                    {{ $meeting->ThoiGianKetThuc ? date('H:i', strtotime($meeting->ThoiGianKetThuc)) : '11:30' }}
                                </div>
                            </td>

                            <!-- Cột Địa điểm & Hình thức -->
                            <td style="padding: 10px 10px;">
                                <div style="font-size: 11.5px; color: var(--color-text-main); line-height: 1.25;" title="{{ $meeting->DiaDiem ?: 'Phòng họp Hội đồng trường STU' }}">
                                    {{ $meeting->DiaDiem ?: 'Phòng họp Hội đồng trường' }}
                                </div>
                                <span class="stu-badge stu-badge-info" style="padding: 1px 6px; font-size: 9.5px; margin-top: 3px; display: inline-block;">
                                    {{ $meeting->HinhThuc ?: 'Trực tiếp' }}
                                </span>
                            </td>

                            <!-- Cột Chủ trì / Người tạo -->
                            <td style="padding: 10px 10px; white-space: nowrap;">
                                <div style="font-weight: 600; font-size: 11.5px; color: var(--color-text-main);">
                                    {{ $meeting->nguoiDung?->HoTen ?? 'PGS.TS. Trần Văn Chủ Tịch' }}
                                </div>
                                <div style="font-size: 10.5px; color: var(--color-text-muted);">
                                    Chủ tịch Hội đồng trường
                                </div>
                            </td>

                            <!-- Cột Kết quả phiên họp -->
                            <td style="padding: 10px 10px; font-size: 11px;">
                                <div style="font-weight: 600; color: #059669; display: flex; align-items: center; gap: 4px;">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    <span>{{ $meeting->nghi_quyet_count ?? ($meeting->nghiQuyet?->count() ?: 1) }} Nghị quyết</span>
                                </div>
                                <div style="color: var(--color-text-muted); font-size: 10.5px; margin-top: 2px;">
                                    {{ $meeting->chuong_trinh_hop_count ?? 3 }} hồ sơ đã duyệt
                                </div>
                            </td>

                            <!-- Cột Trạng thái -->
                            <td style="padding: 10px 10px; white-space: nowrap;">
                                <x-nhan-trang-thai :status="$meeting->TrangThai" />
                            </td>

                            <!-- Cột Thao tác -->
                            <td style="text-align: center; padding: 10px 8px; white-space: nowrap;">
                                <a href="{{ route('meetings.show', $meeting->MaPhienHop) }}" style="font-size: 11px; font-weight: 600; color: var(--color-primary); padding: 3px 8px; border: 1px solid var(--color-primary); border-radius: 4px; display: inline-block; text-decoration: none; background: #ffffff; transition: all 0.15s ease;">
                                    Chi tiết
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- PHÂN TRANG -->
        <div style="padding: 12px 18px; border-top: 1px solid var(--color-border); display: flex; justify-content: space-between; align-items: center; background: #fafbfc;">
            <div style="font-size: 11.5px; color: var(--color-text-muted);">
                Hiển thị trang {{ $meetings->currentPage() }} / {{ $meetings->lastPage() }} (Tổng số {{ $meetings->total() }} phiên họp)
            </div>
            <div>
                {{ $meetings->links() }}
            </div>
        </div>
    @endif
</div>
@endsection
