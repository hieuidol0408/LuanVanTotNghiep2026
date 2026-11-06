@extends('layouts.app')

@section('title', 'Chỉnh sửa Nhiệm vụ - Hội đồng trường STU')

@section('content')
<div class="page-container">
    <div class="page-header mb-4">
        <x-breadcrumb :items="[
            ['label' => 'Bảng điều hành', 'url' => route('dashboard')],
            ['label' => 'Quản lý Nhiệm vụ', 'url' => route('tasks.index')],
            ['label' => '#' . $task->MaNhiemVu, 'url' => route('tasks.show', $task->MaNhiemVu)],
            ['label' => 'Chỉnh sửa', 'url' => null]
        ]" />
        <h1 class="page-title mt-2">Cập nhật Thông tin Nhiệm vụ #{{ $task->MaNhiemVu }}</h1>
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

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('tasks.update', $task->MaNhiemVu) }}">
                @csrf
                @method('PUT')

                <div class="row g-4 mb-4">
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Tên nhiệm vụ <span class="text-danger">*</span></label>
                        <input type="text" name="TenNhiemVu" value="{{ old('TenNhiemVu', $task->TenNhiemVu) }}" class="form-control" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Nội dung chi tiết</label>
                        <textarea name="NoiDung" rows="4" class="form-control">{{ old('NoiDung', $task->NoiDung) }}</textarea>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Ngày giao nhiệm vụ <span class="text-danger">*</span></label>
                        <input type="date" name="NgayGiao" 
                               value="{{ old('NgayGiao', $task->NgayGiao ? \Carbon\Carbon::parse($task->NgayGiao)->format('Y-m-d') : '') }}" 
                               class="form-control" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Hạn hoàn thành <span class="text-danger">*</span></label>
                        <input type="date" name="NgayHoanThanh" 
                               value="{{ old('NgayHoanThanh', $task->NgayHoanThanh ? \Carbon\Carbon::parse($task->NgayHoanThanh)->format('Y-m-d') : '') }}" 
                               class="form-control" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Trạng thái nhiệm vụ <span class="text-danger">*</span></label>
                        <select name="TrangThai" class="form-select" required>
                            <option value="ASSIGNED" {{ old('TrangThai', $task->TrangThai) === 'ASSIGNED' ? 'selected' : '' }}>Chưa thực hiện / Mới giao (ASSIGNED)</option>
                            <option value="IN_PROGRESS" {{ old('TrangThai', $task->TrangThai) === 'IN_PROGRESS' ? 'selected' : '' }}>Đang thực hiện (IN_PROGRESS)</option>
                            <option value="COMPLETED" {{ old('TrangThai', $task->TrangThai) === 'COMPLETED' ? 'selected' : '' }}>Đã hoàn tất (COMPLETED)</option>
                            <option value="OVERDUE" {{ old('TrangThai', $task->TrangThai) === 'OVERDUE' ? 'selected' : '' }}>Quá hạn (OVERDUE)</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                    <a href="{{ route('tasks.show', $task->MaNhiemVu) }}" class="btn btn-outline-secondary px-4">Hủy</a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-check-lg me-1"></i> Lưu thay đổi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
