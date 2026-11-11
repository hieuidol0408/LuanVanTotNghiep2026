@props([
    'title' => '',
    'value' => '0',
    'subtitle' => null,
    'type' => 'primary',
    'link' => null,
    'trend' => null,
])

@php
    $typeClasses = [
        'primary' => 'stu-stat-icon-primary',
        'success' => 'stu-stat-icon-success',
        'warning' => 'stu-stat-icon-warning',
        'danger' => 'stu-stat-icon-danger',
        'info' => 'stu-stat-icon-info',
    ];
    $iconClass = $typeClasses[$type] ?? $typeClasses['primary'];
@endphp

<div {{ $attributes->merge(['class' => 'stu-stat-card']) }}>
    <div class="stu-stat-content">
        <span class="stu-stat-label">{{ $title }}</span>
        <span class="stu-stat-value">{{ $value }}</span>
        
        @if($subtitle)
            <span class="stu-stat-trend" style="color: var(--color-text-muted);">
                {{ $subtitle }}
            </span>
        @elseif($trend)
            <span class="stu-stat-trend" style="color: var(--color-success);">
                {{ $trend }}
            </span>
        @endif

        @if($link)
            <a href="{{ $link }}" style="margin-top: var(--space-2); font-size: var(--font-size-xs); font-weight: var(--font-weight-medium); display: inline-flex; align-items: center; gap: 4px;">
                Xem chi tiết
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>
        @endif
    </div>

    <div class="stu-stat-icon-wrapper {{ $iconClass }}">
        {{ $slot }}
    </div>
</div>
