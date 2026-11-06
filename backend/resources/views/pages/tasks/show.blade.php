@extends('layouts.app')

@section('title', 'Chi tiết Nhiệm vụ #' . $task->MaNhiemVu . ' - Hội đồng trường STU')

@section('content')
<div class="page-container">
    {{-- Header & Breadcrumb --}}
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <x-breadcrumb :items="[
                ['label' => 'Bảng điều hành', 'url' => route('dashboard')],
                ['label' => 'Quản lý Nhiệm vụ', 'url' => route('tasks.index')],
                ['label' => '#' . $task->MaNhiemVu, 'url' => null]
            ]" />
            <div class="d-flex align-items-center gap-3 mt-2">
                <h1 class="page-title mb-0">{{ $task->TenNhiemVu }}</h1>
                @php
                    $isOverdue = $task->TrangThai !== 'COMPLETED' && $task->NgayHoanThanh && \Carbon\Carbon::parse($task->NgayHoanThanh)->isPast();
                @endphp
                @if($task->TrangThai === 'COMPLETED' || $task->TrangThai === 'Hoàn tất')
                    <span class="badge bg-success-subtle text-success fs-6 border border-success-subtle px-3 py-2">
                        <i class="bi bi-check-circle-fill me-1"></i> Đã hoàn thành
                    </span>
                @elseif($isOverdue || $task->TrangThai === 'OVERDUE')
                    <span class="badge bg-danger-subtle text-danger fs-6 border border-danger-subtle px-3 py-2">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Quá hạn
                    </span>
                @elseif($task->TrangThai === 'IN_PROGRESS' || $task->TrangThai === 'Đang thực hiện')
                    <span class="badge bg-warning-subtle text-warning fs-6 border border-warning-subtle px-3 py-2">
                        <i class="bi bi-arrow-repeat me-1"></i> Đang thực hiện
                    </span>
                @else
                    <span class="badge bg-secondary-subtle text-secondary fs-6 border px-3 py-2">
                        <i class="bi bi-hourglass-split me-1"></i> Mới giao
                    </span>
                @endif
            </div>
        </div>

        <div class="d-flex gap-2">
            {{-- Nút nộp báo cáo tiến độ --}}
            <a href="{{ route('task-reports.create', ['task_id' => $task->MaNhiemVu]) }}" class="btn btn-primary">
                <i class="bi bi-journal-plus me-1"></i> Nộp báo cáo tiến độ
            </a>

            @if(Auth::user() && in_array(Auth::user()->role, ['SECRETARY', 'CHAIR', 'ADMIN']))
                <a href="{{ route('tasks.edit', $task->MaNhiemVu) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-pencil me-1"></i> Chỉnh sửa
                </a>
            @endif
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4 mb-4">
        {{-- Cột trái: Thông tin tổng quan & Tiến độ --}}
        <div class="col-lg-8">
            {{-- Card Tiến độ thực hiện --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="fw-bold text-dark mb-0">
                            <i class="bi bi-speedometer2 text-primary me-2"></i>Tiến độ thực hiện hiện tại
                        </h5>
                        <span class="fs-4 fw-bold {{ $currentProgress >= 100 ? 'text-success' : 'text-primary' }}">
                            {{ $currentProgress }}%
                        </span>
                    </div>

                    <div class="progress" style="height: 14px;">
                        <div class="progress-bar progress-bar-striped {{ $currentProgress >= 100 ? 'bg-success' : 'bg-primary' }}" 
                             role="progressbar" style="width: {{ $currentProgress }}%;" 
                             aria-valuenow="{{ $currentProgress }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>

                    <div class="d-flex justify-content-between small text-muted mt-2">
                        <span>0% (Khởi đầu)</span>
                        <span>50% (Đang triển khai)</span>
                        <span>100% (Nghiệm thu / Hoàn tất)</span>
                    </div>
                </div>
            </div>

            {{-- Card Chi tiết nhiệm vụ --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-dark mb-3 border-bottom pb-2">
                        <i class="bi bi-info-circle text-primary me-2"></i>Mô tả &amp; Yêu cầu nhiệm vụ
                    </h5>

                    <div class="text-secondary mb-4" style="line-height: 1.7; white-space: pre-line;">
                        {{ $task->NoiDung ?: 'Chưa có mô tả chi tiết cho nhiệm vụ này.' }}
                    </div>

                    <div class="row g-3 p-3 bg-light rounded">
                        <div class="col-md-6">
                            <div class="small text-muted">Nghị quyết căn cứ:</div>
                            @if($task->nghiQuyet)
                                <a href="{{ route('resolutions.show', $task->nghiQuyet->MaNghiQuyet) }}" class="fw-bold text-primary text-decoration-none">
                                    <i class="bi bi-file-earmark-check me-1"></i>{{ $task->nghiQuyet->SoNghiQuyet }}
                                </a>
                                <div class="small text-muted mt-1">{{ $task->nghiQuyet->TrichYeu }}</div>
                            @else
                                <span class="text-muted">Không xác định</span>
                            @endif
                        </div>
                        <div class="col-md-3">
                            <div class="small text-muted">Ngày giao nhiệm vụ:</div>
                            <div class="fw-semibold text-dark">
                                {{ $task->NgayGiao ? \Carbon\Carbon::parse($task->NgayGiao)->format('d/m/Y') : '--' }}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="small text-muted">Hạn hoàn thành:</div>
                            <div class="fw-semibold {{ $isOverdue ? 'text-danger' : 'text-dark' }}">
                                {{ $task->NgayHoanThanh ? \Carbon\Carbon::parse($task->NgayHoanThanh)->format('d/m/Y') : '--' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Danh sách Lịch sử Báo cáo Tiến độ --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold text-dark mb-0">
                        <i class="bi bi-clock-history text-primary me-2"></i>Nhật ký Báo cáo Tiến độ
                    </h5>
                    <a href="{{ route('task-reports.create', ['task_id' => $task->MaNhiemVu]) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-plus-lg me-1"></i> Nộp báo cáo mới
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Thời gian</th>
                                <th>Đơn vị / Người báo cáo</th>
                                <th style="width: 100px;">Tiến độ</th>
                                <th>Nội dung &amp; Minh chứng</th>
                                <th style="width: 140px;">Thẩm định</th>
                                <th style="width: 110px;" class="text-end">Chi tiết</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($allReports->sortByDesc('MaBaoCao') as $rpt)
                                <tr>
                                    <td class="small">
                                        <div class="fw-bold">{{ $rpt->NgayBaoCao ? \Carbon\Carbon::parse($rpt->NgayBaoCao)->format('d/m/Y') : '--' }}</div>
                                        <span class="text-muted">#BC-{{ $rpt->MaBaoCao }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $rpt->phanCongNhiemVu?->donVi?->TenDonVi ?? 'Đơn vị' }}</div>
                                        <div class="small text-muted">{{ $rpt->nguoiDung?->HoTen ?? 'Cán bộ phụ trách' }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-bold {{ $rpt->TienDo >= 100 ? 'text-success' : 'text-primary' }}">
                                            {{ $rpt->TienDo }}%
                                        </div>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar {{ $rpt->TienDo >= 100 ? 'bg-success' : 'bg-primary' }}" style="width: {{ $rpt->TienDo }}%;"></div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small text-dark">{{ Str::limit($rpt->NoiDung, 60) }}</div>
                                        @if($rpt->NoiDungKhoKhan)
                                            <div class="small text-danger mt-1">
                                                <i class="bi bi-exclamation-circle me-1"></i>Khó khăn: {{ Str::limit($rpt->NoiDungKhoKhan, 40) }}
                                            </div>
                                        @endif
                                        @if($rpt->taiLieu->isNotEmpty())
                                            <div class="mt-1">
                                                @foreach($rpt->taiLieu as $doc)
                                                    <a href="{{ asset($doc->DuongDan) }}" target="_blank" class="badge bg-light text-secondary border text-decoration-none">
                                                        <i class="bi bi-paperclip me-1"></i>{{ Str::limit($doc->TenTaiLieu, 20) }}
                                                    </a>
                                                @endforeach
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
                                                <i class="bi bi-x-circle me-1"></i> Yêu cầu sửa
                                            </span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                                <i class="bi bi-hourglass-split me-1"></i> Chờ duyệt
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('task-reports.show', $rpt->MaBaoCao) }}" class="btn btn-sm btn-outline-primary">
                                            Xem
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <i class="bi bi-journal-x display-6 d-block mb-2 text-muted"></i>
                                        Chưa có báo cáo tiến độ nào được nộp cho nhiệm vụ này.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Cột phải: Đơn vị phụ trách & Phân công (BR-10) --}}
        <div class="col-lg-4">
            {{-- Đơn vị Chủ trì --}}
            @php
                $leadAssignment = $task->phanCongNhiemVu->firstWhere('VaiTroThucHien', 'CHỦ TRÌ');
                $coopAssignments = $task->phanCongNhiemVu->where('VaiTroThucHien', 'PHỐI HỢP');
            @endphp
            <div class="card border-0 shadow-sm mb-4 border-start border-primary border-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-uppercase text-primary small mb-3">
                        <i class="bi bi-award me-1"></i> Đơn vị Chủ trì (BR-10)
                    </h6>

                    @if($leadAssignment && $leadAssignment->donVi)
                        <h5 class="fw-bold text-dark mb-2">{{ $leadAssignment->donVi->TenDonVi }}</h5>
                        <div class="small text-muted mb-2">
                            <i class="bi bi-person-fill text-primary me-1"></i>
                            <strong>Người phụ trách:</strong> {{ $leadAssignment->TenNguoiPhuTrach ?: 'Chưa cập nhật' }}
                        </div>
                        <div class="small text-muted">
                            <i class="bi bi-flag-fill text-secondary me-1"></i>
                            <strong>Trạng thái thực hiện:</strong> 
                            <span class="badge bg-light text-dark border">{{ $leadAssignment->TrangThaiThucHien }}</span>
                        </div>
                    @else
                        <div class="text-muted small fst-italic">Chưa có thông tin đơn vị chủ trì.</div>
                    @endif
                </div>
            </div>

            {{-- Đơn vị Phối hợp --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-uppercase text-secondary small mb-3">
                        <i class="bi bi-people me-1"></i> Đơn vị Phối hợp
                    </h6>

                    @if($coopAssignments->isNotEmpty())
                        <ul class="list-group list-group-flush small">
                            @foreach($coopAssignments as $coop)
                                <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-semibold text-dark">{{ $coop->donVi?->TenDonVi ?? 'Đơn vị phối hợp' }}</div>
                                        <div class="text-muted small">{{ $coop->TenNguoiPhuTrach ?: 'Cán bộ liên lạc' }}</div>
                                    </div>
                                    <span class="badge bg-light text-secondary border">{{ $coop->TrangThaiThucHien }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-muted small fst-italic">Không có đơn vị phối hợp.</div>
                    @endif
                </div>
            </div>

            {{-- Hộp hỗ trợ nghiệp vụ --}}
            <div class="card border-0 bg-primary-subtle text-primary shadow-sm">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-shield-check fs-5 me-2"></i>
                        <h6 class="mb-0 fw-bold">Quy định BR-10</h6>
                    </div>
                    <p class="small mb-0">
                        Nhiệm vụ chỉ được xác nhận hoàn thành (COMPLETED) khi báo cáo tiến độ đạt 100% và được Thư ký hoặc Chủ tịch Hội đồng trường thẩm định phê duyệt.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
