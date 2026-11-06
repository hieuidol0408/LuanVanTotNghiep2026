@extends('layouts.app')

@section('title', 'Thành viên Hội đồng trường STU - Khóa IV')

@section('content')
<div class="page-container">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <x-breadcrumb :items="[
                ['label' => 'Bảng điều hành', 'url' => route('dashboard')],
                ['label' => 'Thành viên Hội đồng', 'url' => null]
            ]" />
            <h1 class="page-title mt-2">Danh sách Thành viên Hội đồng trường STU</h1>
            <p class="text-muted">Nhiệm kỳ IV (2020 - 2025) - Đại học Công nghệ Sài Gòn</p>
        </div>
    </div>

    {{-- Thống kê nhanh --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm border-start border-primary border-4">
                <div class="card-body">
                    <div class="small text-muted text-uppercase fw-semibold">Tổng số thành viên</div>
                    <h3 class="fw-bold text-primary mb-0 mt-2">{{ $stats['total'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm border-start border-success border-4">
                <div class="card-body">
                    <div class="small text-muted text-uppercase fw-semibold">Có quyền biểu quyết (BR-07)</div>
                    <h3 class="fw-bold text-success mb-0 mt-2">{{ $stats['voting_eligible'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm border-start border-warning border-4">
                <div class="card-body">
                    <div class="small text-muted text-uppercase fw-semibold">Thường trực Hội đồng</div>
                    <h3 class="fw-bold text-warning mb-0 mt-2">{{ $stats['chair'] }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Bảng danh sách thành viên --}}
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <x-table>
                <x-slot:thead>
                    <tr>
                        <th style="width: 70px;">Mã TV</th>
                        <th>Họ và tên</th>
                        <th>Chức vụ HĐT</th>
                        <th>Đơn vị công tác</th>
                        <th>Quyền biểu quyết</th>
                        <th>Email liên hệ</th>
                    </tr>
                </x-slot:thead>

                @forelse($members as $m)
                    <tr>
                        <td class="text-muted fw-semibold">#TV-{{ $m->MaThanhVien }}</td>
                        <td>
                            <div class="fw-bold text-dark">{{ $m->nguoiDung?->HoTen ?? 'Thành viên' }}</div>
                            <div class="small text-muted">{{ $m->NhiemKy ?? 'Khóa IV (2020-2025)' }}</div>
                        </td>
                        <td>
                            @php
                                $chucVu = $m->ChucDanhHoiDongTruong ?: ($m->ChucDanh ?: 'Ủy viên HĐT');
                            @endphp
                            @if(str_contains($chucVu, 'Chủ tịch'))
                                <span class="badge bg-primary text-white">{{ $chucVu }}</span>
                            @elseif(str_contains($chucVu, 'Phó Chủ tịch') || str_contains($chucVu, 'Thư ký'))
                                <span class="badge bg-info text-white">{{ $chucVu }}</span>
                            @else
                                <span class="badge bg-light text-dark border">{{ $chucVu }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="text-dark">{{ $m->nguoiDung?->donVi?->TenDonVi ?? 'Hội đồng trường' }}</div>
                        </td>
                        <td>
                            @if($m->CoQuyenBieuQuyet ?? true)
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    <i class="bi bi-check-circle me-1"></i> Có quyền biểu quyết
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border">
                                    <i class="bi bi-dash-circle me-1"></i> Không biểu quyết
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="small text-muted">
                                <i class="bi bi-envelope me-1"></i>{{ $m->nguoiDung?->email ?? '--' }}
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            Chưa có dữ liệu thành viên.
                        </td>
                    </tr>
                @endforelse
            </x-table>
        </div>

        @if($members->hasPages())
            <div class="card-footer bg-white border-top-0 py-3">
                {{ $members->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
