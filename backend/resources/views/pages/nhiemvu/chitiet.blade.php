@extends('layouts.chinh')

@section('title', 'Chi tiết Nhiệm vụ #' . $task->MaNhiemVu . ' - Hội đồng trường STU')

@section('content')
<div class="stu-page-container">
    {{-- Header & Breadcrumb --}}
    <x-thanh-dieu-huong :items="[
        'Bảng điều hành' => route('dashboard'),
        'Quản lý Nhiệm vụ' => route('tasks.index'),
        '#' . $task->MaNhiemVu => null
    ]" />

    @php
        $isOverdue = $task->TrangThai !== 'COMPLETED' && $task->NgayHoanThanh && \Carbon\Carbon::parse($task->NgayHoanThanh)->isPast();
    @endphp

    {{-- Page Header --}}
    <div class="stu-page-header stu-flex stu-items-start stu-justify-between stu-mb-6" style="gap: 16px; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 300px;">
            <div class="stu-flex stu-items-center stu-gap-2 stu-mb-2">
                <span style="font-weight: 800; color: var(--color-primary); font-size: 13px; background: rgba(0, 43, 91, 0.08); padding: 3px 10px; border-radius: var(--radius-sm); letter-spacing: 0.5px;">
                    #NV-{{ str_pad($task->MaNhiemVu, 3, '0', STR_PAD_LEFT) }}
                </span>
                <x-nhan-trang-thai :status="$task->TrangThai" />
                @if($isOverdue)
                    <span class="stu-badge stu-badge-danger" style="font-size: 11px; padding: 2px 8px;">
                        <i class="bi bi-clock-history me-1"></i> Quá hạn
                    </span>
                @endif
            </div>

            <h1 class="stu-page-title" style="font-size: 24px; font-weight: 800; color: var(--color-primary); margin: 0; line-height: 1.35;">
                {{ $task->TenNhiemVu }}
            </h1>

            <div style="font-size: 13px; color: var(--color-text-muted); margin-top: 8px; display: flex; align-items: center; gap: 18px; flex-wrap: wrap;">
                @if($task->nghiQuyet)
                    <span>
                        <i class="bi bi-file-earmark-check text-primary me-1"></i>
                        <strong>Nghị quyết căn cứ:</strong> 
                        <a href="{{ route('resolutions.show', $task->nghiQuyet->MaNghiQuyet) }}" style="color: var(--color-primary); font-weight: 600; text-decoration: none;">
                            {{ $task->nghiQuyet->SoNghiQuyet }}
                        </a>
                    </span>
                @endif
                <span>
                    <i class="bi bi-calendar-event me-1"></i>
                    <strong>Ngày giao:</strong> {{ $task->NgayGiao ? \Carbon\Carbon::parse($task->NgayGiao)->format('d/m/Y') : '--' }}
                </span>
                <span>
                    <i class="bi bi-calendar-check me-1 {{ $isOverdue ? 'text-danger' : '' }}"></i>
                    <strong>Hạn hoàn thành:</strong> 
                    <span class="{{ $isOverdue ? 'text-danger fw-bold' : '' }}">
                        {{ $task->NgayHoanThanh ? \Carbon\Carbon::parse($task->NgayHoanThanh)->format('d/m/Y') : '--' }}
                    </span>
                </span>
            </div>
        </div>

        {{-- Thanh nút thao tác --}}
        <div class="stu-flex stu-items-center stu-gap-2" style="flex-wrap: wrap;">
            <button type="button" class="stu-btn stu-btn-primary" style="height: 38px;" data-modal-open="createTaskReportModal">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                <span>Nộp báo cáo tiến độ</span>
            </button>

            @if(Auth::user() && in_array(Auth::user()->role, ['SECRETARY', 'CHAIR', 'ADMIN']))
                <button type="button" class="stu-btn stu-btn-secondary" style="height: 38px;" data-modal-open="editTaskModal">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Chỉnh sửa</span>
                </button>
            @endif
        </div>
    </div>

    {{-- Main Grid Layout: 2 cột (Trái 2fr, Phải 1fr) --}}
    <div class="stu-grid" style="grid-template-columns: 2fr 1fr; gap: 24px; align-items: start;">
        {{-- CỘT TRÁI (2fr): Tiến độ, Chi tiết nhiệm vụ, Nhật ký báo cáo --}}
        <div>
            {{-- Card 1: Tiến độ thực hiện hiện tại --}}
            <div class="stu-card stu-mb-6" style="padding: 22px 24px; border-radius: 12px; border: 1px solid var(--color-border); background: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="width: 32px; height: 32px; border-radius: 8px; background: rgba(0, 43, 91, 0.08); display: flex; align-items: center; justify-content: center; color: var(--color-primary);">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                        </span>
                        <h3 style="font-size: 16px; font-weight: 700; color: var(--color-text-main); margin: 0;">
                            Tiến độ thực hiện hiện tại
                        </h3>
                    </div>
                    <div style="font-size: 24px; font-weight: 800; color: {{ $currentProgress >= 100 ? 'var(--color-success)' : 'var(--color-primary)' }};">
                        {{ $currentProgress }}%
                    </div>
                </div>

                {{-- Thanh tiến độ chuẩn phong cách STU --}}
                <div style="width: 100%; height: 12px; background: #E2E8F0; border-radius: 999px; overflow: hidden; position: relative; margin-bottom: 10px;">
                    <div style="width: {{ $currentProgress }}%; height: 100%; border-radius: 999px; background: {{ $currentProgress >= 100 ? 'var(--color-success)' : 'linear-gradient(90deg, #002B5B 0%, #0056B3 100%)' }}; transition: width 0.4s ease;"></div>
                </div>

                <div style="display: flex; justify-content: space-between; font-size: 12px; color: var(--color-text-muted);">
                    <span>0% (Khởi đầu)</span>
                    <span style="color: var(--color-text-main); font-weight: 600;">50% (Đang triển khai)</span>
                    <span>100% (Nghiệm thu / Hoàn tất)</span>
                </div>
            </div>

            {{-- Card 2: Mô tả & Yêu cầu nhiệm vụ --}}
            <div class="stu-card stu-mb-6" style="padding: 24px; border-radius: 12px; border: 1px solid var(--color-border); background: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 14px; padding-bottom: 12px; border-bottom: 1px solid var(--color-border);">
                    <span style="width: 32px; height: 32px; border-radius: 8px; background: rgba(0, 43, 91, 0.08); display: flex; align-items: center; justify-content: center; color: var(--color-primary);">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                    <h3 style="font-size: 16px; font-weight: 700; color: var(--color-text-main); margin: 0;">
                        Mô tả &amp; Yêu cầu nhiệm vụ
                    </h3>
                </div>

                <div style="font-size: 14px; line-height: 1.7; color: #334155; white-space: pre-line; margin-bottom: 18px;">
                    {{ $task->NoiDung ?: 'Chưa có mô tả chi tiết cho nhiệm vụ này.' }}
                </div>

                {{-- Hộp thông tin căn cứ & Thời hạn cân đối 3 cột --}}
                <div style="display: grid; grid-template-columns: 1.5fr 1fr 1fr; gap: 14px; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 14px 18px; align-items: start;">
                    <div>
                        <div style="font-size: 11.5px; font-weight: 600; color: var(--color-text-muted); text-transform: uppercase; margin-bottom: 4px;">Nghị quyết căn cứ:</div>
                        @if($task->nghiQuyet)
                            <a href="{{ route('resolutions.show', $task->nghiQuyet->MaNghiQuyet) }}" style="font-weight: 700; color: var(--color-primary); text-decoration: none; display: flex; align-items: center; gap: 6px; font-size: 13.5px;">
                                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span>{{ $task->nghiQuyet->SoNghiQuyet }}</span>
                            </a>
                            <div style="font-size: 12px; color: var(--color-text-muted); margin-top: 4px; line-height: 1.4;">{{ Str::limit($task->nghiQuyet->TrichYeu, 65) }}</div>
                        @else
                            <span style="font-size: 13px; color: var(--color-text-muted);">Không xác định</span>
                        @endif
                    </div>

                    <div>
                        <div style="font-size: 11.5px; font-weight: 600; color: var(--color-text-muted); text-transform: uppercase; margin-bottom: 4px;">Ngày giao nhiệm vụ:</div>
                        <div style="font-size: 14px; font-weight: 700; color: var(--color-text-main);">
                            {{ $task->NgayGiao ? \Carbon\Carbon::parse($task->NgayGiao)->format('d/m/Y') : '--' }}
                        </div>
                    </div>

                    <div>
                        <div style="font-size: 11.5px; font-weight: 600; color: var(--color-text-muted); text-transform: uppercase; margin-bottom: 4px;">Hạn hoàn thành:</div>
                        <div style="font-size: 14px; font-weight: 700; color: {{ $isOverdue ? 'var(--color-danger)' : 'var(--color-text-main)' }};">
                            {{ $task->NgayHoanThanh ? \Carbon\Carbon::parse($task->NgayHoanThanh)->format('d/m/Y') : '--' }}
                        </div>
                        @if($isOverdue)
                            <div style="font-size: 11px; color: var(--color-danger); margin-top: 2px; font-weight: 600;">Đã quá hạn</div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Card 3: Nhật ký Báo cáo Tiến độ --}}
            <div class="stu-card" style="padding: 24px; border-radius: 12px; border: 1px solid var(--color-border); background: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid var(--color-border); flex-wrap: wrap; gap: 10px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="width: 32px; height: 32px; border-radius: 8px; background: rgba(0, 43, 91, 0.08); display: flex; align-items: center; justify-content: center; color: var(--color-primary);">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </span>
                        <h3 style="font-size: 16px; font-weight: 700; color: var(--color-text-main); margin: 0;">
                            Nhật ký Báo cáo Tiến độ ({{ $allReports->count() }})
                        </h3>
                    </div>
                    <button type="button" class="stu-btn stu-btn-outline stu-btn-sm" style="font-weight: 600;" data-modal-open="createTaskReportModal">
                        <i class="bi bi-plus-lg me-1"></i> Nộp báo cáo mới
                    </button>
                </div>

                <div class="stu-table-responsive" style="overflow-x: auto;">
                    <table class="stu-table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
                        <thead>
                            <tr style="background: #F8FAFC; border-bottom: 2px solid #E2E8F0; text-align: left;">
                                <th style="padding: 10px 12px; font-weight: 700; color: var(--color-text-muted); font-size: 12px; text-transform: uppercase;">Thời gian</th>
                                <th style="padding: 10px 12px; font-weight: 700; color: var(--color-text-muted); font-size: 12px; text-transform: uppercase;">Đơn vị / Người báo cáo</th>
                                <th style="padding: 10px 12px; font-weight: 700; color: var(--color-text-muted); font-size: 12px; text-transform: uppercase; width: 110px;">Tiến độ</th>
                                <th style="padding: 10px 12px; font-weight: 700; color: var(--color-text-muted); font-size: 12px; text-transform: uppercase;">Nội dung &amp; Minh chứng</th>
                                <th style="padding: 10px 12px; font-weight: 700; color: var(--color-text-muted); font-size: 12px; text-transform: uppercase; width: 130px;">Thẩm định</th>
                                <th style="padding: 10px 12px; font-weight: 700; color: var(--color-text-muted); font-size: 12px; text-transform: uppercase; width: 80px; text-align: right;">Chi tiết</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($allReports->sortByDesc('MaBaoCao') as $rpt)
                                <tr style="border-bottom: 1px solid var(--color-border); transition: background-color 0.15s ease;">
                                    <td style="padding: 12px;">
                                        <div style="font-weight: 700; color: var(--color-text-main);">
                                            {{ $rpt->NgayBaoCao ? \Carbon\Carbon::parse($rpt->NgayBaoCao)->format('d/m/Y') : '--' }}
                                        </div>
                                        <span style="font-size: 11px; color: var(--color-text-muted);">#BC-{{ str_pad($rpt->MaBaoCao, 3, '0', STR_PAD_LEFT) }}</span>
                                    </td>
                                    <td style="padding: 12px;">
                                        <div style="font-weight: 600; color: var(--color-text-main);">{{ $rpt->phanCongNhiemVu?->donVi?->TenDonVi ?? 'Đơn vị' }}</div>
                                        <div style="font-size: 12px; color: var(--color-text-muted); display: flex; align-items: center; gap: 4px; margin-top: 2px;">
                                            <i class="bi bi-person"></i>
                                            <span>{{ $rpt->nguoiDung?->HoTen ?? 'Cán bộ phụ trách' }}</span>
                                        </div>
                                    </td>
                                    <td style="padding: 12px;">
                                        <div style="font-weight: 800; font-size: 13.5px; color: {{ $rpt->TienDo >= 100 ? 'var(--color-success)' : 'var(--color-primary)' }};">
                                            {{ $rpt->TienDo }}%
                                        </div>
                                        <div style="width: 100%; height: 6px; background: #E2E8F0; border-radius: 999px; overflow: hidden; margin-top: 4px;">
                                            <div style="width: {{ $rpt->TienDo }}%; height: 100%; background: {{ $rpt->TienDo >= 100 ? 'var(--color-success)' : 'var(--color-primary)' }}; border-radius: 999px;"></div>
                                        </div>
                                    </td>
                                    <td style="padding: 12px;">
                                        <div style="color: #334155; line-height: 1.4;">{{ Str::limit($rpt->NoiDung, 60) }}</div>
                                        @if($rpt->NoiDungKhoKhan)
                                            <div style="font-size: 12px; color: var(--color-danger); margin-top: 4px; display: flex; align-items: flex-start; gap: 4px;">
                                                <i class="bi bi-exclamation-triangle" style="margin-top: 1px;"></i>
                                                <span>Khó khăn: {{ Str::limit($rpt->NoiDungKhoKhan, 45) }}</span>
                                            </div>
                                        @endif
                                        @if($rpt->taiLieu->isNotEmpty())
                                            <div style="margin-top: 6px; display: flex; gap: 6px; flex-wrap: wrap;">
                                                @foreach($rpt->taiLieu as $doc)
                                                    <a href="{{ asset($doc->DuongDan) }}" target="_blank" style="display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 4px; background: #F1F5F9; border: 1px solid #CBD5E1; color: #475569; font-size: 11px; text-decoration: none;">
                                                        <i class="bi bi-paperclip"></i>
                                                        <span>{{ Str::limit($doc->TenTaiLieu, 20) }}</span>
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                    <td style="padding: 12px;">
                                        @if($rpt->TrangThaiXacNhan === 'APPROVED')
                                            <span class="stu-badge stu-badge-success" style="font-size: 11px; padding: 3px 8px;">
                                                <i class="bi bi-check-circle me-1"></i> Đã duyệt
                                            </span>
                                        @elseif($rpt->TrangThaiXacNhan === 'REJECTED')
                                            <span class="stu-badge stu-badge-danger" style="font-size: 11px; padding: 3px 8px;">
                                                <i class="bi bi-x-circle me-1"></i> Yêu cầu sửa
                                            </span>
                                        @else
                                            <span class="stu-badge stu-badge-warning" style="font-size: 11px; padding: 3px 8px;">
                                                <i class="bi bi-hourglass-split me-1"></i> Chờ duyệt
                                            </span>
                                        @endif
                                    </td>
                                    <td style="padding: 12px; text-align: right;">
                                        <a href="{{ route('task-reports.show', $rpt->MaBaoCao) }}" class="stu-btn stu-btn-ghost stu-btn-sm" style="font-size: 12px; padding: 4px 8px;">
                                            <span>Xem</span>
                                            <i class="bi bi-chevron-right ms-1" style="font-size: 10px;"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="padding: 36px; text-align: center; color: var(--color-text-muted);">
                                        <i class="bi bi-journal-x" style="font-size: 32px; display: block; margin-bottom: 8px; opacity: 0.5;"></i>
                                        <div>Chưa có báo cáo tiến độ nào được nộp cho nhiệm vụ này.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- CỘT PHẢI (1fr): Đơn vị chủ trì, Đơn vị phối hợp, Hướng dẫn nghiệp vụ BR-10 --}}
        <div>
            {{-- Card Đơn vị Chủ trì --}}
            @php
                $leadAssignment = $task->phanCongNhiemVu->firstWhere('VaiTroThucHien', 'CHỦ TRÌ');
                $coopAssignments = $task->phanCongNhiemVu->where('VaiTroThucHien', 'PHỐI HỢP');
            @endphp
            <div class="stu-card stu-mb-4" style="padding: 20px; border-radius: 12px; border: 1px solid var(--color-border); border-left: 4px solid var(--color-primary); background: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="font-size: 11.5px; font-weight: 700; color: var(--color-primary); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                    <i class="bi bi-award-fill"></i>
                    <span>Đơn vị Chủ trì (BR-10)</span>
                </div>

                @if($leadAssignment && $leadAssignment->donVi)
                    <div style="font-size: 16px; font-weight: 700; color: var(--color-text-main); margin-bottom: 10px; line-height: 1.4;">
                        {{ $leadAssignment->donVi->TenDonVi }}
                    </div>
                    <div style="font-size: 12.5px; color: var(--color-text-muted); margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                        <i class="bi bi-person text-primary"></i>
                        <span>Người phụ trách: <strong style="color: var(--color-text-main);">{{ $leadAssignment->TenNguoiPhuTrach ?: 'Chưa cập nhật' }}</strong></span>
                    </div>
                    <div style="font-size: 12.5px; color: var(--color-text-muted); display: flex; align-items: center; gap: 6px;">
                        <i class="bi bi-flag text-secondary"></i>
                        <span>Trạng thái: 
                            <span class="stu-badge stu-badge-neutral" style="font-size: 11px; padding: 2px 7px;">
                                {{ $leadAssignment->TrangThaiThucHien }}
                            </span>
                        </span>
                    </div>
                @else
                    <div style="font-size: 12.5px; color: var(--color-text-muted); font-style: italic;">Chưa có thông tin đơn vị chủ trì.</div>
                @endif
            </div>

            {{-- Card Đơn vị Phối hợp --}}
            <div class="stu-card stu-mb-4" style="padding: 20px; border-radius: 12px; border: 1px solid var(--color-border); background: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="font-size: 11.5px; font-weight: 700; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                    <i class="bi bi-people-fill"></i>
                    <span>Đơn vị Phối hợp ({{ $coopAssignments->count() }})</span>
                </div>

                @if($coopAssignments->isNotEmpty())
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        @foreach($coopAssignments as $coop)
                            <div style="padding: 10px 12px; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px;">
                                <div style="font-weight: 600; font-size: 13px; color: var(--color-text-main);">
                                    {{ $coop->donVi?->TenDonVi ?? 'Đơn vị phối hợp' }}
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 4px; font-size: 11.5px; color: var(--color-text-muted);">
                                    <span>{{ $coop->TenNguoiPhuTrach ?: 'Cán bộ liên lạc' }}</span>
                                    <span class="stu-badge stu-badge-neutral" style="font-size: 10.5px; padding: 1px 6px;">
                                        {{ $coop->TrangThaiThucHien }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div style="font-size: 12.5px; color: var(--color-text-muted); font-style: italic;">Không có đơn vị phối hợp.</div>
                @endif
            </div>

            {{-- Hộp hỗ trợ quy định BR-10 --}}
            <div style="padding: 16px; background: #F0F9FF; border: 1px dashed #BAE6FD; border-radius: 10px; font-size: 12px; color: #0369A1; line-height: 1.55;">
                <div style="display: flex; align-items: center; gap: 6px; font-weight: 700; margin-bottom: 4px; color: #0284C7;">
                    <i class="bi bi-shield-check" style="font-size: 15px;"></i>
                    <span>Quy định BR-10</span>
                </div>
                Nhiệm vụ chỉ được xác nhận hoàn thành (COMPLETED) khi báo cáo tiến độ đạt 100% và được Thư ký hoặc Chủ tịch Hội đồng trường thẩm định phê duyệt chính thức.
            </div>
        </div>
    </div>
</div>

@if(Auth::user() && in_array(Auth::user()->role, ['SECRETARY', 'CHAIR', 'ADMIN']))
    {{-- Modal Chỉnh sửa Nhiệm vụ --}}
    <x-hop-thoai id="editTaskModal" title="Cập nhật Thông tin Nhiệm vụ #{{ $task->MaNhiemVu }}" size="lg">
        <form method="POST" action="{{ route('tasks.update', $task->MaNhiemVu) }}">
            @csrf
            @method('PUT')

            <div class="stu-form-group stu-mb-4" style="width: 100%; box-sizing: border-box;">
                <label class="stu-form-label" style="font-size: 13px; font-weight: 700;">Tên nhiệm vụ <span style="color: var(--color-danger);">*</span></label>
                <input type="text" name="TenNhiemVu" value="{{ old('TenNhiemVu', $task->TenNhiemVu) }}" class="stu-form-control" required style="height: 38px;">
            </div>

            <div class="stu-form-group stu-mb-4" style="width: 100%; box-sizing: border-box;">
                <label class="stu-form-label" style="font-size: 13px; font-weight: 700;">Nội dung chi tiết</label>
                <textarea name="NoiDung" rows="4" class="stu-form-control" style="font-size: 13px;">{{ old('NoiDung', $task->NoiDung) }}</textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px; margin-bottom: 16px; width: 100%; box-sizing: border-box;">
                <div class="stu-form-group" style="min-width: 0; width: 100%; box-sizing: border-box; margin-bottom: 0;">
                    <label class="stu-form-label" style="font-size: 12.5px; font-weight: 700;">Ngày giao <span style="color: var(--color-danger);">*</span></label>
                    <input type="date" name="NgayGiao" 
                           value="{{ old('NgayGiao', $task->NgayGiao ? \Carbon\Carbon::parse($task->NgayGiao)->format('Y-m-d') : '') }}" 
                           class="stu-form-control" required style="height: 38px; font-size: 13px;">
                </div>

                <div class="stu-form-group" style="min-width: 0; width: 100%; box-sizing: border-box; margin-bottom: 0;">
                    <label class="stu-form-label" style="font-size: 12.5px; font-weight: 700;">Hạn hoàn thành <span style="color: var(--color-danger);">*</span></label>
                    <input type="date" name="NgayHoanThanh" 
                           value="{{ old('NgayHoanThanh', $task->NgayHoanThanh ? \Carbon\Carbon::parse($task->NgayHoanThanh)->format('Y-m-d') : '') }}" 
                           class="stu-form-control" required style="height: 38px; font-size: 13px;">
                </div>

                <div class="stu-form-group" style="min-width: 0; width: 100%; box-sizing: border-box; margin-bottom: 0;">
                    <label class="stu-form-label" style="font-size: 12.5px; font-weight: 700;">Trạng thái <span style="color: var(--color-danger);">*</span></label>
                    <select name="TrangThai" class="stu-form-control" required style="height: 38px; font-size: 13px;">
                        <option value="ASSIGNED" {{ old('TrangThai', $task->TrangThai) === 'ASSIGNED' ? 'selected' : '' }}>Chưa thực hiện (ASSIGNED)</option>
                        <option value="IN_PROGRESS" {{ old('TrangThai', $task->TrangThai) === 'IN_PROGRESS' ? 'selected' : '' }}>Đang thực hiện (IN_PROGRESS)</option>
                        <option value="COMPLETED" {{ old('TrangThai', $task->TrangThai) === 'COMPLETED' ? 'selected' : '' }}>Đã hoàn tất (COMPLETED)</option>
                        <option value="OVERDUE" {{ old('TrangThai', $task->TrangThai) === 'OVERDUE' ? 'selected' : '' }}>Quá hạn (OVERDUE)</option>
                    </select>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid var(--color-border); padding-top: 14px; margin-top: 16px;">
                <button type="button" class="stu-btn stu-btn-secondary" data-modal-close>Hủy bỏ</button>
                <button type="submit" class="stu-btn stu-btn-primary" style="font-weight: 600;">Lưu thay đổi</button>
            </div>
        </form>
    </x-hop-thoai>
@endif

{{-- MODAL NỔI: NỘP BÁO CÁO TIẾN ĐỘ NHIỆM VỤ (BR-10) --}}
<x-hop-thoai id="createTaskReportModal" title="Nộp Báo Cáo Tiến Độ Nhiệm Vụ (BR-10)" size="lg">
    <form method="POST" action="{{ route('task-reports.store') }}" enctype="multipart/form-data">
        @csrf
        <div style="background: #F8FAFC; border: 1px solid var(--color-border); border-radius: 8px; padding: 10px 14px; margin-bottom: 14px; display: flex; align-items: center; justify-content: space-between; font-size: 12.5px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-weight: 800; color: var(--color-primary); background: rgba(0, 43, 91, 0.08); padding: 2px 8px; border-radius: 4px;">#NV-{{ str_pad($task->MaNhiemVu, 3, '0', STR_PAD_LEFT) }}</span>
                <strong style="color: var(--color-text-main);">{{ $task->TenNhiemVu }}</strong>
            </div>
            <span style="color: var(--color-text-muted);">Tiến độ hiện tại: <strong style="color: var(--color-primary);">{{ $currentProgress }}%</strong></span>
        </div>

        {{-- 1. Phân công nhiệm vụ --}}
        <div class="stu-form-group" style="margin-bottom: 14px; width: 100%; box-sizing: border-box;">
            <label class="stu-form-label" style="font-size: 12.5px; font-weight: 700;">
                Phân công phụ trách <span style="color: var(--color-danger);">*</span>
            </label>
            <select name="MaPhanCong" class="stu-form-control" required style="width: 100%; height: 38px; font-size: 13px; border-radius: 8px; border: 1.5px solid #CBD5E1; padding-right: 32px; background-color: #F8FAFC;">
                @foreach($task->phanCongNhiemVu as $assign)
                    <option value="{{ $assign->MaPhanCong }}">
                        {{ $assign->VaiTroThucHien }} — {{ $assign->donVi?->TenDonVi }} (Người phụ trách: {{ $assign->TenNguoiPhuTrach ?: 'Cán bộ đơn vị' }})
                    </option>
                @endforeach
            </select>
        </div>

        {{-- 2. Tỷ lệ tiến độ & Tệp đính kèm --}}
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px; width: 100%; box-sizing: border-box;">
            <div class="stu-form-group" style="min-width: 0; width: 100%; box-sizing: border-box; margin-bottom: 0;">
                <label class="stu-form-label" style="font-size: 12.5px; font-weight: 700; display: flex; align-items: center; justify-content: space-between;">
                    <span>Tỷ lệ tiến độ mới (%) <span style="color: var(--color-danger);">*</span></span>
                    <span id="detail_modal_progress_badge" style="font-size: 12px; font-weight: 800; color: var(--color-primary); background: rgba(0, 43, 91, 0.08); padding: 1px 8px; border-radius: 4px;">{{ $currentProgress }}%</span>
                </label>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <input type="number" id="detail_modal_tiendo_input" name="TienDo" min="0" max="100" 
                           value="{{ old('TienDo', $currentProgress) }}" 
                           class="stu-form-control" required 
                           style="height: 38px; font-size: 14px; font-weight: 700; color: var(--color-primary); width: 85px; text-align: center;">
                    <div style="flex: 1;">
                        <input type="range" class="stu-form-range" id="detail_modal_tiendo_range" min="0" max="100" 
                               value="{{ old('TienDo', $currentProgress) }}" 
                               style="width: 100%; accent-color: var(--color-primary); cursor: pointer;">
                    </div>
                </div>
            </div>

            <div class="stu-form-group" style="min-width: 0; width: 100%; box-sizing: border-box; margin-bottom: 0;">
                <label class="stu-form-label" style="font-size: 12.5px; font-weight: 700;">
                    Minh chứng đính kèm (Tùy chọn)
                </label>
                <input type="file" name="file_minh_chung" 
                       class="stu-form-control" 
                       accept=".pdf,.doc,.docx,.xls,.xlsx,.zip,.rar"
                       style="height: 38px; font-size: 12px; padding: 6px 12px; background: #F8FAFC;">
            </div>
        </div>

        {{-- 3. Nội dung thực hiện --}}
        <div class="stu-form-group" style="margin-bottom: 14px; width: 100%; box-sizing: border-box;">
            <label class="stu-form-label" style="font-size: 12.5px; font-weight: 700;">
                Nội dung kết quả đã thực hiện <span style="color: var(--color-danger);">*</span>
            </label>
            <textarea name="NoiDung" rows="3" class="stu-form-control" 
                      placeholder="Mô tả cụ thể các hạng mục công việc đã triển khai, kết quả đạt được..." 
                      required style="font-size: 13px; line-height: 1.5;">{{ old('NoiDung') }}</textarea>
        </div>

        {{-- 4. Khó khăn vướng mắc --}}
        <div class="stu-form-group" style="margin-bottom: 14px; width: 100%; box-sizing: border-box;">
            <label class="stu-form-label" style="font-size: 12.5px; font-weight: 700;">
                Khó khăn & Kiến nghị (Nếu có)
            </label>
            <textarea name="NoiDungKhoKhan" rows="2" class="stu-form-control" 
                      placeholder="Nêu rõ các vướng mắc phát sinh cần Hội đồng trường tháo gỡ..." 
                      style="font-size: 13px; line-height: 1.5;">{{ old('NoiDungKhoKhan') }}</textarea>
        </div>

        <!-- Footer buttons -->
        <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 18px; padding-top: 14px; border-top: 1px solid var(--color-border);">
            <button type="button" class="stu-btn stu-btn-secondary" data-modal-close>
                <span>Hủy bỏ</span>
            </button>
            <button type="submit" class="stu-btn stu-btn-primary" style="font-weight: 600;">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                <span>Gửi báo cáo tiến độ</span>
            </button>
        </div>
    </form>
</x-hop-thoai>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('edit') === '1' || urlParams.get('chinh_sua') === '1') {
            if (window.STUModal) {
                window.STUModal.open('editTaskModal');
            }
        }
        if (urlParams.get('report') === '1' || urlParams.get('bao_cao') === '1') {
            if (window.STUModal) {
                window.STUModal.open('createTaskReportModal');
            }
        }

        // Đồng bộ slider và input number cho modal nộp báo cáo
        const reportInput = document.getElementById('detail_modal_tiendo_input');
        const reportRange = document.getElementById('detail_modal_tiendo_range');
        const reportBadge = document.getElementById('detail_modal_progress_badge');

        function updateModalProgress(val) {
            val = Math.max(0, Math.min(100, parseInt(val) || 0));
            if (reportInput) reportInput.value = val;
            if (reportRange) reportRange.value = val;
            if (reportBadge) reportBadge.textContent = val + '%';
        }

        if (reportInput && reportRange) {
            reportInput.addEventListener('input', function() { updateModalProgress(this.value); });
            reportRange.addEventListener('input', function() { updateModalProgress(this.value); });
        }
    });
</script>
@endsection
