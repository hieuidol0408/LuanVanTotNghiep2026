@extends('layouts.app')

@section('title', 'Hồ Sơ Cá Nhân - Hội Đồng Trường STU')

@section('header_title')
    <x-breadcrumb :items="[
        ['title' => 'Bảng điều hành', 'url' => url('/dashboard')],
        ['title' => 'Hồ sơ cá nhân']
    ]" />
@endsection

@section('content')
<!-- BANNER HỒ SƠ CÁ NHÂN -->
<div class="stu-card" style="padding: var(--space-6); margin-bottom: var(--space-6); background: #002766; background: linear-gradient(135deg, #003A8C 0%, #001D47 100%) !important; color: #FFFFFF !important; border: none; box-shadow: 0 4px 20px rgba(0, 29, 71, 0.25);">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: var(--space-4);">
        <div style="display: flex; align-items: center; gap: var(--space-5);">
            <div style="width: 72px; height: 72px; border-radius: var(--radius-full); background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; font-size: 28px; font-weight: 700; color: #FFFFFF; border: 3px solid rgba(255,255,255,0.4); box-shadow: 0 4px 12px rgba(0,0,0,0.2);">
                {{ strtoupper(substr($user->full_name ?? 'HĐT', 0, 1)) }}
            </div>
            <div>
                <div style="display: flex; align-items: center; gap: var(--space-3); flex-wrap: wrap; margin-bottom: 6px;">
                    <h1 style="font-size: var(--font-size-2xl); font-weight: 700; color: #FFFFFF !important; margin: 0; letter-spacing: -0.01em;">
                        {{ $user->full_name }}
                    </h1>
                    <span class="stu-badge stu-badge-success" style="font-size: 11px; padding: 2px 8px;">
                        Tài khoản đang hoạt động
                    </span>
                </div>
                <p style="font-size: var(--font-size-sm); color: #BAE0FF !important; margin: 0; display: flex; align-items: center; gap: var(--space-3); flex-wrap: wrap;">
                    <span>Vai trò hệ thống: <strong style="color: #FFFFFF;">{{ $user->role }}</strong></span>
                    <span>•</span>
                    <span>Đơn vị: <strong style="color: #FFFFFF;">{{ $user->donVi?->TenDonVi ?? 'Văn phòng Hội đồng trường' }}</strong></span>
                    <span>•</span>
                    <span>Nhiệm kỳ <strong style="color: #FFFFFF;">{{ $stats['nhiem_ky'] }}</strong></span>
                </p>
            </div>
        </div>

        <div style="display: flex; gap: var(--space-2);">
            <x-button variant="outline" href="{{ url('/dashboard') }}" style="background: rgba(255,255,255,0.15) !important; color: #FFFFFF !important; border-color: rgba(255,255,255,0.4) !important;">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Quay lại Dashboard</span>
            </x-button>
        </div>
    </div>
</div>

<!-- KHU VỰC THỐNG KÊ HOẠT ĐỘNG CÁ NHÂN -->
<section style="margin-bottom: var(--space-6);">
    <div class="stu-grid stu-grid-cols-4">
        <x-stat-card 
            title="Phiên họp tham gia" 
            :value="$stats['meetings_count']" 
            subtitle="Được triệu tập trong nhiệm kỳ" 
            type="primary" 
            link="{{ url('/meetings') }}"
        >
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        </x-stat-card>

        <x-stat-card 
            title="Quyền biểu quyết" 
            :value="$stats['co_quyen_bieu_quyet'] ? 'ĐỦ ĐIỀU KIỆN' : 'KHÔNG'" 
            :subtitle="$stats['co_quyen_bieu_quyet'] ? 'Thành viên chính thức HĐT' : 'Khách mời / Đại biểu'" 
            type="{{ $stats['co_quyen_bieu_quyet'] ? 'success' : 'neutral' }}" 
            link="{{ url('/voting') }}"
        >
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </x-stat-card>

        <x-stat-card 
            title="Hồ sơ đã nộp" 
            :value="$stats['submissions_count']" 
            subtitle="Hồ sơ trình Thường trực HĐT" 
            type="info" 
            link="{{ url('/submissions') }}"
        >
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        </x-stat-card>

        <x-stat-card 
            title="Nhiệm vụ đơn vị" 
            :value="$stats['tasks_count']" 
            subtitle="Nhiệm vụ theo Nghị quyết" 
            type="warning" 
            link="{{ url('/tasks') }}"
        >
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </x-stat-card>
    </div>
