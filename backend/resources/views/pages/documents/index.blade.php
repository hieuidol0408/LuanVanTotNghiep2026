@extends('layouts.app')

@section('title', 'Kho Tài liệu & Văn bản số - Hội đồng trường STU')

@section('content')
<div class="page-container">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <x-breadcrumb :items="[
                ['label' => 'Bảng điều hành', 'url' => route('dashboard')],
                ['label' => 'Kho tài liệu số', 'url' => null]
            ]" />
            <h1 class="page-title mt-2">Kho Văn bản &amp; Tài liệu số Hội đồng trường</h1>
            <p class="text-muted">Quản lý hồ sơ, nghị quyết, biên bản và tài liệu phiên họp theo chuẩn BR-05, BR-06</p>
        </div>
    </div>

    {{-- Bộ lọc & Tìm kiếm --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('documents.index') }}" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small text-muted">Tìm kiếm tài liệu</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Tên văn bản hoặc từ khóa...">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted">Cấp độ bảo mật / Phân loại</label>
                    <select name="type" class="form-select">
                        <option value="">-- Tất cả phân loại --</option>
                        @foreach($types as $tp)
                            <option value="{{ $tp }}" {{ request('type') == $tp ? 'selected' : '' }}>
                                {{ $tp === 'INTERNAL' ? 'Nội bộ HĐT' : ($tp === 'PUBLIC_AGENDA' ? 'Công khai phiên họp' : ($tp === 'REGULATION' ? 'Quy chế - Quy định' : $tp)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel"></i> Lọc
                    </button>
                </div>
            </form>
        </div>
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

                        <div class="pt-2 border-top d-flex justify-content-between align-items-center">
                            @if($canDownload)
                                <a href="{{ route('documents.download', $doc->MaTaiLieu) }}" class="btn btn-sm btn-outline-primary w-100">
                                    <i class="bi bi-download me-1"></i> Tải về tệp
                                </a>
                            @else
                                <button class="btn btn-sm btn-light text-muted w-100" disabled title="Tài liệu nội bộ chỉ dành cho thành viên HĐT">
                                    <i class="bi bi-shield-lock me-1"></i> Hạn chế quyền xem
                                </button>
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

    @if($documents->hasPages())
        <div class="mt-4">
            {{ $documents->links() }}
        </div>
    @endif
</div>
@endsection
