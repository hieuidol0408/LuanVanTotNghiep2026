@extends('layouts.app')

@section('title', 'Danh sách Báo cáo Tiến độ - Hội đồng trường STU')

@section('content')
<div class="page-container">
    {{-- Header & Breadcrumb --}}
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <x-breadcrumb :items="[
                ['label' => 'Bảng điều hành', 'url' => route('dashboard')],
                ['label' => 'Quản lý Nhiệm vụ', 'url' => route('tasks.index')],
                ['label' => 'Báo cáo Tiến độ', 'url' => null]
            ]" />
            <h1 class="page-title mt-2">Báo cáo Tiến độ &amp; Đôn đốc Thực hiện</h1>
            <p class="text-muted">Theo dõi, đánh giá và thẩm định các báo cáo tiến độ từ các đơn vị thực hiện theo BR-10</p>
        </div>
        <div>
            <a href="{{ route('task-reports.create') }}" class="btn btn-primary">
                <i class="bi bi-journal-plus me-1"></i> Nộp báo cáo tiến độ
            </a>
        </div>
    </div>

    {{-- Bộ lọc & Tìm kiếm --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('task-reports.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small text-muted">Trạng thái thẩm định</label>
                    <select name="status" class="form-select">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="Chờ kiểm tra" {{ request('status') === 'Chờ kiểm tra' ? 'selected' : '' }}>Chờ kiểm tra / Chờ duyệt</option>
                        <option value="APPROVED" {{ request('status') === 'APPROVED' ? 'selected' : '' }}>Đã phê duyệt (APPROVED)</option>
                        <option value="REJECTED" {{ request('status') === 'REJECTED' ? 'selected' : '' }}>Yêu cầu bổ sung (REJECTED)</option>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label small text-muted">Đơn vị thực hiện</label>
                    <select name="department_id" class="form-select">
                        <option value="">-- Tất cả đơn vị --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->MaDonVi }}" {{ request('department_id') == $dept->MaDonVi ? 'selected' : '' }}>
                                {{ $dept->TenDonVi }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel"></i> Lọc
                    </button>
                    @if(request()->anyFilled(['status', 'department_id', 'task_id']))
                        <a href="{{ route('task-reports.index') }}" class="btn btn-outline-secondary" title="Xóa bộ lọc">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
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
