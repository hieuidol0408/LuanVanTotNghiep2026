@extends('layouts.app')

@section('title', 'Chỉnh sửa Phiên Họp - Hội đồng trường STU')

@section('header_title')
    <x-breadcrumb :items="[
        ['title' => 'Quản lý phiên họp', 'url' => route('meetings.index')],
        ['title' => '#' . $meeting->MaPhienHop, 'url' => route('meetings.show', $meeting->MaPhienHop)],
        ['title' => 'Chỉnh sửa']
    ]" />
@endsection

@section('content')
<div class="stu-page-header">
    <div class="stu-page-title-group">
        <h1>Chỉnh Sửa Thông Tin Phiên Họp #{{ $meeting->MaPhienHop }}</h1>
        <p>Cập nhật thời gian, địa điểm, hình thức và trạng thái tiến hành của phiên họp.</p>
    </div>
    <div class="stu-page-actions">
        <x-button variant="secondary" href="{{ route('meetings.show', $meeting->MaPhienHop) }}">
            <span>Quay lại chi tiết</span>
        </x-button>
    </div>
</div>

<div class="stu-card" style="max-width: 900px; padding: var(--space-8); margin: 0 auto;">
    <form method="POST" action="{{ route('meetings.update', $meeting->MaPhienHop) }}">
        @csrf
        @method('PUT')

        <!-- Tên phiên họp -->
        <x-form.input 
            name="TenPhienHop" 
            label="Tên phiên họp" 
            value="{{ old('TenPhienHop', $meeting->TenPhienHop) }}" 
            required 
        />

        <div class="stu-grid stu-grid-cols-3">
            <!-- Ngày họp -->
            <x-form.input 
                type="date" 
                name="NgayHop" 
                label="Ngày tổ chức họp" 
                value="{{ old('NgayHop', $meeting->NgayHop ? date('Y-m-d', strtotime($meeting->NgayHop)) : '') }}" 
                required 
            />

            <!-- Giờ bắt đầu -->
            <x-form.input 
                type="time" 
                name="GioBatDau" 
                label="Giờ bắt đầu" 
                value="{{ old('GioBatDau', $meeting->ThoiGianBatDau ? date('H:i', strtotime($meeting->ThoiGianBatDau)) : '08:30') }}" 
                required 
            />

            <!-- Giờ kết thúc -->
            <x-form.input 
                type="time" 
                name="GioKetThuc" 
                label="Giờ kết thúc" 
                value="{{ old('GioKetThuc', $meeting->ThoiGianKetThuc ? date('H:i', strtotime($meeting->ThoiGianKetThuc)) : '11:30') }}" 
                required 
            />
        </div>

        <div class="stu-grid stu-grid-cols-3">
            <!-- Địa điểm tổ chức -->
            <x-form.input 
                name="DiaDiem" 
                label="Địa điểm tổ chức" 
                value="{{ old('DiaDiem', $meeting->DiaDiem) }}" 
                required 
            />

            <!-- Hình thức tổ chức -->
            <x-form.select name="HinhThuc" label="Hình thức tổ chức" required>
                <option value="Trực tiếp" {{ old('HinhThuc', $meeting->HinhThuc) == 'Trực tiếp' ? 'selected' : '' }}>Trực tiếp</option>
                <option value="Trực tuyến" {{ old('HinhThuc', $meeting->HinhThuc) == 'Trực tuyến' ? 'selected' : '' }}>Trực tuyến</option>
                <option value="Kết hợp" {{ old('HinhThuc', $meeting->HinhThuc) == 'Kết hợp' ? 'selected' : '' }}>Kết hợp (Hybrid)</option>
            </x-form.select>

            <!-- Trạng thái phiên họp -->
            <x-form.select name="TrangThai" label="Trạng thái phiên họp" required>
                <option value="Sắp diễn ra" {{ old('TrangThai', $meeting->TrangThai) == 'Sắp diễn ra' ? 'selected' : '' }}>Sắp diễn ra</option>
                <option value="Đang diễn ra" {{ old('TrangThai', $meeting->TrangThai) == 'Đang diễn ra' ? 'selected' : '' }}>Đang diễn ra</option>
                <option value="Đã diễn ra" {{ old('TrangThai', $meeting->TrangThai) == 'Đã diễn ra' ? 'selected' : '' }}>Đã diễn ra</option>
                <option value="Đã kết thúc" {{ old('TrangThai', $meeting->TrangThai) == 'Đã kết thúc' ? 'selected' : '' }}>Đã kết thúc</option>
                <option value="Hủy" {{ old('TrangThai', $meeting->TrangThai) == 'Hủy' ? 'selected' : '' }}>Hủy phiên họp</option>
            </x-form.select>
        </div>

        <!-- Ghi chú / Kịch bản -->
        <x-form.textarea 
            name="GhiChu" 
            label="Ghi chú & Kịch bản phiên họp" 
            rows="4" 
        >{{ old('GhiChu', $meeting->GhiChu) }}</x-form.textarea>

        <!-- Nút hành động -->
        <div class="stu-flex stu-justify-end stu-gap-3" style="margin-top: var(--space-6);">
            <x-button variant="secondary" href="{{ route('meetings.show', $meeting->MaPhienHop) }}">Hủy bỏ</x-button>
            <x-button type="submit" variant="primary">
                <span>Lưu thay đổi</span>
            </x-button>
        </div>
    </form>
</div>
@endsection
