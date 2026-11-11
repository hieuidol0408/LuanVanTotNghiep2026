@extends('layouts.app')

@section('title', 'Tạo mới Hồ sơ trình Hội đồng trường STU')

@section('content')
<div class="stu-page-container">
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="['Hồ sơ trình' => route('submissions.index'), 'Tạo mới hồ sơ' => null]" />

    <div class="stu-page-header stu-mb-6">
        <h1 class="stu-page-title" style="font-size: 24px; font-weight: 800; color: var(--color-primary); margin: 0;">
            Tạo mới Hồ sơ trình Hội đồng trường STU
        </h1>
        <p style="font-size: 13px; color: var(--color-text-muted); margin: 4px 0 0 0;">
            Đăng ký hồ sơ trình mới và đính kèm tài liệu phục vụ phiên họp Hội đồng trường STU.
        </p>
    </div>

    @if($errors->any())
        <x-alert type="danger" :dismissible="true" class="stu-mb-6">
            <div style="font-weight: 700; margin-bottom: 4px;">Vui lòng kiểm tra lại các trường thông tin:</div>
            <ul style="margin: 0; padding-left: 20px; font-size: 13px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-alert>
    @endif

    <div class="stu-card" style="max-width: 900px; padding: 28px;">
        <form method="POST" action="{{ route('submissions.store') }}" enctype="multipart/form-data" id="createSubmissionForm" novalidate>
            @csrf

            <div id="clientValidationErrors" class="stu-alert stu-alert-danger stu-mb-4" style="display: none;"></div>

            {{-- 0. Mã số hồ sơ tự động sinh --}}
            <div class="stu-form-group stu-mb-5">
                <label class="stu-form-label" style="font-size: 13px; font-weight: 700;">
                    Mã số hồ sơ (Tự động sinh)
                </label>
                <input 
                    type="text" 
                    value="{{ $nextCode ?? ('#HS-' . now()->format('my') . '01') }}" 
                    class="stu-form-control" 
                    style="background-color: var(--color-bg-subtle); font-weight: 700; color: var(--color-primary); max-width: 260px;"
                    readonly
                >
                <div style="font-size: 11px; color: var(--color-text-muted); margin-top: 4px;">
                    Quy tắc tự động: <strong>#HS-[2 số tháng][2 số cuối năm][Số thứ tự tăng dần từ 01 trong tháng]</strong> (Tự động reset về 01 khi sang tháng mới).
                </div>
            </div>

            {{-- 1. Tiêu đề hồ sơ trình --}}
            <div class="stu-form-group stu-mb-5">
                <label class="stu-form-label" style="font-size: 13px; font-weight: 700;">
                    Tiêu đề hồ sơ trình <span style="color: var(--color-danger);">*</span>
                </label>
                <input 
                    type="text" 
                    name="TieuDe" 
                    value="{{ old('TieuDe') }}" 
                    class="stu-form-control @error('TieuDe') is-invalid @enderror" 
                    placeholder="Ví dụ: Tờ trình về việc phê duyệt chủ trương đầu tư phòng thí nghiệm thông minh năm học 2026-2027" 
                    required
                    minlength="5"
                    maxlength="255"
                >
                @error('TieuDe')
                    <div style="color: var(--color-danger); font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>

            {{-- 2. Đơn vị trình & Lĩnh vực (2 cột) --}}
            <div class="stu-grid stu-mb-5" style="grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="stu-form-group">
                    <label class="stu-form-label" style="font-size: 13px; font-weight: 700;">
                        Đơn vị trình <span style="color: var(--color-danger);">*</span>
                    </label>
                    <select name="MaDonVi" class="stu-form-control @error('MaDonVi') is-invalid @enderror" required {{ $role === 'UNIT_REP' ? 'readonly' : '' }}>
                        <option value="">-- Chọn đơn vị --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->MaDonVi }}" {{ (old('MaDonVi') == $dept->MaDonVi || ($role === 'UNIT_REP' && $userDeptId == $dept->MaDonVi)) ? 'selected' : '' }}>
                                {{ $dept->TenDonVi }}
                            </option>
                        @endforeach
                    </select>
                    @if($role === 'UNIT_REP')
                        <div style="font-size: 11px; color: var(--color-text-muted); margin-top: 4px;">
                            * Bạn đang thao tác với vai trò Đại diện đơn vị (hệ thống tự động liên kết đơn vị của bạn).
                        </div>
                    @endif
                    @error('MaDonVi')
                        <div style="color: var(--color-danger); font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="stu-form-group">
                    <label class="stu-form-label" style="font-size: 13px; font-weight: 700;">
                        Lĩnh vực <span style="color: var(--color-danger);">*</span>
                    </label>
                    <select name="LinhVuc" class="stu-form-control @error('LinhVuc') is-invalid @enderror" required>
                        <option value="">-- Chọn lĩnh vực --</option>
                        <option value="Đào tạo & Đảm bảo chất lượng" {{ old('LinhVuc') === 'Đào tạo & Đảm bảo chất lượng' ? 'selected' : '' }}>Đào tạo & Đảm bảo chất lượng</option>
                        <option value="Khoa học công nghệ & Dự án" {{ old('LinhVuc') === 'Khoa học công nghệ & Dự án' ? 'selected' : '' }}>Khoa học công nghệ & Dự án</option>
                        <option value="Cơ sở vật chất & Đầu tư xây dựng" {{ old('LinhVuc') === 'Cơ sở vật chất & Đầu tư xây dựng' ? 'selected' : '' }}>Cơ sở vật chất & Đầu tư xây dựng</option>
                        <option value="Tài chính & Kế hoạch ngân sách" {{ old('LinhVuc') === 'Tài chính & Kế hoạch ngân sách' ? 'selected' : '' }}>Tài chính & Kế hoạch ngân sách</option>
                        <option value="Tổ chức cán bộ & Nhân sự" {{ old('LinhVuc') === 'Tổ chức cán bộ & Nhân sự' ? 'selected' : '' }}>Tổ chức cán bộ & Nhân sự</option>
                        <option value="Hợp tác quốc tế & Doanh nghiệp" {{ old('LinhVuc') === 'Hợp tác quốc tế & Doanh nghiệp' ? 'selected' : '' }}>Hợp tác quốc tế & Doanh nghiệp</option>
                        <option value="Khác" {{ old('LinhVuc') === 'Khác' ? 'selected' : '' }}>Khác</option>
                    </select>
                    @error('LinhVuc')
                        <div style="color: var(--color-danger); font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- 3. Nội dung tóm tắt --}}
            <div class="stu-form-group stu-mb-5" style="margin-top: 16px;">
                <label class="stu-form-label" style="font-size: 13px; font-weight: 700;">
                    Nội dung tóm tắt <span style="color: var(--color-danger);">*</span>
                </label>
                <textarea 
                    name="NoiDungTomTat" 
                    rows="4" 
                    class="stu-form-control @error('NoiDungTomTat') is-invalid @enderror" 
                    placeholder="Nhập nội dung tóm tắt của hồ sơ trình..."
                    required
                    minlength="5"
                >{{ old('NoiDungTomTat') }}</textarea>
                @error('NoiDungTomTat')
                    <div style="color: var(--color-danger); font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>

            {{-- 4. Tệp tài liệu đính kèm --}}
            <div class="stu-form-group stu-mb-5" style="background: var(--color-bg-muted); padding: 18px; border-radius: var(--radius-md); border: 1px dashed var(--color-border);">
                <label class="stu-form-label" style="font-size: 13px; font-weight: 700; display: flex; align-items: center; gap: 6px;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                    <span>Tài liệu đính kèm</span>
                </label>
                <p style="font-size: 12px; color: var(--color-text-muted); margin: 4px 0 10px 0;">
                    Hỗ trợ các định dạng văn bản chuẩn: <strong>PDF, DOC, DOCX, XLS, XLSX</strong> (Dung lượng tối đa 20MB).
                </p>
                <input 
                    type="file" 
                    name="file" 
                    class="stu-form-control @error('file') is-invalid @enderror"
                    accept=".pdf,.doc,.docx,.xls,.xlsx"
                >
                @error('file')
                    <div style="color: var(--color-danger); font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>

            {{-- 5. Ghi chú --}}
            <div class="stu-form-group stu-mb-6">
                <label class="stu-form-label" style="font-size: 13px; font-weight: 700;">Ghi chú (nếu có)</label>
                <textarea 
                    name="GhiChu" 
                    rows="2" 
                    class="stu-form-control" 
                    placeholder="Nhập ghi chú..."
                >{{ old('GhiChu') }}</textarea>
            </div>

            {{-- Nút hành động --}}
            <div class="stu-flex stu-items-center stu-justify-between" style="border-top: 1px solid var(--color-border); padding-top: 20px;">
                <a href="{{ route('submissions.index') }}" class="stu-btn stu-btn-ghost">
                    Quay lại danh sách
                </a>
                <button type="submit" class="stu-btn stu-btn-primary" style="padding: 10px 24px;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <span>Lưu hồ sơ trình</span>
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('createSubmissionForm');
    const errorBox = document.getElementById('clientValidationErrors');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        const errors = [];
        const tieuDe = (form.querySelector('[name="TieuDe"]').value || '').trim();
        const maDonVi = form.querySelector('[name="MaDonVi"]').value;
        const linhVuc = form.querySelector('[name="LinhVuc"]').value;
        const noiDung = (form.querySelector('[name="NoiDungTomTat"]').value || '').trim();

        if (!tieuDe || tieuDe.length < 5) {
            errors.push('Vui lòng nhập tiêu đề hồ sơ trình (tối thiểu 5 ký tự).');
        }
        if (!maDonVi) {
            errors.push('Vui lòng chọn đơn vị trình.');
        }
        if (!linhVuc) {
            errors.push('Vui lòng chọn lĩnh vực hồ sơ.');
        }
        if (!noiDung || noiDung.length < 5) {
            errors.push('Vui lòng nhập nội dung tóm tắt (tối thiểu 5 ký tự).');
        }

        if (errors.length > 0) {
            e.preventDefault();
            errorBox.style.display = 'block';
            errorBox.innerHTML = '<strong>Vui lòng kiểm tra lại:</strong><ul style="margin: 6px 0 0 18px;">' +
                errors.map(err => '<li>' + err + '</li>').join('') + '</ul>';
            errorBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
        } else {
            errorBox.style.display = 'none';
        }
    });
});
</script>
@endpush
@endsection
