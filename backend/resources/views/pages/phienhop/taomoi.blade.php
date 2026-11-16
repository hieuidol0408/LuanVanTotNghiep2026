@extends('layouts.chinh')

@section('title', 'Tạo Phiên Họp Mới - Hội đồng trường STU')

@section('header_title')
    <x-thanh-dieu-huong :items="[
        ['title' => 'Quản lý phiên họp', 'url' => route('meetings.index')],
        ['title' => 'Tạo phiên họp mới']
    ]" />
@endsection

@section('content')
<div class="stu-page-header">
    <div class="stu-page-title-group">
        <h1>Lập Kế Hoạch & Tạo Phiên Họp Mới</h1>
        <p>Thiết lập thời gian, địa điểm, hình thức họp và tự động chuẩn bị danh sách mời thành viên Hội đồng trường.</p>
    </div>
    <div class="stu-page-actions">
        <x-nut-bam variant="secondary" href="{{ route('meetings.index') }}">
            <span>Quay lại danh sách</span>
        </x-nut-bam>
    </div>
</div>

<div style="display: flex; justify-content: center; padding: 10px 0 30px 0;">
    <div class="stu-card" style="width: 100%; max-width: 820px; padding: 32px 36px; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04), 0 0 0 1px rgba(0, 0, 0, 0.06); background: #ffffff;">
        <div style="display: flex; align-items: center; justify-content: space-between; padding-bottom: 16px; margin-bottom: 20px; border-bottom: 1px solid var(--color-border);">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="width: 36px; height: 36px; border-radius: 8px; background: rgba(0, 58, 140, 0.1); display: flex; align-items: center; justify-content: center; color: var(--color-primary);">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </span>
                <div>
                    <h2 style="font-size: 18px; font-weight: 700; color: var(--color-text-main); margin: 0;">Lập Kế Hoạch & Tạo Phiên Họp Mới</h2>
                    <p style="font-size: 12px; color: var(--color-text-muted); margin: 2px 0 0 0;">Thiết lập thông tin phiên họp Hội đồng trường STU</p>
                </div>
            </div>
            <a href="{{ route('meetings.index') }}" class="stu-btn stu-btn-ghost stu-btn-sm" style="font-size: 12px;">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                <span>Đóng</span>
            </a>
        </div>
    <form method="POST" action="{{ route('meetings.store') }}" id="createMeetingForm" novalidate>
        @csrf

        <!-- Thông tin người tạo tự động ghi nhận -->
        <div style="background: var(--color-bg-subtle); border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: 12px 16px; margin-bottom: var(--space-5); display: flex; align-items: center; justify-content: space-between; font-size: var(--font-size-sm);">
            <div>
                <span style="color: var(--color-text-muted);">Người tạo phiên họp:</span>
                <strong style="color: var(--color-primary); margin-left: 6px;">{{ auth()->user()?->HoTen ?? session('user_name', 'Thư ký Hội đồng trường') }}</strong>
            </div>
            <span class="stu-badge stu-badge-info">Mã người tạo (ID): #{{ auth()->user()?->MaNguoiDung ?? session('user_id', 1) }}</span>
        </div>

        <!-- Tên phiên họp -->
        <x-bieu-mau.o-nhap 
            name="TenPhienHop" 
            id="tenPhienHopInput"
            label="Tên phiên họp" 
            placeholder="Ví dụ: Phiên họp thường kỳ Lần thứ 5 Hội đồng trường STU (Khóa IV)" 
            value="{{ old('TenPhienHop') }}" 
            required 
            hint="Đặt tên trang trọng, phản ánh đúng kỳ họp và khóa nhiệm kỳ (tối thiểu 5 ký tự)"
        />
        <div id="tenPhienHopError" class="stu-form-error" style="display: none; margin-top: -12px; margin-bottom: 12px;"></div>

        <div class="stu-grid stu-grid-cols-3">
            <!-- Ngày họp -->
            <x-bieu-mau.o-nhap 
                type="date" 
                name="NgayHop" 
                id="ngayHopInput"
                label="Ngày tổ chức họp" 
                value="{{ old('NgayHop', date('Y-m-d')) }}" 
                required 
            />

            <!-- Giờ bắt đầu -->
            <x-bieu-mau.o-nhap 
                type="time" 
                name="GioBatDau" 
                id="gioBatDauInput"
                label="Giờ bắt đầu" 
                value="{{ old('GioBatDau', '08:30') }}" 
                required 
            />

            <!-- Giờ kết thúc -->
            <x-bieu-mau.o-nhap 
                type="time" 
                name="GioKetThuc" 
                id="gioKetThucInput"
                label="Giờ kết thúc" 
                value="{{ old('GioKetThuc', '11:30') }}" 
                required 
                hint="Phải lớn hơn giờ bắt đầu"
            />
        </div>
        <div id="timeRangeClientError" class="stu-form-error" style="display: none; margin-top: -8px; margin-bottom: 12px;"></div>

        <div class="stu-grid stu-grid-cols-2">
            <!-- Địa điểm tổ chức -->
            <x-bieu-mau.o-nhap 
                name="DiaDiem" 
                id="diaDiemInput"
                label="Địa điểm tổ chức" 
                placeholder="Ví dụ: Phòng họp Hội đồng trường, Tầng 3 Nhà A" 
                value="{{ old('DiaDiem', 'Phòng họp Hội đồng trường, Tòa nhà A') }}" 
                required 
            />

            <!-- Hình thức tổ chức -->
            <x-bieu-mau.o-chon name="HinhThuc" label="Hình thức tổ chức họp" required>
                <option value="Trực tiếp" {{ old('HinhThuc') == 'Trực tiếp' ? 'selected' : '' }}>Trực tiếp (Tại trường STU)</option>
                <option value="Trực tuyến" {{ old('HinhThuc') == 'Trực tuyến' ? 'selected' : '' }}>Trực tuyến</option>
                <option value="Kết hợp" {{ old('HinhThuc') == 'Kết hợp' ? 'selected' : '' }}>Kết hợp (Trực tiếp và Trực tuyến)</option>
            </x-bieu-mau.o-chon>
        </div>
        <div id="diaDiemError" class="stu-form-error" style="display: none; margin-top: -12px; margin-bottom: 12px;"></div>

        <!-- Ghi chú / Kịch bản cuộc họp -->
        <x-bieu-mau.vung-van-ban 
            name="GhiChu" 
            label="Ghi chú & Kịch bản phiên họp" 
            placeholder="Ví dụ: A, B, C..." 
            rows="3" 
            hint="Ghi chú ngắn gọn nội dung phiên họp (không bắt buộc)"
        >{{ old('GhiChu') }}</x-bieu-mau.vung-van-ban>

        <x-canh-bao type="info" title="Quy tắc nghiệp vụ tự động (BR-02):" :dismissible="false">
            Hệ thống tự động lưu vết người tạo và tạo danh sách mời tất cả thành viên Hội đồng trường chính thức, đảm bảo không trùng lặp thành viên và không trùng khung giờ họp.
        </x-canh-bao>

        <!-- Nút hành động -->
        <div class="stu-flex stu-justify-end stu-gap-3" style="margin-top: var(--space-6);">
            <x-nut-bam variant="secondary" href="{{ route('meetings.index') }}">Hủy bỏ</x-nut-bam>
            <x-nut-bam type="submit" variant="primary">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                <span>Xác nhận tạo phiên họp</span>
            </x-nut-bam>
        </div>
    </form>
