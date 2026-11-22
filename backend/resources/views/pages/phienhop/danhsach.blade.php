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

        @php
            $modalHour = (int) date('H');
            if ($modalHour < 8) {
                $modalDefaultStart = '08:30';
                $modalDefaultEnd = '11:30';
            } elseif ($modalHour < 13) {
                $modalDefaultStart = '14:00';
                $modalDefaultEnd = '16:30';
            } elseif ($modalHour < 17) {
                $modalDefaultStart = date('H:00', strtotime('+1 hour'));
                $modalDefaultEnd = date('H:30', strtotime('+3 hours'));
            } else {
                $modalDefaultStart = '08:30';
                $modalDefaultEnd = '11:30';
            }
        @endphp

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-bottom: 12px;">
            <!-- Ngày họp -->
            <x-bieu-mau.o-nhap 
                type="date" 
                name="NgayHop" 
                id="modalNgayHopInput"
                label="Ngày tổ chức họp" 
                value="{{ old('NgayHop', date('Y-m-d')) }}" 
                min="{{ date('Y-m-d') }}"
                required 
            />

            <!-- Giờ bắt đầu -->
            <x-bieu-mau.o-nhap 
                type="time" 
                name="GioBatDau" 
                id="modalGioBatDauInput"
                label="Giờ bắt đầu" 
                value="{{ old('GioBatDau', $modalDefaultStart) }}" 
                required 
            />

            <!-- Giờ kết thúc -->
            <x-bieu-mau.o-nhap 
                type="time" 
                name="GioKetThuc" 
                id="modalGioKetThucInput"
                label="Giờ kết thúc" 
                value="{{ old('GioKetThuc', $modalDefaultEnd) }}" 
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

        <!-- CHỌN THÀNH VIÊN HỘI ĐỒNG TRƯỜNG THAM GIA (PHÂN QUYỀN BIỂU QUYẾT) -->
        <div style="margin-top: 14px; margin-bottom: 14px; border: 1px solid var(--color-border); border-radius: 8px; overflow: hidden; background: #ffffff;">
            <div style="background: #F8FAFC; padding: 10px 14px; border-bottom: 1px solid var(--color-border); display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color: var(--color-primary);"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <strong style="font-size: 13px; color: var(--color-text-main);">1. Thành viên Hội đồng trường tham gia <span style="color: var(--color-danger);">*</span></strong>
                    <span class="stu-badge stu-badge-info" id="modalSelectedMembersBadge" style="font-size: 11px;">Đã chọn: {{ count($officialMembers ?? []) }}/{{ count($officialMembers ?? []) }}</span>
                </div>
                <div style="display: flex; gap: 6px;">
                    <button type="button" class="stu-btn stu-btn-ghost stu-btn-xs" id="modalBtnSelectAll" style="font-size: 11px; padding: 2px 8px;">Chọn tất cả</button>
                    <button type="button" class="stu-btn stu-btn-ghost stu-btn-xs" id="modalBtnDeselectAll" style="font-size: 11px; padding: 2px 8px;">Bỏ chọn tất cả</button>
                </div>
            </div>
            
            <div style="padding: 10px 14px; max-height: 180px; overflow-y: auto;">
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 8px;">
                    @forelse($officialMembers ?? [] as $m)
                        <label style="display: flex; align-items: center; gap: 8px; padding: 6px 10px; border-radius: 6px; border: 1px solid var(--color-border); background: #ffffff; cursor: pointer; transition: all 0.15s ease;">
                            <input type="checkbox" name="member_ids[]" value="{{ $m->MaThanhVien }}" class="modal-member-cb" checked style="accent-color: var(--color-primary); width: 16px; height: 16px;">
                            <div style="flex: 1; min-width: 0;">
                                <div style="font-size: 12px; font-weight: 600; color: var(--color-text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    {{ $m->nguoiDung?->HoTen ?? 'Thành viên #' . $m->MaThanhVien }}
                                </div>
                                <div style="font-size: 11px; color: var(--color-text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    {{ $m->ChucDanhHoiDongTruong ?? $m->ChucDanh ?? 'Thành viên HĐT' }} - {{ $m->nguoiDung?->donVi?->TenDonVi ?? 'STU' }}
                                </div>
                            </div>
                            @if($m->CoQuyenBieuQuyet ?? true)
                                <span class="stu-badge stu-badge-success" style="font-size: 9.5px; padding: 1px 5px; flex-shrink: 0;" title="Có quyền biểu quyết">Biểu quyết</span>
                            @else
                                <span class="stu-badge stu-badge-secondary" style="font-size: 9.5px; padding: 1px 5px; flex-shrink: 0;" title="Không có quyền biểu quyết">Dự thính</span>
                            @endif
                        </label>
                    @empty
                        <p style="font-size: 12px; color: var(--color-text-muted); margin: 0;">Chưa có dữ liệu thành viên Hội đồng trường.</p>
                    @endforelse
                </div>
            </div>
            <div id="modalMemberError" class="stu-form-error" style="display: none; padding: 4px 14px 6px 14px; font-size: 12px; color: var(--color-danger);"></div>
            <div style="background: #F1F5F9; padding: 6px 14px; border-top: 1px solid var(--color-border); font-size: 11px; color: var(--color-text-muted);">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align: -1px; color: var(--color-primary);"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                Chỉ những thành viên được chọn tham gia mới có tên trong danh sách điểm danh và được phân quyền tham gia biểu quyết tại phiên họp này.
            </div>
        </div>

        <!-- KHÁCH MỜI THAM DỰ PHIÊN HỌP (TÙY CHỌN) -->
        <div style="margin-bottom: 14px; border: 1px solid var(--color-border); border-radius: 8px; overflow: hidden; background: #ffffff;">
            <div style="background: #F8FAFC; padding: 10px 14px; border-bottom: 1px solid var(--color-border); display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color: var(--color-text-muted);"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                    <strong style="font-size: 13px; color: var(--color-text-main);">2. Khách mời tham dự phiên họp</strong>
                    <span style="font-size: 11px; color: var(--color-text-muted);">(Không bắt buộc - Không có quyền biểu quyết)</span>
                </div>
                <button type="button" class="stu-btn stu-btn-secondary stu-btn-xs" id="modalBtnAddGuest" style="font-size: 11px; gap: 4px;">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    <span>Thêm khách mời ngoài HĐT</span>
                </button>
            </div>

            @if(count($existingGuests ?? []) > 0)
                <div style="padding: 8px 14px; border-bottom: 1px dashed var(--color-border); background: #FAFAFA;">
                    <span style="font-size: 11.5px; font-weight: 600; color: var(--color-text-muted); margin-bottom: 4px; display: block;">Chọn từ danh bạ khách mời đã có:</span>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px; max-height: 70px; overflow-y: auto;">
                        @foreach($existingGuests as $guest)
                            <label style="display: inline-flex; align-items: center; gap: 6px; padding: 3px 8px; border-radius: 4px; border: 1px solid var(--color-border); background: #ffffff; font-size: 11.5px; cursor: pointer;">
                                <input type="checkbox" name="guest_ids[]" value="{{ $guest->MaKhachMoi }}" style="accent-color: var(--color-primary);">
                                <span>{{ $guest->HoTen }} ({{ $guest->DonViCongTac ?? 'Khách mời' }})</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            <div id="modalNewGuestsList" style="padding: 10px 14px; display: flex; flex-direction: column; gap: 8px;"></div>
        </div>

        <div style="background: rgba(0, 58, 140, 0.05); border: 1px dashed var(--color-primary); border-radius: 8px; padding: 10px 14px; margin-top: 10px; font-size: 12px; color: var(--color-primary);">
            <strong>Quy tắc nghiệp vụ tự động (BR-02 & BR-08):</strong> Hệ thống ghi nhận người lập, bẫy lỗi khung giờ quá khứ và chống xung đột thời gian với các phiên họp khác của Hội đồng.
        </div>

        <!-- Nút hành động trong footer modal -->
        <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 18px; padding-top: 14px; border-top: 1px solid var(--color-border);">
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

        // Xử lý chọn / bỏ chọn thành viên HĐT trong modal
        const memberCheckboxes = document.querySelectorAll('.modal-member-cb');
        const badgeCounter = document.getElementById('modalSelectedMembersBadge');
        const btnSelectAll = document.getElementById('modalBtnSelectAll');
        const btnDeselectAll = document.getElementById('modalBtnDeselectAll');

        function updateMemberCount() {
            const checked = document.querySelectorAll('.modal-member-cb:checked').length;
            const total = memberCheckboxes.length;
            if (badgeCounter) {
                badgeCounter.textContent = `Đã chọn: ${checked}/${total}`;
            }
        }

        memberCheckboxes.forEach(cb => cb.addEventListener('change', updateMemberCount));

        if (btnSelectAll) {
            btnSelectAll.addEventListener('click', function () {
                memberCheckboxes.forEach(cb => cb.checked = true);
                updateMemberCount();
            });
        }
        if (btnDeselectAll) {
            btnDeselectAll.addEventListener('click', function () {
                memberCheckboxes.forEach(cb => cb.checked = false);
                updateMemberCount();
            });
        }

        // Thêm dòng nhập khách mời ngoài HĐT
        const btnAddGuest = document.getElementById('modalBtnAddGuest');
        const newGuestsList = document.getElementById('modalNewGuestsList');
        if (btnAddGuest && newGuestsList) {
            btnAddGuest.addEventListener('click', function () {
                const row = document.createElement('div');
                row.style.cssText = 'display: grid; grid-template-columns: 1.5fr 1.5fr 1fr 1fr auto; gap: 8px; align-items: center; background: #F8FAFC; padding: 6px 10px; border-radius: 6px; border: 1px solid var(--color-border);';
                row.innerHTML = `
                    <input type="text" name="new_guest_names[]" placeholder="Họ tên khách mời *" class="stu-form-control" style="font-size: 11.5px; height: 32px;" required>
                    <input type="text" name="new_guest_depts[]" placeholder="Cơ quan / Đơn vị" class="stu-form-control" style="font-size: 11.5px; height: 32px;">
                    <input type="text" name="new_guest_roles[]" placeholder="Chức vụ" class="stu-form-control" style="font-size: 11.5px; height: 32px;">
                    <input type="text" name="new_guest_purposes[]" placeholder="Nội dung mời" value="Mời tham dự phiên họp" class="stu-form-control" style="font-size: 11.5px; height: 32px;">
                    <button type="button" class="stu-btn stu-btn-ghost stu-btn-xs" style="color: var(--color-danger); padding: 4px;" title="Xóa dòng này" onclick="this.parentElement.remove()">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                `;
                newGuestsList.appendChild(row);
            });
        }

        // Tự động hỗ trợ & đồng bộ giờ kết thúc trong modal
        const modalStartInputEl = document.getElementById('modalGioBatDauInput');
        const modalEndInputEl = document.getElementById('modalGioKetThucInput');
        const modalDateInputEl = document.getElementById('modalNgayHopInput');
        const modalTimeErrorEl = document.getElementById('modalTimeRangeError');

        modalStartInputEl?.addEventListener('change', function () {
            if (modalTimeErrorEl) modalTimeErrorEl.style.display = 'none';
            const now = new Date();
            const todayStr = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;

            // Tự động hỗ trợ: Nếu người dùng gõ 12 giờ trưa nhưng dính SA thành 00:xx
            if (modalDateInputEl && modalDateInputEl.value === todayStr && this.value.startsWith('00:') && now.getHours() >= 6) {
                this.value = '12:' + this.value.substring(3);
            }

            // Tự động đẩy giờ kết thúc lên sau giờ bắt đầu (+ 2 tiếng) nếu đang nhỏ hơn hoặc bằng
            if (modalEndInputEl && this.value) {
                if (!modalEndInputEl.value || modalEndInputEl.value <= this.value) {
                    const [h, m] = this.value.split(':').map(Number);
                    const endH = Math.min(23, h + 2);
                    modalEndInputEl.value = `${String(endH).padStart(2, '0')}:${String(m).padStart(2, '0')}`;
                }
            }
        });

        // Bẫy lỗi form submit
        const modalForm = document.getElementById('modalCreateMeetingForm');
        if (modalForm) {
            modalForm.addEventListener('submit', function (e) {
                const titleEl = document.getElementById('modalTenPhienHopInput');
                const dateEl = document.getElementById('modalNgayHopInput');
                const startEl = document.getElementById('modalGioBatDauInput');
                const endEl = document.getElementById('modalGioKetThucInput');
                const locEl = document.getElementById('modalDiaDiemInput');

                const titleErr = document.getElementById('modalTenPhienHopError');
                const timeErr = document.getElementById('modalTimeRangeError');
                const locErr = document.getElementById('modalDiaDiemError');
                const memberErr = document.getElementById('modalMemberError');

                if (titleErr) titleErr.style.display = 'none';
                if (timeErr) timeErr.style.display = 'none';
                if (locErr) locErr.style.display = 'none';
                if (memberErr) memberErr.style.display = 'none';

                let hasErr = false;

                // 1. Kiểm tra tên phiên họp
                if (!titleEl || !titleEl.value.trim() || titleEl.value.trim().length < 5) {
                    if (titleErr) {
                        titleErr.textContent = 'Vui lòng nhập tên phiên họp tối thiểu 5 ký tự.';
                        titleErr.style.display = 'block';
                    }
                    hasErr = true;
                }

                // 2. Kiểm tra địa điểm
                if (!locEl || !locEl.value.trim() || locEl.value.trim().length < 3) {
                    if (locErr) {
                        locErr.textContent = 'Vui lòng nhập địa điểm tổ chức hợp lệ (tối thiểu 3 ký tự).';
                        locErr.style.display = 'block';
                    }
                    hasErr = true;
                }

                // 3. Bẫy lỗi thời gian bắt đầu trong quá khứ so với thời điểm hiện tại
                if (dateEl && startEl && dateEl.value && startEl.value) {
                    const now = new Date();
                    const todayStr = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;

                    // Tự động chuẩn hóa 00:xx -> 12:xx nếu người dùng chọn 12:xx trưa trên máy 12h
                    if (dateEl.value === todayStr && startEl.value.startsWith('00:') && now.getHours() >= 6) {
                        startEl.value = '12:' + startEl.value.substring(3);
                    }

                    const startDateTime = new Date(`${dateEl.value}T${startEl.value.length === 5 ? startEl.value + ':00' : startEl.value}`);
                    // So sánh theo cấp độ phút: đưa 'now' về mốc đầu phút (giây = 0, mili giây = 0)
                    // Thí dụ: nếu hiện tại là 12:53 thì 12:51, 12:52 không cho, nhưng từ 12:53 trở đi thì cho phép
                    const currentMinute = new Date(now.getFullYear(), now.getMonth(), now.getDate(), now.getHours(), now.getMinutes(), 0, 0);

                    if (startDateTime < currentMinute) {
                        if (timeErr) {
                            timeErr.textContent = 'Thời gian bắt đầu phiên họp không được nằm trong quá khứ so với thời điểm hiện tại.';
                            timeErr.style.display = 'block';
                        }
                        hasErr = true;
                    }
                }

                // 4. Bẫy lỗi giờ kết thúc phải lớn hơn giờ bắt đầu
                if (startEl && endEl && (!startEl.value || !endEl.value || endEl.value <= startEl.value)) {
                    if (timeErr) {
                        timeErr.textContent = 'Giờ kết thúc bắt buộc phải diễn ra sau Giờ bắt đầu.';
                        timeErr.style.display = 'block';
                    }
                    hasErr = true;
                }

                // 5. Bẫy lỗi bắt buộc chọn ít nhất 1 thành viên HĐT tham gia
                const checkedCount = document.querySelectorAll('.modal-member-cb:checked').length;
                if (checkedCount === 0) {
                    if (memberErr) {
                        memberErr.textContent = 'Vui lòng chọn ít nhất một thành viên Hội đồng trường tham gia phiên họp để phân quyền biểu quyết.';
                        memberErr.style.display = 'block';
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
