@extends('layouts.chinh')

@section('title', 'Nộp Báo cáo Tiến độ Nhiệm vụ - Hội đồng trường STU')

@section('content')
<div class="stu-page-container">
    {{-- Breadcrumb & Tiêu đề nền phía sau --}}
    <x-thanh-dieu-huong :items="[
        'Bảng điều hành' => route('dashboard'),
        'Quản lý Nhiệm vụ' => route('tasks.index'),
        'Báo cáo tiến độ' => route('task-reports.index'),
        'Nộp báo cáo mới' => null
    ]" />

    <div class="stu-page-header stu-flex stu-items-center stu-justify-between stu-mb-6">
        <div>
            <h1 class="stu-page-title" style="font-size: 24px; font-weight: 800; color: var(--color-primary); margin: 0;">
                Báo cáo Tiến độ &amp; Đôn đốc Thực hiện
            </h1>
            <p style="font-size: 13px; color: var(--color-text-muted); margin: 4px 0 0 0;">
                Theo dõi, đánh giá và thẩm định các báo cáo tiến độ từ các đơn vị thực hiện theo BR-10
            </p>
        </div>
    </div>

    {{-- MODAL NỔI (FLOATING MODAL / FORM DẠNG NỔI BR-10) --}}
    <div class="stu-modal-backdrop show" id="createReportModalBackdrop" style="opacity: 1; visibility: visible; display: flex; align-items: center; justify-content: center; position: fixed; inset: 0; background-color: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); z-index: 9999; padding: 20px;" role="dialog" aria-modal="true" aria-labelledby="modalReportTitle">
        <div class="stu-modal stu-modal-lg" style="width: 100%; max-width: 820px; background: #ffffff; border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35); overflow: hidden; transform: scale(1); animation: modalFloatIn 0.25s ease-out;">
            
            {{-- Modal Header --}}
            <div class="stu-modal-header" style="display: flex; align-items: center; justify-content: space-between; padding: 18px 24px; border-bottom: 1px solid var(--color-border); background: #ffffff;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <span style="width: 38px; height: 38px; border-radius: 10px; background: rgba(0, 43, 91, 0.08); display: flex; align-items: center; justify-content: center; color: var(--color-primary);">
                        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </span>
                    <div>
                        <h2 id="modalReportTitle" style="font-size: 18px; font-weight: 700; color: var(--color-text-main); margin: 0;">Nộp Báo cáo Tiến độ Nhiệm vụ</h2>
                        <p style="font-size: 12px; color: var(--color-text-muted); margin: 2px 0 0 0;">Cập nhật tiến độ và minh chứng thực hiện theo BR-10</p>
                    </div>
                </div>
                <a href="{{ route('task-reports.index') }}" class="stu-modal-close" style="color: var(--color-text-muted); width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: background 0.15s ease;" title="Đóng cửa sổ">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </a>
            </div>

            {{-- Modal Body Form --}}
            <form method="POST" action="{{ route('task-reports.store') }}" enctype="multipart/form-data" id="taskReportForm">
                @csrf

                <div class="stu-modal-body" style="padding: 24px; max-height: calc(85vh - 140px); overflow-y: auto;">
                    @if ($errors->any())
                        <x-canh-bao type="danger" :dismissible="true" class="stu-mb-4">
                            <ul style="margin: 0; padding-left: 20px; font-size: 13px;">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </x-canh-bao>
                    @endif

                    {{-- 1. Nhiệm vụ được phân công --}}
                    <div class="stu-form-group" style="margin-bottom: 16px; width: 100%; box-sizing: border-box;">
                        <label class="stu-form-label" style="font-size: 12.5px; font-weight: 700; display: flex; align-items: center; justify-content: space-between;">
                            <span>Nhiệm vụ được phân công <span style="color: var(--color-danger);">*</span></span>
                            <span style="font-size: 11px; font-weight: normal; color: var(--color-text-muted);">Quy định BR-10</span>
                        </label>
                        <select name="MaPhanCong" class="stu-form-control @error('MaPhanCong') is-invalid @enderror" required style="width: 100%; height: 40px; font-size: 13px; border-radius: 8px; border: 1.5px solid #CBD5E1; padding-right: 32px; background-color: #F8FAFC;">
                            <option value="">-- Chọn nhiệm vụ cần nộp báo cáo --</option>
                            @php
                                $groupedAssignments = ($assignments ?? collect([]))->groupBy(function($item) {
                                    return $item->donVi?->TenDonVi ?: 'Đơn vị khác';
                                });
                            @endphp
                            @foreach($groupedAssignments as $unitName => $assignList)
                                <optgroup label="🏢 {{ $unitName }} ({{ $assignList->count() }} nhiệm vụ)">
                                    @foreach($assignList as $assign)
                                        <option value="{{ $assign->MaPhanCong }}" 
                                            {{ old('MaPhanCong', $selectedAssignmentId) == $assign->MaPhanCong ? 'selected' : '' }}>
                                            #{{ $assign->MaNhiemVu }} - {{ Str::limit($assign->nhiemVu?->TenNhiemVu, 60) }} [{{ $assign->VaiTroThucHien }}]
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        <div style="font-size: 11.5px; color: var(--color-text-muted); margin-top: 4px;">
                            Chỉ hiển thị các nhiệm vụ đang thực hiện mà đơn vị được phân công phụ trách.
                        </div>
                    </div>

                    {{-- 2. Hàng 2 cột: Tỷ lệ tiến độ & Tệp minh chứng --}}
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px; width: 100%; box-sizing: border-box;">
                        {{-- Tỷ lệ tiến độ hoàn thành --}}
                        <div class="stu-form-group" style="min-width: 0; width: 100%; box-sizing: border-box; margin-bottom: 0;">
                            <label class="stu-form-label" style="font-size: 12.5px; font-weight: 700; display: flex; align-items: center; justify-content: space-between;">
                                <span>Tỷ lệ tiến độ hoàn thành (%) <span style="color: var(--color-danger);">*</span></span>
                                <span id="progress_badge" style="font-size: 12px; font-weight: 800; color: var(--color-primary); background: rgba(0, 43, 91, 0.08); padding: 1px 8px; border-radius: 4px;">{{ old('TienDo', 0) }}%</span>
                            </label>
                            
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <input type="number" id="tiendo_input" name="TienDo" min="0" max="100" 
                                       value="{{ old('TienDo', 0) }}" 
                                       class="stu-form-control @error('TienDo') is-invalid @enderror" 
                                       required 
                                       style="height: 38px; font-size: 14px; font-weight: 700; color: var(--color-primary); width: 85px; text-align: center;">
                                <div style="flex: 1;">
                                    <input type="range" class="stu-form-range" id="tiendo_range" min="0" max="100" 
                                           value="{{ old('TienDo', 0) }}" 
                                           style="width: 100%; accent-color: var(--color-primary); cursor: pointer;">
                                </div>
                            </div>
                            <div style="font-size: 11px; color: var(--color-text-muted); margin-top: 4px;">
                                Quy định BR-10: Tiến độ từ 0% đến 100%. Đạt 100% sẽ được xét nghiệm thu hoàn tất.
                            </div>
                        </div>

                        {{-- Tệp minh chứng / Báo cáo chi tiết --}}
                        <div class="stu-form-group" style="min-width: 0; width: 100%; box-sizing: border-box; margin-bottom: 0;">
                            <label class="stu-form-label" style="font-size: 12.5px; font-weight: 700;">
                                Tệp minh chứng / Báo cáo chi tiết (Tùy chọn)
                            </label>
                            <input type="file" name="file_minh_chung" 
                                   class="stu-form-control @error('file_minh_chung') is-invalid @enderror" 
                                   accept=".pdf,.doc,.docx,.xls,.xlsx,.zip,.rar"
                                   style="height: 38px; font-size: 12px; padding: 6px 12px; background: #F8FAFC;">
                            <div style="font-size: 11px; color: var(--color-text-muted); margin-top: 4px;">
                                Định dạng hỗ trợ: PDF, Word, Excel, ZIP (Tối đa 20MB).
                            </div>
                        </div>
                    </div>

                    {{-- 3. Nội dung công việc đã thực hiện --}}
                    <div class="stu-form-group" style="margin-bottom: 16px; width: 100%; box-sizing: border-box;">
                        <label class="stu-form-label" style="font-size: 12.5px; font-weight: 700;">
                            Nội dung công việc đã thực hiện <span style="color: var(--color-danger);">*</span>
                        </label>
                        <textarea name="NoiDung" rows="4" 
                                  class="stu-form-control @error('NoiDung') is-invalid @enderror" 
                                  placeholder="Mô tả chi tiết những đầu việc đã hoàn tất trong kỳ báo cáo, kết quả đạt được, sản phẩm đầu ra bàn giao..." 
                                  required 
                                  style="font-size: 13px; line-height: 1.5;">{{ old('NoiDung') }}</textarea>
                    </div>

                    {{-- 4. Khó khăn, vướng mắc & Đề xuất hỗ trợ --}}
                    <div class="stu-form-group" style="margin-bottom: 6px; width: 100%; box-sizing: border-box;">
                        <label class="stu-form-label" style="font-size: 12.5px; font-weight: 700;">
                            Khó khăn, vướng mắc &amp; Đề xuất hỗ trợ (Nếu có)
                        </label>
                        <textarea name="NoiDungKhoKhan" rows="2" 
                                  class="stu-form-control @error('NoiDungKhoKhan') is-invalid @enderror" 
                                  placeholder="Nêu rõ các trở ngại về nhân sự, kinh phí, pháp lý hoặc đề xuất Hội đồng trường chỉ đạo phối hợp..."
                                  style="font-size: 13px; line-height: 1.5;">{{ old('NoiDungKhoKhan') }}</textarea>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="stu-modal-footer" style="display: flex; justify-content: flex-end; align-items: center; gap: 10px; padding: 14px 24px; border-top: 1px solid var(--color-border); background: #ffffff;">
                    <a href="{{ route('task-reports.index') }}" class="stu-btn stu-btn-secondary" style="height: 38px;">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span>Hủy bỏ</span>
                    </a>
                    <button type="submit" class="stu-btn stu-btn-primary" style="height: 38px; font-weight: 600;">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        <span>Nộp báo cáo tiến độ</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    @keyframes modalFloatIn {
        from {
            opacity: 0;
            transform: scale(0.95) translateY(-10px);
        }
        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('tiendo_input');
        const range = document.getElementById('tiendo_range');
        const badge = document.getElementById('progress_badge');

        function updateProgress(val) {
            val = Math.max(0, Math.min(100, parseInt(val) || 0));
            if (input) input.value = val;
            if (range) range.value = val;
            if (badge) badge.textContent = val + '%';
        }

        if (input && range) {
            input.addEventListener('input', function() { updateProgress(this.value); });
            range.addEventListener('input', function() { updateProgress(this.value); });
        }

        // Bấm Esc hoặc click ra ngoài backdrop để đóng modal quay lại danh sách
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                window.location.href = "{{ route('task-reports.index') }}";
            }
        });

        const backdrop = document.getElementById('createReportModalBackdrop');
        if (backdrop) {
            backdrop.addEventListener('click', function(e) {
                if (e.target === backdrop) {
                    window.location.href = "{{ route('task-reports.index') }}";
                }
            });
        }
    });
</script>
@endsection
