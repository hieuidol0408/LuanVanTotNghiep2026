@extends('layouts.chinh')

@section('title', 'Chương trình họp - Hội đồng trường STU')

@section('header_title')
    <x-thanh-dieu-huong :items="[
        ['title' => 'Quản lý phiên họp', 'url' => route('meetings.index')],
        ['title' => 'Chương trình họp']
    ]" />
@endsection

@section('content')
<div class="stu-page-header">
    <div class="stu-page-title-group">
        <h1>Chương trình họp Hội Đồng Trường</h1>
        <p>Quản lý và điều hành thứ tự nghị sự, liên kết hồ sơ trình vào các phiên họp theo quy tắc <strong>BR-08</strong>.</p>
    </div>
    <div class="stu-page-actions" style="display: flex; gap: 10px;">
        @if(in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN']))
            <button type="button" class="stu-btn stu-btn-primary" data-modal-open="createAgendaModal" style="height: 38px; display: inline-flex; align-items: center; gap: 6px; font-weight: 600;">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                <span>Thêm mục chương trình họp</span>
            </button>
        @endif
        <a href="{{ route('meetings.index') }}" class="stu-btn stu-btn-ghost" style="height: 38px;">
            <i class="bi bi-calendar-event"></i>
            <span>Quản lý phiên họp</span>
        </a>
        <a href="{{ route('submissions.index') }}" class="stu-btn stu-btn-ghost" style="height: 38px;">
            <i class="bi bi-file-earmark-text"></i>
            <span>Hồ sơ trình</span>
        </a>
    </div>
</div>

{{-- KPI Cards --}}
<div class="stu-grid stu-grid-cols-4 stu-mb-6" style="gap: 16px; margin-bottom: 24px;">
    <div class="stu-card" style="padding: 18px; border-left: 4px solid var(--color-primary); background: #ffffff; border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <div style="font-size: 12px; font-weight: 600; color: var(--color-text-muted); text-transform: uppercase;">Tổng mục nghị sự</div>
        <div style="font-size: 26px; font-weight: 800; color: var(--color-primary); margin-top: 4px;">{{ $totalAgendas }}</div>
    </div>
    <div class="stu-card" style="padding: 18px; border-left: 4px solid #0EA5E9; background: #ffffff; border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <div style="font-size: 12px; font-weight: 600; color: var(--color-text-muted); text-transform: uppercase;">Hồ sơ đã xếp lịch</div>
        <div style="font-size: 26px; font-weight: 800; color: #0EA5E9; margin-top: 4px;">{{ $totalDossiers }}</div>
    </div>
    <div class="stu-card" style="padding: 18px; border-left: 4px solid var(--color-success); background: #ffffff; border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <div style="font-size: 12px; font-weight: 600; color: var(--color-text-muted); text-transform: uppercase;">Đã thông qua</div>
        <div style="font-size: 26px; font-weight: 800; color: var(--color-success); margin-top: 4px;">{{ $approvedAgendas }}</div>
    </div>
    <div class="stu-card" style="padding: 18px; border-left: 4px solid var(--color-warning); background: #ffffff; border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <div style="font-size: 12px; font-weight: 600; color: var(--color-text-muted); text-transform: uppercase;">Phiên có chương trình</div>
        <div style="font-size: 26px; font-weight: 800; color: var(--color-warning); margin-top: 4px;">{{ $activeMeetingsCount }}</div>
    </div>
</div>

@if($selectedMeeting)
    {{-- Banner thông tin phiên họp đang lọc --}}
    <div class="stu-card" style="padding: 16px 20px; margin-bottom: 20px; background: linear-gradient(135deg, #EFF6FF 0%, #FFFFFF 100%); border-left: 4px solid var(--color-primary); border-radius: 10px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div>
                <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: var(--color-primary); letter-spacing: 0.5px;">
                    Đang xem chương trình của phiên họp
                </div>
                <div style="font-size: 16px; font-weight: 700; color: #1E293B; margin-top: 2px;">
                    {{ $selectedMeeting->TenPhienHop }}
                </div>
                <div style="font-size: 12px; color: var(--color-text-muted); margin-top: 4px;">
                    <i class="bi bi-calendar3"></i> {{ $selectedMeeting->ThoiGianBatDau ? date('d/m/Y H:i', strtotime($selectedMeeting->ThoiGianBatDau)) : 'Chưa định ngày' }}
                    &bull; <i class="bi bi-geo-alt"></i> {{ $selectedMeeting->DiaDiem }}
                    &bull; <span class="stu-badge stu-badge-info">{{ $selectedMeeting->HinhThuc }}</span>
                </div>
            </div>
            <div>
                <a href="{{ route('meetings.show', $selectedMeeting->MaPhienHop) }}" class="stu-btn stu-btn-primary" style="height: 36px; padding: 0 16px;">
                    <i class="bi bi-box-arrow-up-right"></i>
                    <span>Chi tiết phiên họp</span>
                </a>
            </div>
        </div>
    </div>
@endif

<!-- BỘ LỌC ĐA TIÊU CHÍ -->
<div class="stu-card" style="padding: 18px 24px; margin-bottom: var(--space-6); border-radius: 12px; border: 1px solid var(--color-border); box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05); background: #ffffff;">
    <form method="GET" action="{{ route('agendas.index') }}" style="display: grid; grid-template-columns: 2fr 2fr 1.5fr auto auto; gap: 12px; align-items: center;">
        <!-- Ô tìm kiếm -->
        <div style="position: relative;">
            <svg width="15" height="15" fill="none" stroke="#94A3B8" stroke-width="2" viewBox="0 0 24 24" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); pointer-events: none;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            <input 
                type="text" 
                name="search" 
                placeholder="Tìm tiêu đề hồ sơ, mã hồ sơ, ghi chú..." 
                value="{{ request('search') }}" 
                class="stu-form-control"
                style="padding-left: 36px; height: 38px;"
            >
        </div>

        <!-- Lọc theo Phiên họp -->
        <div>
            <select name="meeting_id" class="stu-form-control" style="height: 38px;">
                <option value="ALL">-- Tất cả các phiên họp --</option>
                @foreach($meetings as $m)
                    <option value="{{ $m->MaPhienHop }}" {{ (request('meeting_id') == $m->MaPhienHop) ? 'selected' : '' }}>
                        #{{ $m->MaPhienHop }} - {{ Str::limit($m->TenPhienHop, 45) }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Lọc theo trạng thái xử lý -->
        <div>
            <select name="status" class="stu-form-control" style="height: 38px;">
                <option value="ALL">-- Tất cả trạng thái --</option>
                <option value="Chưa xem xét" {{ request('status') == 'Chưa xem xét' ? 'selected' : '' }}>Chưa xem xét</option>
                <option value="Đang xem xét" {{ request('status') == 'Đang xem xét' ? 'selected' : '' }}>Đang xem xét</option>
                <option value="Đã thông qua" {{ request('status') == 'Đã thông qua' ? 'selected' : '' }}>Đã thông qua</option>
                <option value="Hoãn lại" {{ request('status') == 'Hoãn lại' ? 'selected' : '' }}>Hoãn lại</option>
            </select>
        </div>

        <!-- Nút Lọc -->
        <div>
            <button type="submit" class="stu-btn stu-btn-primary" style="height: 38px; padding: 0 18px; font-weight: 600;">
                <i class="bi bi-funnel"></i>
                <span>Lọc</span>
            </button>
        </div>

        <!-- Nút Xóa bộ lọc -->
        <div>
            @if(request()->anyFilled(['search', 'meeting_id', 'status']))
                <a href="{{ route('agendas.index') }}" class="stu-btn stu-btn-ghost" style="height: 38px; padding: 0 14px;" title="Xóa bộ lọc">
                    <i class="bi bi-arrow-counterclockwise"></i>
                    <span>Xóa</span>
                </a>
            @endif
        </div>
    </form>
