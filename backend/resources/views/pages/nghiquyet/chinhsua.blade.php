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

    <div style="display: flex; justify-content: center; padding: 10px 0 30px 0;">
        <div class="stu-card" style="width: 100%; max-width: 860px; padding: 32px 36px; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04), 0 0 0 1px rgba(0, 0, 0, 0.06); background: #ffffff;">
            <div style="display: flex; align-items: center; justify-content: space-between; padding-bottom: 16px; margin-bottom: 20px; border-bottom: 1px solid var(--color-border);">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="width: 36px; height: 36px; border-radius: 8px; background: rgba(0, 58, 140, 0.1); display: flex; align-items: center; justify-content: center; color: var(--color-primary);">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </span>
                    <div>
                        <h2 style="font-size: 18px; font-weight: 700; color: var(--color-text-main); margin: 0;">Chỉnh sửa Nghị quyết {{ $resolution->SoNghiQuyet }}</h2>
                        <p style="font-size: 12px; color: var(--color-text-muted); margin: 2px 0 0 0;">Cập nhật dự thảo nghị quyết Hội đồng trường</p>
                    </div>
                </div>
                <a href="{{ route('resolutions.show', $resolution->MaNghiQuyet) }}" class="stu-btn stu-btn-ghost stu-btn-sm" style="font-size: 12px;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    <span>Đóng</span>
                </a>
            </div>
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
                    <label class="stu-form-label" style="font-size: 13px; font-weight: 700;">Trích yếu <span style="color: var(--color-danger);">*</span></label>
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
</div>
@endsection
