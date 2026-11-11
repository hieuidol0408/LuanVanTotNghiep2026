@extends('layouts.chinh')

@section('title', 'Thành viên Hội đồng trường STU - Khóa IV')

@section('content')
<div class="stu-page-container">
    <div class="stu-page-header stu-flex stu-items-center stu-justify-between stu-mb-6">
        <div>
            <x-thanh-dieu-huong :items="['Thành viên Hội đồng' => null]" />
            <h1 class="stu-page-title" style="font-size: 24px; font-weight: 800; color: var(--color-primary); margin: 0;">Danh sách Thành viên Hội đồng trường STU</h1>
            <p style="font-size: 13px; color: var(--color-text-muted); margin: 4px 0 0 0;">Nhiệm kỳ IV (2020 - 2025) - Đại học Công nghệ Sài Gòn</p>
        </div>
    </div>

    {{-- Thống kê nhanh --}}
    <div class="stu-grid stu-grid-cols-3 stu-mb-6" style="gap: 16px;">
        <div class="stu-card" style="padding: 18px; border-left: 4px solid var(--color-primary);">
            <div style="font-size: 12px; font-weight: 600; color: var(--color-text-muted); text-transform: uppercase;">Tổng số thành viên</div>
            <div style="font-size: 26px; font-weight: 800; color: var(--color-primary); margin-top: 4px;">{{ $stats['total'] }}</div>
        </div>
        <div class="stu-card" style="padding: 18px; border-left: 4px solid var(--color-success);">
            <div style="font-size: 12px; font-weight: 600; color: var(--color-text-muted); text-transform: uppercase;">Có quyền biểu quyết (BR-07)</div>
            <div style="font-size: 26px; font-weight: 800; color: var(--color-success); margin-top: 4px;">{{ $stats['voting_eligible'] }}</div>
        </div>
        <div class="stu-card" style="padding: 18px; border-left: 4px solid var(--color-warning);">
            <div style="font-size: 12px; font-weight: 600; color: var(--color-text-muted); text-transform: uppercase;">Thường trực Hội đồng</div>
            <div style="font-size: 26px; font-weight: 800; color: var(--color-warning); margin-top: 4px;">{{ $stats['chair'] }}</div>
        </div>
    </div>

    {{-- Bảng danh sách thành viên --}}
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <x-bang-du-lieu>
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
                        <td class="text-end">
                            @if(auth()->user() && in_array(auth()->user()->role, ['ADMIN', 'CHAIR', 'SECRETARY']))
                                <form method="POST" action="{{ route('members.destroy', $m->MaThanhVien) }}" style="display:inline-block; margin:0;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa thành viên này khỏi danh sách không?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa thành viên">
                                        <i class="bi bi-trash"></i> Xóa
                                    </button>
                                </form>
                            @else
                                <span class="text-muted small">--</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            Chưa có dữ liệu thành viên.
                        </td>
                    </tr>
                @endforelse
            </x-bang-du-lieu>
        </div>

        @if($members->hasPages())
            <div class="card-footer bg-white border-top-0 py-3">
                {{ $members->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
