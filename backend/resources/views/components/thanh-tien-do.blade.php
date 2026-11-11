@props([
    'value' => 0,
    'label' => null,
    'showPercent' => true,
])

@php
    $clampedValue = max(0, min(100, (int) $value));
    
    $colorClass = match(true) {
        $clampedValue < 40  => 'stu-progress-fill-low',
        $clampedValue < 80  => 'stu-progress-fill-medium',
        default             => 'stu-progress-fill-high',
    };
@endphp

<div {{ $attributes->merge(['class' => 'stu-progress']) }}>
    @if($label || $showPercent)
        <div class="stu-progress-info">
            <span>{{ $label }}</span>
            @if($showPercent)
                <span style="font-weight: 600;">{{ $clampedValue }}%</span>
            @endif
        </div>
    @endif
    <div class="stu-progress-track">
        <div class="stu-progress-fill {{ $colorClass }}" style="width: {{ $clampedValue }}%;"></div>
    </div>
</div>
