@extends('layouts.app')

@section('title', 'Tạo Phiên Họp Mới - Hội đồng trường STU')

@section('header_title')
    <x-breadcrumb :items="[
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
        <x-button variant="secondary" href="{{ route('meetings.index') }}">
            <span>Quay lại danh sách</span>
        </x-button>
    </div>
</div>

<div class="stu-card" style="max-width: 900px; padding: var(--space-8); margin: 0 auto;">
    <form method="POST" action="{{ route('meetings.store') }}" id="createMeetingForm">
        @csrf

        <!-- Tên phiên họp -->
        <x-form.input 
            name="TenPhienHop" 
            label="Tên phiên họp" 
            placeholder="Ví dụ: Phiên họp thường kỳ Lần thứ 5 Hội đồng trường STU (Khóa IV)" 
            value="{{ old('TenPhienHop') }}" 
            required 
            hint="Đặt tên trang trọng, phản ánh đúng kỳ họp và khóa nhiệm kỳ (BR-11)"
        />

        <div class="stu-grid stu-grid-cols-3">
            <!-- Ngày họp -->
            <x-form.input 
                type="date" 
                name="NgayHop" 
                id="ngayHopInput"
                label="Ngày tổ chức họp" 
                value="{{ old('NgayHop', date('Y-m-d')) }}" 
                required 
            />

            <!-- Giờ bắt đầu -->
            <x-form.input 
                type="time" 
                name="GioBatDau" 
                id="gioBatDauInput"
                label="Giờ bắt đầu" 
                value="{{ old('GioBatDau', '08:30') }}" 
                required 
            />

            <!-- Giờ kết thúc -->
            <x-form.input 
                type="time" 
                name="GioKetThuc" 
                id="gioKetThucInput"
                label="Giờ kết thúc" 
                value="{{ old('GioKetThuc', '11:30') }}" 
                required 
                hint="Phải sau giờ bắt đầu"
            />
        </div>

        <div class="stu-grid stu-grid-cols-2">
            <!-- Địa điểm tổ chức -->
            <x-form.input 
                name="DiaDiem" 
                label="Địa điểm tổ chức" 
                placeholder="Ví dụ: Phòng họp Hội đồng trường, Tầng 3 Nhà A" 
                value="{{ old('DiaDiem', 'Phòng họp Hội đồng trường, Tòa nhà A') }}" 
                required 
            />

            <!-- Hình thức tổ chức -->
            <x-form.select name="HinhThuc" label="Hình thức tổ chức họp" required>
                <option value="Trực tiếp" {{ old('HinhThuc') == 'Trực tiếp' ? 'selected' : '' }}>Trực tiếp (Tại trường STU)</option>
                <option value="Trực tuyến" {{ old('HinhThuc') == 'Trực tuyến' ? 'selected' : '' }}>Trực tuyến (Google Meet / Zoom)</option>
                <option value="Kết hợp" {{ old('HinhThuc') == 'Kết hợp' ? 'selected' : '' }}>Kết hợp (Hybrid vừa trực tiếp vừa online)</option>
            </x-form.select>
        </div>

        <!-- Ghi chú / Kịch bản cuộc họp -->
        <x-form.textarea 
            name="GhiChu" 
            label="Ghi chú & Kịch bản phiên họp" 
            placeholder="Nhập thông tin tóm tắt nội dung cuộc họp hoặc ghi chú chuẩn bị..." 
            rows="4" 
            hint="Trường không bắt buộc (NULLable theo BR-11)"
        >{{ old('GhiChu') }}</x-form.textarea>

        <x-alert type="info" title="Quy tắc nghiệp vụ tự động (BR-02):" :dismissible="false">
            Hệ thống sẽ tự động tạo danh sách mời tất cả các thành viên Hội đồng trường chính thức với trạng thái ban đầu và đảm bảo không bị trùng lặp thành viên.
        </x-alert>

        <!-- Nút hành động -->
        <div class="stu-flex stu-justify-end stu-gap-3" style="margin-top: var(--space-6);">
            <x-button variant="secondary" href="{{ route('meetings.index') }}">Hủy bỏ</x-button>
            <x-button type="submit" variant="primary">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                <span>Xác nhận tạo phiên họp</span>
            </x-button>
        </div>
    </form>
</div>
@endsection
