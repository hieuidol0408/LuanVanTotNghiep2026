@extends('layouts.chinh')

@section('title', 'Chỉnh sửa Phiên Họp - Hội đồng trường STU')

@section('header_title')
    <x-thanh-dieu-huong :items="[
        ['title' => 'Quản lý phiên họp', 'url' => route('meetings.index')],
        ['title' => '#' . $meeting->MaPhienHop, 'url' => route('meetings.show', $meeting->MaPhienHop)],
        ['title' => 'Chỉnh sửa']
    ]" />
@endsection

@section('content')
<div class="stu-page-header">
    <div class="stu-page-title-group">
        <h1>Chỉnh Sửa &amp; Dời Lịch Phiên Họp #{{ $meeting->MaPhienHop }}</h1>
        <p>Cập nhật trực tiếp ngày/giờ họp (khi hoãn hoặc dời lịch), gia hạn Giờ kết thúc khi phiên họp kéo dài, hoặc thay đổi trạng thái tiến hành.</p>
    </div>
    <div class="stu-page-actions">
        <x-nut-bam variant="secondary" href="{{ route('meetings.show', $meeting->MaPhienHop) }}">
            <span>Quay lại chi tiết</span>
        </x-nut-bam>
    </div>
</div>

<div class="stu-card" style="max-width: 900px; padding: var(--space-8); margin: 0 auto;">
    <form method="POST" action="{{ route('meetings.update', $meeting->MaPhienHop) }}" id="editMeetingForm" novalidate>
        @csrf
        @method('PUT')

        <!-- Tên phiên họp -->
        <x-bieu-mau.o-nhap 
            name="TenPhienHop" 
            id="editTenPhienHop"
            label="Tên phiên họp" 
            value="{{ old('TenPhienHop', $meeting->TenPhienHop) }}" 
            required 
        />
        <div id="editTitleError" class="stu-form-error" style="display: none; margin-top: -12px; margin-bottom: 12px;"></div>

        <div class="stu-grid stu-grid-cols-3">
            <!-- Ngày họp -->
            <x-bieu-mau.o-nhap 
                type="date" 
                name="NgayHop" 
                id="editNgayHop"
                label="Ngày tổ chức họp (Dời ngày)" 
                value="{{ old('NgayHop', $meeting->NgayHop ? date('Y-m-d', strtotime($meeting->NgayHop)) : '') }}" 
                required 
            />

            <!-- Giờ bắt đầu -->
            <x-bieu-mau.o-nhap 
                type="time" 
                name="GioBatDau" 
                id="editGioBatDau"
                label="Giờ bắt đầu" 
                value="{{ old('GioBatDau', $meeting->ThoiGianBatDau ? date('H:i', strtotime($meeting->ThoiGianBatDau)) : '08:30') }}" 
                required 
            />

            <!-- Giờ kết thúc -->
            <x-bieu-mau.o-nhap 
                type="time" 
                name="GioKetThuc" 
                id="editGioKetThuc"
                label="Giờ kết thúc (Có thể cập nhật lại)" 
                value="{{ old('GioKetThuc', $meeting->ThoiGianKetThuc ? date('H:i', strtotime($meeting->ThoiGianKetThuc)) : '11:30') }}" 
                required 
                hint="Cho phép cập nhật khi họp trễ hơn dự kiến (> Giờ bắt đầu)"
            />
        </div>
        <div id="editTimeError" class="stu-form-error" style="display: none; margin-top: -8px; margin-bottom: 12px;"></div>

        <div class="stu-grid stu-grid-cols-3">
            <!-- Địa điểm tổ chức -->
            <x-bieu-mau.o-nhap 
                name="DiaDiem" 
                id="editDiaDiem"
                label="Địa điểm tổ chức" 
                value="{{ old('DiaDiem', $meeting->DiaDiem) }}" 
                required 
            />

            <!-- Hình thức tổ chức -->
            <x-bieu-mau.o-chon name="HinhThuc" label="Hình thức tổ chức" required>
                <option value="Trực tiếp" {{ old('HinhThuc', $meeting->HinhThuc) == 'Trực tiếp' ? 'selected' : '' }}>Trực tiếp</option>
                <option value="Trực tuyến" {{ old('HinhThuc', $meeting->HinhThuc) == 'Trực tuyến' ? 'selected' : '' }}>Trực tuyến</option>
                <option value="Kết hợp" {{ old('HinhThuc', $meeting->HinhThuc) == 'Kết hợp' ? 'selected' : '' }}>Kết hợp</option>
            </x-bieu-mau.o-chon>

            <!-- Trạng thái phiên họp -->
            <x-bieu-mau.o-chon name="TrangThai" label="Trạng thái phiên họp" required>
                <option value="Đang chuẩn bị" {{ old('TrangThai', $meeting->TrangThai) == 'Đang chuẩn bị' ? 'selected' : '' }}>Đang chuẩn bị</option>
                <option value="Sắp diễn ra" {{ old('TrangThai', $meeting->TrangThai) == 'Sắp diễn ra' ? 'selected' : '' }}>Sắp diễn ra</option>
                <option value="Đang diễn ra" {{ old('TrangThai', $meeting->TrangThai) == 'Đang diễn ra' ? 'selected' : '' }}>Đang diễn ra</option>
                <option value="Đã diễn ra" {{ old('TrangThai', $meeting->TrangThai) == 'Đã diễn ra' ? 'selected' : '' }}>Đã diễn ra</option>
                <option value="Đã kết thúc" {{ old('TrangThai', $meeting->TrangThai) == 'Đã kết thúc' ? 'selected' : '' }}>Đã kết thúc</option>
                <option value="Hủy" {{ old('TrangThai', $meeting->TrangThai) == 'Hủy' ? 'selected' : '' }}>Hủy phiên họp</option>
            </x-bieu-mau.o-chon>
        </div>

        <!-- Ghi chú / Kịch bản -->
        <x-bieu-mau.vung-van-ban 
            name="GhiChu" 
            label="Ghi chú & Kịch bản phiên họp" 
            rows="3" 
            placeholder="Ví dụ: A, B, C..."
        >{{ old('GhiChu', $meeting->GhiChu) }}</x-bieu-mau.vung-van-ban>

        <!-- Nút hành động -->
        <div class="stu-flex stu-justify-end stu-gap-3" style="margin-top: var(--space-6);">
            <x-nut-bam variant="secondary" href="{{ route('meetings.show', $meeting->MaPhienHop) }}">Hủy bỏ</x-nut-bam>
            <x-nut-bam type="submit" variant="primary">
                <span>Lưu thay đổi</span>
            </x-nut-bam>
        </div>
    </form>
</div>

<script>
    document.getElementById('editMeetingForm')?.addEventListener('submit', function (e) {
        const titleEl = document.getElementById('editTenPhienHop');
        const startEl = document.getElementById('editGioBatDau');
        const endEl = document.getElementById('editGioKetThuc');
        const titleErr = document.getElementById('editTitleError');
        const timeErr = document.getElementById('editTimeError');

        titleErr.style.display = 'none';
        timeErr.style.display = 'none';

        let hasErr = false;
        if (!titleEl.value.trim() || titleEl.value.trim().length < 5) {
            titleErr.textContent = 'Vui lòng nhập tên phiên họp tối thiểu 5 ký tự.';
            titleErr.style.display = 'block';
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
