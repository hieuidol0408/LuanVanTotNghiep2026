@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'href' => null,
    'disabled' => false,
])

@php
    $variantClass = match($variant) {
        'secondary' => 'stu-btn-secondary',
        'outline'   => 'stu-btn-outline',
        'danger'    => 'stu-btn-danger',
        default     => 'stu-btn-primary',
    };

    $sizeClass = match($size) {
        'sm'    => 'stu-btn-sm',
        'lg'    => 'stu-btn-lg',
        default => '',
    };

    $classes = trim("stu-btn {$variantClass} {$sizeClass}");
@endphp

@if($href)
    <a href="{{ $disabled ? '#' : $href }}" {{ $attributes->merge(['class' => $classes]) }} @if($disabled) aria-disabled="true" @endif>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }} @if($disabled) disabled @endif>
        {{ $slot }}
    </button>
@endif
