@extends('layouts.app')

@section('title', 'Phân công & Theo dõi Nhiệm vụ - Hội đồng trường STU')

@section('content')
<div class="page-container">
    {{-- Header & Breadcrumb --}}
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <x-breadcrumb :items="[
                ['label' => 'Bảng điều hành', 'url' => route('dashboard')],
                ['label' => 'Quản lý Nhiệm vụ', 'url' => null]
            ]" />
            <h1 class="page-title mt-2">Theo dõi &amp; Phân công Nhiệm vụ</h1>
            <p class="text-muted">Phân rã nhiệm vụ từ các Nghị quyết Hội đồng trường đã ban hành và đôn đốc tiến độ thực hiện</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('task-reports.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-journal-check me-1"></i> Báo cáo tiến độ
            </a>
            @if(Auth::user() && Auth::user()->role !== 'EXECUTING_UNIT')
                <a href="{{ route('tasks.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Giao nhiệm vụ mới
                </a>
            @endif
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card kpi-card shadow-sm border-0 border-start border-primary border-4">
                <div class="card-body">
                    <div class="text-muted small fw-semibold text-uppercase">Tổng số nhiệm vụ</div>
                    <div class="d-flex align-items-baseline justify-content-between mt-2">
                        <h2 class="mb-0 fw-bold text-primary">{{ $totalTasks }}</h2>
                        <span class="badge bg-primary-subtle text-primary"><i class="bi bi-list-task"></i></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card kpi-card shadow-sm border-0 border-start border-warning border-4">
                <div class="card-body">
                    <div class="text-muted small fw-semibold text-uppercase">Đang thực hiện</div>
                    <div class="d-flex align-items-baseline justify-content-between mt-2">
                        <h2 class="mb-0 fw-bold text-warning">{{ $inProgressTasks }}</h2>
                        <span class="badge bg-warning-subtle text-warning"><i class="bi bi-arrow-repeat"></i></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card kpi-card shadow-sm border-0 border-start border-success border-4">
                <div class="card-body">
                    <div class="text-muted small fw-semibold text-uppercase">Đã hoàn thành</div>
                    <div class="d-flex align-items-baseline justify-content-between mt-2">
                        <h2 class="mb-0 fw-bold text-success">{{ $completedTasks }}</h2>
                        <span class="badge bg-success-subtle text-success"><i class="bi bi-check2-circle"></i></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card kpi-card shadow-sm border-0 border-start border-danger border-4">
                <div class="card-body">
                    <div class="text-muted small fw-semibold text-uppercase">Quá hạn thực hiện</div>
                    <div class="d-flex align-items-baseline justify-content-between mt-2">
                        <h2 class="mb-0 fw-bold text-danger">{{ $overdueTasks }}</h2>
                        <span class="badge bg-danger-subtle text-danger"><i class="bi bi-exclamation-triangle"></i></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bộ lọc & Tìm kiếm --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('tasks.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small text-muted">Tìm kiếm nhiệm vụ</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Tên hoặc nội dung nhiệm vụ...">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="ASSIGNED" {{ request('status') === 'ASSIGNED' ? 'selected' : '' }}>Chưa thực hiện / Mới giao</option>
                        <option value="IN_PROGRESS" {{ request('status') === 'IN_PROGRESS' ? 'selected' : '' }}>Đang thực hiện</option>
                        <option value="COMPLETED" {{ request('status') === 'COMPLETED' ? 'selected' : '' }}>Đã hoàn thành</option>
                        <option value="OVERDUE" {{ request('status') === 'OVERDUE' ? 'selected' : '' }}>Quá hạn</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Nghị quyết căn cứ</label>
                    <select name="resolution_id" class="form-select">
                        <option value="">-- Tất cả Nghị quyết --</option>
                        @foreach($resolutions as $res)
                            <option value="{{ $res->MaNghiQuyet }}" {{ request('resolution_id') == $res->MaNghiQuyet ? 'selected' : '' }}>
                                {{ $res->SoNghiQuyet }} - {{ Str::limit($res->TrichYeu, 35) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel"></i> Lọc
                    </button>
                    @if(request()->anyFilled(['search', 'status', 'resolution_id', 'department_id']))
                        <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary" title="Xóa bộ lọc">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
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
