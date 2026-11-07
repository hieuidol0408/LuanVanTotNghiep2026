@extends('layouts.app')

@section('title', 'Phân công & Theo dõi Nhiệm vụ - Hội đồng trường STU')

@section('content')
<div class="stu-page-container">
    {{-- Header & Breadcrumb --}}
    <x-breadcrumb :items="['Quản lý Nhiệm vụ' => null]" />

    <div class="stu-page-header stu-flex stu-items-center stu-justify-between stu-mb-6">
        <div>
            <h1 class="stu-page-title" style="font-size: 24px; font-weight: 800; color: var(--color-primary); margin: 0;">
                Theo dõi &amp; Phân công Nhiệm vụ
            </h1>
            <p style="font-size: 13px; color: var(--color-text-muted); margin: 4px 0 0 0;">
                Phân rã nhiệm vụ từ các Nghị quyết Hội đồng trường đã ban hành và đôn đốc tiến độ thực hiện
            </p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('task-reports.index') }}" class="stu-btn stu-btn-ghost" style="height: 38px;">
                <i class="bi bi-journal-check"></i>
                <span>Báo cáo tiến độ</span>
            </a>
            @if(Auth::user() && Auth::user()->role !== 'EXECUTING_UNIT')
                <a href="{{ route('tasks.create') }}" class="stu-btn stu-btn-primary" style="height: 38px;">
                    <i class="bi bi-plus-circle"></i>
                    <span>Giao nhiệm vụ mới</span>
                </a>
            @endif
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="stu-grid stu-grid-cols-4 stu-mb-6" style="gap: 16px;">
        <div class="stu-card" style="padding: 18px; border-left: 4px solid var(--color-primary);">
            <div style="font-size: 12px; font-weight: 600; color: var(--color-text-muted); text-transform: uppercase;">Tổng số nhiệm vụ</div>
            <div style="font-size: 26px; font-weight: 800; color: var(--color-primary); margin-top: 4px;">{{ $totalTasks }}</div>
        </div>
        <div class="stu-card" style="padding: 18px; border-left: 4px solid var(--color-warning);">
            <div style="font-size: 12px; font-weight: 600; color: var(--color-text-muted); text-transform: uppercase;">Đang thực hiện</div>
            <div style="font-size: 26px; font-weight: 800; color: var(--color-warning); margin-top: 4px;">{{ $inProgressTasks }}</div>
        </div>
        <div class="stu-card" style="padding: 18px; border-left: 4px solid var(--color-success);">
            <div style="font-size: 12px; font-weight: 600; color: var(--color-text-muted); text-transform: uppercase;">Đã hoàn thành</div>
            <div style="font-size: 26px; font-weight: 800; color: var(--color-success); margin-top: 4px;">{{ $completedTasks }}</div>
        </div>
        <div class="stu-card" style="padding: 18px; border-left: 4px solid var(--color-danger);">
            <div style="font-size: 12px; font-weight: 600; color: var(--color-text-muted); text-transform: uppercase;">Quá hạn thực hiện</div>
            <div style="font-size: 26px; font-weight: 800; color: var(--color-danger); margin-top: 4px;">{{ $overdueTasks }}</div>
        </div>
    </div>

    {{-- Bộ lọc & Tìm kiếm --}}
    <div class="stu-card stu-mb-6" style="padding: 20px 24px; border-radius: 12px; border: 1px solid var(--color-border); box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05); background: #ffffff;">
        <form method="GET" action="{{ route('tasks.index') }}" class="stu-grid" style="grid-template-columns: 2fr 1.4fr 2fr auto auto; gap: 14px; align-items: flex-end;">
            <div class="stu-form-group" style="margin-bottom: 0;">
                <label class="stu-form-label">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <span>Tìm kiếm nhiệm vụ</span>
                </label>
                <div style="position: relative;">
                    <svg width="15" height="15" fill="none" stroke="#94A3B8" stroke-width="2" viewBox="0 0 24 24" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); pointer-events: none;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <input type="text" name="search" value="{{ request('search') }}" class="stu-form-control" style="padding-left: 36px;" placeholder="Tên hoặc nội dung nhiệm vụ...">
                </div>
            </div>
            <div class="stu-form-group" style="margin-bottom: 0;">
                <label class="stu-form-label">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Trạng thái</span>
                </label>
                <select name="status" class="stu-form-control">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="ASSIGNED" {{ request('status') === 'ASSIGNED' ? 'selected' : '' }}>Chưa thực hiện / Mới giao</option>
                    <option value="IN_PROGRESS" {{ request('status') === 'IN_PROGRESS' ? 'selected' : '' }}>Đang thực hiện</option>
                    <option value="COMPLETED" {{ request('status') === 'COMPLETED' ? 'selected' : '' }}>Đã hoàn thành</option>
                    <option value="OVERDUE" {{ request('status') === 'OVERDUE' ? 'selected' : '' }}>Quá hạn</option>
                </select>
            </div>
            <div class="stu-form-group" style="margin-bottom: 0;">
                <label class="stu-form-label">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Nghị quyết căn cứ</span>
                </label>
                <select name="resolution_id" class="stu-form-control">
                    <option value="">-- Tất cả Nghị quyết --</option>
                    @foreach($resolutions as $res)
                        <option value="{{ $res->MaNghiQuyet }}" {{ request('resolution_id') == $res->MaNghiQuyet ? 'selected' : '' }}>
                            {{ $res->SoNghiQuyet }} - {{ Str::limit($res->TrichYeu, 35) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <button type="submit" class="stu-btn stu-btn-primary" style="height: 38px; padding: 0 16px; font-weight: 600; box-shadow: 0 1px 2px 0 rgba(0, 58, 140, 0.2);">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span>Lọc</span>
                </button>
            </div>
            <div>
                <a href="{{ route('tasks.index') }}" class="stu-btn stu-btn-ghost" style="height: 38px; padding: 0 14px;" title="Xóa bộ lọc về mặc định">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Xóa</span>
                </a>
            </div>
        </form>
    </div>

    {{-- Bảng danh sách Nhiệm vụ --}}
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <x-table>
                <x-slot:thead>
                    <tr>
                        <th style="width: 60px;">Mã</th>
                        <th>Nhiệm vụ &amp; Nghị quyết</th>
                        <th>Đơn vị Chủ trì &amp; Phối hợp</th>
                        <th>Thời hạn</th>
                        <th style="width: 140px;">Trạng thái</th>
                        <th style="width: 130px;" class="text-end">Thao tác</th>
                    </tr>
                </x-slot:thead>

                @forelse($tasks as $t)
                    @php
                        $lead = $t->phanCongNhiemVu->firstWhere('VaiTroThucHien', 'CHỦ TRÌ');
                        $coops = $t->phanCongNhiemVu->where('VaiTroThucHien', 'PHỐI HỢP');
                        $isOverdue = $t->TrangThai !== 'COMPLETED' && $t->NgayHoanThanh && \Carbon\Carbon::parse($t->NgayHoanThanh)->isPast();
                    @endphp
                    <tr>
                        <td class="text-muted fw-semibold">#{{ $t->MaNhiemVu }}</td>
                        <td>
                            <div class="fw-bold text-dark">
                                <a href="{{ route('tasks.show', $t->MaNhiemVu) }}" class="text-decoration-none text-dark hover-primary">
                                    {{ $t->TenNhiemVu }}
                                </a>
                            </div>
                            @if($t->nghiQuyet)
                                <div class="small text-muted mt-1">
                                    <i class="bi bi-file-earmark-check me-1 text-primary"></i>
                                    <a href="{{ route('resolutions.show', $t->nghiQuyet->MaNghiQuyet) }}" class="text-decoration-none text-secondary">
                                        {{ $t->nghiQuyet->SoNghiQuyet }}
                                    </a>
                                </div>
                            @endif
                        </td>
                        <td>
                            @if($lead && $lead->donVi)
                                <div>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                        Chủ trì: {{ $lead->donVi->TenDonVi }}
                                    </span>
                                </div>
                                <div class="small text-muted mt-1">
                                    <i class="bi bi-person me-1"></i>{{ $lead->TenNguoiPhuTrach ?: 'Chưa ghi nhận' }}
                                </div>
                            @else
                                <span class="text-muted fst-italic">Chưa chỉ định chủ trì</span>
                            @endif

                            @if($coops->isNotEmpty())
                                <div class="mt-1 small text-muted">
                                    <i class="bi bi-people me-1"></i>Phối hợp: {{ $coops->pluck('donVi.TenDonVi')->filter()->implode(', ') }}
                                </div>
                            @endif
                        </td>
                        <td>
                            <div class="small">
                                <span class="text-muted">Giao:</span> {{ $t->NgayGiao ? \Carbon\Carbon::parse($t->NgayGiao)->format('d/m/Y') : '--' }}
                            </div>
                            <div class="small {{ $isOverdue ? 'text-danger fw-bold' : 'text-dark' }}">
                                <span class="text-muted">Hạn:</span> {{ $t->NgayHoanThanh ? \Carbon\Carbon::parse($t->NgayHoanThanh)->format('d/m/Y') : '--' }}
                                @if($isOverdue)
                                    <i class="bi bi-exclamation-circle-fill text-danger ms-1" title="Đã quá hạn hoàn thành"></i>
                                @endif
                            </div>
                        </td>
                        <td>
                            @if($t->TrangThai === 'COMPLETED' || $t->TrangThai === 'Hoàn tất')
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    <i class="bi bi-check-circle me-1"></i> Hoàn tất
                                </span>
                            @elseif($isOverdue || $t->TrangThai === 'OVERDUE')
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                    <i class="bi bi-clock-history me-1"></i> Quá hạn
                                </span>
                            @elseif($t->TrangThai === 'IN_PROGRESS' || $t->TrangThai === 'Đang thực hiện')
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                    <i class="bi bi-arrow-repeat me-1"></i> Đang thực hiện
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border">
                                    <i class="bi bi-hourglass-split me-1"></i> Mới giao
                                </span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="btn-group">
                                <a href="{{ route('tasks.show', $t->MaNhiemVu) }}" class="btn btn-sm btn-outline-primary" title="Xem chi tiết & Tiến độ">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if(Auth::user() && in_array(Auth::user()->role, ['SECRETARY', 'CHAIR', 'ADMIN']))
                                    <a href="{{ route('tasks.edit', $t->MaNhiemVu) }}" class="btn btn-sm btn-outline-secondary" title="Chỉnh sửa">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-clipboard-x display-6 d-block mb-3 text-muted"></i>
                            Không tìm thấy nhiệm vụ nào phù hợp với điều kiện tìm kiếm.
                        </td>
                    </tr>
                @endforelse
            </x-table>
        </div>

        @if($tasks->hasPages())
            <div class="card-footer bg-white border-top-0 py-3">
                {{ $tasks->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
