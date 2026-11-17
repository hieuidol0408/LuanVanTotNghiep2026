@extends('layouts.chinh')

@section('title', 'Quản lý Phiên Họp - Hội đồng trường STU')

@section('header_title')
    <x-thanh-dieu-huong :items="[
        ['title' => 'Quản lý phiên họp']
    ]" />
@endsection

@section('content')
<div class="stu-page-header">
    <div class="stu-page-title-group">
        <h1>Danh sách Phiên Họp Hội Đồng Trường</h1>
        <p>Quản lý lịch họp, triệu tập thành viên, khách mời và điều hành chương trình họp.</p>
    </div>
    <div class="stu-page-actions">
        @if(in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN']))
            <x-nut-bam variant="primary" data-modal-open="createMeetingModal">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                <span>Tạo phiên họp mới</span>
            </x-nut-bam>
        @endif
    </div>
</div>

<!-- THANH TÌM KIẾM & BỘ LỌC ĐA TIÊU CHÍ -->
<div class="stu-card" style="padding: 18px 24px; margin-bottom: var(--space-6); border-radius: 12px; border: 1px solid var(--color-border); box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05); background: #ffffff;">
    <form method="GET" action="{{ route('meetings.index') }}" style="display: grid; grid-template-columns: 2.2fr 1.2fr 1.2fr 1.2fr auto auto; gap: 12px; align-items: center;">
        <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
        <!-- Ô tìm kiếm -->
        <div style="position: relative;">
            <svg width="15" height="15" fill="none" stroke="#94A3B8" stroke-width="2" viewBox="0 0 24 24" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); pointer-events: none;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            <input 
                type="text" 
                name="search" 
                placeholder="Tìm theo tên phiên họp, địa điểm..." 
                value="{{ request('search') }}" 
                class="stu-form-control"
                style="padding-left: 36px; height: 38px;"
            >
        </div>

        <!-- Lọc theo năm -->
        <div>
            <select name="year" class="stu-form-control" style="height: 38px;">
                <option value="">-- Tất cả các năm --</option>
                @foreach($years as $y)
                    <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>Năm {{ $y }}</option>
                @endforeach
            </select>
        </div>

        <!-- Lọc theo hình thức -->
        <div>
            <select name="format" class="stu-form-control" style="height: 38px;">
                <option value="">-- Hình thức họp --</option>
                <option value="Trực tiếp" {{ request('format') == 'Trực tiếp' ? 'selected' : '' }}>Trực tiếp</option>
                <option value="Trực tuyến" {{ request('format') == 'Trực tuyến' ? 'selected' : '' }}>Trực tuyến</option>
                <option value="Kết hợp" {{ request('format') == 'Kết hợp' ? 'selected' : '' }}>Kết hợp</option>
            </select>
        </div>

        <!-- Lọc theo trạng thái -->
        <div>
            <select name="status" class="stu-form-control" style="height: 38px;">
                <option value="">-- Trạng thái họp --</option>
                <option value="Đang chuẩn bị" {{ request('status') == 'Đang chuẩn bị' ? 'selected' : '' }}>Đang chuẩn bị</option>
                <option value="Sắp diễn ra" {{ request('status') == 'Sắp diễn ra' ? 'selected' : '' }}>Sắp diễn ra</option>
                <option value="Đang diễn ra" {{ request('status') == 'Đang diễn ra' ? 'selected' : '' }}>Đang diễn ra</option>
                <option value="Đã diễn ra" {{ request('status') == 'Đã diễn ra' ? 'selected' : '' }}>Đã diễn ra</option>
                <option value="Đã kết thúc" {{ request('status') == 'Đã kết thúc' ? 'selected' : '' }}>Đã kết thúc</option>
                <option value="Hủy" {{ request('status') == 'Hủy' ? 'selected' : '' }}>Hủy phiên họp</option>
            </select>
        </div>

        <!-- Nút áp dụng bộ lọc -->
        <div>
            <button type="submit" class="stu-btn stu-btn-primary" style="height: 38px; padding: 0 18px; font-weight: 600; box-shadow: 0 1px 2px 0 rgba(0, 58, 140, 0.2);">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <span>Lọc</span>
            </button>
        </div>
        <div>
            @if(request()->anyFilled(['search', 'year', 'format', 'status']))
                <a href="{{ route('meetings.index') }}" class="stu-btn stu-btn-ghost" style="height: 38px; padding: 0 14px;" title="Đặt lại bộ lọc">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Đặt lại</span>
                </a>
            @endif
        </div>
    </form>
