@extends('layouts.chinh')

@section('title', 'Tạo Phiên Họp Mới - Hội đồng trường STU')

@section('header_title')
    <x-thanh-dieu-huong :items="[
        ['title' => 'Quản lý phiên họp', 'url' => route('meetings.index')],
        ['title' => 'Tạo phiên họp mới']
    ]" />
@endsection

@section('content')
<div class="stu-page-header">
    <div class="stu-page-title-group">
        <h1>Lập Kế Hoạch & Tạo Phiên Họp Mới</h1>
        <p>Thiết lập thời gian, địa điểm, hình thức họp và tự động chuẩn bị danh sách mời thành viên Hội đồng trường.</p>
    </div>
    <div class="stu-page-actions">
        <x-nut-bam variant="secondary" href="{{ route('meetings.index') }}">
            <span>Quay lại danh sách</span>
        </x-nut-bam>
    </div>
</div>

<div style="display: flex; justify-content: center; padding: 10px 0 30px 0;">
    <div class="stu-card" style="width: 100%; max-width: 820px; padding: 32px 36px; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04), 0 0 0 1px rgba(0, 0, 0, 0.06); background: #ffffff;">
        <div style="display: flex; align-items: center; justify-content: space-between; padding-bottom: 16px; margin-bottom: 20px; border-bottom: 1px solid var(--color-border);">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="width: 36px; height: 36px; border-radius: 8px; background: rgba(0, 58, 140, 0.1); display: flex; align-items: center; justify-content: center; color: var(--color-primary);">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </span>
                <div>
                    <h2 style="font-size: 18px; font-weight: 700; color: var(--color-text-main); margin: 0;">Lập Kế Hoạch & Tạo Phiên Họp Mới</h2>
                    <p style="font-size: 12px; color: var(--color-text-muted); margin: 2px 0 0 0;">Thiết lập thông tin phiên họp Hội đồng trường STU</p>
                </div>
            </div>
            <a href="{{ route('meetings.index') }}" class="stu-btn stu-btn-ghost stu-btn-sm" style="font-size: 12px;">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                <span>Đóng</span>
            </a>
        </div>
    <form method="POST" action="{{ route('meetings.store') }}" id="createMeetingForm" novalidate>
        @csrf

        <!-- Thông tin người tạo tự động ghi nhận -->
        <div style="background: var(--color-bg-subtle); border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: 12px 16px; margin-bottom: var(--space-5); display: flex; align-items: center; justify-content: space-between; font-size: var(--font-size-sm);">
            <div>
                <span style="color: var(--color-text-muted);">Người tạo phiên họp:</span>
                <strong style="color: var(--color-primary); margin-left: 6px;">{{ auth()->user()?->HoTen ?? session('user_name', 'Thư ký Hội đồng trường') }}</strong>
            </div>
            <span class="stu-badge stu-badge-info">Mã người tạo (ID): #{{ auth()->user()?->MaNguoiDung ?? session('user_id', 1) }}</span>
        </div>

        <!-- Tên phiên họp -->
        <x-bieu-mau.o-nhap 
            name="TenPhienHop" 
            id="tenPhienHopInput"
            label="Tên phiên họp" 
            placeholder="Ví dụ: Phiên họp thường kỳ Lần thứ 5 Hội đồng trường STU (Khóa IV)" 
            value="{{ old('TenPhienHop') }}" 
            required 
            hint="Đặt tên trang trọng, phản ánh đúng kỳ họp và khóa nhiệm kỳ (tối thiểu 5 ký tự)"
        />
        <div id="tenPhienHopError" class="stu-form-error" style="display: none; margin-top: -12px; margin-bottom: 12px;"></div>

        <div class="stu-grid stu-grid-cols-3">
            <!-- Ngày họp -->
            <x-bieu-mau.o-nhap 
                type="date" 
                name="NgayHop" 
                id="ngayHopInput"
                label="Ngày tổ chức họp" 
                value="{{ old('NgayHop', date('Y-m-d')) }}" 
                min="{{ date('Y-m-d') }}"
                required 
            />

            <!-- Giờ bắt đầu -->
            <x-bieu-mau.o-nhap 
                type="time" 
                name="GioBatDau" 
                id="gioBatDauInput"
                label="Giờ bắt đầu" 
                value="{{ old('GioBatDau', '08:30') }}" 
                required 
            />

            <!-- Giờ kết thúc -->
            <x-bieu-mau.o-nhap 
                type="time" 
                name="GioKetThuc" 
                id="gioKetThucInput"
                label="Giờ kết thúc" 
                value="{{ old('GioKetThuc', '11:30') }}" 
                required 
                hint="Phải lớn hơn giờ bắt đầu"
            />
        </div>
        <div id="timeRangeClientError" class="stu-form-error" style="display: none; margin-top: -8px; margin-bottom: 12px;"></div>

        <div class="stu-grid stu-grid-cols-2">
            <!-- Địa điểm tổ chức -->
            <x-bieu-mau.o-nhap 
                name="DiaDiem" 
                id="diaDiemInput"
                label="Địa điểm tổ chức" 
                placeholder="Ví dụ: Phòng họp Hội đồng trường, Tầng 3 Nhà A" 
                value="{{ old('DiaDiem', 'Phòng họp Hội đồng trường, Tòa nhà A') }}" 
                required 
            />

            <!-- Hình thức tổ chức -->
            <x-bieu-mau.o-chon name="HinhThuc" label="Hình thức tổ chức họp" required>
                <option value="Trực tiếp" {{ old('HinhThuc') == 'Trực tiếp' ? 'selected' : '' }}>Trực tiếp (Tại trường STU)</option>
                <option value="Trực tuyến" {{ old('HinhThuc') == 'Trực tuyến' ? 'selected' : '' }}>Trực tuyến</option>
                <option value="Kết hợp" {{ old('HinhThuc') == 'Kết hợp' ? 'selected' : '' }}>Kết hợp (Trực tiếp và Trực tuyến)</option>
            </x-bieu-mau.o-chon>
        </div>
        <div id="diaDiemError" class="stu-form-error" style="display: none; margin-top: -12px; margin-bottom: 12px;"></div>

        <!-- Ghi chú / Kịch bản cuộc họp -->
        <x-bieu-mau.vung-van-ban 
            name="GhiChu" 
            label="Ghi chú & Kịch bản phiên họp" 
            placeholder="Ví dụ: A, B, C..." 
            rows="3" 
            hint="Ghi chú ngắn gọn nội dung phiên họp (không bắt buộc)"
        >{{ old('GhiChu') }}</x-bieu-mau.vung-van-ban>

        <!-- CHỌN THÀNH VIÊN HỘI ĐỒNG TRƯỜNG THAM GIA (PHÂN QUYỀN BIỂU QUYẾT) -->
        <div style="margin-top: var(--space-4); margin-bottom: var(--space-4); border: 1px solid var(--color-border); border-radius: var(--radius-md); overflow: hidden; background: #ffffff;">
            <div style="background: #F8FAFC; padding: 12px 16px; border-bottom: 1px solid var(--color-border); display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color: var(--color-primary);"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <strong style="font-size: 13.5px; color: var(--color-text-main);">1. Thành viên Hội đồng trường tham gia <span style="color: var(--color-danger);">*</span></strong>
                    <span class="stu-badge stu-badge-info" id="selectedMembersBadge" style="font-size: 11.5px;">Đã chọn: {{ count($officialMembers ?? []) }}/{{ count($officialMembers ?? []) }}</span>
                </div>
                <div style="display: flex; gap: 8px;">
                    <button type="button" class="stu-btn stu-btn-ghost stu-btn-xs" id="btnSelectAllMembers" style="font-size: 11.5px; padding: 3px 10px;">Chọn tất cả</button>
                    <button type="button" class="stu-btn stu-btn-ghost stu-btn-xs" id="btnDeselectAllMembers" style="font-size: 11.5px; padding: 3px 10px;">Bỏ chọn tất cả</button>
                </div>
            </div>
            
            <div style="padding: 12px 16px; max-height: 220px; overflow-y: auto;">
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 10px;">
                    @forelse($officialMembers ?? [] as $m)
                        <label style="display: flex; align-items: center; gap: 10px; padding: 8px 12px; border-radius: 8px; border: 1px solid var(--color-border); background: #ffffff; cursor: pointer; transition: all 0.15s ease;">
                            <input type="checkbox" name="member_ids[]" value="{{ $m->MaThanhVien }}" class="member-cb" checked style="accent-color: var(--color-primary); width: 17px; height: 17px;">
                            <div style="flex: 1; min-width: 0;">
                                <div style="font-size: 13px; font-weight: 600; color: var(--color-text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    {{ $m->nguoiDung?->HoTen ?? 'Thành viên #' . $m->MaThanhVien }}
                                </div>
                                <div style="font-size: 11.5px; color: var(--color-text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    {{ $m->ChucDanhHoiDongTruong ?? $m->ChucDanh ?? 'Thành viên HĐT' }} - {{ $m->nguoiDung?->donVi?->TenDonVi ?? 'STU' }}
                                </div>
                            </div>
                            @if($m->CoQuyenBieuQuyet ?? true)
                                <span class="stu-badge stu-badge-success" style="font-size: 10px; padding: 2px 6px; flex-shrink: 0;" title="Có quyền bỏ phiếu biểu quyết">Biểu quyết</span>
                            @else
                                <span class="stu-badge stu-badge-secondary" style="font-size: 10px; padding: 2px 6px; flex-shrink: 0;" title="Không có quyền bỏ phiếu biểu quyết">Dự thính</span>
                            @endif
                        </label>
                    @empty
                        <p style="font-size: 12px; color: var(--color-text-muted); margin: 0;">Chưa có dữ liệu thành viên Hội đồng trường.</p>
                    @endforelse
                </div>
            </div>
            <div id="memberError" class="stu-form-error" style="display: none; padding: 4px 16px 8px 16px; font-size: 12px; color: var(--color-danger);"></div>
            <div style="background: #F1F5F9; padding: 8px 16px; border-top: 1px solid var(--color-border); font-size: 11.5px; color: var(--color-text-muted);">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align: -2px; color: var(--color-primary);"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                Chỉ những thành viên được chọn tham gia mới có tên trong danh sách điểm danh và được phân quyền tham gia biểu quyết tại phiên họp này.
            </div>
        </div>

        <!-- KHÁCH MỜI THAM DỰ PHIÊN HỌP (TÙY CHỌN) -->
        <div style="margin-bottom: var(--space-4); border: 1px solid var(--color-border); border-radius: var(--radius-md); overflow: hidden; background: #ffffff;">
            <div style="background: #F8FAFC; padding: 12px 16px; border-bottom: 1px solid var(--color-border); display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color: var(--color-text-muted);"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                    <strong style="font-size: 13.5px; color: var(--color-text-main);">2. Khách mời tham dự phiên họp</strong>
                    <span style="font-size: 11.5px; color: var(--color-text-muted);">(Không bắt buộc - Không có quyền biểu quyết)</span>
                </div>
                <button type="button" class="stu-btn stu-btn-secondary stu-btn-xs" id="btnAddGuest" style="font-size: 11.5px; gap: 4px;">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    <span>Thêm khách mời ngoài HĐT</span>
                </button>
            </div>

            @if(count($existingGuests ?? []) > 0)
                <div style="padding: 10px 16px; border-bottom: 1px dashed var(--color-border); background: #FAFAFA;">
                    <span style="font-size: 12px; font-weight: 600; color: var(--color-text-muted); margin-bottom: 6px; display: block;">Chọn từ danh bạ khách mời đã có:</span>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px; max-height: 80px; overflow-y: auto;">
                        @foreach($existingGuests as $guest)
                            <label style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 6px; border: 1px solid var(--color-border); background: #ffffff; font-size: 12px; cursor: pointer;">
                                <input type="checkbox" name="guest_ids[]" value="{{ $guest->MaKhachMoi }}" style="accent-color: var(--color-primary);">
                                <span>{{ $guest->HoTen }} ({{ $guest->DonViCongTac ?? 'Khách mời' }})</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            <div id="newGuestsList" style="padding: 12px 16px; display: flex; flex-direction: column; gap: 10px;"></div>
        </div>

        <x-canh-bao type="info" title="Quy tắc nghiệp vụ tự động (BR-02 & BR-08):" :dismissible="false">
            Hệ thống tự động ghi nhận người tạo, bẫy lỗi thời gian quá khứ, kiểm tra không trùng lịch họp (BR-08) và chỉ cấp quyền biểu quyết cho các thành viên Hội đồng được chọn triệu tập (BR-02).
        </x-canh-bao>

        <!-- Nút hành động -->
        <div class="stu-flex stu-justify-end stu-gap-3" style="margin-top: var(--space-6);">
            <x-nut-bam variant="secondary" href="{{ route('meetings.index') }}">Hủy bỏ</x-nut-bam>
            <x-nut-bam type="submit" variant="primary">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                <span>Xác nhận tạo phiên họp</span>
            </x-nut-bam>
        </div>
    </form>
