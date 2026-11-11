@extends('layouts.chinh')

@section('title', 'Design System & Blade Components - Hội đồng trường STU')

@section('header_title')
    <x-thanh-dieu-huong :items="[
        ['title' => 'Design System', 'url' => url('/design-system')],
        ['title' => 'Thư viện Blade Components Chuẩn']
    ]" />
@endsection

@section('content')
<div class="stu-page-header">
    <div class="stu-page-title-group">
        <h1>Thư viện Design System & Blade Components</h1>
        <p>Quy chuẩn thiết kế Modern Enterprise Dashboard cho Hệ thống Quản lý Hoạt động Hội đồng trường STU.</p>
    </div>
    <div class="stu-page-actions">
        <x-nut-bam variant="outline" data-stu-theme-toggle>
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            <span>Đổi Light/Dark Mode</span>
        </x-nut-bam>
        <x-nut-bam variant="primary" data-modal-open="demoModal">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            <span>Mở Modal Mẫu</span>
        </x-nut-bam>
    </div>
</div>

<!-- 1. THẺ THỐNG KÊ (STAT CARDS) -->
<section style="margin-bottom: var(--space-8);">
    <h2 style="font-size: var(--font-size-lg); font-weight: 700; margin-bottom: var(--space-4); color: var(--color-primary);">
        1. Thẻ Thống kê (Stat Cards)
    </h2>
    <div class="stu-grid stu-grid-cols-4">
        <x-the-thong-ke title="Tổng số phiên họp" value="12" subtitle="3 phiên sắp diễn ra" type="primary" link="{{ url('/meetings') }}">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        </x-the-thong-ke>

        <x-the-thong-ke title="Nghị quyết đã ban hành" value="48" trend="+15% so với năm trước" type="success" link="{{ url('/resolutions') }}">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </x-the-thong-ke>

        <x-the-thong-ke title="Nhiệm vụ đang thực hiện" value="26" subtitle="5 nhiệm vụ gần đến hạn" type="warning" link="{{ url('/tasks') }}">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </x-the-thong-ke>

        <x-the-thong-ke title="Báo cáo chờ xác nhận" value="7" subtitle="Cần thẩm định tiến độ" type="danger" link="{{ url('/reports') }}">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </x-the-thong-ke>
    </div>
</section>

