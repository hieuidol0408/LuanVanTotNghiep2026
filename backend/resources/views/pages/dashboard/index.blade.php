@extends('layouts.app')

@section('title', 'Bảng Điều Hành Hoạt Động Hội Đồng Trường STU')

@section('header_title')
    <x-breadcrumb :items="[
        ['title' => 'Bảng điều hành']
    ]" />
@endsection

@section('content')
<!-- BANNER CHÀO MỪNG & VAI TRÒ HIỆN TẠI -->
<div class="stu-card" style="padding: var(--space-5) var(--space-6); margin-bottom: var(--space-6); background: linear-gradient(135deg, var(--stu-blue-700) 0%, var(--stu-blue-900) 100%); color: var(--stu-white); border: none;">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: var(--space-4);">
        <div style="display: flex; align-items: center; gap: var(--space-4);">
            <div style="width: 52px; height: 52px; border-radius: var(--radius-full); background: rgba(255,255,255,0.18); display: flex; align-items: center; justify-content: center; font-size: var(--font-size-xl); font-weight: 700; color: var(--stu-white); border: 2px solid rgba(255,255,255,0.3);">
                {{ strtoupper(substr($user->full_name ?? session('user_name', 'HĐT'), 0, 1)) }}
            </div>
            <div>
                <h1 style="font-size: var(--font-size-xl); font-weight: 700; color: var(--stu-white); margin-bottom: 4px; letter-spacing: -0.01em;">
                    Xin chào, {{ $user->full_name ?? session('user_name', 'Thành viên Hội đồng') }}
                </h1>
                <p style="font-size: var(--font-size-sm); color: var(--stu-blue-100); margin: 0; display: flex; align-items: center; gap: var(--space-2);">
                    <span>Vai trò hệ thống: <strong>{{ $role }}</strong></span>
                    <span>•</span>
                    <span>Nhiệm kỳ <strong>Khóa IV (2020 - 2025)</strong></span>
                </p>
            </div>
        </div>

        <div style="display: flex; gap: var(--space-2);">
            @if(in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN']))
                <x-button variant="outline" href="{{ url('/meetings') }}" style="background: rgba(255,255,255,0.12); color: var(--stu-white); border-color: rgba(255,255,255,0.35);">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    <span>Tạo phiên họp mới</span>
                </x-button>
            @endif
            @if(in_array($role, ['UNIT_REP', 'ADMIN']))
                <x-button variant="outline" href="{{ url('/submissions') }}" style="background: rgba(255,255,255,0.12); color: var(--stu-white); border-color: rgba(255,255,255,0.35);">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Nộp hồ sơ trình</span>
                </x-button>
            @endif
        </div>
    </div>
</div>

<!-- 1. KHU VỰC THỐNG KÊ KPI CỐT LÕI -->
<section style="margin-bottom: var(--space-8);">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--space-4);">
        <h2 style="font-size: var(--font-size-base); font-weight: 700; color: var(--color-text-main); text-transform: uppercase; letter-spacing: 0.04em;">
            Chỉ số hoạt động cốt lõi
        </h2>
        <span style="font-size: var(--font-size-xs); color: var(--color-text-muted);">
            Dữ liệu đồng bộ thời gian thực từ cơ sở dữ liệu STU
        </span>
    </div>

    <div class="stu-grid stu-grid-cols-4">
        <!-- 1. Tổng phiên họp -->
        <x-stat-card 
            title="Tổng số phiên họp" 
            :value="$kpi['total_meetings']" 
            :subtitle="$kpi['upcoming_meetings'] . ' phiên sắp diễn ra'" 
            type="primary" 
            link="{{ url('/meetings') }}"
        >
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        </x-stat-card>

        <!-- 2. Nghị quyết ban hành -->
        <x-stat-card 
            title="Nghị quyết ban hành" 
            :value="$kpi['enacted_resolutions']" 
            subtitle="Đầy đủ văn bản ký số" 
            type="info" 
            link="{{ url('/resolutions') }}"
        >
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
        </x-stat-card>

        <!-- 3. Nhiệm vụ đang thực hiện -->
        <x-stat-card 
            title="Nhiệm vụ đang thực hiện" 
            :value="$kpi['tasks_in_progress']" 
            :subtitle="'Đã hoàn thành: ' . $kpi['tasks_completed']" 
            type="warning" 
            link="{{ url('/tasks') }}"
        >
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </x-stat-card>

        <!-- 4. Hồ sơ trình / Báo cáo chờ duyệt (Tùy vai trò) -->
        @if($role === 'EXECUTING_UNIT')
            <x-stat-card 
                title="Tiến độ hoàn thành" 
                :value="$kpi['completion_rate'] . '%'" 
                :subtitle="$kpi['tasks_completed'] . '/' . $kpi['total_tasks'] . ' nhiệm vụ'" 
                type="success" 
                link="{{ url('/tasks') }}"
            >
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </x-stat-card>
        @else
            <x-stat-card 
                title="Hồ sơ trình chờ xử lý" 
                :value="$kpi['pending_dossiers']" 
                :subtitle="$kpi['pending_reports'] . ' báo cáo cần duyệt'" 
                type="danger" 
                link="{{ url('/submissions') }}"
            >
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </x-stat-card>
        @endif
    </div>