</div>

<!-- BẢNG DỮ LIỆU CHƯƠNG TRÌNH HỌP -->
<x-bang-du-lieu 
    :headers="['Thứ tự', 'Phiên họp', 'Hồ sơ trình xem xét', 'Đơn vị trình', 'Trạng thái', 'Ghi chú']"
    :hasActions="true"
    actionTitle="Thao tác"
    :empty="$agendas->isEmpty()"
    emptyTitle="Chưa có mục chương trình họp nào"
    emptyMessage="Không tìm thấy mục chương trình nghị sự nào phù hợp với điều kiện tìm kiếm và bộ lọc."
>
    @foreach($agendas as $agenda)
        @php 
            $dossier = $agenda->hoSoTrinh;
            $meeting = $agenda->phienHop;
        @endphp
        <tr>
            <td style="font-weight: 700; color: var(--color-primary); text-align: center; width: 65px;">
                <span class="stu-badge stu-badge-primary" style="font-size: 12px; padding: 4px 8px;">
                    #{{ $agenda->ThuTu }}
                </span>
            </td>
            <td style="max-width: 200px;">
                @if($meeting)
                    <a href="{{ route('meetings.show', $meeting->MaPhienHop) }}" style="font-weight: 600; color: var(--color-primary); display: block;" title="{{ $meeting->TenPhienHop }}">
                        {{ Str::limit($meeting->TenPhienHop, 40) }}
                    </a>
                    <div style="font-size: 11px; color: var(--color-text-muted); margin-top: 2px;">
                        <i class="bi bi-calendar3"></i> {{ $meeting->ThoiGianBatDau ? date('d/m/Y', strtotime($meeting->ThoiGianBatDau)) : 'Chưa định ngày' }}
                        &bull; {{ $meeting->HinhThuc }}
                    </div>
                @else
                    <span style="color: var(--color-text-muted);">Phiên họp #{{ $agenda->MaPhienHop }}</span>
                @endif
            </td>
            <td class="stu-max-w-xs">
                @if($dossier)
                    <a href="{{ route('submissions.show', $dossier->MaHoSo) }}" style="font-weight: 600; color: #1E293B; display: block;" title="{{ $dossier->TieuDe }}">
                        {{ $dossier->TieuDe }}
                    </a>
                    <div style="display: flex; gap: 6px; align-items: center; margin-top: 4px;">
                        <span style="font-size: 11px; color: var(--color-text-muted);">Mã: {{ $dossier->ma_ho_so_hien_thi }}</span>
                        @if($dossier->LinhVuc)
                            <span class="stu-badge stu-badge-info" style="font-size: 10px; padding: 1px 6px;">{{ $dossier->LinhVuc }}</span>
                        @endif
                        @if($agenda->noiDungBieuQuyet && $agenda->noiDungBieuQuyet->count() > 0)
                            <span class="stu-badge stu-badge-success" style="font-size: 10px; padding: 1px 6px;">
                                <i class="bi bi-check2-square"></i> {{ $agenda->noiDungBieuQuyet->count() }} biểu quyết
                            </span>
                        @endif
                    </div>
                @else
                    <span style="color: var(--color-text-muted);">Hồ sơ #{{ $agenda->MaHoSo }}</span>
                @endif
            </td>
            <td style="font-size: var(--font-size-xs); white-space: nowrap;">
                <div style="font-weight: 500;">{{ $dossier?->donVi?->TenDonVi ?: '-' }}</div>
                <div style="font-size: 11px; color: var(--color-text-muted);">{{ $dossier?->nguoiDung?->HoTen ?: '' }}</div>
            </td>
            <td style="white-space: nowrap;">
                <x-nhan-trang-thai :status="$agenda->TrangThai" />
            </td>
            <td style="font-size: var(--font-size-xs); color: var(--color-text-muted); max-width: 160px;">
                {{ $agenda->GhiChu ?: '-' }}
            </td>
            <td class="col-actions">
                <div class="stu-table-actions-group">
                    @if(in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN']))
                        <button type="button" class="stu-btn stu-btn-secondary stu-btn-sm" data-modal-open="editAgendaModal-{{ $agenda->MaChuongTrinh }}" title="Cập nhật thứ tự, trạng thái và ghi chú chương trình họp">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            <span>Cập nhật</span>
                        </button>
                    @endif
                    @if($meeting)
                        <a href="{{ route('meetings.show', $meeting->MaPhienHop) }}" class="stu-btn stu-btn-outline stu-btn-sm" title="Xem chi tiết phiên họp">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <span>Phiên họp</span>
                        </a>
                    @endif
                    @if($dossier)
                        <a href="{{ route('submissions.show', $dossier->MaHoSo) }}" class="stu-btn stu-btn-outline stu-btn-sm" title="Xem hồ sơ trình">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>Hồ sơ</span>
                        </a>
                    @endif
                    @if(in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN']))
                        <form method="POST" action="{{ route('agendas.destroy', $agenda->MaChuongTrinh) }}" onsubmit="return confirm('Bạn có chắc chắn muốn xóa mục chương trình họp này khỏi giao diện?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="stu-btn stu-btn-danger stu-btn-sm" title="Ẩn mục chương trình họp (Soft Delete)">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                <span>Xóa</span>
                            </button>
                        </form>
                    @endif
                </div>
            </td>
        </tr>
    @endforeach
</x-bang-du-lieu>

<div class="stu-mt-6" style="width: 100%;">
        {{ $agendas->links() }}
    </div>

@if(in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN']))
    {{-- Modal Thêm Mục Chương trình Họp Mới --}}
    <x-hop-thoai id="createAgendaModal" title="Đưa Hồ Sơ Trình Vào Chương Trình Họp (BR-08)" size="md">
        <form method="POST" action="{{ route('agendas.store') }}">
            @csrf
            <div class="stu-form-group" style="margin-bottom: 14px;">
                <label class="stu-form-label" style="font-weight: 600; margin-bottom: 6px; display: block;">
                    Phiên họp Hội đồng trường <span style="color: #dc2626;">*</span>
                </label>
                <select name="MaPhienHop" required class="stu-form-control">
                    <option value="">-- Chọn phiên họp --</option>
                    @foreach($meetings as $m)
                        <option value="{{ $m->MaPhienHop }}" {{ ($selectedMeeting && $selectedMeeting->MaPhienHop == $m->MaPhienHop) ? 'selected' : '' }}>
                            {{ $m->TenPhienHop }} ({{ $m->ThoiGianBatDau ? date('d/m/Y', strtotime($m->ThoiGianBatDau)) : '' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="stu-form-group" style="margin-bottom: 14px;">
                <label class="stu-form-label" style="font-weight: 600; margin-bottom: 6px; display: block;">
                    Hồ sơ trình <span style="color: #dc2626;">*</span>
                </label>
                <select name="MaHoSo" required class="stu-form-control">
                    <option value="">-- Chọn hồ sơ trình --</option>
                    @foreach($availableDossiers ?? [] as $d)
                        <option value="{{ $d->MaHoSo }}">
                            [{{ $d->ma_ho_so_hien_thi }}] {{ $d->TieuDe }} ({{ $d->donVi?->TenDonVi }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                <div class="stu-form-group" style="margin-bottom: 0;">
                    <label class="stu-form-label" style="font-weight: 600; margin-bottom: 6px; display: block;">
                        Thứ tự xem xét (Tự động nếu để trống)
                    </label>
                    <input type="number" name="ThuTu" min="1" class="stu-form-control" placeholder="Ví dụ: 1, 2, 3...">
                </div>

                <div class="stu-form-group" style="margin-bottom: 0;">
                    <label class="stu-form-label" style="font-weight: 600; margin-bottom: 6px; display: block;">
                        Trạng thái xử lý
                    </label>
                    <select name="TrangThai" class="stu-form-control">
                        <option value="Chưa xem xét" selected>Chưa xem xét</option>
                        <option value="Đang thảo luận">Đang thảo luận</option>
                        <option value="Đã thông qua">Đã thông qua</option>
                        <option value="Tạm hoãn">Tạm hoãn</option>
                    </select>
                </div>
            </div>

            <div class="stu-form-group" style="margin-bottom: 20px;">
                <label class="stu-form-label" style="font-weight: 600; margin-bottom: 6px; display: block;">
                    Ghi chú kịch bản / Thời lượng dự kiến
                </label>
                <textarea name="GhiChu" rows="2" class="stu-form-control" placeholder="Ví dụ: Báo cáo 15 phút, thảo luận 20 phút..."></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; padding-top: 16px; border-top: 1px solid var(--color-border);">
                <button type="button" class="stu-btn stu-btn-secondary" data-modal-close>Hủy</button>
                <button type="submit" class="stu-btn stu-btn-primary">Đưa vào chương trình</button>
            </div>
        </form>
    </x-hop-thoai>

    {{-- Danh sách Modal Cập nhật từng Mục Chương trình Họp --}}
    @foreach($agendas as $agenda)
        <x-hop-thoai id="editAgendaModal-{{ $agenda->MaChuongTrinh }}" title="Cập Nhật Mục Chương Trình Họp #{{ $agenda->ThuTu }}" size="md">
            <form method="POST" action="{{ route('agendas.update', $agenda->MaChuongTrinh) }}">
                @csrf
                @method('PUT')

                <div style="background: var(--color-bg-subtle); padding: 12px 14px; border-radius: 8px; margin-bottom: 16px; font-size: 13px;">
                    <div style="color: var(--color-text-muted); margin-bottom: 4px;">
                        <strong>Phiên họp:</strong> {{ $agenda->phienHop?->TenPhienHop ?? ('#' . $agenda->MaPhienHop) }}
                    </div>
                    <div style="color: var(--color-primary); font-weight: 700;">
                        <strong>Hồ sơ trình:</strong> {{ $agenda->hoSoTrinh?->TieuDe ?? ('Hồ sơ #' . $agenda->MaHoSo) }}
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                    <div class="stu-form-group" style="margin-bottom: 0;">
                        <label class="stu-form-label" style="font-weight: 600; margin-bottom: 6px; display: block;">
                            Thứ tự xem xét <span style="color: #dc2626;">*</span>
                        </label>
                        <input type="number" name="ThuTu" min="1" required value="{{ $agenda->ThuTu }}" class="stu-form-control">
                    </div>

                    <div class="stu-form-group" style="margin-bottom: 0;">
                        <label class="stu-form-label" style="font-weight: 600; margin-bottom: 6px; display: block;">
                            Trạng thái xử lý <span style="color: #dc2626;">*</span>
                        </label>
                        <select name="TrangThai" required class="stu-form-control">
                            @foreach(['Chưa xem xét', 'Đang thảo luận', 'Đã thông qua', 'Tạm hoãn'] as $st)
                                <option value="{{ $st }}" {{ $agenda->TrangThai === $st ? 'selected' : '' }}>{{ $st }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="stu-form-group" style="margin-bottom: 20px;">
                    <label class="stu-form-label" style="font-weight: 600; margin-bottom: 6px; display: block;">
                        Ghi chú điều hành / Kết luận thảo luận
                    </label>
                    <textarea name="GhiChu" rows="3" class="stu-form-control" placeholder="Cập nhật ghi chú điều hành, thời lượng hoặc tóm tắt ý kiến thảo luận...">{{ $agenda->GhiChu }}</textarea>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px; padding-top: 16px; border-top: 1px solid var(--color-border);">
                    <button type="button" class="stu-btn stu-btn-secondary" data-modal-close>Đóng</button>
                    <button type="submit" class="stu-btn stu-btn-primary">Lưu cập nhật</button>
                </div>
            </form>
        </x-hop-thoai>
    @endforeach
@endif

@endsection
