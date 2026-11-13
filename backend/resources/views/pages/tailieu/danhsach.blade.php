@extends('layouts.chinh')

@section('title', 'Kho Tài liệu & Văn bản số - Hội đồng trường STU')

@section('content')
<div class="stu-page-container">
    <div class="stu-page-header stu-flex stu-items-center stu-justify-between stu-mb-6">
        <div>
            <x-thanh-dieu-huong :items="['Kho tài liệu số' => null]" />
            <h1 class="stu-page-title" style="font-size: 24px; font-weight: 800; color: var(--color-primary); margin: 0;">Kho Văn bản &amp; Tài liệu số Hội đồng trường</h1>
            <p style="font-size: 13px; color: var(--color-text-muted); margin: 4px 0 0 0;">Quản lý hồ sơ, nghị quyết, biên bản và tài liệu phiên họp theo chuẩn BR-05, BR-06</p>
        </div>
    </div>

    {{-- Bộ lọc & Tìm kiếm --}}
    <div class="stu-card stu-mb-6" style="padding: 20px 24px; border-radius: 12px; border: 1px solid var(--color-border); box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05); background: #ffffff;">
        <form method="GET" action="{{ route('documents.index') }}" class="stu-grid" style="grid-template-columns: 2fr 1.5fr auto auto; gap: 14px; align-items: flex-end;">
            <div class="stu-form-group" style="margin-bottom: 0;">
                <label class="stu-form-label">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <span>Tìm kiếm tài liệu</span>
                </label>
                <div style="position: relative;">
                    <svg width="15" height="15" fill="none" stroke="#94A3B8" stroke-width="2" viewBox="0 0 24 24" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); pointer-events: none;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <input type="text" name="search" value="{{ request('search') }}" class="stu-form-control" style="padding-left: 36px;" placeholder="Tên văn bản hoặc từ khóa...">
                </div>
            </div>
            <div class="stu-form-group" style="margin-bottom: 0;">
                <label class="stu-form-label">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <span>Cấp độ bảo mật / Phân loại</span>
                </label>
                <select name="type" class="stu-form-control">
                    <option value="">-- Tất cả phân loại --</option>
                    @foreach($types as $tp)
                        <option value="{{ $tp }}" {{ request('type') == $tp ? 'selected' : '' }}>
                            {{ $tp === 'INTERNAL' ? 'Nội bộ HĐT' : ($tp === 'PUBLIC_AGENDA' ? 'Công khai phiên họp' : ($tp === 'REGULATION' ? 'Quy chế - Quy định' : $tp)) }}
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
            @if(request()->anyFilled(['search', 'type', 'meeting_id']))
                <div>
                    <a href="{{ route('documents.index') }}" class="stu-btn stu-btn-ghost" style="height: 38px; padding: 0 14px;" title="Đặt lại bộ lọc về mặc định">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>Đặt lại</span>
                    </a>
                </div>
            @endif
        </form>
    </div>

    {{-- Danh sách tài liệu dạng lưới Card --}}
    <div class="row g-3">
        @forelse($documents as $doc)
            @php
                $isInternal = $doc->LoaiTaiLieu === 'INTERNAL';
                $canDownload = !$isInternal || $isCouncilMember;
            @endphp
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge {{ $isInternal ? 'bg-danger-subtle text-danger border border-danger-subtle' : 'bg-primary-subtle text-primary border border-primary-subtle' }}">
                                <i class="bi {{ $isInternal ? 'bi-lock-fill' : 'bi-file-earmark-text' }} me-1"></i>
                                {{ $isInternal ? 'Nội bộ HĐT' : 'Tài liệu chung' }}
                            </span>
                            <span class="small text-muted">#{{ $doc->MaTaiLieu }}</span>
                        </div>

                        <h6 class="fw-bold text-dark mb-2 text-truncate" title="{{ $doc->TenTaiLieu }}">
                            {{ $doc->TenTaiLieu }}
                        </h6>

                        <div class="small text-muted mb-3 flex-grow-1">
                            <div><i class="bi bi-person me-1"></i>{{ $doc->nguoiDung?->HoTen ?? 'Ban Thư ký HĐT' }}</div>
                            <div><i class="bi bi-calendar3 me-1"></i>{{ $doc->NgayTaiLen ? \Carbon\Carbon::parse($doc->NgayTaiLen)->format('d/m/Y H:i') : '--' }}</div>
                            <div><i class="bi bi-hdd me-1"></i>{{ $doc->DungLuong ? round($doc->DungLuong / 1024, 1) . ' KB' : 'Tệp đính kèm' }}</div>
                        </div>

                        <div class="pt-2 border-top d-flex justify-content-between align-items-center gap-2">
                            @if($canDownload)
                                <a href="{{ route('documents.download', $doc->MaTaiLieu) }}" class="btn btn-sm btn-outline-primary flex-grow-1">
                                    <i class="bi bi-download me-1"></i> Tải về tệp
                                </a>
                            @else
                                <button class="btn btn-sm btn-light text-muted flex-grow-1" disabled title="Tài liệu nội bộ chỉ dành cho thành viên HĐT">
                                    <i class="bi bi-shield-lock me-1"></i> Hạn chế quyền xem
                                </button>
                            @endif
                            @if(auth()->user() && in_array(auth()->user()->role, ['CHAIR', 'SECRETARY', 'ADMIN']))
                                <form method="POST" action="{{ route('documents.destroy', $doc->MaTaiLieu) }}" style="margin:0;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa tài liệu này không?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa tài liệu">
                                        <i class="bi bi-trash"></i> Xóa
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5 text-muted">
                <i class="bi bi-folder-x display-5 d-block mb-3"></i>
                Không tìm thấy tài liệu nào phù hợp với điều kiện tìm kiếm.
            </div>
        @endforelse
    </div>

    <div class="stu-mt-6" style="width: 100%;">
        {{ $documents->links() }}
    </div>
</div>
@endsection