</div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Xử lý chọn / bỏ chọn thành viên HĐT
        const memberCheckboxes = document.querySelectorAll('.member-cb');
        const badgeCounter = document.getElementById('selectedMembersBadge');
        const btnSelectAll = document.getElementById('btnSelectAllMembers');
        const btnDeselectAll = document.getElementById('btnDeselectAllMembers');

        function updateMemberCount() {
            const checked = document.querySelectorAll('.member-cb:checked').length;
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

        // Thêm dòng khách mời động
        const btnAddGuest = document.getElementById('btnAddGuest');
        const newGuestsList = document.getElementById('newGuestsList');
        if (btnAddGuest && newGuestsList) {
            btnAddGuest.addEventListener('click', function () {
                const row = document.createElement('div');
                row.style.cssText = 'display: grid; grid-template-columns: 1.5fr 1.5fr 1fr 1fr auto; gap: 8px; align-items: center; background: #F8FAFC; padding: 8px 12px; border-radius: 6px; border: 1px solid var(--color-border);';
                row.innerHTML = `
                    <input type="text" name="new_guest_names[]" placeholder="Họ tên khách mời *" class="stu-form-control" style="font-size: 12px; height: 34px;" required>
                    <input type="text" name="new_guest_depts[]" placeholder="Cơ quan / Đơn vị" class="stu-form-control" style="font-size: 12px; height: 34px;">
                    <input type="text" name="new_guest_roles[]" placeholder="Chức vụ" class="stu-form-control" style="font-size: 12px; height: 34px;">
                    <input type="text" name="new_guest_purposes[]" placeholder="Nội dung mời" value="Mời tham dự phiên họp" class="stu-form-control" style="font-size: 12px; height: 34px;">
                    <button type="button" class="stu-btn stu-btn-ghost stu-btn-xs" style="color: var(--color-danger); padding: 4px;" title="Xóa dòng này" onclick="this.parentElement.remove()">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                `;
                newGuestsList.appendChild(row);
            });
        }

        // Bẫy lỗi submit form
        document.getElementById('createMeetingForm')?.addEventListener('submit', function (e) {
            const titleEl = document.getElementById('tenPhienHopInput');
            const dateEl = document.getElementById('ngayHopInput');
            const startEl = document.getElementById('gioBatDauInput');
            const endEl = document.getElementById('gioKetThucInput');
            const locEl = document.getElementById('diaDiemInput');

            const titleErr = document.getElementById('tenPhienHopError');
            const timeErr = document.getElementById('timeRangeClientError');
            const locErr = document.getElementById('diaDiemError');
            const memberErr = document.getElementById('memberError');

            if (titleErr) titleErr.style.display = 'none';
            if (timeErr) timeErr.style.display = 'none';
            if (locErr) locErr.style.display = 'none';
            if (memberErr) memberErr.style.display = 'none';

            let hasErr = false;

            // 1. Kiểm tra tên phiên họp
            if (!titleEl.value.trim() || titleEl.value.trim().length < 5) {
                if (titleErr) {
                    titleErr.textContent = 'Vui lòng nhập tên phiên họp tối thiểu 5 ký tự.';
                    titleErr.style.display = 'block';
                }
                hasErr = true;
            }

            // 2. Kiểm tra địa điểm
            if (!locEl.value.trim() || locEl.value.trim().length < 3) {
                if (locErr) {
                    locErr.textContent = 'Vui lòng nhập địa điểm tổ chức hợp lệ (tối thiểu 3 ký tự).';
                    locErr.style.display = 'block';
                }
                hasErr = true;
            }

            // 3. Bẫy lỗi thời gian bắt đầu trong quá khứ so với thời điểm hiện tại
            if (dateEl && startEl && dateEl.value && startEl.value) {
                const startDateTime = new Date(`${dateEl.value}T${startEl.value.length === 5 ? startEl.value + ':00' : startEl.value}`);
                const now = new Date();
                if (startDateTime < now) {
                    if (timeErr) {
                        timeErr.textContent = 'Thời gian bắt đầu phiên họp không được nằm trong quá khứ so với thời điểm hiện tại.';
                        timeErr.style.display = 'block';
                    }
                    hasErr = true;
                }
            }

            // 4. Bẫy lỗi giờ kết thúc phải lớn hơn giờ bắt đầu
            if (!startEl.value || !endEl.value || endEl.value <= startEl.value) {
                if (timeErr) {
                    timeErr.textContent = 'Giờ kết thúc bắt buộc phải lớn hơn Giờ bắt đầu.';
                    timeErr.style.display = 'block';
                }
                hasErr = true;
            }

            // 5. Bẫy lỗi bắt buộc chọn ít nhất 1 thành viên HĐT
            const checkedMembers = document.querySelectorAll('.member-cb:checked').length;
            if (checkedMembers === 0) {
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
    });
</script>
@endsection
