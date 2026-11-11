@extends('layouts.chinh')

@section('title', 'Chỉnh sửa Nghị quyết: ' . $resolution->SoNghiQuyet)

@section('content')
<div class="stu-page-container">
    {{-- Breadcrumb --}}
    <x-thanh-dieu-huong :items="['Nghị quyết HĐT' => route('resolutions.index'), $resolution->SoNghiQuyet => route('resolutions.show', $resolution->MaNghiQuyet), 'Chỉnh sửa' => null]" />

    <div class="stu-page-header stu-mb-6">
        <h1 class="stu-page-title" style="font-size: 24px; font-weight: 800; color: var(--color-primary); margin: 0;">
            Chỉnh sửa Nghị quyết {{ $resolution->SoNghiQuyet }}
        </h1>
        <p style="font-size: 13px; color: var(--color-text-muted); margin: 4px 0 0 0;">
            Cập nhật nội dung dự thảo trước khi trình Chủ tịch ký ban hành chính thức.
        </p>
    </div>

    @if($errors->any())
        <x-canh-bao type="danger" :dismissible="true" class="stu-mb-6">
            <ul style="margin: 0; padding-left: 20px; font-size: 13px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-canh-bao>
    @endif

    <div class="stu-card" style="max-width: 960px; padding: 28px;">
        <form method="POST" action="{{ route('resolutions.update', $resolution->MaNghiQuyet) }}">
            @csrf
            @method('PUT')

            <div class="stu-grid" style="grid-template-columns: 1.5fr 1.5fr 1fr; gap: 16px;" class="stu-mb-5">
                <div class="stu-form-group">
                    <label class="stu-form-label" style="font-size: 13px; font-weight: 700;">Số hiệu Nghị quyết <span style="color: var(--color-danger);">*</span></label>
                    <input type="text" name="SoNghiQuyet" value="{{ old('SoNghiQuyet', $resolution->SoNghiQuyet) }}" class="stu-form-control" required>
                </div>

                <div class="stu-form-group">
                    <label class="stu-form-label" style="font-size: 13px; font-weight: 700;">Phiên họp ban hành <span style="color: var(--color-danger);">*</span></label>
                    <select name="MaPhienHop" class="stu-form-control" required>
                        @foreach($meetings as $m)
                            <option value="{{ $m->MaPhienHop }}" {{ old('MaPhienHop', $resolution->MaPhienHop) == $m->MaPhienHop ? 'selected' : '' }}>
                                {{ $m->TenPhienHop }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="stu-form-group">
                    <label class="stu-form-label" style="font-size: 13px; font-weight: 700;">Ngày ban hành <span style="color: var(--color-danger);">*</span></label>
                    <input type="date" name="NgayBanHanh" value="{{ old('NgayBanHanh', $resolution->NgayBanHanh ? \Carbon\Carbon::parse($resolution->NgayBanHanh)->format('Y-m-d') : '') }}" class="stu-form-control" required>
                </div>
            </div>

            <div class="stu-grid stu-grid-cols-2 stu-gap-4 stu-mb-5">
                <div class="stu-form-group" style="margin-bottom: 0;">
                    <label class="stu-form-label" style="font-size: 13px; font-weight: 700;">Trích yếu nội dung <span style="color: var(--color-danger);">*</span></label>
                    <input type="text" name="TrichYeu" value="{{ old('TrichYeu', $resolution->TrichYeu) }}" class="stu-form-control" required>
                </div>

                <div class="stu-form-group" style="margin-bottom: 0;">
                    <label class="stu-form-label" style="font-size: 13px; font-weight: 700;">Trạng thái xử lý <span style="color: var(--color-danger);">*</span></label>
                    <select name="TrangThai" class="stu-form-control" required>
                        <option value="Dự thảo" {{ old('TrangThai', $resolution->TrangThai) === 'Dự thảo' ? 'selected' : '' }}>Dự thảo (DRAFT)</option>
                        <option value="Đã ký ban hành" {{ old('TrangThai', $resolution->TrangThai) === 'Đã ký ban hành' ? 'selected' : '' }}>Đã ký ban hành (SIGNED)</option>
                        <option value="Đã công bố hiệu lực" {{ old('TrangThai', $resolution->TrangThai) === 'Đã công bố hiệu lực' ? 'selected' : '' }}>Đã công bố hiệu lực (PUBLISHED)</option>
                    </select>
                </div>
            </div>

            <div class="stu-form-group stu-mb-6" style="margin-top: 14px;">
                <label class="stu-form-label" style="font-size: 13px; font-weight: 700;">Toàn văn nội dung Nghị quyết <span style="color: var(--color-danger);">*</span></label>
                <textarea name="NoiDung" rows="8" class="stu-form-control" required>{{ old('NoiDung', $resolution->NoiDung) }}</textarea>
            </div>

            <div class="stu-flex stu-items-center stu-justify-between" style="border-top: 1px solid var(--color-border); padding-top: 20px;">
                <a href="{{ route('resolutions.show', $resolution->MaNghiQuyet) }}" class="stu-btn stu-btn-ghost">Hủy bỏ</a>
                <button type="submit" class="stu-btn stu-btn-primary" style="padding: 10px 24px;">Lưu cập nhật</button>
            </div>
        </form>
    </div>
</div>
@endsection
