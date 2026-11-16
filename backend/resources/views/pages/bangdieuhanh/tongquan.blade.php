@extends('layouts.chinh')

@section('title', 'Bảng Điều Hành Hoạt Động Hội Đồng Trường STU')

@push('styles')
<style>
    /* Tối ưu không gian cho Bảng điều hành để chụp màn hình trọn vẹn 1 khung nhìn (Fit 100vh) */
    .stu-content {
        padding: 10px 18px 12px 18px !important;
        max-width: 1650px !important;
    }
    .stu-footer {
        display: none !important; /* Ẩn chân trang để không chiếm chiều cao khi chụp màn hình */
    }
    .stu-card {
        border-radius: 8px !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05) !important;
    }
    .dashboard-banner {
        margin-bottom: 22px !important;
    }
    .dashboard-kpi-section {
        margin-top: 20px !important;
        margin-bottom: 14px !important;
    }
    .stu-card-header {
        padding: 6px 12px !important;
        min-height: unset !important;
        border-bottom: 1px solid var(--color-border-subtle) !important;
    }
    .stu-card-title {
        font-size: 12.5px !important;
        font-weight: 700 !important;
        color: var(--color-text-main) !important;
    }
    .stu-stat-card {
        padding: 7px 12px !important;
        border-radius: 8px !important;
        min-height: unset !important;
        box-shadow: 0 1px 2px rgba(0,0,0,0.04) !important;
    }
    .stu-stat-value {
        font-size: 21px !important;
        font-weight: 800 !important;
        line-height: 1.15 !important;
        margin-top: 1px !important;
    }
    .stu-stat-label {
        font-size: 11px !important;
        font-weight: 600 !important;
        color: var(--color-text-secondary) !important;
    }
    .stu-stat-icon-wrapper {
        width: 32px !important;
        height: 32px !important;
        border-radius: 7px !important;
    }
    .stu-stat-icon-wrapper svg {
        width: 17px !important;
        height: 17px !important;
    }
    .stu-stat-trend {
        font-size: 10px !important;
        margin-top: 1px !important;
    }
    /* Compact Table Styles */
    .dashboard-table th {
        background: var(--color-bg-subtle) !important;
        color: var(--color-text-secondary) !important;
        font-weight: 600 !important;
        font-size: 10px !important;
        text-transform: uppercase !important;
        letter-spacing: 0.03em !important;
        padding: 5px 8px !important;
        border-bottom: 1px solid var(--color-border) !important;
    }
    .dashboard-table td {
        padding: 5px 8px !important;
        border-bottom: 1px solid var(--color-border-subtle) !important;
        vertical-align: middle !important;
        font-size: 11.5px !important;
    }
    .dashboard-table tr:last-child td {
        border-bottom: none !important;
    }
</style>
@endpush

@section('header_title')
    <x-thanh-dieu-huong :items="[
        ['title' => 'Bảng điều hành']
    ]" />
@endsection

