@extends('layouts.chinh')

@section('title', 'Nộp Báo cáo Tiến độ Nhiệm vụ - Hội đồng trường STU')

@section('content')
<div class="page-container">
    <div class="page-header mb-4">
        <x-thanh-dieu-huong :items="[
            ['label' => 'Bảng điều hành', 'url' => route('dashboard')],
            ['label' => 'Quản lý Nhiệm vụ', 'url' => route('tasks.index')],
            ['label' => 'Nộp Báo cáo tiến độ', 'url' => null]
        ]" />
        <h1 class="page-title mt-2">Nộp Báo cáo Tiến độ Nhiệm vụ</h1>
        <p class="text-muted">Cập nhật tỷ lệ hoàn thành, kết quả đạt được, khó khăn vướng mắc và đính kèm minh chứng theo BR-10</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger shadow-sm mb-4">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div style="display: flex; justify-content: center; padding: 10px 0 30px 0;">
        <div class="stu-card" style="width: 100%; max-width: 820px; padding: 32px 36px; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04), 0 0 0 1px rgba(0, 0, 0, 0.06); background: #ffffff;">
            <div style="display: flex; align-items: center; justify-content: space-between; padding-bottom: 16px; margin-bottom: 20px; border-bottom: 1px solid var(--color-border);">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="width: 36px; height: 36px; border-radius: 8px; background: rgba(0, 58, 140, 0.1); display: flex; align-items: center; justify-content: center; color: var(--color-primary);">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </span>
                    <div>
                        <h2 style="font-size: 18px; font-weight: 700; color: var(--color-text-main); margin: 0;">Nộp Báo cáo Tiến độ Nhiệm vụ</h2>
                        <p style="font-size: 12px; color: var(--color-text-muted); margin: 2px 0 0 0;">Cập nhật tiến độ và minh chứng thực hiện theo BR-10</p>
                    </div>
                </div>
                <a href="{{ route('task-reports.index') }}" class="stu-btn stu-btn-ghost stu-btn-sm" style="font-size: 12px;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    <span>Đóng</span>
                </a>
            </div>
            <form method="POST" action="{{ route('task-reports.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="row g-4 mb-4">
                    {{-- Chọn Nhiệm vụ --}}
                    <div class="col-12">
                        <label class="form-label fw-semibold">Nhiệm vụ được phân công <span class="text-danger">*</span></label>
                        <select name="MaPhanCong" class="form-select @error('MaPhanCong') is-invalid @enderror" required>
                            <option value="">-- Chọn nhiệm vụ cần nộp báo cáo --</option>
                            @foreach($assignments as $assign)
                                <option value="{{ $assign->MaPhanCong }}" 
                                    {{ old('MaPhanCong', $selectedAssignmentId) == $assign->MaPhanCong ? 'selected' : '' }}>
                                    #{{ $assign->MaNhiemVu }} - {{ $assign->nhiemVu?->TenNhiemVu }} 
                                    ({{ $assign->VaiTroThucHien }} - {{ $assign->donVi?->TenDonVi }})
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">Chỉ hiển thị các nhiệm vụ đang thực hiện mà đơn vị được phân công phụ trách.</div>
                    </div>

                    {{-- Tiến độ (0 - 100%) --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Tỷ lệ tiến độ hoàn thành (%) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" id="tiendo_input" name="TienDo" min="0" max="100" 
                                   value="{{ old('TienDo', 0) }}" 
                                   class="form-control @error('TienDo') is-invalid @enderror" required>
                            <span class="input-group-text">%</span>
                        </div>
                        <input type="range" class="form-range mt-2" id="tiendo_range" min="0" max="100" 
                               value="{{ old('TienDo', 0) }}" oninput="document.getElementById('tiendo_input').value = this.value">
                        <div class="form-text small">Quy định BR-10: Tiến độ từ 0% đến 100%. Đạt 100% sẽ được xét nghiệm thu hoàn tất.</div>
                    </div>

                    {{-- Tệp minh chứng --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Tệp minh chứng / Báo cáo chi tiết (Tùy chọn)</label>
                        <input type="file" name="file_minh_chung" class="form-control @error('file_minh_chung') is-invalid @enderror" 
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.zip,.rar">
                        <div class="form-text small">Định dạng hỗ trợ: PDF, Word, Excel, ZIP (Tối đa 20MB).</div>
                    </div>

                    {{-- Nội dung thực hiện --}}
                    <div class="col-12">
                        <label class="form-label fw-semibold">Nội dung công việc đã thực hiện <span class="text-danger">*</span></label>
                        <textarea name="NoiDung" rows="5" class="form-control @error('NoiDung') is-invalid @enderror" 
                                  placeholder="Mô tả chi tiết những đầu việc đã hoàn tất trong kỳ báo cáo, kết quả đạt được, sản phẩm đầu ra bàn giao..." required>{{ old('NoiDung') }}</textarea>
                    </div>

                    {{-- Khó khăn vướng mắc --}}
                    <div class="col-12">
                        <label class="form-label fw-semibold">Khó khăn, vướng mắc &amp; Đề xuất hỗ trợ (Nếu có)</label>
                        <textarea name="NoiDungKhoKhan" rows="3" class="form-control @error('NoiDungKhoKhan') is-invalid @enderror" 
                                  placeholder="Nêu rõ các trở ngại về nhân sự, kinh phí, pháp lý hoặc đề xuất Hội đồng trường chỉ đạo phối hợp...">{{ old('NoiDungKhoKhan') }}</textarea>
                    </div>
                </div>

                {{-- Footer Actions --}}
                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                    <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary px-4">
                        <i class="bi bi-x-circle me-1"></i> Hủy
                    </a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-send-check me-1"></i> Nộp báo cáo tiến độ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('tiendo_input')?.addEventListener('input', function() {
        var range = document.getElementById('tiendo_range');
        if (range) range.value = this.value;
    });
</script>
@endsection
