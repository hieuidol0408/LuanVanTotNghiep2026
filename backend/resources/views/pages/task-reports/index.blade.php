@extends('layouts.app')

@section('title', 'Danh sách Báo cáo Tiến độ - Hội đồng trường STU')

@section('content')
<div class="stu-page-container">
    {{-- Header & Breadcrumb --}}
    <x-breadcrumb :items="['Báo cáo Tiến độ' => null]" />

    <div class="stu-page-header stu-flex stu-items-center stu-justify-between stu-mb-6">
        <div>
            <h1 class="stu-page-title" style="font-size: 24px; font-weight: 800; color: var(--color-primary); margin: 0;">
                Báo cáo Tiến độ &amp; Đôn đốc Thực hiện
            </h1>
            <p style="font-size: 13px; color: var(--color-text-muted); margin: 4px 0 0 0;">
                Theo dõi, đánh giá và thẩm định các báo cáo tiến độ từ các đơn vị thực hiện theo BR-10
            </p>
        </div>
        <div>
            <a href="{{ route('task-reports.create') }}" class="stu-btn stu-btn-primary" style="height: 38px;">
                <i class="bi bi-journal-plus"></i>
                <span>Nộp báo cáo tiến độ</span>
            </a>
        </div>
    </div>

    {{-- Bộ lọc & Tìm kiếm --}}
    <div class="stu-card stu-mb-6" style="padding: 20px 24px; border-radius: 12px; border: 1px solid var(--color-border); box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05); background: #ffffff;">
        <form method="GET" action="{{ route('task-reports.index') }}" class="stu-grid" style="grid-template-columns: 2fr 2fr auto auto; gap: 14px; align-items: flex-end;">
            <div class="stu-form-group" style="margin-bottom: 0;">
                <label class="stu-form-label">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Trạng thái thẩm định</span>
                </label>
                <select name="status" class="stu-form-control">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="Chờ kiểm tra" {{ request('status') === 'Chờ kiểm tra' ? 'selected' : '' }}>Chờ kiểm tra / Chờ duyệt</option>
                    <option value="APPROVED" {{ request('status') === 'APPROVED' ? 'selected' : '' }}>Đã phê duyệt (APPROVED)</option>
                    <option value="REJECTED" {{ request('status') === 'REJECTED' ? 'selected' : '' }}>Yêu cầu bổ sung (REJECTED)</option>
                </select>
            </div>
            <div class="stu-form-group" style="margin-bottom: 0;">
                <label class="stu-form-label">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <span>Đơn vị thực hiện</span>
                </label>
                <select name="department_id" class="stu-form-control">
                    <option value="">-- Tất cả đơn vị --</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->MaDonVi }}" {{ request('department_id') == $dept->MaDonVi ? 'selected' : '' }}>
                            {{ $dept->TenDonVi }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <button type="submit" class="stu-btn stu-btn-primary" style="height: 38px; padding: 0 18px; font-weight: 600; box-shadow: 0 1px 2px 0 rgba(0, 58, 140, 0.2);">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span>Lọc</span>
                </button>
            </div>
            <div>
                <a href="{{ route('task-reports.index') }}" class="stu-btn stu-btn-ghost" style="height: 38px; padding: 0 14px;" title="Xóa bộ lọc về mặc định">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Xóa</span>
                </a>
            </div>
        </form>
    </div>

    {{-- Bảng danh sách Báo cáo --}}
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <x-table>
                <x-slot:thead>
                    <tr>
                        <th style="width: 80px;">Mã BC</th>
                        <th>Nhiệm vụ &amp; Nghị quyết</th>
                        <th>Đơn vị báo cáo</th>
                        <th style="width: 120px;">Tiến độ</th>
                        <th>Ngày gửi</th>
                        <th style="width: 150px;">Trạng thái</th>
                        <th style="width: 110px;" class="text-end">Thao tác</th>
                    </tr>
                </x-slot:thead>

                @forelse($reports as $rpt)
                    @php
                        $assignment = $rpt->phanCongNhiemVu;
                        $task = $assignment?->nhiemVu;
                    @endphp
                    <tr>
                        <td class="text-muted fw-semibold">#BC-{{ $rpt->MaBaoCao }}</td>
                        <td>
                            @if($task)
                                <div class="fw-bold text-dark">
                                    <a href="{{ route('tasks.show', $task->MaNhiemVu) }}" class="text-decoration-none text-dark hover-primary">
                                        {{ $task->TenNhiemVu }}
                                    </a>
                                </div>
                                @if($task->nghiQuyet)
                                    <div class="small text-muted mt-1">
                                        <i class="bi bi-file-earmark-check me-1 text-primary"></i>
                                        {{ $task->nghiQuyet->SoNghiQuyet }}
                                    </div>
                                @endif
                            @else
                                <span class="text-muted fst-italic">Nhiệm vụ không xác định</span>
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $assignment?->donVi?->TenDonVi ?? '--' }}</div>
                            <div class="small text-muted">
                                <i class="bi bi-person me-1"></i>{{ $rpt->nguoiDung?->HoTen ?? 'Cán bộ' }}
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span class="fw-bold {{ $rpt->TienDo >= 100 ? 'text-success' : 'text-primary' }}">{{ $rpt->TienDo }}%</span>
                            </div>
                            <div class="progress" style="height: 6px; width: 80px;">
                                <div class="progress-bar {{ $rpt->TienDo >= 100 ? 'bg-success' : 'bg-primary' }}" style="width: {{ $rpt->TienDo }}%;"></div>
                            </div>
                        </td>
                        <td>
                            <div class="small text-dark">{{ $rpt->NgayBaoCao ? \Carbon\Carbon::parse($rpt->NgayBaoCao)->format('d/m/Y') : '--' }}</div>
                            @if($rpt->taiLieu->isNotEmpty())
                                <div class="small text-primary mt-1">
                                    <i class="bi bi-paperclip me-1"></i>{{ $rpt->taiLieu->count() }} tệp đính kèm
                                </div>
                            @endif
                        </td>
                        <td>
                            @if($rpt->TrangThaiXacNhan === 'APPROVED')
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    <i class="bi bi-check-circle me-1"></i> Đã duyệt
                                </span>
                            @elseif($rpt->TrangThaiXacNhan === 'REJECTED')
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                    <i class="bi bi-x-circle me-1"></i> Yêu cầu bổ sung
                                </span>
                            @else
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                    <i class="bi bi-hourglass-split me-1"></i> Chờ kiểm tra
                                </span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('task-reports.show', $rpt->MaBaoCao) }}" class="btn btn-sm btn-outline-primary" title="Xem chi tiết & Thẩm định">
                                <i class="bi bi-eye"></i> Xem
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-clipboard-x display-6 d-block mb-3 text-muted"></i>
                            Không có báo cáo tiến độ nào phù hợp.
                        </td>
                    </tr>
                @endforelse
            </x-table>
        </div>

        @if($reports->hasPages())
            <div class="card-footer bg-white border-top-0 py-3">
                {{ $reports->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
