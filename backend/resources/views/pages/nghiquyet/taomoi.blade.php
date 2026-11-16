@extends('layouts.chinh')

@section('title', 'Soạn thảo Nghị quyết mới - Hội đồng trường STU')

@section('content')
<div class="stu-page-container">
    {{-- Breadcrumb --}}
    <x-thanh-dieu-huong :items="['Nghị quyết HĐT' => route('resolutions.index'), 'Soạn thảo mới' => null]" />

    <div class="stu-page-header stu-mb-6">
        <h1 class="stu-page-title" style="font-size: 24px; font-weight: 800; color: var(--color-primary); margin: 0;">
            Soạn thảo Dự thảo Nghị quyết Hội đồng trường
        </h1>
        <p style="font-size: 13px; color: var(--color-text-muted); margin: 4px 0 0 0;">
            Khởi tạo nghị quyết căn cứ trên kết quả phiên họp, hồ sơ tờ trình và kết quả biểu quyết đã được thông qua.
        </p>
    </div>

    @if($errors->any())
        <x-canh-bao type="danger" :dismissible="true" class="stu-mb-6">
            <div style="font-weight: 700; margin-bottom: 4px;">Vui lòng kiểm tra lại thông tin:</div>
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
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                    </span>
                    <div>
                        <h2 style="font-size: 18px; font-weight: 700; color: var(--color-text-main); margin: 0;">Soạn thảo Dự thảo Nghị quyết Hội đồng trường</h2>
                        <p style="font-size: 12px; color: var(--color-text-muted); margin: 2px 0 0 0;">Căn cứ kết quả phiên họp và biểu quyết</p>
                    </div>
                </div>
                <a href="{{ route('resolutions.index') }}" class="stu-btn stu-btn-ghost stu-btn-sm" style="font-size: 12px;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    <span>Đóng</span>
                </a>
            </div>
        <form method="POST" action="{{ route('resolutions.store') }}" enctype="multipart/form-data">
            @csrf

            {{-- 1. Số hiệu & Ngày ban hành & Phiên họp (3 cột) --}}
            <div class="stu-grid" style="grid-template-columns: 1.5fr 1.5fr 1fr; gap: 16px;" class="stu-mb-5">
                <div class="stu-form-group">
                    <label class="stu-form-label" style="font-size: 13px; font-weight: 700;">
                        Số hiệu Nghị quyết <span style="color: var(--color-danger);">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="SoNghiQuyet" 
                        value="{{ old('SoNghiQuyet') }}" 
                        class="stu-form-control @error('SoNghiQuyet') is-invalid @enderror" 
                        placeholder="Ví dụ: 15/NQ-HĐT hoặc 08/NQ-HĐT-2026" 
                        required
                    >
                    @error('SoNghiQuyet')
                        <div style="color: var(--color-danger); font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="stu-form-group">
                    <label class="stu-form-label" style="font-size: 13px; font-weight: 700;">
                        Kỳ họp Hội đồng trường ban hành <span style="color: var(--color-danger);">*</span>
                    </label>
                    <select name="MaPhienHop" class="stu-form-control @error('MaPhienHop') is-invalid @enderror" required onchange="window.location.href='{{ route('resolutions.create') }}?meeting_id=' + this.value;">
                        <option value="">-- Chọn phiên họp --</option>
                        @foreach($meetings as $m)
                            <option value="{{ $m->MaPhienHop }}" {{ (old('MaPhienHop', $selectedMeetingId) == $m->MaPhienHop) ? 'selected' : '' }}>
                                {{ $m->TenPhienHop }} ({{ $m->NgayHop ? \Carbon\Carbon::parse($m->NgayHop)->format('d/m/Y') : '' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="stu-form-group">
                    <label class="stu-form-label" style="font-size: 13px; font-weight: 700;">
                        Ngày ký / ban hành <span style="color: var(--color-danger);">*</span>
                    </label>
                    <input 
                        type="date" 
                        name="NgayBanHanh" 
                        value="{{ old('NgayBanHanh', date('Y-m-d')) }}" 
                        class="stu-form-control @error('NgayBanHanh') is-invalid @enderror" 
                        required
                    >
                </div>
            </div>

            {{-- 2. Trích yếu nội dung --}}
            <div class="stu-form-group stu-mb-5" style="margin-top: 14px;">
                <label class="stu-form-label" style="font-size: 13px; font-weight: 700;">
                    Trích yếu nội dung Nghị quyết <span style="color: var(--color-danger);">*</span>
                </label>
                <input 
                    type="text" 
                    name="TrichYeu" 
                    value="{{ old('TrichYeu') }}" 
                    class="stu-form-control @error('TrichYeu') is-invalid @enderror" 
                    placeholder="Ví dụ: Về việc thông qua đề án thành lập Trung tâm Đổi mới Sáng tạo & Khởi nghiệp STU" 
                    required
                >
                @error('TrichYeu')
                    <div style="color: var(--color-danger); font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>

            {{-- 3. Toàn văn nội dung Nghị quyết --}}
            <div class="stu-form-group stu-mb-5">
                <label class="stu-form-label" style="font-size: 13px; font-weight: 700;">
                    Toàn văn Quyết nghị / Các điều khoản <span style="color: var(--color-danger);">*</span>
                </label>
                <textarea 
                    name="NoiDung" 
                    rows="8" 
                    class="stu-form-control @error('NoiDung') is-invalid @enderror" 
                    placeholder="QUYẾT NGHỊ:&#10;Điều 1. Thông qua chủ trương đầu tư...&#10;Điều 2. Giao Ban Giám hiệu và các phòng ban liên quan tổ chức thực hiện...&#10;Điều 3. Nghị quyết này có hiệu lực kể từ ngày ký..."
                    required
                >{{ old('NoiDung') }}</textarea>
                @error('NoiDung')
                    <div style="color: var(--color-danger); font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>

            {{-- 4. Liên kết Căn cứ: Hồ sơ tờ trình & Nội dung biểu quyết (Khóa chính kép) --}}
            <div class="stu-grid stu-grid-cols-2 stu-gap-4 stu-mb-5" style="background: var(--color-bg-muted); padding: 18px; border-radius: var(--radius-md); border: 1px solid var(--color-border);">
                <div>
                    <label class="stu-form-label" style="font-size: 12px; font-weight: 700; text-transform: uppercase;">
                        Căn cứ Hồ sơ tờ trình xem xét
                    </label>
                    <div style="max-height: 140px; overflow-y: auto; background: #fff; padding: 10px; border-radius: var(--radius-sm); border: 1px solid var(--color-border);">
                        @forelse($dossiers as $dos)
                            <label style="display: flex; align-items: flex-start; gap: 8px; font-size: 12px; margin-bottom: 6px; cursor: pointer;">
                                <input type="checkbox" name="dossier_ids[]" value="{{ $dos->MaHoSo }}" style="margin-top: 2px;">
                                <span>#HS-{{ str_pad($dos->MaHoSo, 3, '0', STR_PAD_LEFT) }}: {{ $dos->TieuDe }}</span>
                            </label>
                        @empty
                            <span style="font-size: 12px; color: var(--color-text-muted);">Phiên họp này chưa có hồ sơ trình liên kết.</span>
                        @endforelse
                    </div>
                </div>

                <div>
                    <label class="stu-form-label" style="font-size: 12px; font-weight: 700; text-transform: uppercase;">
                        Căn cứ Kết quả biểu quyết đã thông qua
                    </label>
                    <div style="max-height: 140px; overflow-y: auto; background: #fff; padding: 10px; border-radius: var(--radius-sm); border: 1px solid var(--color-border);">
                        @forelse($votings as $vot)
                            <label style="display: flex; align-items: flex-start; gap: 8px; font-size: 12px; margin-bottom: 6px; cursor: pointer;">
                                <input type="checkbox" name="voting_ids[]" value="{{ $vot->MaNoiDung }}" style="margin-top: 2px;">
                                <span>#BQ-{{ str_pad($vot->MaNoiDung, 3, '0', STR_PAD_LEFT) }}: {{ $vot->NoiDung }}</span>
                            </label>
                        @empty
                            <span style="font-size: 12px; color: var(--color-text-muted);">Chưa có nội dung biểu quyết cho phiên này.</span>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- 5. Tải lên tệp văn bản chính thức --}}
            <div class="stu-form-group stu-mb-6">
                <label class="stu-form-label" style="font-size: 13px; font-weight: 700; display: flex; align-items: center; gap: 6px;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                    <span>Văn bản Nghị quyết bản scan có chữ ký & đóng dấu (PDF, Word)</span>
                </label>
                <input type="file" name="file" class="stu-form-control" accept=".pdf,.doc,.docx">
                <div style="font-size: 11px; color: var(--color-text-muted); margin-top: 4px;">
                    Tập tin đính kèm tối đa 20MB, được lưu trữ an toàn trong kho dữ liệu nghị quyết HĐT.
                </div>
            </div>

            {{-- Nút hành động --}}
            <div class="stu-flex stu-items-center stu-justify-between" style="border-top: 1px solid var(--color-border); padding-top: 20px;">
                <a href="{{ route('resolutions.index') }}" class="stu-btn stu-btn-ghost">
                    Quay lại danh sách
                </a>
                <button type="submit" class="stu-btn stu-btn-primary" style="padding: 10px 24px;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <span>Lưu dự thảo Nghị quyết</span>
                </button>
            </div>
        </form>
    </div>
</div>
</div>
@endsection