@section('content')
<!-- 1. BANNER CHÀO MỪNG & VAI TRÒ HIỆN TẠI (Nhỏ gọn, thanh lịch) -->
<div class="stu-card dashboard-banner" style="padding: 9px 16px; margin-bottom: 22px !important; background: linear-gradient(135deg, var(--stu-blue-700) 0%, var(--stu-blue-900) 100%); color: var(--stu-white); border: none;">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 6px;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <div style="width: 34px; height: 34px; border-radius: var(--radius-full); background: rgba(255,255,255,0.18); display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 700; color: var(--stu-white); border: 1.5px solid rgba(255,255,255,0.35); flex-shrink: 0;">
                {{ strtoupper(substr($user->full_name ?? session('user_name', 'HĐT'), 0, 1)) }}
            </div>
            <div>
                <div style="font-size: 14px; font-weight: 700; color: var(--stu-white); letter-spacing: -0.01em; line-height: 1.2;">
                    Xin chào, {{ $user->full_name ?? session('user_name', 'Thành viên Hội đồng') }}
                </div>
                <div style="font-size: 10.5px; color: var(--stu-blue-100); margin-top: 1px; display: flex; align-items: center; gap: 8px;">
                    <span>Vai trò hệ thống: <strong>{{ $role }}</strong></span>
                    <span>•</span>
                    <span>Nhiệm kỳ: <strong>Khóa IV (2020 - 2025)</strong></span>
                </div>
            </div>
        </div>

        <div style="display: flex; gap: 6px;">
            @if(in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN']))
                <a href="{{ url('/meetings') }}" class="stu-btn stu-btn-outline" style="background: rgba(255,255,255,0.15); color: #fff; border-color: rgba(255,255,255,0.4); padding: 3px 9px; font-size: 11px; border-radius: 5px; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    <span>Tạo phiên họp mới</span>
                </a>
            @endif
            @if(in_array($role, ['UNIT_REP', 'ADMIN']))
                <a href="{{ url('/submissions') }}" class="stu-btn stu-btn-outline" style="background: rgba(255,255,255,0.15); color: #fff; border-color: rgba(255,255,255,0.4); padding: 3px 9px; font-size: 11px; border-radius: 5px; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Nộp hồ sơ trình</span>
                </a>
            @endif
        </div>
    </div>
</div>

<!-- 2. KHU VỰC THỐNG KÊ KPI CỐT LÕI (Nhỏ gọn, tinh giản) -->
<section class="dashboard-kpi-section" style="margin-top: 20px !important; margin-bottom: 14px !important;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 7px;">
        <h2 style="font-size: 11px; font-weight: 700; color: var(--color-text-secondary); text-transform: uppercase; letter-spacing: 0.04em; margin: 0;">
            Chỉ số hoạt động cốt lõi
        </h2>
        <span style="font-size: 10px; color: var(--color-text-muted);">
            Dữ liệu đồng bộ thời gian thực từ cơ sở dữ liệu STU
        </span>
    </div>

    <div class="stu-grid stu-grid-cols-4" style="gap: 8px;">
        <!-- 1. Tổng phiên họp -->
        <x-the-thong-ke 
            title="Tổng số phiên họp" 
            :value="$kpi['total_meetings']" 
            :subtitle="$kpi['upcoming_meetings'] . ' phiên sắp diễn ra'" 
            type="primary" 
            link="{{ url('/meetings') }}"
        >
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        </x-the-thong-ke>

        <!-- 2. Nghị quyết ban hành -->
        <x-the-thong-ke 
            title="Nghị quyết ban hành" 
            :value="$kpi['enacted_resolutions']" 
            subtitle="Đầy đủ văn bản ký số" 
            type="info" 
            link="{{ url('/resolutions') }}"
        >
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
        </x-the-thong-ke>

        <!-- 3. Nhiệm vụ đang thực hiện -->
        <x-the-thong-ke 
            title="Nhiệm vụ đang thực hiện" 
            :value="$kpi['tasks_in_progress']" 
            :subtitle="'Đã hoàn thành: ' . $kpi['tasks_completed']" 
            type="warning" 
            link="{{ url('/tasks') }}"
        >
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </x-the-thong-ke>

        <!-- 4. Hồ sơ trình / Báo cáo chờ duyệt (Tùy vai trò) -->
        @if($role === 'EXECUTING_UNIT')
            <x-the-thong-ke 
                title="Tiến độ hoàn thành" 
                :value="$kpi['completion_rate'] . '%'" 
                :subtitle="$kpi['tasks_completed'] . '/' . $kpi['total_tasks'] . ' nhiệm vụ'" 
                type="success" 
                link="{{ url('/tasks') }}"
            >
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </x-the-thong-ke>
        @else
            <x-the-thong-ke 
                title="Hồ sơ trình chờ xử lý" 
                :value="$kpi['pending_dossiers']" 
                :subtitle="$kpi['pending_reports'] . ' báo cáo cần duyệt'" 
                type="danger" 
                link="{{ url('/submissions') }}"
            >
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </x-the-thong-ke>
        @endif
    </div>
</section>

<!-- 3. BỐ CỤC CHÍNH 2 CỘT TỐI ƯU CHIỀU CAO (KHÔNG BỊ CUỘN TRANG) -->
<div style="display: grid; grid-template-columns: 1.55fr 1fr; gap: 8px; align-items: start;">
    
    <!-- CỘT TRÁI: PHIÊN HỌP & THEO DÕI NHIỆM VỤ -->
    <div style="display: flex; flex-direction: column; gap: 8px;">
        
        <!-- 3.1 Phiên họp sắp tới (Hiển thị 3 phiên gọn gàng) -->
        <div class="stu-card">
            <div class="stu-card-header">
                <h3 class="stu-card-title">Lịch phiên họp Hội đồng trường</h3>
                <div class="stu-card-actions">
                    <a href="{{ url('/meetings') }}" style="font-size: 11px; font-weight: 600; color: var(--color-primary); display: flex; align-items: center; gap: 3px; text-decoration: none;">
                        Xem tất cả
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
            <div style="overflow: hidden;">
                @if($upcomingMeetings->isEmpty())
                    <div class="stu-empty-state" style="padding: 16px;">
                        <div class="stu-empty-title" style="font-size: 12px;">Chưa có phiên họp nào</div>
                        <div class="stu-empty-desc" style="font-size: 11px;">Hiện tại chưa có phiên họp mới nào được lập lịch.</div>
                    </div>
                @else
                    <table class="dashboard-table" style="width: 100%; border-collapse: separate; border-spacing: 0; table-layout: fixed;">
                        <colgroup>
                            <col style="width: 36%;">
                            <col style="width: 17%;">
                            <col style="width: 22%;">
                            <col style="width: 15%;">
                            <col style="width: 10%;">
                        </colgroup>
                        <thead>
                            <tr>
                                <th>Tên phiên họp</th>
                                <th>Thời gian</th>
                                <th>Địa điểm</th>
                                <th>Trạng thái</th>
                                <th style="text-align: center; white-space: nowrap;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($upcomingMeetings->take(3) as $meeting)
                                @php
                                    $rawStatus = $meeting->TrangThai ?: 'PREPARING';
                                    $displayStatus = match(strtoupper($rawStatus)) {
                                        'PREPARING' => 'Chuẩn bị',
                                        'SCHEDULED' => 'Đã lên lịch',
                                        'HAPPENING', 'ONGOING' => 'Đang diễn ra',
                                        'COMPLETED' => 'Đã hoàn thành',
                                        'CANCELLED' => 'Đã hủy',
                                        default => $rawStatus
                                    };
                                @endphp
                                <tr>
                                    <td>
                                        <a href="{{ url('/meetings/' . $meeting->MaPhienHop) }}" style="color: var(--color-primary); font-weight: 600; font-size: 12px; line-height: 1.25; display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; text-decoration: none;" title="{{ $meeting->TenPhienHop }}">
                                            {{ $meeting->TenPhienHop }}
                                        </a>
                                        <span style="font-size: 10px; color: var(--color-text-muted);">Mã: #{{ $meeting->MaPhienHop }}</span>
                                    </td>
                                    <td style="white-space: nowrap;">
                                        <div style="font-weight: 600; font-size: 11.5px; color: var(--color-text-main);">{{ $meeting->ThoiGianBatDau ? date('d/m/Y', strtotime($meeting->ThoiGianBatDau)) : '—' }}</div>
                                        <div style="font-size: 10px; color: var(--color-text-muted);">{{ $meeting->ThoiGianBatDau ? date('H:i', strtotime($meeting->ThoiGianBatDau)) : '' }}</div>
                                    </td>
                                    <td>
                                        <div style="font-size: 11px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 140px;" title="{{ $meeting->DiaDiem ?: 'Phòng họp Hội đồng trường' }}">
                                            {{ $meeting->DiaDiem ?: 'Phòng họp Hội đồng trường' }}
                                        </div>
                                        <span class="stu-badge stu-badge-info" style="padding: 1px 5px; font-size: 9.5px; margin-top: 2px; display: inline-block;">
                                            {{ $meeting->HinhThuc === 'IN_PERSON' ? 'Trực tiếp' : ($meeting->HinhThuc === 'ONLINE' ? 'Trực tuyến' : ($meeting->HinhThuc ?: 'Trực tiếp')) }}
                                        </span>
                                    </td>
                                    <td style="white-space: nowrap;">
                                        <x-nhan-trang-thai :status="$displayStatus" />
                                    </td>
                                    <td style="text-align: center; white-space: nowrap;">
                                        <a href="{{ url('/meetings/' . $meeting->MaPhienHop) }}" style="font-size: 11px; font-weight: 600; color: var(--color-primary); padding: 2px 7px; border: 1px solid var(--color-primary); border-radius: 4px; white-space: nowrap; display: inline-block; text-decoration: none; background: #fff;">
                                            Chi tiết
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        <!-- 3.2 Nhiệm vụ phân công theo Nghị quyết (Hiển thị 3 nhiệm vụ tiêu biểu) -->
        <div class="stu-card">
            <div class="stu-card-header">
                <h3 class="stu-card-title">Nhiệm vụ phân công theo Nghị quyết</h3>
                <div class="stu-card-actions">
                    <a href="{{ url('/tasks') }}" style="font-size: 11px; font-weight: 600; color: var(--color-primary); display: flex; align-items: center; gap: 3px; text-decoration: none;">
                        Xem tất cả
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
            <div style="overflow: hidden;">
                @if($trackedTasks->isEmpty())
                    <div class="stu-empty-state" style="padding: 16px;">
                        <div class="stu-empty-title" style="font-size: 12px;">Không có nhiệm vụ cần hiển thị</div>
                        <div class="stu-empty-desc" style="font-size: 11px;">Tất cả nhiệm vụ đã được nghiệm thu hoặc chưa có phân công mới.</div>
                    </div>
                @else
                    <table class="dashboard-table" style="width: 100%; border-collapse: separate; border-spacing: 0; table-layout: fixed;">
                        <colgroup>
                            <col style="width: 38%;">
                            <col style="width: 25%;">
                            <col style="width: 20%;">
                            <col style="width: 17%;">
                        </colgroup>
                        <thead>
                            <tr>
                                <th>Nhiệm vụ</th>
                                <th>Đơn vị & Hạn</th>
                                <th>Tiến độ</th>
                                <th style="text-align: center;">Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($trackedTasks->take(3) as $task)
                                @php
                                    $assignee = $task->phanCongNhiemVu->first();
                                    $unitName = $assignee?->donVi?->TenDonVi ?: 'Đang cập nhật';
                                    $rawStatus = strtoupper($task->TrangThai ?: 'IN_PROGRESS');
                                    $isCompleted = in_array($rawStatus, ['COMPLETED', 'ĐÃ HOÀN THÀNH', 'HOÀN THÀNH']);
                                    $isOverdue = $task->NgayHoanThanh && $task->NgayHoanThanh->toDateString() < now()->toDateString() && !$isCompleted;
                                    $progressVal = $isCompleted ? 100 : ($isOverdue ? 40 : 70);

                                    if ($isCompleted) {
                                        $displayStatus = 'Đã hoàn thành';
                                        $badgeType = 'success';
                                    } elseif ($isOverdue) {
                                        $displayStatus = 'Quá hạn';
                                        $badgeType = 'danger';
                                    } elseif ($rawStatus === 'PENDING' || $rawStatus === 'ASSIGNED') {
                                        $displayStatus = 'Chờ tiếp nhận';
                                        $badgeType = 'warning';
                                    } else {
                                        $displayStatus = 'Đang làm';
                                        $badgeType = 'warning';
                                    }
                                @endphp
                                <tr>
                                    <td>
                                        <a href="{{ url('/tasks/' . $task->MaNhiemVu) }}" style="font-weight: 600; font-size: 12px; color: var(--color-primary); display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.25; text-decoration: none;" title="{{ $task->TenNhiemVu }}">
                                            {{ $task->TenNhiemVu }}
                                        </a>
                                        <span style="font-size: 10px; color: var(--color-text-muted);">{{ $task->nghiQuyet?->SoNghiQuyet ?: 'NQ-HĐT' }}</span>
                                    </td>
                                    <td>
                                        <div style="font-weight: 500; font-size: 11px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $unitName }}">{{ $unitName }}</div>
                                        <div style="font-size: 10px; {{ $isOverdue ? 'color: var(--color-danger); font-weight: 600;' : 'color: var(--color-text-muted);' }}">
                                            {{ $task->NgayHoanThanh ? $task->NgayHoanThanh->format('d/m/Y') : 'Không hạn' }}
                                        </div>
                                    </td>
                                    <td>
                                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 2px;">
                                            <span style="font-size: 10px; font-weight: 700; color: var(--color-text-main);">{{ $progressVal }}%</span>
                                        </div>
                                        <div style="height: 5px; width: 100%; background: var(--color-bg-subtle); border-radius: 4px; overflow: hidden;">
                                            <div style="height: 100%; width: {{ $progressVal }}%; background: {{ $isCompleted ? 'var(--color-success)' : ($isOverdue ? 'var(--color-danger)' : 'var(--color-warning)') }}; border-radius: 4px;"></div>
                                        </div>
                                    </td>
                                    <td style="text-align: center; white-space: nowrap;">
                                        <x-nhan-trang-thai :status="$displayStatus" :type="$badgeType" />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

    </div>

    <!-- CỘT PHẢI: TIẾN ĐỘ TỔNG THỂ & NGHỊ QUYẾT MỚI & BÁO CÁO CHỜ DUYỆT -->
    <div style="display: flex; flex-direction: column; gap: 8px;">
        
        <!-- 3.3 Biểu đồ phân bổ tiến độ nhiệm vụ (Rút gọn thanh lịch) -->
        <div class="stu-card" style="padding: 8px 12px;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 5px;">
                <h3 class="stu-card-title">Tiến độ thực hiện nhiệm vụ</h3>
                <span class="stu-badge stu-badge-success" style="font-size: 10px; padding: 1px 6px;">{{ $kpi['completion_rate'] }}% Đạt</span>
            </div>

            <!-- Thanh tỷ lệ phân bổ tiến độ trực quan -->
            <div style="margin-bottom: 6px;">
                <div style="height: 8px; width: 100%; display: flex; border-radius: 4px; overflow: hidden; background: var(--color-bg-subtle);">
                    @if($kpi['total_tasks'] > 0)
                        <div style="width: {{ ($kpi['tasks_completed'] / $kpi['total_tasks']) * 100 }}%; background-color: var(--color-success);" title="Hoàn thành: {{ $kpi['tasks_completed'] }}"></div>
                        <div style="width: {{ ($kpi['tasks_in_progress'] / $kpi['total_tasks']) * 100 }}%; background-color: var(--color-warning);" title="Đang thực hiện: {{ $kpi['tasks_in_progress'] }}"></div>
                        <div style="width: {{ ($kpi['tasks_overdue'] / $kpi['total_tasks']) * 100 }}%; background-color: var(--color-danger);" title="Quá hạn: {{ $kpi['tasks_overdue'] }}"></div>
                    @else
                        <div style="width: 100%; background-color: var(--color-border);"></div>
                    @endif
                </div>
            </div>

            <!-- 3 Chip thống kê dạng ngang hiện đại -->
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 5px; font-size: 11px; text-align: center;">
                <div style="background: rgba(16, 185, 129, 0.08); border-radius: 6px; padding: 3px 4px; border: 1px solid rgba(16, 185, 129, 0.2);">
                    <div style="color: var(--color-success); font-weight: 700; font-size: 12.5px; line-height: 1.1;">{{ $kpi['tasks_completed'] }}</div>
                    <div style="color: var(--color-text-secondary); font-size: 9.5px;">Hoàn thành</div>
                </div>
                <div style="background: rgba(245, 158, 11, 0.08); border-radius: 6px; padding: 3px 4px; border: 1px solid rgba(245, 158, 11, 0.2);">
                    <div style="color: var(--color-warning); font-weight: 700; font-size: 12.5px; line-height: 1.1;">{{ $kpi['tasks_in_progress'] }}</div>
                    <div style="color: var(--color-text-secondary); font-size: 9.5px;">Đang làm</div>
                </div>
                <div style="background: rgba(239, 68, 68, 0.08); border-radius: 6px; padding: 3px 4px; border: 1px solid rgba(239, 68, 68, 0.2);">
                    <div style="color: var(--color-danger); font-weight: 700; font-size: 12.5px; line-height: 1.1;">{{ $kpi['tasks_overdue'] }}</div>
                    <div style="color: var(--color-text-secondary); font-size: 9.5px;">Quá hạn</div>
                </div>
            </div>

            <div style="border-top: 1px dashed var(--color-border); padding-top: 4px; margin-top: 5px; display: flex; align-items: center; justify-content: space-between; font-size: 10.5px;">
                <span style="color: var(--color-text-muted);">Tổng số nhiệm vụ:</span>
                <strong>{{ $kpi['total_tasks'] }} nhiệm vụ</strong>
            </div>
        </div>

        <!-- 3.4 Nghị quyết mới ban hành (Top 3 gọn đẹp) -->
        <div class="stu-card">
            <div class="stu-card-header">
                <h3 class="stu-card-title">Nghị quyết mới ban hành</h3>
                <div class="stu-card-actions">
                    <a href="{{ url('/resolutions') }}" style="font-size: 11px; font-weight: 600; color: var(--color-primary); text-decoration: none;">
                        Xem tất cả
                    </a>
                </div>
            </div>
            <div>
                @if($recentResolutions->isEmpty())
                    <div class="stu-empty-state" style="padding: 12px;">
                        <div class="stu-empty-desc" style="font-size: 11px;">Chưa có nghị quyết nào.</div>
                    </div>
                @else
                    @foreach($recentResolutions->take(3) as $resolution)
                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 5px 10px; border-bottom: 1px solid var(--color-border-subtle); gap: 8px;">
                            <div style="min-width: 0; flex: 1;">
                                <div style="font-weight: 700; color: var(--color-primary); font-size: 11.5px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    {{ $resolution->SoNghiQuyet }}
                                </div>
                                <div style="font-size: 10.5px; color: var(--color-text-secondary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $resolution->TrichYeu }}">
                                    {{ $resolution->TrichYeu }}
                                </div>
                            </div>
                            <div style="text-align: right; white-space: nowrap; flex-shrink: 0; display: flex; align-items: center; gap: 6px;">
                                <span style="font-size: 10px; color: var(--color-text-muted);">
                                    {{ $resolution->NgayBanHanh ? $resolution->NgayBanHanh->format('d/m/Y') : '' }}
                                </span>
                                <a href="{{ url('/resolutions/' . $resolution->MaNghiQuyet) }}" style="font-size: 10.5px; font-weight: 600; color: var(--color-primary); padding: 1px 6px; border: 1px solid var(--color-primary); border-radius: 4px; text-decoration: none; background: #fff;">
                                    Xem
                                </a>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        <!-- 3.5 Báo cáo tiến độ chờ duyệt (Dành cho Chủ tịch, Thư ký, VP Hiệu trưởng, Admin) -->
        @if(in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN', 'RECTOR_OFFICE']))
            <div class="stu-card" style="padding: 6px 10px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 3px;">
                    <h4 style="font-size: 11.5px; font-weight: 700; margin: 0; color: var(--color-text-main);">
                        Báo cáo tiến độ chờ duyệt
                    </h4>
                    <span class="stu-badge stu-badge-warning" style="font-size: 9.5px; padding: 1px 5px;">
                        {{ $pendingReports->count() }} chờ duyệt
                    </span>
                </div>
                @if($pendingReports->isEmpty())
                    <div style="font-size: 10.5px; color: var(--color-text-muted); padding: 4px 0;">
                        Tất cả báo cáo đã được thẩm định.
                    </div>
                @else
                    @foreach($pendingReports->take(1) as $report)
                        @php
                            $rTask = $report->phanCongNhiemVu?->nhiemVu;
                            $rUnit = $report->phanCongNhiemVu?->donVi?->TenDonVi ?: 'Đơn vị';
                        @endphp
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; padding-top: 2px;">
                            <div style="min-width: 0; flex: 1;">
                                <div style="font-size: 11px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $rTask?->TenNhiemVu }}">
                                    {{ $rTask?->TenNhiemVu ?: 'Báo cáo định kỳ' }}
                                </div>
                                <div style="font-size: 9.5px; color: var(--color-text-muted);">
                                    {{ $rUnit }} • {{ $report->NgayBaoCao ? $report->NgayBaoCao->format('d/m') : '' }}
                                </div>
                            </div>
                            <div style="display: flex; align-items: center; gap: 6px; flex-shrink: 0;">
                                <span class="stu-badge stu-badge-warning" style="font-size: 9.5px; padding: 1px 5px;">
                                    {{ $report->TienDo ?? 0 }}%
                                </span>
                                <a href="{{ url('/task-reports') }}" class="stu-btn stu-btn-primary" style="padding: 2px 7px; font-size: 10.5px; border-radius: 4px; text-decoration: none;">
                                    Duyệt
                                </a>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        @endif

    </div>

</div>
@endsection