</div>

<!-- BẢNG DỮ LIỆU PHIÊN HỌP (KHÔNG TRÀN & STICKY ACTION COLUMN) -->
<x-bang-du-lieu 
    :headers="['Mã', 'Tên phiên họp', 'Thời gian', 'Địa điểm & Hình thức', 'Người tạo', 'Trạng thái', 'Hồ sơ / TV']"
    :hasActions="true"
    actionTitle="Thao tác"
    :empty="$meetings->isEmpty()"
    emptyTitle="Không tìm thấy phiên họp phù hợp"
    emptyMessage="Vui lòng thử lại với từ khóa tìm kiếm hoặc điều kiện lọc khác."
>
    @foreach($meetings as $meeting)
        <tr>
            <td style="font-weight: 700; color: var(--color-primary); white-space: nowrap;">
                #{{ $meeting->MaPhienHop }}
            </td>
            <td class="stu-max-w-xs">
                <a href="{{ route('meetings.show', $meeting->MaPhienHop) }}" style="font-weight: 600; color: var(--color-primary); display: block;" title="{{ $meeting->TenPhienHop }}">
                    {{ $meeting->TenPhienHop }}
                </a>
                @if($meeting->GhiChu)
                    <div class="stu-truncate" style="font-size: 11px; color: var(--color-text-muted);" title="{{ $meeting->GhiChu }}">
                        Ghi chú: {{ $meeting->GhiChu }}
                    </div>
                @endif
            </td>
            <td style="white-space: nowrap;">
                <div style="font-weight: 500;">
                    {{ $meeting->ThoiGianBatDau ? date('d/m/Y', strtotime($meeting->ThoiGianBatDau)) : 'Chưa định ngày' }}
                </div>
                <div style="font-size: 11px; color: var(--color-text-muted);">
                    {{ $meeting->ThoiGianBatDau ? date('H:i', strtotime($meeting->ThoiGianBatDau)) : '' }} - 
                    {{ $meeting->ThoiGianKetThuc ? date('H:i', strtotime($meeting->ThoiGianKetThuc)) : '' }}
                </div>
            </td>
            <td style="font-size: var(--font-size-xs);">
                <div style="font-weight: 500;">{{ $meeting->DiaDiem }}</div>
                <span class="stu-badge stu-badge-info" style="margin-top: 4px; padding: 2px 6px;">
                    {{ $meeting->HinhThuc }}
                </span>
            </td>
            <td style="font-size: var(--font-size-xs); white-space: nowrap;">
                <div style="font-weight: 600; color: var(--color-text-primary);">
                    {{ $meeting->nguoiDung?->HoTen ?? 'ThS. Nguyễn Thị Thư Ký' }}
                </div>
                <div style="font-size: 11px; color: var(--color-text-muted);">
                    ID: #{{ $meeting->MaNguoiDung ?? 3 }}
                </div>
            </td>
            <td>
                <x-nhan-trang-thai :status="$meeting->TrangThai" />
            </td>
            <td style="font-size: var(--font-size-xs); white-space: nowrap;">
                <div><strong>{{ $meeting->chuong_trinh_hop_count ?? 0 }}</strong> hồ sơ</div>
                <div style="color: var(--color-text-muted);"><strong>{{ $meeting->tham_du_count ?? 0 }}</strong> thành viên</div>
            </td>
            <td class="col-actions">
                <div class="stu-actions-2x2">
                    {{-- Hàng 1 (Trên): Chi tiết & Sửa --}}
                    <a href="{{ route('meetings.show', $meeting->MaPhienHop) }}" class="stu-btn-2x2 btn-view" title="Xem chi tiết phiên họp #{{ $meeting->MaPhienHop }}">
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <span>Chi tiết</span>
                    </a>
                    @if(in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN']))
                        <a href="{{ route('meetings.edit', $meeting->MaPhienHop) }}" class="stu-btn-2x2 btn-update" title="Chỉnh sửa & dời lịch phiên họp">
                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            <span>Sửa</span>
                        </a>
                    @endif

                    {{-- Hàng 2 (Dưới): Chương trình họp & Ẩn --}}
                    <a href="{{ route('agendas.index', ['meeting_id' => $meeting->MaPhienHop]) }}" class="stu-btn-2x2 btn-view" title="Xem chương trình họp & hồ sơ trình của phiên này">
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        <span>Ch.trình</span>
                    </a>
                    @if(in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN']))
                        <form method="POST" action="{{ route('meetings.destroy', $meeting->MaPhienHop) }}" onsubmit="return confirm('Bạn có chắc chắn muốn ẩn phiên họp #{{ $meeting->MaPhienHop }} khỏi danh sách? (Dữ liệu vẫn được bảo toàn trong hệ thống)');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="stu-btn-2x2 btn-delete" title="Ẩn phiên họp (Bảo toàn dữ liệu)">
                                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                <span>Ẩn</span>
                            </button>
                        </form>
                    @endif
                </div>
            </td>
        </tr>
    @endforeach
</x-bang-du-lieu>

<!-- PHÂN TRANG -->
<div class="stu-mt-6" style="width: 100%;">
    {{ $meetings->links() }}
</div>

<!-- MODAL FORM LƠ LỬNG: TẠO PHIÊN HỌP MỚI -->
<x-hop-thoai id="createMeetingModal" title="Lập Kế Hoạch & Tạo Phiên Họp Mới" size="lg">
    <form method="POST" action="{{ route('meetings.store') }}" id="modalCreateMeetingForm" novalidate>
        @csrf

        <!-- Thông tin người tạo tự động -->
        <div style="background: var(--color-bg-subtle); border: 1px solid var(--color-border); border-radius: 8px; padding: 10px 14px; margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between; font-size: 12.5px;">
            <div>
                <span style="color: var(--color-text-muted);">Người tạo phiên họp:</span>
                <strong style="color: var(--color-primary); margin-left: 6px;">{{ auth()->user()?->HoTen ?? session('user_name', 'Thư ký Hội đồng trường') }}</strong>
            </div>
            <span class="stu-badge stu-badge-info" style="font-size: 11px;">Mã người tạo: #{{ auth()->user()?->MaNguoiDung ?? session('user_id', 1) }}</span>
        </div>

        <!-- Tên phiên họp -->
        <x-bieu-mau.o-nhap 
            name="TenPhienHop" 
            id="modalTenPhienHopInput"
            label="Tên phiên họp" 
            placeholder="Ví dụ: Phiên họp thường kỳ Lần thứ 5 Hội đồng trường STU (Khóa IV)" 
            value="{{ old('TenPhienHop') }}" 
            required 
            hint="Đặt tên trang trọng, phản ánh đúng kỳ họp và khóa nhiệm kỳ (tối thiểu 5 ký tự)"
        />
        <div id="modalTenPhienHopError" class="stu-form-error" style="display: none; margin-top: -10px; margin-bottom: 12px; font-size: 12px; color: var(--color-danger);"></div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-bottom: 12px;">
            <!-- Ngày họp -->
            <x-bieu-mau.o-nhap 
                type="date" 
                name="NgayHop" 
                id="modalNgayHopInput"
                label="Ngày tổ chức họp" 
                value="{{ old('NgayHop', date('Y-m-d')) }}" 
                required 
            />

            <!-- Giờ bắt đầu -->
            <x-bieu-mau.o-nhap 
                type="time" 
                name="GioBatDau" 
                id="modalGioBatDauInput"
                label="Giờ bắt đầu" 
                value="{{ old('GioBatDau', '08:30') }}" 
                required 
            />

            <!-- Giờ kết thúc -->
            <x-bieu-mau.o-nhap 
                type="time" 
                name="GioKetThuc" 
                id="modalGioKetThucInput"
                label="Giờ kết thúc" 
                value="{{ old('GioKetThuc', '11:30') }}" 
                required 
                hint="Phải lớn hơn giờ bắt đầu"
            />
        </div>
        <div id="modalTimeRangeError" class="stu-form-error" style="display: none; margin-top: -8px; margin-bottom: 12px; font-size: 12px; color: var(--color-danger);"></div>

        <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 12px; margin-bottom: 12px;">
            <!-- Địa điểm tổ chức -->
            <x-bieu-mau.o-nhap 
                name="DiaDiem" 
                id="modalDiaDiemInput"
                label="Địa điểm tổ chức" 
                placeholder="Ví dụ: Phòng họp Hội đồng trường, Tầng 3 Nhà A" 
                value="{{ old('DiaDiem', 'Phòng họp Hội đồng trường, Tòa nhà A') }}" 
                required 
            />

            <!-- Hình thức tổ chức -->
            <x-bieu-mau.o-chon name="HinhThuc" label="Hình thức tổ chức họp" required>
                <option value="Trực tiếp" {{ old('HinhThuc') == 'Trực tiếp' ? 'selected' : '' }}>Trực tiếp (Tại STU)</option>
                <option value="Trực tuyến" {{ old('HinhThuc') == 'Trực tuyến' ? 'selected' : '' }}>Trực tuyến</option>
                <option value="Kết hợp" {{ old('HinhThuc') == 'Kết hợp' ? 'selected' : '' }}>Kết hợp (Trực tiếp & Online)</option>
            </x-bieu-mau.o-chon>
        </div>
        <div id="modalDiaDiemError" class="stu-form-error" style="display: none; margin-top: -8px; margin-bottom: 12px; font-size: 12px; color: var(--color-danger);"></div>

        <!-- Ghi chú / Kịch bản cuộc họp -->
        <x-bieu-mau.vung-van-ban 
            name="GhiChu" 
            label="Ghi chú & Kịch bản phiên họp" 
            placeholder="Ví dụ: Thảo luận thông qua định mức tài chính và công tác nhân sự..." 
            rows="2" 
            hint="Ghi chú ngắn gọn nội dung phiên họp (không bắt buộc)"
        >{{ old('GhiChu') }}</x-bieu-mau.vung-van-ban>

        <div style="background: rgba(0, 58, 140, 0.05); border: 1px dashed var(--color-primary); border-radius: 8px; padding: 10px 14px; margin-top: 12px; font-size: 12px; color: var(--color-primary);">
            <strong>Quy tắc nghiệp vụ tự động (BR-02):</strong> Hệ thống tự động lưu vết người tạo và lập danh sách triệu tập thành viên HĐT, đảm bảo không trùng khung giờ phòng họp.
        </div>

        <!-- Nút hành động trong footer modal -->
        <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; padding-top: 14px; border-top: 1px solid var(--color-border);">
            <button type="button" class="stu-btn stu-btn-secondary" data-modal-close>
                <span>Hủy bỏ</span>
            </button>
            <button type="submit" class="stu-btn stu-btn-primary" style="font-weight: 600;">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                <span>Xác nhận tạo phiên họp</span>
            </button>
        </div>
    </form>
</x-hop-thoai>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        @if(request('create') || $errors->any())
            window.STUModal?.open('createMeetingModal');
        @endif

        const modalForm = document.getElementById('modalCreateMeetingForm');
        if (modalForm) {
            modalForm.addEventListener('submit', function (e) {
                const titleEl = document.getElementById('modalTenPhienHopInput');
                const startEl = document.getElementById('modalGioBatDauInput');
                const endEl = document.getElementById('modalGioKetThucInput');
                const locEl = document.getElementById('modalDiaDiemInput');
                const titleErr = document.getElementById('modalTenPhienHopError');
                const timeErr = document.getElementById('modalTimeRangeError');
                const locErr = document.getElementById('modalDiaDiemError');

                if (titleErr) titleErr.style.display = 'none';
                if (timeErr) timeErr.style.display = 'none';
                if (locErr) locErr.style.display = 'none';

                let hasErr = false;
                if (!titleEl || !titleEl.value.trim() || titleEl.value.trim().length < 5) {
                    if (titleErr) {
                        titleErr.textContent = 'Vui lòng nhập tên phiên họp tối thiểu 5 ký tự.';
                        titleErr.style.display = 'block';
                    }
                    hasErr = true;
                }
                if (!locEl || !locEl.value.trim() || locEl.value.trim().length < 3) {
                    if (locErr) {
                        locErr.textContent = 'Vui lòng nhập địa điểm tổ chức hợp lệ.';
                        locErr.style.display = 'block';
                    }
                    hasErr = true;
                }
                if (startEl && endEl && (!startEl.value || !endEl.value || endEl.value <= startEl.value)) {
                    if (timeErr) {
                        timeErr.textContent = 'Giờ kết thúc bắt buộc phải lớn hơn Giờ bắt đầu.';
                        timeErr.style.display = 'block';
                    }
                    hasErr = true;
                }
                if (hasErr) {
                    e.preventDefault();
                }
            });
        }
    });
</script>
@endsection