</section>

<!-- BỐ CỤC CHÍNH 2 CỘT -->
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: var(--space-6); align-items: start;">
    <!-- CỘT TRÁI: FORM CẬP NHẬT THÔNG TIN & ĐỔI MẬT KHẨU -->
    <div style="display: flex; flex-direction: column; gap: var(--space-6);">
        <div class="stu-card">
            <div class="stu-card-header">
                <h3 class="stu-card-title">Cập nhật thông tin cá nhân</h3>
            </div>
            <div class="stu-card-body" style="padding: var(--space-6);">
                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-4); margin-bottom: var(--space-4);">
                        <div>
                            <label class="stu-form-label" for="HoTen" style="font-weight: 600; font-size: 13px; margin-bottom: 6px; display: block;">
                                Họ và tên <span style="color: var(--color-danger);">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="HoTen" 
                                name="HoTen" 
                                value="{{ old('HoTen', $user->full_name) }}" 
                                class="stu-form-control" 
                                required 
                                style="width: 100%; height: 38px; border-radius: 8px; border: 1px solid var(--color-border); padding: 0 12px; font-size: 13px;"
                            >
                            @error('HoTen')
                                <span style="color: var(--color-danger); font-size: 12px; margin-top: 4px; display: block;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="stu-form-label" for="email" style="font-weight: 600; font-size: 13px; margin-bottom: 6px; display: block;">
                                Địa chỉ Email <span style="font-size: 11px; color: var(--color-text-muted); font-weight: 400;">(Dùng đăng nhập hệ thống)</span>
                            </label>
                            <input 
                                type="email" 
                                id="email" 
                                value="{{ $user->email }}" 
                                class="stu-form-control" 
                                disabled 
                                style="width: 100%; height: 38px; border-radius: 8px; border: 1px solid var(--color-border); padding: 0 12px; font-size: 13px; background: var(--color-bg-subtle); color: var(--color-text-muted);"
                            >
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-4); margin-bottom: var(--space-6);">
                        <div>
                            <label class="stu-form-label" style="font-weight: 600; font-size: 13px; margin-bottom: 6px; display: block;">
                                Đơn vị / Khoa / Phòng ban
                            </label>
                            <input 
                                type="text" 
                                value="{{ $user->donVi?->TenDonVi ?? 'Văn phòng Hội đồng trường' }}" 
                                class="stu-form-control" 
                                disabled 
                                style="width: 100%; height: 38px; border-radius: 8px; border: 1px solid var(--color-border); padding: 0 12px; font-size: 13px; background: var(--color-bg-subtle); color: var(--color-text-muted);"
                            >
                        </div>

                        <div>
                            <label class="stu-form-label" style="font-weight: 600; font-size: 13px; margin-bottom: 6px; display: block;">
                                Vai trò người dùng
                            </label>
                            <input 
                                type="text" 
                                value="{{ $user->vaiTro?->TenVaiTro ?? $user->role }}" 
                                class="stu-form-control" 
                                disabled 
                                style="width: 100%; height: 38px; border-radius: 8px; border: 1px solid var(--color-border); padding: 0 12px; font-size: 13px; background: var(--color-bg-subtle); color: var(--color-text-muted);"
                            >
                        </div>
                    </div>

                    <!-- KHỐI ĐỔI MẬT KHẨU -->
                    <div style="border-top: 1px dashed var(--color-border); padding-top: var(--space-5); margin-bottom: var(--space-6);">
                        <h4 style="font-size: 14px; font-weight: 700; color: var(--color-text-main); margin-bottom: 4px;">
                            Thay đổi mật khẩu
                        </h4>
                        <p style="font-size: 12px; color: var(--color-text-muted); margin-bottom: var(--space-4);">
                            Để trống phần này nếu bạn không có nhu cầu thay đổi mật khẩu đăng nhập.
                        </p>

                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: var(--space-4);">
                            <div>
                                <label class="stu-form-label" for="current_password" style="font-weight: 500; font-size: 12px; margin-bottom: 6px; display: block;">
                                    Mật khẩu hiện tại
                                </label>
                                <input 
                                    type="password" 
                                    id="current_password" 
                                    name="current_password" 
                                    placeholder="••••••••" 
                                    class="stu-form-control" 
                                    style="width: 100%; height: 38px; border-radius: 8px; border: 1px solid var(--color-border); padding: 0 12px; font-size: 13px;"
                                >
                                @error('current_password')
                                    <span style="color: var(--color-danger); font-size: 11px; margin-top: 4px; display: block;">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label class="stu-form-label" for="new_password" style="font-weight: 500; font-size: 12px; margin-bottom: 6px; display: block;">
                                    Mật khẩu mới
                                </label>
                                <input 
                                    type="password" 
                                    id="new_password" 
                                    name="new_password" 
                                    placeholder="Ít nhất 6 ký tự" 
                                    class="stu-form-control" 
                                    style="width: 100%; height: 38px; border-radius: 8px; border: 1px solid var(--color-border); padding: 0 12px; font-size: 13px;"
                                >
                                @error('new_password')
                                    <span style="color: var(--color-danger); font-size: 11px; margin-top: 4px; display: block;">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label class="stu-form-label" for="new_password_confirmation" style="font-weight: 500; font-size: 12px; margin-bottom: 6px; display: block;">
                                    Xác nhận mật khẩu mới
                                </label>
                                <input 
                                    type="password" 
                                    id="new_password_confirmation" 
                                    name="new_password_confirmation" 
                                    placeholder="Nhập lại mật khẩu mới" 
                                    class="stu-form-control" 
                                    style="width: 100%; height: 38px; border-radius: 8px; border: 1px solid var(--color-border); padding: 0 12px; font-size: 13px;"
                                >
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: var(--space-3);">
                        <x-button type="submit" variant="primary">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            <span>Lưu thay đổi</span>
                        </x-button>
                    </div>
                </form>
            </div>
        </div>

        <!-- DANH SÁCH PHIÊN HỌP THAM DỰ GẦN ĐÂY -->
        <div class="stu-card">
            <div class="stu-card-header">
                <h3 class="stu-card-title">Phiên họp được triệu tập gần đây</h3>
                <div class="stu-card-actions">
                    <a href="{{ url('/meetings') }}" style="font-size: var(--font-size-xs); font-weight: 600; color: var(--color-primary); display: flex; align-items: center; gap: 4px;">
                        Tất cả phiên họp
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
            <div style="overflow: hidden;">
                @if($recentAttendances->isEmpty())
                    <div class="stu-empty-state" style="padding: var(--space-6); text-align: center;">
                        <div class="stu-empty-title" style="font-weight: 600; margin-bottom: 4px;">Chưa có dữ liệu phiên họp</div>
                        <div class="stu-empty-desc" style="font-size: 12px; color: var(--color-text-muted);">Thành viên chưa có lịch sử tham dự phiên họp nào trong hệ thống.</div>
                    </div>
                @else
                    <table style="width: 100%; border-collapse: separate; border-spacing: 0; font-size: var(--font-size-sm); table-layout: fixed;">
                        <colgroup>
                            <col style="width: 48%;">
                            <col style="width: 26%;">
                            <col style="width: 26%;">
                        </colgroup>
                        <thead>
                            <tr>
                                <th style="background: var(--color-bg-subtle); color: var(--color-text-secondary); font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em; padding: 10px 12px; border-bottom: 1px solid var(--color-border);">Tên phiên họp</th>
                                <th style="background: var(--color-bg-subtle); color: var(--color-text-secondary); font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em; padding: 10px 12px; border-bottom: 1px solid var(--color-border);">Thời gian</th>
                                <th style="background: var(--color-bg-subtle); color: var(--color-text-secondary); font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em; padding: 10px 12px; border-bottom: 1px solid var(--color-border);">Trạng thái tham dự</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentAttendances as $att)
                                <tr>
                                    <td style="padding: 10px 12px; border-bottom: 1px solid var(--color-border-subtle); vertical-align: middle;">
                                        <a href="{{ url('/meetings/' . $att->phienHop?->MaPhienHop) }}" style="color: var(--color-primary); font-weight: 600; font-size: 13px; line-height: 1.35; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;" title="{{ $att->phienHop?->TenPhienHop }}">
                                            {{ $att->phienHop?->TenPhienHop ?: 'Phiên họp HĐT' }}
                                        </a>
                                        <span style="font-size: 11px; color: var(--color-text-muted);">Mã: #{{ $att->phienHop?->MaPhienHop }}</span>
                                    </td>
                                    <td style="padding: 10px 12px; border-bottom: 1px solid var(--color-border-subtle); vertical-align: middle; white-space: nowrap;">
                                        <div style="font-weight: 500; font-size: 13px;">{{ $att->phienHop?->ThoiGianBatDau ? date('d/m/Y', strtotime($att->phienHop->ThoiGianBatDau)) : '—' }}</div>
                                        <div style="font-size: 11px; color: var(--color-text-muted);">{{ $att->phienHop?->ThoiGianBatDau ? date('H:i', strtotime($att->phienHop->ThoiGianBatDau)) : '' }}</div>
                                    </td>
                                    <td style="padding: 10px 12px; border-bottom: 1px solid var(--color-border-subtle); vertical-align: middle; white-space: nowrap;">
                                        <x-status-badge :status="$att->TrangThaiThamDu ?: 'Có mặt'" />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>

    <!-- CỘT PHẢI: THÔNG TIN HỘI ĐỒNG TRƯỜNG & PHÁP LÝ -->
    <div style="display: flex; flex-direction: column; gap: var(--space-6);">
        <!-- THÔNG TIN TỔ CHỨC HĐT -->
        <div class="stu-card" style="padding: var(--space-6);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--space-4);">
                <h3 class="stu-card-title">Chức vụ trong Hội đồng trường</h3>
                <span class="stu-badge stu-badge-info">{{ $stats['nhiem_ky'] }}</span>
            </div>

            <div style="display: flex; flex-direction: column; gap: var(--space-4); font-size: var(--font-size-sm);">
                <div>
                    <div style="font-size: 11px; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 2px;">
                        Chức danh HĐT
                    </div>
                    <div style="font-weight: 700; color: var(--color-primary); font-size: 15px;">
                        {{ $stats['chuc_danh_hdt'] }}
                    </div>
                </div>

                <div>
                    <div style="font-size: 11px; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 2px;">
                        Quyền hạn biểu quyết
                    </div>
                    <div>
                        @if($stats['co_quyen_bieu_quyet'])
                            <span class="stu-badge stu-badge-success" style="padding: 3px 8px;">
                                <span class="stu-badge-dot"></span> Có quyền biểu quyết & bỏ phiếu
                            </span>
                        @else
                            <span class="stu-badge stu-badge-neutral" style="padding: 3px 8px;">
                                <span class="stu-badge-dot"></span> Không có quyền biểu quyết
                            </span>
                        @endif
                    </div>
                </div>

                @if($thanhVien?->NgayBatDauThamGia)
                    <div>
                        <div style="font-size: 11px; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 2px;">
                            Thời gian tham gia HĐT
                        </div>
                        <div style="font-weight: 500;">
                            Từ ngày {{ \Carbon\Carbon::parse($thanhVien->NgayBatDauThamGia)->format('d/m/Y') }}
                            @if($thanhVien->NgayKetThuc)
                                đến {{ \Carbon\Carbon::parse($thanhVien->NgayKetThuc)->format('d/m/Y') }}
                            @endif
                        </div>
                    </div>
                @endif

                <div style="border-top: 1px dashed var(--color-border); padding-top: var(--space-3); font-size: 12px; color: var(--color-text-muted); line-height: 1.5;">
                    Thành viên Hội đồng trường ĐH Công nghệ Sài Gòn chịu trách nhiệm theo Luật Giáo dục Đại học số 34/2018/QH14 và Quy chế tổ chức & hoạt động của Trường.
                </div>
            </div>
        </div>

        <!-- HỖ TRỢ VÀ BẢO MẬT -->
        <div class="stu-card" style="padding: var(--space-6); background: var(--color-bg-subtle);">
            <h4 style="font-size: 13px; font-weight: 700; color: var(--color-text-main); margin-bottom: var(--space-2);">
                Hỗ trợ kỹ thuật & Quản trị
            </h4>
            <p style="font-size: 12px; color: var(--color-text-muted); line-height: 1.5; margin-bottom: var(--space-3);">
                Nếu cần điều chỉnh thông tin đơn vị, quyền hạn biểu quyết hoặc chức vụ HĐT, vui lòng liên hệ Ban Thư ký Hội đồng trường:
            </p>
            <div style="font-size: 12px; color: var(--color-text-body); display: flex; flex-direction: column; gap: 4px;">
                <div><strong>Email:</strong> hoidongtruong@stu.edu.vn</div>
                <div><strong>Hotline:</strong> (028) 3850 5520 - Ext: 102</div>
                <div><strong>Văn phòng:</strong> Phòng A202, Trụ sở chính STU</div>
            </div>
        </div>
    </div>
</div>
@endsection