<!-- 2. BẢNG DỮ LIỆU CHUẨN HOÁ (TABLE) - KHẮC PHỤC TRIỆT ĐỂ LỖI TRÀN KHUNG & XÉN NÚT -->
<section style="margin-bottom: var(--space-8);">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--space-4);">
        <div>
            <h2 style="font-size: var(--font-size-lg); font-weight: 700; color: var(--color-primary);">
                2. Bảng Dữ liệu & Xử lý Tràn Khung (Data Table Component)
            </h2>
            <p style="font-size: var(--font-size-xs); color: var(--color-text-muted);">
                Áp dụng <code>overflow-x-auto</code>, giới hạn chiều rộng cột nội dung với <code>stu-max-w-xs</code> và cố định cột Thao tác (<code>col-actions sticky right-0</code>).
            </p>
        </div>
        <span class="stu-badge stu-badge-success">Đã khắc phục lỗi tràn khung ảnh 2</span>
    </div>

    <x-bang-du-lieu 
        cardTitle="Danh sách Nghị quyết Hội đồng trường STU (Mẫu kiểm thử)"
        :headers="['Số hiệu', 'Trích yếu nội dung', 'Phiên họp & Ngày', 'Trạng thái', 'Tiến độ thực hiện']" 
        :hasActions="true"
        actionTitle="Thao tác"
    >
        <tr>
            <td style="font-weight: 700; color: var(--color-primary); white-space: nowrap;">
                TEST-PUB-6adef1868bdd3
            </td>
            <td class="stu-max-w-xs stu-truncate" title="Nghị quyết chuẩn bị công bố hiệu lực thi hành các quy định đào tạo năm 2026">
                Nghị quyết chuẩn bị công bố hiệu lực thi hành các quy định đào tạo năm 2026
            </td>
            <td style="white-space: nowrap;">
                <div style="font-weight: 500;">Phiên họp lần thứ 5 HĐT (Khóa IV)</div>
                <div style="font-size: 11px; color: var(--color-text-muted);">26/10/2026</div>
            </td>
            <td>
                <x-nhan-trang-thai status="Đã công bố hiệu lực" />
            </td>
            <td style="min-width: 140px;">
                <x-thanh-tien-do value="85" label="85%" />
            </td>
            <td class="col-actions">
                <div class="stu-table-actions-group">
                    <x-nut-bam variant="outline" size="sm">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <span>Xem</span>
                    </x-nut-bam>
                    <x-nut-bam variant="primary" size="sm">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        <span>Công bố</span>
                    </x-nut-bam>
                </div>
            </td>
        </tr>

        <tr>
            <td style="font-weight: 700; color: var(--color-primary); white-space: nowrap;">
                TEST-SIGN-6adef1866507a
            </td>
            <td class="stu-max-w-xs stu-truncate" title="Nghị quyết chờ ký ban hành về việc bổ nhiệm nhân sự chủ chốt các khoa phòng">
                Nghị quyết chờ ký ban hành về việc bổ nhiệm nhân sự chủ chốt các khoa phòng
            </td>
            <td style="white-space: nowrap;">
                <div style="font-weight: 500;">Phiên họp lần thứ 5 HĐT (Khóa IV)</div>
                <div style="font-size: 11px; color: var(--color-text-muted);">26/10/2026</div>
            </td>
            <td>
                <x-nhan-trang-thai status="Đã ký ban hành" />
            </td>
            <td style="min-width: 140px;">
                <x-thanh-tien-do value="50" label="50%" />
            </td>
            <td class="col-actions">
                <div class="stu-table-actions-group">
                    <x-nut-bam variant="outline" size="sm">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <span>Xem</span>
                    </x-nut-bam>
                    <x-nut-bam variant="secondary" size="sm">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        <span>Ký ban hành</span>
                    </x-nut-bam>
                </div>
            </td>
        </tr>

        <tr>
            <td style="font-weight: 700; color: var(--color-primary); white-space: nowrap;">
                99/NQ-HĐT-6adef18611546
            </td>
            <td class="stu-max-w-xs stu-truncate" title="Nghị quyết về việc thông qua kế hoạch đầu tư phòng thí nghiệm AI và nâng cấp cơ sở vật chất">
                Nghị quyết về việc thông qua kế hoạch đầu tư phòng thí nghiệm AI và nâng cấp cơ sở vật chất
            </td>
            <td style="white-space: nowrap;">
                <div style="font-weight: 500;">Phiên họp lần thứ 5 HĐT (Khóa IV)</div>
                <div style="font-size: 11px; color: var(--color-text-muted);">26/10/2026</div>
            </td>
            <td>
                <x-nhan-trang-thai status="Dự thảo" />
            </td>
            <td style="min-width: 140px;">
                <x-thanh-tien-do value="15" label="15%" />
            </td>
            <td class="col-actions">
                <div class="stu-table-actions-group">
                    <x-nut-bam variant="outline" size="sm">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <span>Xem</span>
                    </x-nut-bam>
                    <x-nut-bam variant="primary" size="sm">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        <span>Phân rã NV</span>
                    </x-nut-bam>
                </div>
            </td>
        </tr>
    </x-bang-du-lieu>
</section>

