@extends('layouts.app')

@section('title', 'Giao Nhiệm vụ mới từ Nghị quyết - Hội đồng trường STU')

@section('content')
<div class="page-container">
    <div class="page-header mb-4">
        <x-breadcrumb :items="[
            ['label' => 'Bảng điều hành', 'url' => route('dashboard')],
            ['label' => 'Quản lý Nhiệm vụ', 'url' => route('tasks.index')],
            ['label' => 'Giao nhiệm vụ mới', 'url' => null]
        ]" />
        <h1 class="page-title mt-2">Phân công Nhiệm vụ từ Nghị quyết</h1>
        <p class="text-muted">Thiết lập đơn vị chủ trì, đơn vị phối hợp và thời hạn hoàn thành theo quy định BR-10</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger shadow-sm mb-4">
            <div class="d-flex align-items-center mb-2">
                <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                <h6 class="mb-0 fw-bold">Vui lòng kiểm tra lại thông tin:</h6>
            </div>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('tasks.store') }}">
                @csrf

                {{-- Phần 1: Căn cứ Nghị quyết & Tên nhiệm vụ --}}
                <div class="row g-4 mb-4">
                    <div class="col-12">
                        <h5 class="fw-bold text-primary border-bottom pb-2">
                            <i class="bi bi-file-earmark-text me-2"></i>1. Căn cứ Nghị quyết ban hành
                        </h5>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Nghị quyết căn cứ <span class="text-danger">*</span></label>
                        <select name="MaNghiQuyet" class="form-select @error('MaNghiQuyet') is-invalid @enderror" required>
                            <option value="">-- Chọn Nghị quyết đã ban hành (PUBLISHED) --</option>
                            @foreach($resolutions as $res)
                                <option value="{{ $res->MaNghiQuyet }}" 
                                    {{ old('MaNghiQuyet', $selectedResolutionId) == $res->MaNghiQuyet ? 'selected' : '' }}>
                                    {{ $res->SoNghiQuyet }} - {{ $res->TrichYeu }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">Chỉ được phân công nhiệm vụ từ các Nghị quyết đã hoàn tất quy trình ký và công bố.</div>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Tên nhiệm vụ cụ thể <span class="text-danger">*</span></label>
                        <input type="text" name="TenNhiemVu" value="{{ old('TenNhiemVu') }}" 
                               class="form-control @error('TenNhiemVu') is-invalid @enderror" 
                               placeholder="Ví dụ: Xây dựng Đề án mở rộng ngành Công nghệ bán dẫn năm học 2026-2027..." required>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Nội dung chi tiết &amp; Yêu cầu sản phẩm</label>
                        <textarea name="NoiDung" rows="4" class="form-control @error('NoiDung') is-invalid @enderror" 
                                  placeholder="Mô tả cụ thể các mục tiêu cần đạt được, các mốc thời gian trung gian, sản phẩm chuyển giao bàn giao...">{{ old('NoiDung') }}</textarea>
                    </div>
                </div>

                {{-- Phần 2: Thời hạn triển khai --}}
                <div class="row g-4 mb-4">
                    <div class="col-12">
                        <h5 class="fw-bold text-primary border-bottom pb-2">
                            <i class="bi bi-calendar3 me-2"></i>2. Thời hạn triển khai &amp; Hoàn thành
                        </h5>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Ngày giao nhiệm vụ <span class="text-danger">*</span></label>
                        <input type="date" name="NgayGiao" value="{{ old('NgayGiao', date('Y-m-d')) }}" 
                               class="form-control @error('NgayGiao') is-invalid @enderror" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Hạn hoàn thành (Deadline) <span class="text-danger">*</span></label>
                        <input type="date" name="NgayHoanThanh" value="{{ old('NgayHoanThanh') }}" 
                               class="form-control @error('NgayHoanThanh') is-invalid @enderror" required>
                        <div class="form-text">Thời hạn hoàn thành phải sau hoặc bằng ngày giao nhiệm vụ.</div>
                    </div>
                </div>

                {{-- Phần 3: Phân công Đơn vị Chủ trì & Phối hợp (BR-10) --}}
                <div class="row g-4 mb-4">
                    <div class="col-12">
                        <h5 class="fw-bold text-primary border-bottom pb-2">
                            <i class="bi bi-people me-2"></i>3. Phân công Trách nhiệm thực hiện (BR-10)
                        </h5>
                    </div>

                    {{-- Đơn vị Chủ trì --}}
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded border border-primary-subtle">
                            <h6 class="fw-bold text-dark mb-3">
                                <i class="bi bi-award text-primary me-2"></i>Đơn vị Chủ trì thực hiện (Bắt buộc)
                            </h6>
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Chọn Đơn vị Chủ trì <span class="text-danger">*</span></label>
                                <select name="chu_tri_don_vi_id" class="form-select @error('chu_tri_don_vi_id') is-invalid @enderror" required>
                                    <option value="">-- Chọn phòng ban / khoa chủ trì --</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->MaDonVi }}" {{ old('chu_tri_don_vi_id') == $dept->MaDonVi ? 'selected' : '' }}>
                                            {{ $dept->TenDonVi }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="form-label fw-semibold small">Người phụ trách đầu mối <span class="text-danger">*</span></label>
                                <input type="text" name="chu_tri_nguoi_phu_trach" value="{{ old('chu_tri_nguoi_phu_trach') }}" 
                                       class="form-control @error('chu_tri_nguoi_phu_trach') is-invalid @enderror" 
                                       placeholder="Họ tên Trưởng đơn vị / Trưởng nhóm phụ trách..." required>
                                <div class="form-text small">Bắt buộc theo ràng buộc nghiệp vụ BR-10.</div>
                            </div>
                        </div>
                    </div>

                    {{-- Đơn vị Phối hợp --}}
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded border">
                            <h6 class="fw-bold text-dark mb-3">
                                <i class="bi bi-diagram-3 text-secondary me-2"></i>Đơn vị Phối hợp thực hiện (Tùy chọn)
                            </h6>
                            <p class="small text-muted mb-2">Chọn các đơn vị tham gia phối hợp thực hiện cùng đơn vị chủ trì:</p>
                            <div class="overflow-auto border rounded bg-white p-2" style="max-height: 180px;">
                                @foreach($departments as $dept)
                                    <div class="form-check py-1">
                                        <input class="form-check-input" type="checkbox" name="phoi_hop_don_vi_ids[]" 
                                               value="{{ $dept->MaDonVi }}" id="coop_{{ $dept->MaDonVi }}"
                                               {{ is_array(old('phoi_hop_don_vi_ids')) && in_array($dept->MaDonVi, old('phoi_hop_don_vi_ids')) ? 'checked' : '' }}>
                                        <label class="form-check-label small" for="coop_{{ $dept->MaDonVi }}">
                                            {{ $dept->TenDonVi }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Footer Actions --}}
                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                    <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary px-4">
                        <i class="bi bi-x-circle me-1"></i> Hủy
                    </a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-check2-circle me-1"></i> Lưu và Giao nhiệm vụ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
