@extends('layouts.chinh')

@section('title', 'Chỉnh sửa Nhiệm vụ - Hội đồng trường STU')

@section('content')
<div class="stu-page-container">
    <div class="stu-page-header stu-mb-6">
        <x-thanh-dieu-huong :items="[
            'Bảng điều hành' => route('dashboard'),
            'Quản lý Nhiệm vụ' => route('tasks.index'),
            '#' . $task->MaNhiemVu => route('tasks.show', $task->MaNhiemVu),
            'Chỉnh sửa' => null
        ]" />
        <h1 class="stu-page-title" style="font-size: 24px; font-weight: 800; color: var(--color-primary); margin: 8px 0 0 0;">
            Cập nhật Thông tin Nhiệm vụ #{{ $task->MaNhiemVu }}
        </h1>
    </div>

    @if ($errors->any())
        <x-canh-bao type="danger" :dismissible="true" class="stu-mb-6">
            <div style="font-weight: 700; margin-bottom: 4px;">Vui lòng kiểm tra lại thông tin:</div>
            <ul style="margin: 0; padding-left: 20px; font-size: 13px;">
                @foreach ($errors->all() as $error)
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
                        <h2 style="font-size: 18px; font-weight: 700; color: var(--color-text-main); margin: 0;">Chỉnh sửa Nhiệm vụ #{{ $task->MaNhiemVu }}</h2>
                        <p style="font-size: 12px; color: var(--color-text-muted); margin: 2px 0 0 0;">Cập nhật tiến độ, nội dung và thời hạn thực hiện</p>
                    </div>
                </div>
                <a href="{{ route('tasks.show', $task->MaNhiemVu) }}" class="stu-btn stu-btn-ghost stu-btn-sm" style="font-size: 12px;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    <span>Đóng</span>
                </a>
            </div>

            <form method="POST" action="{{ route('tasks.update', $task->MaNhiemVu) }}">
                @csrf
                @method('PUT')

                <div class="stu-form-group stu-mb-4">
                    <label class="stu-form-label" style="font-size: 13px; font-weight: 700;">Tên nhiệm vụ <span style="color: var(--color-danger);">*</span></label>
                    <input type="text" name="TenNhiemVu" value="{{ old('TenNhiemVu', $task->TenNhiemVu) }}" class="stu-form-control" required>
                </div>

                <div class="stu-form-group stu-mb-4">
                    <label class="stu-form-label" style="font-size: 13px; font-weight: 700;">Nội dung chi tiết</label>
                    <textarea name="NoiDung" rows="4" class="stu-form-control">{{ old('NoiDung', $task->NoiDung) }}</textarea>
                </div>

                <div class="stu-grid" style="grid-template-columns: 1fr 1fr 1fr; gap: 16px;" class="stu-mb-5">
                    <div class="stu-form-group">
                        <label class="stu-form-label" style="font-size: 13px; font-weight: 700;">Ngày giao nhiệm vụ <span style="color: var(--color-danger);">*</span></label>
                        <input type="date" name="NgayGiao" 
                               value="{{ old('NgayGiao', $task->NgayGiao ? \Carbon\Carbon::parse($task->NgayGiao)->format('Y-m-d') : '') }}" 
                               class="stu-form-control" required>
                    </div>

                    <div class="stu-form-group">
                        <label class="stu-form-label" style="font-size: 13px; font-weight: 700;">Hạn hoàn thành <span style="color: var(--color-danger);">*</span></label>
                        <input type="date" name="NgayHoanThanh" 
                               value="{{ old('NgayHoanThanh', $task->NgayHoanThanh ? \Carbon\Carbon::parse($task->NgayHoanThanh)->format('Y-m-d') : '') }}" 
                               class="stu-form-control" required>
                    </div>

                    <div class="stu-form-group">
                        <label class="stu-form-label" style="font-size: 13px; font-weight: 700;">Trạng thái nhiệm vụ <span style="color: var(--color-danger);">*</span></label>
                        <select name="TrangThai" class="stu-form-control" required>
                            <option value="ASSIGNED" {{ old('TrangThai', $task->TrangThai) === 'ASSIGNED' ? 'selected' : '' }}>Chưa thực hiện (ASSIGNED)</option>
                            <option value="IN_PROGRESS" {{ old('TrangThai', $task->TrangThai) === 'IN_PROGRESS' ? 'selected' : '' }}>Đang thực hiện (IN_PROGRESS)</option>
                            <option value="COMPLETED" {{ old('TrangThai', $task->TrangThai) === 'COMPLETED' ? 'selected' : '' }}>Đã hoàn tất (COMPLETED)</option>
                            <option value="OVERDUE" {{ old('TrangThai', $task->TrangThai) === 'OVERDUE' ? 'selected' : '' }}>Quá hạn (OVERDUE)</option>
                        </select>
                    </div>
                </div>

                <div class="stu-flex stu-items-center stu-justify-between" style="border-top: 1px solid var(--color-border); padding-top: 20px; margin-top: 20px;">
                    <a href="{{ route('tasks.show', $task->MaNhiemVu) }}" class="stu-btn stu-btn-ghost">Hủy bỏ</a>
                    <button type="submit" class="stu-btn stu-btn-primary" style="padding: 10px 24px;">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