<!-- 3. HỆ THỐNG TABS & FORM CONTROLS -->
<section style="margin-bottom: var(--space-8);">
    <h2 style="font-size: var(--font-size-lg); font-weight: 700; margin-bottom: var(--space-4); color: var(--color-primary);">
        3. Tabs & Biểu mẫu Form Controls
    </h2>

    <x-the-chuyen :tabs="[
        ['id' => 'tab-form', 'target' => '#pane-form', 'label' => 'Biểu mẫu nhập liệu (Form)', 'active' => true],
        ['id' => 'tab-badges', 'target' => '#pane-badges', 'label' => 'Hệ thống Trạng thái Nghiệp vụ', 'badge' => '8'],
        ['id' => 'tab-alerts', 'target' => '#pane-alerts', 'label' => 'Thông báo Hệ thống (Alerts)']
    ]">
        <!-- Pane 1: Form Inputs -->
        <div class="stu-tab-pane active" id="pane-form">
            <div class="stu-card" style="padding: var(--space-6);">
                <div class="stu-grid stu-grid-cols-2">
                    <x-bieu-mau.o-nhap name="HoTen" label="Họ và tên thành viên" placeholder="Ví dụ: PGS. TS. Nguyễn Văn A" required hint="Tuân thủ BR-11: Đánh dấu sao đỏ cho trường bắt buộc" />
                    
                    <x-bieu-mau.o-nhap type="email" name="Email" label="Địa chỉ Email" placeholder="nguyenvana@stu.edu.vn" required hint="Tuân thủ BR-01: Duy nhất trên toàn hệ thống" />

                    <x-bieu-mau.o-chon name="MaDonVi" label="Đơn vị công tác" required>
                        <option value="CNTT">Khoa Công nghệ Thông tin</option>
                        <option value="DDT">Khoa Điện - Điện tử</option>
                        <option value="QTKD">Khoa Quản trị Kinh doanh</option>
                    </x-bieu-mau.o-chon>

                    <x-bieu-mau.o-chon name="ChucDanh" label="Chức danh trong Hội đồng" required>
                        <option value="CHAIR">Chủ tịch Hội đồng trường</option>
                        <option value="SECRETARY">Thư ký Hội đồng trường</option>
                        <option value="MEMBER">Thành viên Hội đồng trường</option>
                    </x-bieu-mau.o-chon>
                </div>

                <x-bieu-mau.vung-van-ban name="GhiChu" label="Ghi chú nghiệp vụ" placeholder="Nhập ghi chú bổ sung (nếu có)..." rows="3" hint="Trường không bắt buộc (NULLable theo BR-11)" />

                <div class="stu-flex stu-justify-end stu-gap-3" style="margin-top: var(--space-4);">
                    <x-nut-bam variant="secondary">Hủy bỏ</x-nut-bam>
                    <x-nut-bam variant="primary">Lưu thông tin</x-nut-bam>
                </div>
            </div>
        </div>

        <!-- Pane 2: Status Badges -->
        <div class="stu-tab-pane" id="pane-badges">
            <div class="stu-card" style="padding: var(--space-6);">
                <p style="font-size: var(--font-size-sm); color: var(--color-text-muted); margin-bottom: var(--space-4);">
                    Tất cả trạng thái tự động phân bổ bảng màu theo quy chuẩn thương hiệu STU:
                </p>
                <div style="display: flex; flex-wrap: wrap; gap: var(--space-2);">
                    <x-nhan-trang-thai status="Hoàn thành" />
                    <x-nhan-trang-thai status="Đã công bố hiệu lực" />
                    <x-nhan-trang-thai status="Có mặt" />
                    <x-nhan-trang-thai status="Đủ điều kiện" />
                    <x-nhan-trang-thai status="Đang thực hiện" />
                    <x-nhan-trang-thai status="Đang mở" />
                    <x-nhan-trang-thai status="Chờ kiểm tra" />
                    <x-nhan-trang-thai status="Có xin phép" />
                    <x-nhan-trang-thai status="Dự thảo" />
                    <x-nhan-trang-thai status="Mới tạo" />
                    <x-nhan-trang-thai status="Chưa xem xét" />
                    <x-nhan-trang-thai status="Vắng không phép" />
                    <x-nhan-trang-thai status="Đã hủy" />
                    <x-nhan-trang-thai status="Quá hạn" />
                </div>
            </div>
        </div>

        <!-- Pane 3: Alerts -->
        <div class="stu-tab-pane" id="pane-alerts">
            <div class="stu-card" style="padding: var(--space-6);">
                <x-canh-bao type="success" title="Thao tác thành công">
                    Nghị quyết số 99/NQ-HĐT đã được công bố hiệu lực thi hành toàn trường.
                </x-canh-bao>
                <x-canh-bao type="warning" title="Cần kiểm tra bổ sung">
                    Hồ sơ trình số HSTR-04 chưa có ý kiến thẩm định từ Ban Đào tạo.
                </x-canh-bao>
                <x-canh-bao type="danger" title="Quyền truy cập bị từ chối">
                    Bạn không có quyền thực hiện thao tác ký số nghị quyết này.
                </x-canh-bao>
                <x-canh-bao type="info" title="Lịch họp sắp tới">
                    Phiên họp thường kỳ Lần thứ 5 sẽ bắt đầu lúc 08:30 ngày 20/11/2026 tại Phòng Họp A.
                </x-canh-bao>
            </div>
        </div>
    </x-the-chuyen>
</section>

<!-- 4. MODAL DIALOG MẪU -->
<x-hop-thoai id="demoModal" title="Xác nhận Ban hành Nghị quyết" size="md">
    <p style="font-size: var(--font-size-sm); color: var(--color-text-body); margin-bottom: var(--space-4);">
        Bạn có chắc chắn muốn ký duyệt và ban hành <strong>Nghị quyết số 99/NQ-HĐT</strong>? Sau khi ban hành, nghị quyết sẽ có hiệu lực và tự động chuyển sang trạng thái phân rã nhiệm vụ cho các đơn vị.
    </p>
    <x-canh-bao type="info" title="Lưu ý pháp lý" :dismissible="false">
        Hành động này sẽ được ghi nhận vào nhật ký kiểm toán hệ thống cùng dấu thời gian và tài khoản thực hiện.
    </x-canh-bao>

    <x-slot:footer>
        <x-nut-bam variant="secondary" data-modal-close>Đóng</x-nut-bam>
        <x-nut-bam variant="primary" data-modal-close>Xác nhận ban hành</x-nut-bam>
    </x-slot:footer>
</x-hop-thoai>
@endsection