</div>
</div>

<script>
    document.getElementById('createMeetingForm')?.addEventListener('submit', function (e) {
        const titleEl = document.getElementById('tenPhienHopInput');
        const startEl = document.getElementById('gioBatDauInput');
        const endEl = document.getElementById('gioKetThucInput');
        const locEl = document.getElementById('diaDiemInput');
        const titleErr = document.getElementById('tenPhienHopError');
        const timeErr = document.getElementById('timeRangeClientError');
        const locErr = document.getElementById('diaDiemError');

        titleErr.style.display = 'none';
        timeErr.style.display = 'none';
        locErr.style.display = 'none';

        let hasErr = false;
        if (!titleEl.value.trim() || titleEl.value.trim().length < 5) {
            titleErr.textContent = 'Vui lòng nhập tên phiên họp tối thiểu 5 ký tự.';
            titleErr.style.display = 'block';
            hasErr = true;
        }
        if (!locEl.value.trim() || locEl.value.trim().length < 3) {
            locErr.textContent = 'Vui lòng nhập địa điểm tổ chức hợp lệ.';
            locErr.style.display = 'block';
            hasErr = true;
        }
        if (!startEl.value || !endEl.value || endEl.value <= startEl.value) {
            timeErr.textContent = 'Giờ kết thúc bắt buộc phải lớn hơn Giờ bắt đầu.';
            timeErr.style.display = 'block';
            hasErr = true;
        }
        if (hasErr) {
            e.preventDefault();
        }
    });
</script>
@endsection
