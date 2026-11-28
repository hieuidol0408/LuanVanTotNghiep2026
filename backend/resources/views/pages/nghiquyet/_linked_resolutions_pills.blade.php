@php
    $linkedResolutions = collect($resolutions ?? []);
@endphp

@if($linkedResolutions->isNotEmpty())
    <div style="display: flex; flex-wrap: wrap; gap: 5px; margin-top: 5px;">
        @foreach($linkedResolutions as $nq)
            <a href="{{ route('resolutions.show', $nq->MaNghiQuyet) }}"
               title="Xem Nghị quyết {{ $nq->SoNghiQuyet }}: {{ $nq->TrichYeu }}"
               style="display: inline-flex; align-items: center; gap: 4px; padding: 2px 7px; border-radius: 999px; background: #EFF6FF; border: 1px solid #BFDBFE; color: #1D4ED8; font-size: 11px; font-weight: 600; text-decoration: none;">
                <span>NQ #{{ $nq->MaNghiQuyet }} · {{ $nq->SoNghiQuyet }}</span>
            </a>
        @endforeach
    </div>
@endif