</section>

<!-- 2. BỐ CỤC CHÍNH 2 CỘT -->
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: var(--space-6); align-items: start;">
    <!-- CỘT TRÁI: PHIÊN HỌP & THEO DÕI NHIỆM VỤ -->
    <div style="display: flex; flex-direction: column; gap: var(--space-6);">
        <!-- 2.1 Phiên họp sắp tới -->
        <div class="stu-card">
            <div class="stu-card-header">
                <h3 class="stu-card-title">Lịch phiên họp Hội đồng trường</h3>
                <div class="stu-card-actions">
                    <a href="{{ url('/meetings') }}" style="font-size: var(--font-size-xs); font-weight: 600; color: var(--color-primary); display: flex; align-items: center; gap: 4px;">
                        Xem tất cả
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
            <div style="overflow: hidden;">
                @if($upcomingMeetings->isEmpty())
                    <div class="stu-empty-state">
                        <div class="stu-empty-title">Chưa có phiên họp nào</div>
                        <div class="stu-empty-desc">Hiện tại chưa có phiên họp mới nào được lập lịch.</div>
                    </div>
                @else
                    <table style="width: 100%; border-collapse: separate; border-spacing: 0; font-size: var(--font-size-sm); table-layout: fixed;">
                        <colgroup>
                            <col style="width: 35%;">
                            <col style="width: 15%;">
                            <col style="width: 20%;">
                            <col style="width: 16%;">
                            <col style="width: 14%;">
                        </colgroup>
                        <thead>
                            <tr>
                                <th style="background: var(--color-bg-subtle); color: var(--color-text-secondary); font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em; padding: 10px 12px; border-bottom: 1px solid var(--color-border);">Tên phiên họp</th>
                                <th style="background: var(--color-bg-subtle); color: var(--color-text-secondary); font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em; padding: 10px 12px; border-bottom: 1px solid var(--color-border);">Thời gian</th>
                                <th style="background: var(--color-bg-subtle); color: var(--color-text-secondary); font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em; padding: 10px 12px; border-bottom: 1px solid var(--color-border);">Địa điểm</th>
                                <th style="background: var(--color-bg-subtle); color: var(--color-text-secondary); font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em; padding: 10px 12px; border-bottom: 1px solid var(--color-border);">Trạng thái</th>
                                <th style="background: var(--color-bg-subtle); color: var(--color-text-secondary); font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em; padding: 10px 12px; border-bottom: 1px solid var(--color-border); text-align: center;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($upcomingMeetings as $meeting)
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
                                    <td style="padding: 10px 12px; border-bottom: 1px solid var(--color-border-subtle); vertical-align: middle;">
                                        <a href="{{ url('/meetings/' . $meeting->MaPhienHop) }}" style="color: var(--color-primary); font-weight: 600; font-size: 13px; line-height: 1.35; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;" title="{{ $meeting->TenPhienHop }}">
                                            {{ $meeting->TenPhienHop }}
                                        </a>
                                        <span style="font-size: 11px; color: var(--color-text-muted);">Mã: #{{ $meeting->MaPhienHop }}</span>
                                    </td>
                                    <td style="padding: 10px 12px; border-bottom: 1px solid var(--color-border-subtle); vertical-align: middle; white-space: nowrap;">
                                        <div style="font-weight: 600; font-size: 13px; color: var(--color-text-main);">{{ $meeting->ThoiGianBatDau ? date('d/m/Y', strtotime($meeting->ThoiGianBatDau)) : '—' }}</div>
                                        <div style="font-size: 11px; color: var(--color-text-muted);">{{ $meeting->ThoiGianBatDau ? date('H:i', strtotime($meeting->ThoiGianBatDau)) : '' }}</div>
                                    </td>
                                    <td style="padding: 10px 12px; border-bottom: 1px solid var(--color-border-subtle); vertical-align: middle;">
                                        <div style="font-size: 12px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 150px;" title="{{ $meeting->DiaDiem ?: 'Phòng họp A - Cơ sở chính' }}">
                                            {{ $meeting->DiaDiem ?: 'Phòng họp A - Cơ sở chính' }}
                                        </div>
                                        <span class="stu-badge stu-badge-info" style="padding: 1px 6px; font-size: 10px; margin-top: 3px; display: inline-block;">
                                            {{ $meeting->HinhThuc === 'IN_PERSON' ? 'Trực tiếp' : ($meeting->HinhThuc === 'ONLINE' ? 'Trực tuyến' : ($meeting->HinhThuc ?: 'Trực tiếp')) }}
                                        </span>
                                    </td>
                                    <td style="padding: 10px 12px; border-bottom: 1px solid var(--color-border-subtle); vertical-align: middle; white-space: nowrap;">
                                        <x-status-badge :status="$displayStatus" />
                                    </td>
                                    <td style="padding: 10px 12px; border-bottom: 1px solid var(--color-border-subtle); vertical-align: middle; text-align: center; white-space: nowrap;">
                                        <a href="{{ url('/meetings/' . $meeting->MaPhienHop) }}" style="font-size: 12px; font-weight: 500; color: var(--color-primary); padding: 4px 10px; border: 1px solid var(--color-primary); border-radius: 6px; white-space: nowrap; display: inline-block; text-decoration: none; background: #fff; transition: all 0.2s;" onmouseover="this.style.background='var(--color-primary)';this.style.color='#fff';" onmouseout="this.style.background='#fff';this.style.color='var(--color-primary)';">
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

        <!-- 2.2 Theo dõi thực hiện nhiệm vụ -->
        <div class="stu-card">
            <div class="stu-card-header">
                <h3 class="stu-card-title">Nhiệm vụ phân công theo Nghị quyết</h3>
                <div class="stu-card-actions">
                    <a href="{{ url('/tasks') }}" style="font-size: var(--font-size-xs); font-weight: 600; color: var(--color-primary); display: flex; align-items: center; gap: 4px;">
                        Xem tất cả
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
            <div style="overflow: hidden;">
                @if($trackedTasks->isEmpty())
                    <div class="stu-empty-state">
                        <div class="stu-empty-title">Không có nhiệm vụ cần hiển thị</div>
                        <div class="stu-empty-desc">Tất cả nhiệm vụ đã được nghiệm thu hoặc chưa có phân công mới.</div>
                    </div>
                @else
                    <table style="width: 100%; border-collapse: separate; border-spacing: 0; font-size: var(--font-size-sm); table-layout: fixed;">
                        <colgroup>
                            <col style="width: 36%;">
                            <col style="width: 28%;">
                            <col style="width: 20%;">
                            <col style="width: 16%;">
                        </colgroup>
                        <thead>
                            <tr>
                                <th style="background: var(--color-bg-subtle); color: var(--color-text-secondary); font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em; padding: 8px 12px; border-bottom: 1px solid var(--color-border);">Nhiệm vụ</th>
                                <th style="background: var(--color-bg-subtle); color: var(--color-text-secondary); font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em; padding: 8px 12px; border-bottom: 1px solid var(--color-border);">Đơn vị & Hạn</th>
                                <th style="background: var(--color-bg-subtle); color: var(--color-text-secondary); font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em; padding: 8px 12px; border-bottom: 1px solid var(--color-border);">Tiến độ</th>
                                <th style="background: var(--color-bg-subtle); color: var(--color-text-secondary); font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em; padding: 8px 12px; border-bottom: 1px solid var(--color-border);">Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($trackedTasks as $task)
                                @php
                                    $assignee = $task->phanCongNhiemVu->first();
                                    $unitName = $assignee?->donVi?->TenDonVi ?: 'Đang cập nhật';
                                    $person = $assignee?->TenNguoiPhuTrach;
                                    $isOverdue = $task->NgayHoanThanh && $task->NgayHoanThanh->toDateString() < now()->toDateString() && !in_array($task->TrangThai, ['COMPLETED', 'Đã hoàn thành']);
                                    $progressVal = in_array($task->TrangThai, ['COMPLETED', 'Đã hoàn thành']) ? 100 : ($isOverdue ? 40 : 70);
                                @endphp
                                <tr>
                                    <td style="padding: 10px 12px; border-bottom: 1px solid var(--color-border-subtle); vertical-align: middle;">
                                        <a href="{{ url('/tasks/' . $task->MaNhiemVu) }}" style="font-weight: 600; font-size: 13px; color: var(--color-primary); display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.35;" title="{{ $task->TenNhiemVu }}">
                                            {{ $task->TenNhiemVu }}
                                        </a>
                                        <span style="font-size: 11px; color: var(--color-text-muted);">{{ $task->nghiQuyet?->SoNghiQuyet ?: 'NQ-HĐT' }}</span>
                                    </td>
                                    <td style="padding: 8px 12px; border-bottom: 1px solid var(--color-border-subtle); vertical-align: middle; overflow: hidden;">
                                        <div style="font-weight: 500; font-size: 12px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $unitName }}">{{ $unitName }}</div>
                                        <div style="font-size: 11px; {{ $isOverdue ? 'color: var(--color-danger); font-weight: 600;' : 'color: var(--color-text-muted);' }}">
                                            {{ $task->NgayHoanThanh ? $task->NgayHoanThanh->format('d/m/Y') : 'Không thời hạn' }}
                                        </div>
                                    </td>
                                    <td style="padding: 8px 12px; border-bottom: 1px solid var(--color-border-subtle); vertical-align: middle;">
                                        <x-progress-bar :value="$progressVal" />
                                    </td>
                                    <td style="padding: 8px 12px; border-bottom: 1px solid var(--color-border-subtle); vertical-align: middle;">
                                        <x-status-badge :status="$isOverdue ? 'Quá hạn' : ($task->TrangThai ?: 'Đang thực hiện')" />
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
    <div style="display: flex; flex-direction: column; gap: var(--space-6);">
        <!-- 2.3 Biểu đồ phân bổ tiến độ nhiệm vụ -->
        <div class="stu-card" style="padding: var(--space-6);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--space-4);">
                <h3 class="stu-card-title">Tiến độ thực hiện nhiệm vụ</h3>
                <span class="stu-badge stu-badge-success">{{ $kpi['completion_rate'] }}% Đạt</span>
            </div>

            <!-- Thanh tỷ lệ phân bổ tiến độ trực quan -->
            <div style="margin-bottom: var(--space-5);">
                <div style="height: 12px; width: 100%; display: flex; border-radius: var(--radius-full); overflow: hidden; background: var(--color-bg-subtle);">
                    @if($kpi['total_tasks'] > 0)
                        <div style="width: {{ ($kpi['tasks_completed'] / $kpi['total_tasks']) * 100 }}%; background-color: var(--color-success);" title="Hoàn thành: {{ $kpi['tasks_completed'] }}"></div>
                        <div style="width: {{ ($kpi['tasks_in_progress'] / $kpi['total_tasks']) * 100 }}%; background-color: var(--color-warning);" title="Đang thực hiện: {{ $kpi['tasks_in_progress'] }}"></div>
                        <div style="width: {{ ($kpi['tasks_overdue'] / $kpi['total_tasks']) * 100 }}%; background-color: var(--color-danger);" title="Quá hạn: {{ $kpi['tasks_overdue'] }}"></div>
                    @else
                        <div style="width: 100%; background-color: var(--color-border);"></div>
                    @endif
                </div>
            </div>

            <!-- Bảng phân tích chi tiết -->
            <div style="display: flex; flex-direction: column; gap: var(--space-3); font-size: var(--font-size-sm);">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="width: 10px; height: 10px; border-radius: 2px; background: var(--color-success);"></span>
                        <span>Đã hoàn thành</span>
                    </div>
                    <strong>{{ $kpi['tasks_completed'] }}</strong>
                </div>

                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="width: 10px; height: 10px; border-radius: 2px; background: var(--color-warning);"></span>
                        <span>Đang thực hiện</span>
                    </div>
                    <strong>{{ $kpi['tasks_in_progress'] }}</strong>
                </div>

                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="width: 10px; height: 10px; border-radius: 2px; background: var(--color-danger);"></span>
                        <span>Quá hạn tiến độ</span>
                    </div>
                    <strong style="color: var(--color-danger);">{{ $kpi['tasks_overdue'] }}</strong>
                </div>

                <div style="border-top: 1px dashed var(--color-border); padding-top: var(--space-2); display: flex; align-items: center; justify-content: space-between;">
                    <span style="color: var(--color-text-muted);">Tổng số nhiệm vụ</span>
                    <strong>{{ $kpi['total_tasks'] }}</strong>
                </div>
            </div>
        </div>

        <!-- 2.4 Nghị quyết mới ban hành -->
        <x-table 
            cardTitle="Nghị quyết mới ban hành"
            :headers="['Số hiệu & Trích yếu', 'Ngày BH']"
            :hasActions="true"
            actionTitle=""
            :empty="$recentResolutions->isEmpty()"
            emptyTitle="Chưa có nghị quyết"
            emptyMessage="Chưa có nghị quyết nào được ban hành."
        >
            <x-slot:cardActions>
                <a href="{{ url('/resolutions') }}" style="font-size: var(--font-size-xs); font-weight: 600; color: var(--color-primary); display: flex; align-items: center; gap: 4px;">
                    Xem tất cả
                </a>
            </x-slot:cardActions>

            @foreach($recentResolutions as $resolution)
                <tr>
                    <td class="stu-max-w-xs">
                        <div style="font-weight: 700; color: var(--color-primary); font-size: 13px;">
                            {{ $resolution->SoNghiQuyet }}
                        </div>
                        <div class="stu-truncate" style="font-size: 12px; color: var(--color-text-secondary);" title="{{ $resolution->TrichYeu }}">
                            {{ $resolution->TrichYeu }}
                        </div>
                    </td>
                    <td style="white-space: nowrap; font-size: 11px; color: var(--color-text-muted);">
                        {{ $resolution->NgayBanHanh ? $resolution->NgayBanHanh->format('d/m/Y') : '' }}
                    </td>
                    <td class="col-actions">
                        <x-button variant="outline" size="sm" href="{{ url('/resolutions/' . $resolution->MaNghiQuyet) }}">
                            <span>Xem</span>
                        </x-button>
                    </td>
                </tr>
            @endforeach
        </x-table>

        <!-- 2.5 Báo cáo chờ duyệt (Dành cho Chủ tịch, Thư ký, VP Hiệu trưởng, Admin) -->
        @if(in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN', 'RECTOR_OFFICE']))
            <x-table 
                cardTitle="Báo cáo tiến độ chờ duyệt"
                :headers="['Nhiệm vụ & Đơn vị', 'Tiến độ']"
                :hasActions="true"
                actionTitle=""
                :empty="$pendingReports->isEmpty()"
                emptyTitle="Không có báo cáo chờ duyệt"
                emptyMessage="Tất cả báo cáo tiến độ đã được thẩm định."
            >
                @foreach($pendingReports as $report)
                    @php
                        $rTask = $report->phanCongNhiemVu?->nhiemVu;
                        $rUnit = $report->phanCongNhiemVu?->donVi?->TenDonVi ?: 'Đơn vị';
                    @endphp
                    <tr>
                        <td class="stu-max-w-xs">
                            <div class="stu-truncate" style="font-weight: 600; font-size: 12px;" title="{{ $rTask?->TenNhiemVu }}">
                                {{ $rTask?->TenNhiemVu ?: 'Báo cáo định kỳ' }}
                            </div>
                            <div style="font-size: 11px; color: var(--color-text-muted);">
                                {{ $rUnit }} • {{ $report->NgayBaoCao ? $report->NgayBaoCao->format('d/m') : '' }}
                            </div>
                        </td>
                        <td style="white-space: nowrap;">
                            <span class="stu-badge stu-badge-warning" style="padding: 2px 6px;">
                                {{ $report->TienDo ?? 0 }}%
                            </span>
                        </td>
                        <td class="col-actions">
                            <x-button variant="primary" size="sm" href="{{ url('/reports/' . $report->MaBaoCao) }}">
                                <span>Duyệt</span>
                            </x-button>
                        </td>
                    </tr>
                @endforeach
            </x-table>
        @endif
    </div>
</div>
@endsection
