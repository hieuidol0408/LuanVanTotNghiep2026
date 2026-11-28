@php
    $linkedResolutions = collect($resolutions ?? []);
    $emptyMessage = $emptyMessage ?? 'Chưa có Nghị quyết Hội đồng trường nào liên kết.';
    $compact = (bool) ($compact ?? false);
    $unlinkType = $unlinkType ?? null;
    $targetId = $targetId ?? null;
    $currentRole = $role ?? (auth()->user()?->role ?? session('user_role') ?? 'CHAIR');
    $canUnlink = in_array($unlinkType, ['dossier', 'voting'], true)
        && ! empty($targetId)
        && in_array($currentRole, ['CHAIR', 'SECRETARY', 'ADMIN'], true);
@endphp

<div class="stu-card" style="padding: {{ $compact ? '22px' : '24px' }}; background: #ffffff;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; gap: 10px; flex-wrap: wrap;">
        <h3 style="font-size: {{ $compact ? '15px' : '16px' }}; font-weight: {{ $compact ? '800' : '700' }}; color: var(--color-primary); margin: 0; display: flex; align-items: center; gap: 8px;">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
            <span>Nghị quyết Hội đồng trường liên kết ({{ $linkedResolutions->count() }})</span>
        </h3>
    </div>

    @if($linkedResolutions->isNotEmpty())
        <div style="display: flex; flex-direction: column; gap: 10px;">
            @foreach($linkedResolutions as $nq)
                <div style="padding: 12px 14px; border: 1px solid var(--color-border); border-left: 3px solid var(--color-primary); border-radius: 8px; background: #F8FAFC;">
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-wrap: wrap;">
                        <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                            <a href="{{ route('resolutions.show', $nq->MaNghiQuyet) }}" style="font-weight: 800; color: var(--color-primary); text-decoration: none; font-size: 13px;">
                                #NQ-{{ $nq->MaNghiQuyet }} · {{ $nq->SoNghiQuyet }}
                            </a>
                            <x-nhan-trang-thai :status="$nq->TrangThai" />
                        </div>
                        <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                            <a href="{{ route('resolutions.show', $nq->MaNghiQuyet) }}" class="stu-btn stu-btn-outline stu-btn-sm" style="padding: 3px 9px; font-size: 11.5px;">
                                Xem nghị quyết &rarr;
                            </a>
                            @if($canUnlink)
                                <form method="POST" action="{{ route('resolutions.links.destroy', [$nq->MaNghiQuyet, $unlinkType, $targetId]) }}" style="display: inline-block; margin: 0;" onsubmit="return confirm('Bạn có chắc muốn gỡ liên kết với Nghị quyết {{ $nq->SoNghiQuyet }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="stu-btn stu-btn-ghost stu-btn-sm" style="padding: 3px 9px; font-size: 11.5px; color: var(--color-danger); border: 1px solid rgba(220, 38, 38, 0.25);" title="Gỡ liên kết với nghị quyết này">
                                        Gỡ liên kết
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                    <div style="font-size: 13px; font-weight: 600; color: var(--color-text-primary); margin-top: 6px; line-height: 1.4;">
                        {{ $nq->TrichYeu }}
                    </div>
                    <div style="font-size: 11.5px; color: var(--color-text-muted); margin-top: 4px; display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                        <span>Ngày ban hành: <strong>{{ $nq->NgayBanHanh ? \Carbon\Carbon::parse($nq->NgayBanHanh)->format('d/m/Y') : '—' }}</strong></span>
                        @if($nq->phienHop)
                            <span>
                                Phiên họp:
                                <a href="{{ route('meetings.show', $nq->MaPhienHop) }}" style="color: var(--color-primary); text-decoration: none; font-weight: 600;">
                                    #{{ $nq->MaPhienHop }} - {{ Str::limit($nq->phienHop->TenPhienHop, 45) }}
                                </a>
                            </span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div style="background: #f8fafc; border: 1.5px dashed #cbd5e1; border-radius: 8px; padding: 16px; text-align: center; font-size: 12.5px; color: var(--color-text-muted);">
            {{ $emptyMessage }}
        </div>
    @endif
</div>
