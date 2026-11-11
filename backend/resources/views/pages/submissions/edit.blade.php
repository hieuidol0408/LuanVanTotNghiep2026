@extends('layouts.app')

@section('title', 'Cập nhật Hồ sơ trình ' . $dossier->ma_ho_so_hien_thi . ' - Hội đồng trường STU')

@section('content')
<div class="stu-page-container">
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="[
        'Hồ sơ trình' => route('submissions.index'),
        $dossier->ma_ho_so_hien_thi => route('submissions.show', $dossier->MaHoSo),
        'Chỉnh sửa hồ sơ' => null
    ]" />

    <div class="stu-page-header stu-mb-6">
        <div class="stu-flex stu-items-center stu-gap-2 stu-mb-2">
            <span style="font-weight: 800; color: var(--color-primary); font-size: 13px; background: rgba(0, 43, 91, 0.08); padding: 3px 8px; border-radius: var(--radius-sm);">
                {{ $dossier->ma_ho_so_hien_thi }}
            </span>
            <x-status-badge :status="$dossier->TrangThai" />
        </div>
        <h1 class="stu-page-title" style="font-size: 24px; font-weight: 800; color: var(--color-primary); margin: 0;">
            Cập nhật Hồ sơ trình Hội đồng trường
        </h1>
        <p style="font-size: 13px; color: var(--color-text-muted); margin: 4px 0 0 0;">
            Chỉnh sửa thông tin hồ sơ trình hoặc bổ sung tài liệu đính kèm.
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
        <form method="POST" action="{{ route('submissions.update', $dossier->MaHoSo) }}" enctype="multipart/form-data" id="editSubmissionForm" novalidate>
            @csrf
            @method('PUT')

            <div id="clientValidationErrors" class="stu-alert stu-alert-danger stu-mb-4" style="display: none;"></div>

            {{-- 0. Mã số hồ sơ --}}
            <div class="stu-grid stu-mb-5" style="grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="stu-form-group">
                    <label class="stu-form-label" style="font-size: 13px; font-weight: 700;">
                        Mã số hồ sơ
                    </label>
                    <input 
                        type="text" 
                        value="{{ $dossier->ma_ho_so_hien_thi }}" 
                        class="stu-form-control" 
                        style="background-color: var(--color-bg-subtle); font-weight: 700; color: var(--color-primary);"
                        readonly
                    >
                </div>

                @if(in_array($role, ['SECRETARY', 'CHAIR', 'ADMIN']))
                    <div class="stu-form-group">
                        <label class="stu-form-label" style="font-size: 13px; font-weight: 700;">
                            Trạng thái hồ sơ
                        </label>
                        @php $curStatus = old('TrangThai', $dossier->TrangThai); @endphp
                        <select name="TrangThai" class="stu-form-control">
                            @foreach(['Mới tạo', 'Đầy đủ', 'Chờ bổ sung', 'Đủ điều kiện'] as $st)
                                <option value="{{ $st }}" {{ $curStatus === $st ? 'selected' : '' }}>{{ $st }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>

            {{-- 1. Tiêu đề hồ sơ trình --}}
            <div class="stu-form-group stu-mb-5">
                <label class="stu-form-label" style="font-size: 13px; font-weight: 700;">
                    Tiêu đề hồ sơ trình <span style="color: var(--color-danger);">*</span>
                </label>
                <input 
                    type="text" 
                    name="TieuDe" 
                    value="{{ old('TieuDe', $dossier->TieuDe) }}" 
                    class="stu-form-control @error('TieuDe') is-invalid @enderror" 
                    placeholder="Nhập tiêu đề hồ sơ trình..." 
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
                            <option value="{{ $dept->MaDonVi }}" {{ old('MaDonVi', $dossier->MaDonVi) == $dept->MaDonVi ? 'selected' : '' }}>
                                {{ $dept->TenDonVi }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="stu-form-group">
                    <label class="stu-form-label" style="font-size: 13px; font-weight: 700;">
                        Lĩnh vực <span style="color: var(--color-danger);">*</span>
                    </label>
                    @php
                        $currentField = old('LinhVuc', $dossier->LinhVuc);
                        $standardFields = [
                            'Đào tạo & Đảm bảo chất lượng',
                            'Khoa học công nghệ & Dự án',
                            'Cơ sở vật chất & Đầu tư xây dựng',
                            'Tài chính & Kế hoạch ngân sách',
                            'Tổ chức cán bộ & Nhân sự',
                            'Hợp tác quốc tế & Doanh nghiệp',
                            'Khác',
                        ];
                        if ($currentField && !in_array($currentField, $standardFields)) {
                            array_unshift($standardFields, $currentField);
                        }
                    @endphp
                    <select name="LinhVuc" class="stu-form-control @error('LinhVuc') is-invalid @enderror" required>
                        <option value="">-- Chọn lĩnh vực --</option>
                        @foreach($standardFields as $fieldItem)
                            <option value="{{ $fieldItem }}" {{ $currentField === $fieldItem ? 'selected' : '' }}>{{ $fieldItem }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- 3. Nội dung tóm tắt --}}
            <div class="stu-form-group stu-mb-5" style="margin-top: 16px;">
                <label class="stu-form-label" style="font-size: 13px; font-weight: 700;">
                    Nội dung tóm tắt <span style="color: var(--color-danger);">*</span>
                </label>
                <textarea 
                    name="NoiDungTomTat" 
                    rows="5" 
                    class="stu-form-control @error('NoiDungTomTat') is-invalid @enderror" 
                    required
                    minlength="5"
                >{{ old('NoiDungTomTat', $dossier->NoiDungTomTat) }}</textarea>
            </div>

            {{-- 4. Danh sách tài liệu hiện có & Đính kèm thêm tài liệu mới --}}
            <div class="stu-form-group stu-mb-5" style="background: var(--color-bg-muted); padding: 18px; border-radius: var(--radius-md); border: 1px dashed var(--color-border);">
                <label class="stu-form-label" style="font-size: 13px; font-weight: 700; display: flex; align-items: center; gap: 6px;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                    <span>Tài liệu đính kèm</span>
                </label>

                @if($dossier->taiLieu && $dossier->taiLieu->count() > 0)
                    <div style="margin: 8px 0 12px 0; padding: 10px 14px; background: #ffffff; border-radius: var(--radius-sm); border: 1px solid var(--color-border); font-size: 12px;">
                        <div style="font-weight: 700; color: var(--color-text-secondary); margin-bottom: 6px;">Tài liệu đã đính kèm hiện tại ({{ $dossier->taiLieu->count() }}):</div>
                        <ul style="margin: 0; padding-left: 18px; color: var(--color-text-primary);">
                            @foreach($dossier->taiLieu as $doc)
                                <li>{{ $doc->TenTaiLieu }} <span style="color: var(--color-text-muted);">({{ $doc->LoaiTaiLieu ?? 'Tờ trình' }})</span></li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <p style="font-size: 12px; color: var(--color-text-muted); margin: 4px 0 10px 0;">
                    Chọn tệp nếu muốn tải lên tài liệu đính kèm bổ sung (Định dạng: <strong>PDF, DOC, DOCX, XLS, XLSX</strong> - Tối đa 20MB).
                </p>
                <input 
                    type="file" 
                    name="file" 
                    class="stu-form-control @error('file') is-invalid @enderror"
                    accept=".pdf,.doc,.docx,.xls,.xlsx"
                >
            </div>

            {{-- 5. Ghi chú --}}
            <div class="stu-form-group stu-mb-6">
                <label class="stu-form-label" style="font-size: 13px; font-weight: 700;">Ghi chú</label>
                <textarea 
                    name="GhiChu" 
                    rows="3" 
                    class="stu-form-control" 
                    placeholder="Nhập ghi chú..."
                >{{ old('GhiChu', $dossier->GhiChu) }}</textarea>
            </div>

            {{-- Nút hành động --}}
            <div class="stu-flex stu-items-center stu-justify-between" style="border-top: 1px solid var(--color-border); padding-top: 20px;">
                <a href="{{ route('submissions.show', $dossier->MaHoSo) }}" class="stu-btn stu-btn-ghost">
                    Hủy &amp; Quay lại chi tiết
                </a>
                <button type="submit" class="stu-btn stu-btn-primary" style="padding: 10px 24px;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <span>Lưu cập nhật hồ sơ</span>
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('editSubmissionForm');
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
