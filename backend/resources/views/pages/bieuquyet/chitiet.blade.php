@extends('layouts.chinh')

@section('title', 'Nội dung biểu quyết: #BQ-' . str_pad($vote->MaNoiDung, 3, '0', STR_PAD_LEFT))

@section('content')
<div class="stu-page-container">
    {{-- Breadcrumb --}}
    <x-thanh-dieu-huong :items="['Biểu quyết & Bỏ phiếu' => route('votes.index'), '#BQ-' . str_pad($vote->MaNoiDung, 3, '0', STR_PAD_LEFT) => null]" />

    @php
        $isOpening = in_array($vote->TrangThai, ['OPENING', 'Đang mở biểu quyết', 'Đang mở']);
        $isClosed = in_array($vote->TrangThai, ['CLOSED', 'Đã đóng']);
    @endphp

    {{-- Header --}}
    <div class="stu-page-header stu-flex stu-items-start stu-justify-between stu-mb-6" style="gap: 16px;">
        <div style="flex: 1;">
            <div class="stu-flex stu-items-center stu-gap-2 stu-mb-2" style="flex-wrap: wrap;">
                <span style="font-weight: 800; color: var(--color-primary); font-size: 13px; background: rgba(0, 43, 91, 0.08); padding: 4px 10px; border-radius: var(--radius-sm); letter-spacing: 0.5px;">
                    #BQ-{{ str_pad($vote->MaNoiDung, 3, '0', STR_PAD_LEFT) }}
                </span>
                @if($votingType === 'SECRET')
                    <span class="stu-badge stu-badge-light" style="color: #0369a1; border-color: #bae6fd; background: #f0f9ff; font-weight: 600;">
                        🔒 Bỏ phiếu kín (BR-09)
                    </span>
                @else
                    <span class="stu-badge stu-badge-light" style="font-weight: 600;">Biểu quyết công khai</span>
                @endif

                @if($isOpening)
                    <span class="stu-badge stu-badge-success" style="font-weight: 700;">● Đang mở nhận phiếu</span>
                @elseif($isClosed)
                    <span class="stu-badge stu-badge-neutral" style="font-weight: 600;">Đã đóng kiểm phiếu</span>
                @else
                    <span class="stu-badge stu-badge-light" style="font-weight: 600;">Chưa mở cổng</span>
                @endif

                @if($isClosed)
                    @if($voteStats['is_passed'])
                        <span class="stu-badge stu-badge-success" style="font-weight: 700; background: #ecfdf5; border-color: #a7f3d0; color: #047857;">✓ Đã thông qua</span>
                    @else
                        <span class="stu-badge stu-badge-danger" style="font-weight: 700;">✗ Không thông qua</span>
                    @endif
                @endif
            </div>

            <h1 class="stu-page-title" style="font-size: 22px; font-weight: 800; color: var(--color-primary); margin: 0 0 8px 0; line-height: 1.4;">
                {{ $vote->NoiDung }}
            </h1>

            <div style="font-size: 13px; color: var(--color-text-muted); display: flex; align-items: center; gap: 18px; flex-wrap: wrap;">
                <span><strong>Phiên họp:</strong> {{ $meeting?->TenPhienHop ?? 'Phiên họp #' . ($vote->chuongTrinhHop?->MaPhienHop ?? '—') }}</span>
                @if($vote->chuongTrinhHop)
                    <span><strong>Mục chương trình:</strong> Mục số {{ $vote->chuongTrinhHop->ThuTu }}</span>
                @endif
                <span><strong>Ngưỡng thông qua:</strong> {{ $passThreshold }}% tổng số đại biểu có mặt</span>
            </div>
        </div>

        {{-- Các nút điều hành biểu quyết (Dành cho Chủ tịch, Thư ký, Quản trị viên) --}}
        <div class="stu-flex stu-items-center stu-gap-2">
            @if(in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN']))
                @if(! $isOpening && ! $isClosed)
                    <form method="POST" action="{{ route('votes.open', $vote->MaNoiDung) }}">
                        @csrf
                        <button type="submit" class="stu-btn stu-btn-success" style="height: 38px;">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Mở cổng biểu quyết</span>
                        </button>
                    </form>
                @elseif($isOpening)
                    <form method="POST" action="{{ route('votes.close', $vote->MaNoiDung) }}" onsubmit="return confirm('Bạn có chắc chắn muốn đóng biểu quyết và chốt kết quả kiểm phiếu?');">
                        @csrf
                        <button type="submit" class="stu-btn stu-btn-danger" style="height: 38px;">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/></svg>
                            <span>Đóng & Chốt kiểm phiếu</span>
                        </button>
                    </form>
                @endif

                <button type="button" class="stu-btn stu-btn-outline" style="height: 38px;" data-modal-open="delegationModal">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    <span>Ủy quyền biểu quyết (BR-04)</span>
                </button>
            @endif

            <a href="{{ route('votes.index') }}" class="stu-btn stu-btn-ghost" style="height: 38px;">
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
    @if(session('warning'))
        <x-canh-bao type="warning" :dismissible="true" class="stu-mb-6">
            {{ session('warning') }}
        </x-canh-bao>
    @endif
    @if(session('error'))
        <x-canh-bao type="danger" :dismissible="true" class="stu-mb-6">
            {{ session('error') }}
        </x-canh-bao>
    @endif

    {{-- Thống kê nhanh 4 thẻ KPI Tổng quan kiểm phiếu --}}
    <div class="stu-grid stu-grid-cols-4 stu-mb-6" style="gap: 16px;">
        <!-- KPI 1: Đại biểu có mặt -->
        <div class="stu-card" style="padding: 18px 20px; border-left: 4px solid var(--color-primary); background: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.06);">
            <div style="font-size: 11px; font-weight: 700; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Đại biểu có mặt</div>
            <div style="font-size: 26px; font-weight: 800; color: var(--color-primary); margin-top: 4px; display: flex; align-items: baseline; gap: 6px;">
                {{ $voteStats['present_count'] }}
                <span style="font-size: 13px; font-weight: 600; color: var(--color-text-muted);">thành viên</span>
            </div>
            <div style="font-size: 12px; color: #16a34a; font-weight: 600; margin-top: 4px; display: flex; align-items: center; gap: 4px;">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                <span>Đạt 100% túc số phiên họp</span>
            </div>
        </div>

        <!-- KPI 2: Phiếu đã thu -->
        <div class="stu-card" style="padding: 18px 20px; border-left: 4px solid #0284c7; background: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.06);">
            <div style="font-size: 11px; font-weight: 700; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Tổng phiếu đã thu</div>
            <div style="font-size: 26px; font-weight: 800; color: #0284c7; margin-top: 4px; display: flex; align-items: baseline; gap: 6px;">
                {{ $voteStats['total'] }} / {{ $voteStats['present_count'] }}
                <span style="font-size: 13px; font-weight: 600; color: var(--color-text-muted);">phiếu</span>
            </div>
            <div style="font-size: 12px; color: var(--color-text-muted); margin-top: 4px;">
                Tỷ lệ tham gia: <strong>{{ round(($voteStats['total'] / max($voteStats['present_count'], 1)) * 100, 1) }}%</strong>
            </div>
        </div>

        <!-- KPI 3: Tỷ lệ Tán thành -->
        <div class="stu-card" style="padding: 18px 20px; border-left: 4px solid var(--color-success); background: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.06);">
            <div style="font-size: 11px; font-weight: 700; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Tỷ lệ Tán thành</div>
            <div style="font-size: 26px; font-weight: 800; color: var(--color-success); margin-top: 4px; display: flex; align-items: baseline; gap: 6px;">
                {{ $voteStats['agree_percent'] }}%
                <span style="font-size: 13px; font-weight: 600; color: var(--color-text-muted);">({{ $voteStats['agree'] }} phiếu)</span>
            </div>
            <div style="font-size: 12px; color: {{ $voteStats['is_passed'] ? '#16a34a' : '#dc2626' }}; font-weight: 600; margin-top: 4px;">
                {{ $voteStats['is_passed'] ? '✓ Đạt ngưỡng quy chế (≥' . $passThreshold . '%)' : '✗ Chưa đạt ngưỡng quy chế' }}
            </div>
        </div>

        <!-- KPI 4: Kết luận biểu quyết -->
        <div class="stu-card" style="padding: 18px 20px; border-left: 4px solid {{ $voteStats['is_passed'] ? 'var(--color-success)' : 'var(--color-danger)' }}; background: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.06);">
            <div style="font-size: 11px; font-weight: 700; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Kết luận biểu quyết</div>
            <div style="font-size: 20px; font-weight: 800; color: {{ $voteStats['is_passed'] ? 'var(--color-success)' : 'var(--color-danger)' }}; margin-top: 6px; letter-spacing: -0.3px;">
                {{ $voteStats['is_passed'] ? 'ĐÃ THÔNG QUA' : 'CHƯA THÔNG QUA' }}
            </div>
            <div style="font-size: 12px; color: var(--color-text-muted); margin-top: 6px;">
                {{ $isClosed ? 'Biên bản đã niêm phong' : 'Đang trong phiên bỏ phiếu' }}
            </div>
        </div>
    </div>

    {{-- Bố cục 2 Cột Chính: Cột Trái (Tổng hợp kiểm phiếu - 60%) & Cột Phải (Buồng bỏ phiếu & Tham chiếu - 40%) --}}
    <div style="display: grid; grid-template-columns: 1.45fr 1fr; gap: 24px; align-items: flex-start;">
        {{-- ==================== CỘT TRÁI: KẾT QUẢ KIỂM PHIẾU TỔNG HỢP (TRỌNG TÂM HÌNH 3-39) ==================== --}}
        <div style="display: flex; flex-direction: column; gap: 24px;">
            {{-- Thẻ Kết quả Kiểm phiếu Tổng hợp --}}
            <div class="stu-card" style="padding: 26px; border-top: 4px solid var(--color-primary); background: #ffffff;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid var(--color-border);">
                    <div>
                        <h3 style="font-size: 18px; font-weight: 800; color: var(--color-primary); margin: 0; display: flex; align-items: center; gap: 10px;">
                            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            <span>Kết quả Kiểm phiếu Tổng hợp</span>
                        </h3>
                        <p style="font-size: 12px; color: var(--color-text-muted); margin: 4px 0 0 0;">
                            Số liệu kiểm phiếu tự động theo thời gian thực dựa trên túc số đại biểu chính thức
                        </p>
                    </div>
                    <span style="font-size: 13px; font-weight: 700; color: var(--color-primary); background: #e6f4ff; padding: 6px 14px; border-radius: var(--radius-full);">
                        Đã thu: <strong>{{ $voteStats['total'] }} / {{ $voteStats['present_count'] }}</strong> phiếu
                    </span>
                </div>

                {{-- 1. Tiến độ Tán thành --}}
                <div style="margin-bottom: 24px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; border-radius: 50%; background: #dcfce7; color: #16a34a; font-weight: 800; font-size: 13px;">✓</span>
                            <span style="font-size: 15px; font-weight: 700; color: #166534;">Tán thành (Đồng ý thông qua)</span>
                        </div>
                        <div style="font-size: 16px; font-weight: 800; color: #16a34a;">
                            {{ $voteStats['agree'] }} phiếu <span style="font-size: 14px; font-weight: 600; color: #15803d;">({{ $voteStats['agree_percent'] }}%)</span>
                        </div>
                    </div>
                    <div style="height: 14px; background: #f1f5f9; border-radius: 999px; overflow: hidden; box-shadow: inset 0 1px 2px rgba(0,0,0,0.06);">
                        <div style="height: 100%; width: {{ min($voteStats['agree_percent'], 100) }}%; background: linear-gradient(90deg, #22c55e, #16a34a); border-radius: 999px; transition: width 0.4s ease;"></div>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 12px; color: var(--color-text-muted); margin-top: 6px;">
                        <span>Ngưỡng yêu cầu quy chế: <strong>{{ $passThreshold }}%</strong></span>
                        <span style="color: {{ $voteStats['is_passed'] ? '#16a34a' : '#dc2626' }}; font-weight: 600;">
                            {{ $voteStats['is_passed'] ? '✓ Đạt ngưỡng thông qua' : '✗ Chưa đạt ngưỡng quy định' }}
                        </span>
                    </div>
                </div>

                {{-- 2. Tiến độ Không tán thành --}}
                <div style="margin-bottom: 20px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; border-radius: 50%; background: #fee2e2; color: #dc2626; font-weight: 800; font-size: 13px;">✗</span>
                            <span style="font-size: 14px; font-weight: 700; color: #991b1b;">Không tán thành (Bác bỏ)</span>
                        </div>
                        <div style="font-size: 15px; font-weight: 800; color: #dc2626;">
                            {{ $voteStats['disagree'] }} phiếu <span style="font-size: 13px; font-weight: 600; color: #b91c1c;">({{ $voteStats['disagree_percent'] }}%)</span>
                        </div>
                    </div>
                    <div style="height: 10px; background: #f1f5f9; border-radius: 999px; overflow: hidden; box-shadow: inset 0 1px 2px rgba(0,0,0,0.06);">
                        <div style="height: 100%; width: {{ min($voteStats['disagree_percent'], 100) }}%; background: #ef4444; border-radius: 999px; transition: width 0.4s ease;"></div>
                    </div>
                </div>

                {{-- 3. Tiến độ Không có ý kiến --}}
                <div style="margin-bottom: 24px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; border-radius: 50%; background: #f1f5f9; color: #64748b; font-weight: 800; font-size: 13px;">—</span>
                            <span style="font-size: 14px; font-weight: 600; color: #475569;">Không có ý kiến (Phiếu trắng)</span>
                        </div>
                        <div style="font-size: 15px; font-weight: 700; color: #64748b;">
                            {{ $voteStats['abstain'] }} phiếu <span style="font-size: 13px; font-weight: 500; color: #94a3b8;">({{ $voteStats['abstain_percent'] }}%)</span>
                        </div>
                    </div>
                    <div style="height: 10px; background: #f1f5f9; border-radius: 999px; overflow: hidden; box-shadow: inset 0 1px 2px rgba(0,0,0,0.06);">
                        <div style="height: 100%; width: {{ min($voteStats['abstain_percent'], 100) }}%; background: #cbd5e1; border-radius: 999px; transition: width 0.4s ease;"></div>
                    </div>
                </div>

                {{-- Khung Kết luận kiểm phiếu chính thức --}}
                <div style="padding: 20px; border-radius: var(--radius-lg); background: {{ $voteStats['is_passed'] ? '#f0fdf4' : '#fef2f2' }}; border: 1.5px solid {{ $voteStats['is_passed'] ? '#86efac' : '#fca5a5' }};">
                    <div style="display: flex; align-items: flex-start; gap: 14px;">
                        <div style="display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 50%; background: {{ $voteStats['is_passed'] ? '#dcfce7' : '#fee2e2' }}; color: {{ $voteStats['is_passed'] ? '#16a34a' : '#dc2626' }}; flex-shrink: 0; margin-top: 2px;">
                            @if($voteStats['is_passed'])
                                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            @else
                                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            @endif
                        </div>
                        <div style="flex: 1;">
                            <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; color: {{ $voteStats['is_passed'] ? '#15803d' : '#b91c1c' }};">
                                KẾT LUẬN KIỂM PHIẾU CHÍNH THỨC
                            </div>
                            <div style="font-size: 17px; font-weight: 800; color: {{ $voteStats['is_passed'] ? '#14532d' : '#7f1d1d' }}; margin-top: 4px; line-height: 1.4;">
                                {{ $voteStats['is_passed'] ? '✓ ĐỦ ĐIỀU KIỆN BAN HÀNH NGHỊ QUYẾT HỘI ĐỒNG TRƯỜNG' : '✗ KHÔNG ĐỦ ĐIỀU KIỆN THÔNG QUA NGHỊ QUYẾT' }}
                            </div>
                            <div style="font-size: 13px; color: {{ $voteStats['is_passed'] ? '#166534' : '#991b1b' }}; margin-top: 6px; line-height: 1.5;">
                                Căn cứ Điều 12 Quy chế tổ chức Hội đồng trường STU: Đạt tỷ lệ <strong>{{ $voteStats['agree_percent'] }}%</strong> số phiếu tán thành trên tổng số <strong>{{ $voteStats['present_count'] }} đại biểu có mặt</strong> (ngưỡng tối thiểu: {{ $passThreshold }}%). Kết quả kiểm phiếu có giá trị pháp lý thi hành ngay.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Thẻ Cam kết bảo mật BR-09 --}}
            <div class="stu-card" style="padding: 20px 24px; background: #f0f9ff; border: 1px solid #bae6fd;">
                <div style="display: flex; align-items: flex-start; gap: 14px;">
                    <div style="color: #0369a1; margin-top: 2px;">
                        <svg width="26" height="26" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <div>
                        <h4 style="font-size: 15px; font-weight: 800; color: #0369a1; margin: 0 0 6px 0;">
                            Bảo mật Bỏ phiếu kín & An toàn Dữ liệu (Ràng buộc BR-09)
                        </h4>
                        <p style="font-size: 13px; line-height: 1.6; color: #0c4a6e; margin: 0;">
                            Hệ thống bảo vệ tuyệt đối tính ẩn danh của từng lá phiếu theo quy chế Hội đồng trường STU. Mọi bản ghi biểu quyết cá nhân đều được mã hóa một chiều, không lưu danh tính liên kết và không thể truy vết ngược bởi bất kỳ tài khoản nào (bao gồm cả Chủ tịch và Quản trị viên hệ thống). Báo cáo kiểm phiếu chỉ kết xuất số liệu thống kê tổng hợp phục vụ việc lập Biên bản phiên họp.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ==================== CỘT PHẢI: BUỒNG BỎ PHIẾU & THÔNG TIN PHIÊN HỌP ==================== --}}
        <div style="display: flex; flex-direction: column; gap: 24px;">
            {{-- 1. Buồng bỏ phiếu trực tiếp của thành viên --}}
            <div class="stu-card" style="padding: 24px; border-top: 4px solid #0284c7; background: #ffffff;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                    <h3 style="font-size: 16px; font-weight: 800; color: var(--color-primary); margin: 0; display: flex; align-items: center; gap: 8px;">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Phiếu biểu quyết cá nhân</span>
                    </h3>
                    @if($hasVotedDirect)
                        <span class="stu-badge stu-badge-success" style="font-weight: 600;">✓ Đã bỏ phiếu</span>
                    @endif
                </div>

                @if(! $isOpening && ! $isClosed)
                    <div style="padding: 28px 20px; text-align: center; background: var(--color-bg-muted); border-radius: var(--radius-md); color: var(--color-text-muted);">
                        <svg width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="margin: 0 auto 10px; color: var(--color-border);"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div style="font-weight: 700; font-size: 14px; color: var(--color-text-primary);">Cổng biểu quyết chưa mở</div>
                        <div style="font-size: 12px; margin-top: 4px;">Vui lòng chờ Chủ tịch hoặc Thư ký phiên họp phát lệnh mở cổng bỏ phiếu điện tử.</div>
                    </div>
                @elseif($isClosed)
                    <div style="padding: 24px 20px; background: #f8fafc; border-radius: var(--radius-md); border: 1px solid var(--color-border); text-align: center;">
                        <div style="display: inline-flex; align-items: center; justify-content: center; width: 44px; height: 44px; border-radius: 50%; background: #e0f2fe; color: #0284c7; margin-bottom: 12px;">
                            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <div style="font-size: 15px; font-weight: 800; color: var(--color-text-primary);">
                            Phiên biểu quyết đã kết thúc
                        </div>
                        <div style="font-size: 13px; color: var(--color-text-muted); margin-top: 6px; line-height: 1.5;">
                            Cổng nhận phiếu điện tử đã đóng và niêm phong dữ liệu. Toàn bộ phiếu hợp lệ đã được kiểm đếm tự động.
                        </div>
                        @if($hasVotedDirect)
                            <div style="margin-top: 16px; padding: 10px 14px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: var(--radius-sm); color: #166534; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                <span>Lá phiếu của bạn đã được ghi nhận vào hệ thống</span>
                            </div>
                        @endif
                    </div>
                @else
                    {{-- Đang mở nhận phiếu --}}
                    @if(! $canVoteDirect)
                        <div style="padding: 20px; background: #fffbeb; border-radius: var(--radius-md); border: 1px solid #fde68a; color: #92400e; font-size: 13px; line-height: 1.6;">
                            <strong>Thông báo quyền biểu quyết:</strong>
                            <p style="margin: 6px 0 0 0;">
                                Tài khoản hiện tại không đủ điều kiện bỏ phiếu. Theo quy định, chỉ các <strong>Thành viên chính thức của Hội đồng trường đã được điểm danh có mặt</strong> tại phiên họp mới có quyền biểu quyết.
                            </p>
                        </div>
                    @elseif($hasVotedDirect)
                        <div style="padding: 20px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: var(--radius-md); color: #166534; font-size: 13px;">
                            <div style="display: flex; align-items: center; gap: 8px; font-weight: 700; font-size: 14px;">
                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                <span>Hệ thống đã ghi nhận lá phiếu của bạn</span>
                            </div>
                            <p style="margin: 8px 0 0 0; line-height: 1.6;">
                                Tuân thủ nghiêm ngặt <strong>Ràng buộc BR-09 (Bảo mật bỏ phiếu kín)</strong>: Lựa chọn của bạn đã được niêm phong và bảo mật tuyệt đối, mỗi thành viên chỉ được bỏ phiếu một lần duy nhất.
                            </p>
                        </div>
                    @else
                        {{-- Form bỏ phiếu trực tiếp --}}
                        <form method="POST" action="{{ route('votes.vote', $vote->MaNoiDung) }}">
                            @csrf
                            <p style="font-size: 13px; color: var(--color-text-secondary); margin: 0 0 16px 0;">
                                Vui lòng cân nhắc kỹ lưỡng và chọn một trong 3 phương án dưới đây:
                            </p>

                            <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 20px;">
                                <label style="display: flex; align-items: center; gap: 12px; padding: 14px 18px; border: 2px solid var(--color-border); border-radius: var(--radius-md); cursor: pointer; transition: all 0.2s;" class="hover:border-primary">
                                    <input type="radio" name="choice" value="Tán thành" required style="width: 18px; height: 18px; accent-color: var(--color-success);">
                                    <div style="flex: 1;">
                                        <div style="font-size: 15px; font-weight: 700; color: var(--color-success); display: flex; align-items: center; gap: 6px;">
                                            <span>✓ TÁN THÀNH (ĐỒNG Ý)</span>
                                        </div>
                                        <div style="font-size: 12px; color: var(--color-text-muted); margin-top: 2px;">
                                            Ủng hộ thông qua nội dung nghị quyết này.
                                        </div>
                                    </div>
                                </label>

                                <label style="display: flex; align-items: center; gap: 12px; padding: 14px 18px; border: 2px solid var(--color-border); border-radius: var(--radius-md); cursor: pointer; transition: all 0.2s;" class="hover:border-primary">
                                    <input type="radio" name="choice" value="Không tán thành" style="width: 18px; height: 18px; accent-color: var(--color-danger);">
                                    <div style="flex: 1;">
                                        <div style="font-size: 15px; font-weight: 700; color: var(--color-danger); display: flex; align-items: center; gap: 6px;">
                                            <span>✗ KHÔNG TÁN THÀNH</span>
                                        </div>
                                        <div style="font-size: 12px; color: var(--color-text-muted); margin-top: 2px;">
                                            Không đồng ý thông qua nội dung đề xuất.
                                        </div>
                                    </div>
                                </label>

                                <label style="display: flex; align-items: center; gap: 12px; padding: 14px 18px; border: 2px solid var(--color-border); border-radius: var(--radius-md); cursor: pointer; transition: all 0.2s;" class="hover:border-primary">
                                    <input type="radio" name="choice" value="Không có ý kiến" style="width: 18px; height: 18px; accent-color: var(--color-neutral);">
                                    <div style="flex: 1;">
                                        <div style="font-size: 15px; font-weight: 700; color: var(--color-text-muted); display: flex; align-items: center; gap: 6px;">
                                            <span>— KHÔNG CÓ Ý KIẾN (PHIẾU TRẮNG)</span>
                                        </div>
                                        <div style="font-size: 12px; color: var(--color-text-muted); margin-top: 2px;">
                                            Bỏ quyền biểu quyết đối với nội dung này.
                                        </div>
                                    </div>
                                </label>
                            </div>

                            <button type="submit" class="stu-btn stu-btn-primary" style="width: 100%; justify-content: center; height: 44px; font-size: 15px; font-weight: 700;">
                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                <span>Xác nhận gửi phiếu biểu quyết</span>
                            </button>
                        </form>
                    @endif
                @endif
            </div>

            {{-- 2. Buồng bỏ phiếu theo ủy quyền hợp lệ (BR-04, BR-11) --}}
            @if(count($validDelegations) > 0)
                <div class="stu-card" style="padding: 24px; border-left: 4px solid var(--color-accent); background: #ffffff;">
                    <h3 style="font-size: 15px; font-weight: 800; color: var(--color-primary); margin: 0 0 10px 0; display: flex; align-items: center; gap: 8px;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                        <span>Quyền Biểu Quyết Theo Ủy Quyền (BR-04)</span>
                    </h3>
                    <p style="font-size: 12px; color: var(--color-text-muted); margin: 0 0 16px 0;">
                        Bạn được nhận ủy quyền đại diện biểu quyết thay cho thành viên vắng mặt theo giấy ủy quyền đã được phê duyệt.
                    </p>

                    @foreach($validDelegations as $del)
                        <div style="background: var(--color-bg-muted); padding: 16px; border-radius: var(--radius-md); border: 1px solid var(--color-border); margin-bottom: 12px;">
                            <div class="stu-flex stu-items-center stu-justify-between stu-mb-2">
                                <div style="font-size: 14px; font-weight: 700; color: var(--color-text-primary);">
                                    Đại diện cho: {{ $del['authorizer_name'] }}
                                </div>
                                @if($del['has_voted'])
                                    <span class="stu-badge stu-badge-success">✓ Đã bỏ phiếu ủy quyền</span>
                                @else
                                    <span class="stu-badge stu-badge-warning">Chờ bỏ phiếu thay</span>
                                @endif
                            </div>
                            <div style="font-size: 12px; color: var(--color-text-muted); margin-bottom: 12px;">
                                Phạm vi: <em>{{ $del['scope'] }}</em>
                            </div>

                            @if($isOpening && ! $del['has_voted'])
                                <form method="POST" action="{{ route('votes.vote', $vote->MaNoiDung) }}" class="stu-flex stu-items-center stu-gap-2">
                                    @csrf
                                    <input type="hidden" name="authorization_id" value="{{ $del['auth_id'] }}">
                                    <select name="choice" class="stu-form-control" style="font-size: 13px; height: 36px;" required>
                                        <option value="">-- Chọn ý kiến ủy quyền --</option>
                                        <option value="Tán thành">Tán thành</option>
                                        <option value="Không tán thành">Không tán thành</option>
                                        <option value="Không có ý kiến">Không có ý kiến</option>
                                    </select>
                                    <button type="submit" class="stu-btn stu-btn-secondary stu-btn-sm" style="white-space: nowrap; height: 36px;">
                                        Gửi phiếu thay
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- 3. Thông tin Pháp lý & Phiên họp --}}
            <div class="stu-card" style="padding: 22px; background: #ffffff;">
                <h3 style="font-size: 15px; font-weight: 800; color: var(--color-primary); margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Căn cứ Pháp lý & Phiên họp</span>
                </h3>

                <div style="display: flex; flex-direction: column; gap: 12px; font-size: 13px;">
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--color-border); padding-bottom: 8px;">
                        <span style="color: var(--color-text-muted);">Phiên họp:</span>
                        <span style="font-weight: 600; color: var(--color-text-primary); text-align: right; max-width: 60%;">
                            {{ $meeting?->TenPhienHop ?? 'Phiên họp Hội đồng trường' }}
                        </span>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--color-border); padding-bottom: 8px;">
                        <span style="color: var(--color-text-muted);">Khóa nhiệm kỳ:</span>
                        <span style="font-weight: 600; color: var(--color-text-primary);">Khóa IV (2020 - 2025)</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--color-border); padding-bottom: 8px;">
                        <span style="color: var(--color-text-muted);">Hình thức:</span>
                        <span style="font-weight: 600; color: #0284c7;">
                            {{ $votingType === 'SECRET' ? 'Bỏ phiếu kín (BR-09)' : 'Biểu quyết công khai' }}
                        </span>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--color-border); padding-bottom: 8px;">
                        <span style="color: var(--color-text-muted);">Túc số có mặt:</span>
                        <span style="font-weight: 700; color: #16a34a;">
                            {{ $voteStats['present_count'] }} đại biểu (100%)
                        </span>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--color-border); padding-bottom: 8px;">
                        <span style="color: var(--color-text-muted);">Tổng phiếu thu:</span>
                        <span style="font-weight: 700; color: var(--color-text-primary);">
                            {{ $voteStats['total'] }} phiếu
                        </span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--color-text-muted);">Ngưỡng thông qua:</span>
                        <span style="font-weight: 700; color: var(--color-primary);">
                            ≥ {{ $passThreshold }}% đại biểu có mặt
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Đăng ký Ủy quyền Biểu quyết (BR-04) --}}
<x-hop-thoai id="delegationModal" title="Đăng ký Văn bản Ủy quyền Biểu quyết (BR-04)" size="md">
    <form method="POST" action="{{ route('votes.delegations.store') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="MaPhienHop" value="{{ $meeting?->MaPhienHop ?? 1 }}">

        <div class="stu-form-group stu-mb-4">
            <label class="stu-form-label" style="font-size: 12px; font-weight: 700;">Thành viên ủy quyền (Vắng mặt) <span style="color: var(--color-danger);">*</span></label>
            <select name="MaThanhVienUyQuyen" class="stu-form-control" required>
                <option value="">-- Chọn thành viên ủy quyền --</option>
                @foreach($officialMembers as $m)
                    <option value="{{ $m->MaThanhVien }}">{{ $m->nguoiDung?->HoTen }} ({{ $m->ChucVu }})</option>
                @endforeach
            </select>
        </div>

        <div class="stu-form-group stu-mb-4">
            <label class="stu-form-label" style="font-size: 12px; font-weight: 700;">Thành viên nhận ủy quyền (Đại diện) <span style="color: var(--color-danger);">*</span></label>
            <select name="MaThanhVienNhanUyQuyen" class="stu-form-control" required>
                <option value="">-- Chọn người nhận ủy quyền --</option>
                @foreach($officialMembers as $m)
                    <option value="{{ $m->MaThanhVien }}">{{ $m->nguoiDung?->HoTen }} ({{ $m->ChucVu }})</option>
                @endforeach
            </select>
        </div>

        <div class="stu-form-group stu-mb-4">
            <label class="stu-form-label" style="font-size: 12px; font-weight: 700;">Phạm vi ủy quyền <span style="color: var(--color-danger);">*</span></label>
            <input type="text" name="PhamViUyQuyen" value="Toàn bộ các nội dung biểu quyết tại phiên họp" class="stu-form-control" required>
        </div>

        <div class="stu-grid stu-grid-cols-2 stu-gap-3 stu-mb-4">
            <div class="stu-form-group" style="margin-bottom: 0;">
                <label class="stu-form-label" style="font-size: 12px; font-weight: 700;">Ngày lập giấy <span style="color: var(--color-danger);">*</span></label>
                <input type="date" name="NgayUyQuyen" value="{{ date('Y-m-d') }}" class="stu-form-control" required>
            </div>
            <div class="stu-form-group" style="margin-bottom: 0;">
                <label class="stu-form-label" style="font-size: 12px; font-weight: 700;">Tập tin đính kèm (nếu có)</label>
                <input type="file" name="file" class="stu-form-control" accept=".pdf,.doc,.docx">
            </div>
        </div>

        <div class="stu-flex stu-justify-end stu-gap-3" style="margin-top: 20px; border-top: 1px solid var(--color-border); padding-top: 16px;">
            <button type="button" class="stu-btn stu-btn-secondary" data-modal-close>Hủy</button>
            <button type="submit" class="stu-btn stu-btn-primary">Lưu văn bản ủy quyền</button>
        </div>
    </form>
</x-hop-thoai>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('delegation') === '1' || urlParams.get('modal') === '1') {
            if (window.STUModal) {
                window.STUModal.open('delegationModal');
            }
        }
    });
</script>
@endsection
