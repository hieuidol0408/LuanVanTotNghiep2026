@extends('layouts.chinh')

@section('title', 'Chi tiết Báo cáo Tiến độ #BC-' . $report->MaBaoCao . ' - Hội đồng trường STU')

@section('content')
<div class="page-container">
    {{-- Header & Breadcrumb --}}
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <x-thanh-dieu-huong :items="[
                ['label' => 'Bảng điều hành', 'url' => route('dashboard')],
                ['label' => 'Quản lý Nhiệm vụ', 'url' => route('tasks.index')],
                ['label' => 'Báo cáo Tiến độ', 'url' => route('task-reports.index')],
                ['label' => '#BC-' . $report->MaBaoCao, 'url' => null]
            ]" />
            <div class="d-flex align-items-center gap-3 mt-2">
                <h1 class="page-title mb-0">Báo cáo Tiến độ #BC-{{ $report->MaBaoCao }}</h1>
                @if($report->TrangThaiXacNhan === 'APPROVED')
                    <span class="badge bg-success-subtle text-success fs-6 border border-success-subtle px-3 py-2">
                        <i class="bi bi-check-circle-fill me-1"></i> Đã phê duyệt (APPROVED)
                    </span>
                @elseif($report->TrangThaiXacNhan === 'REJECTED')
                    <span class="badge bg-danger-subtle text-danger fs-6 border border-danger-subtle px-3 py-2">
                        <i class="bi bi-x-circle-fill me-1"></i> Yêu cầu bổ sung (REJECTED)
                    </span>
                @else
                    <span class="badge bg-warning-subtle text-warning fs-6 border border-warning-subtle px-3 py-2">
                        <i class="bi bi-hourglass-split me-1"></i> Chờ kiểm tra
                    </span>
                @endif
            </div>
        </div>

        <div>
            @if($report->phanCongNhiemVu?->nhiemVu)
                <a href="{{ route('tasks.show', $report->phanCongNhiemVu->nhiemVu->MaNhiemVu) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Về chi tiết nhiệm vụ
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

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4 mb-4">
        {{-- Cột trái: Nội dung báo cáo & Tệp minh chứng --}}
        <div class="col-lg-8">
            {{-- Card Tiến độ --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="fw-bold text-dark mb-0">
                            <i class="bi bi-percent text-primary me-2"></i>Tiến độ báo cáo ghi nhận
                        </h5>
                        <span class="fs-3 fw-bold {{ $report->TienDo >= 100 ? 'text-success' : 'text-primary' }}">
                            {{ $report->TienDo }}%
                        </span>
                    </div>

                    <div class="progress" style="height: 12px;">
                        <div class="progress-bar {{ $report->TienDo >= 100 ? 'bg-success' : 'bg-primary' }}" 
                             role="progressbar" style="width: {{ $report->TienDo }}%;"></div>
                    </div>
                </div>
            </div>

            {{-- Card Nội dung chi tiết --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-dark mb-3 border-bottom pb-2">
                        <i class="bi bi-file-earmark-text text-primary me-2"></i>Kết quả công việc đã thực hiện
                    </h5>
                    <div class="text-secondary mb-4" style="line-height: 1.8; white-space: pre-line;">
                        {{ $report->NoiDung }}
                    </div>

                    @if($report->NoiDungKhoKhan)
                        <h6 class="fw-bold text-danger mb-2">
                            <i class="bi bi-exclamation-circle me-1"></i> Khó khăn, vướng mắc &amp; Đề xuất:
                        </h6>
                        <div class="p-3 bg-danger-subtle rounded text-danger mb-4" style="white-space: pre-line;">
                            {{ $report->NoiDungKhoKhan }}
                        </div>
                    @endif

                    {{-- Tệp minh chứng đính kèm --}}
                    <h6 class="fw-bold text-dark mb-2 border-top pt-3">
                        <i class="bi bi-paperclip text-primary me-1"></i> Tệp minh chứng đính kèm:
                    </h6>
                    @if($report->taiLieu->isNotEmpty())
                        <div class="list-group list-group-flush">
                            @foreach($report->taiLieu as $doc)
                                <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-file-earmark-arrow-down fs-4 text-primary me-3"></i>
                                        <div>
                                            <div class="fw-semibold text-dark">{{ $doc->TenTaiLieu }}</div>
                                            <div class="small text-muted">{{ $doc->DungLuong ? $doc->DungLuong . ' KB' : 'Tài liệu' }} • {{ $doc->NgayTaiLen ? \Carbon\Carbon::parse($doc->NgayTaiLen)->format('d/m/Y H:i') : '' }}</div>
                                        </div>
                                    </div>
                                    <a href="{{ asset($doc->DuongDan) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-download me-1"></i> Tải về
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="small text-muted mb-0 fst-italic">Báo cáo không đính kèm tệp văn bản bổ sung.</p>
                    @endif
                </div>
            </div>

            {{-- Thẩm định & Phê duyệt (Dành cho Thư ký, Chủ tịch Hội đồng) --}}
            @if(Auth::user() && in_array(Auth::user()->role, ['SECRETARY', 'CHAIR', 'VICE_CHAIR', 'ADMIN']))
                <div class="card border-0 shadow-sm border-top border-primary border-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold text-dark mb-3">
                            <i class="bi bi-shield-check text-primary me-2"></i>Thẩm định &amp; Phê duyệt Báo cáo Tiến độ (BR-10)
                        </h5>

                        <form method="POST" action="{{ route('task-reports.review', $report->MaBaoCao) }}">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Ý kiến thẩm định / Ghi chú phản hồi</label>
                                <textarea name="GhiChu" rows="3" class="form-control" 
                                          placeholder="Nhập nhận xét, ý kiến chỉ đạo hoặc lý do yêu cầu điều chỉnh báo cáo...">{{ old('GhiChu', $report->GhiChu) }}</textarea>
                            </div>

                            <div class="d-flex gap-2 justify-content-end">
                                <button type="submit" name="action" value="reject" class="btn btn-outline-danger px-3">
                                    <i class="bi bi-x-circle me-1"></i> Yêu cầu bổ sung
                                </button>
                                <button type="submit" name="action" value="approve" class="btn btn-success px-4">
                                    <i class="bi bi-check2-circle me-1"></i> Phê duyệt báo cáo
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </div>

        {{-- Cột phải: Thông tin liên kết --}}
        <div class="col-lg-4">
            {{-- Thông tin nhiệm vụ liên quan --}}
            @php
                $assignment = $report->phanCongNhiemVu;
                $task = $assignment?->nhiemVu;
            @endphp
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-uppercase text-secondary small mb-3">
                        <i class="bi bi-list-task me-1"></i> Nhiệm vụ liên quan
                    </h6>

                    @if($task)
                        <h6 class="fw-bold text-dark mb-2">
                            <a href="{{ route('tasks.show', $task->MaNhiemVu) }}" class="text-decoration-none text-dark hover-primary">
                                {{ $task->TenNhiemVu }}
                            </a>
                        </h6>

                        @if($task->nghiQuyet)
                            <div class="small text-muted mb-2">
                                <i class="bi bi-file-earmark-check me-1 text-primary"></i>
                                Căn cứ: {{ $task->nghiQuyet->SoNghiQuyet }}
                            </div>
                        @endif

                        <div class="small text-muted mb-2">
                            <i class="bi bi-clock me-1"></i>
                            Hạn hoàn thành: {{ $task->NgayHoanThanh ? \Carbon\Carbon::parse($task->NgayHoanThanh)->format('d/m/Y') : '--' }}
                        </div>
                    @else
                        <div class="text-muted small fst-italic">Không có thông tin nhiệm vụ.</div>
                    @endif
                </div>
            </div>

            {{-- Đơn vị và Người nộp --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-uppercase text-secondary small mb-3">
                        <i class="bi bi-building me-1"></i> Đơn vị thực hiện
                    </h6>

                    <div class="fw-bold text-dark">{{ $assignment?->donVi?->TenDonVi ?? 'Chưa xác định' }}</div>
                    <div class="small text-muted mt-1">
                        <i class="bi bi-tag me-1"></i> Vai trò: <span class="badge bg-light text-dark border">{{ $assignment?->VaiTroThucHien }}</span>
                    </div>

                    <hr class="my-3">

                    <h6 class="fw-bold text-uppercase text-secondary small mb-2">
                        <i class="bi bi-person me-1"></i> Người nộp báo cáo
                    </h6>
                    <div class="fw-semibold text-dark">{{ $report->nguoiDung?->HoTen ?? 'Cán bộ' }}</div>
                    <div class="small text-muted">{{ $report->nguoiDung?->email ?? '' }}</div>
                    <div class="small text-muted mt-1">
                        <i class="bi bi-calendar-event me-1"></i> Ngày gửi: {{ $report->NgayBaoCao ? \Carbon\Carbon::parse($report->NgayBaoCao)->format('d/m/Y') : '--' }}
                    </div>
                </div>
            </div>

            {{-- Trạng thái Thẩm định hiện tại --}}
            @if($report->NgayXacNhan)
                <div class="card border-0 shadow-sm mb-4 bg-light">
                    <div class="card-body p-4">
                        <h6 class="fw-bold text-uppercase text-secondary small mb-2">
                            <i class="bi bi-journal-check me-1"></i> Kết quả thẩm định
                        </h6>
                        <div class="small text-muted">
                            Ngày thẩm định: {{ \Carbon\Carbon::parse($report->NgayXacNhan)->format('d/m/Y') }}
                        </div>
                        @if($report->GhiChu)
                            <div class="small mt-2 p-2 bg-white rounded border">
                                <strong>Ý kiến phản hồi:</strong> {{ $report->GhiChu }}
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
