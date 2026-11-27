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

    {{-- Tìm kiếm thành viên --}}
    <div class="stu-card stu-mb-6" style="padding: 20px 24px; border-radius: 12px; border: 1px solid var(--color-border); box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05); background: #ffffff;">
        <form method="GET" action="{{ route('members.index') }}" class="stu-grid" style="grid-template-columns: 1fr auto auto; gap: 14px; align-items: flex-end;">
            <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
            <div class="stu-form-group" style="margin-bottom: 0;">
                <label class="stu-form-label">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <span>Tìm kiếm thành viên</span>
                </label>
                <div style="position: relative;">
                    <svg width="15" height="15" fill="none" stroke="#94A3B8" stroke-width="2" viewBox="0 0 24 24" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); pointer-events: none;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <input type="text" name="search" value="{{ request('search') }}" class="stu-form-control" style="padding-left: 36px;" placeholder="Mã thành viên (#TV-...), họ tên, chức vụ hoặc email...">
                </div>
            </div>
            <div>
                <button type="submit" class="stu-btn stu-btn-primary" style="height: 38px; padding: 0 18px; font-weight: 600; box-shadow: 0 1px 2px 0 rgba(0, 58, 140, 0.2);">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span>Tìm</span>
                </button>
            </div>
            <div>
                <a href="{{ route('members.index') }}" class="stu-btn stu-btn-ghost" style="height: 38px; padding: 0 14px;" title="Đặt lại bộ lọc về mặc định">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Đặt lại</span>
                </a>
            </div>
        </form>
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
                                <form method="POST" action="{{ route('members.destroy', $m->MaThanhVien) }}" style="display:inline-block; margin:0;" onsubmit="return confirm('Bạn có chắc chắn muốn ẩn thành viên này khỏi danh sách không? (Dữ liệu vẫn được bảo toàn trong hệ thống)');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-secondary" title="Ẩn thành viên (Bảo toàn dữ liệu)">
                                        <i class="bi bi-eye-slash"></i> Ẩn
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

        <div class="stu-mt-6" style="width: 100%;">
        {{ $members->links() }}
    </div>
    </div>
</div>
@endsection
