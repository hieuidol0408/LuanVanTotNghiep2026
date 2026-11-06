@props([
    'id' => '',
    'title' => 'Thông báo',
    'size' => 'md',
    'footer' => null,
])

@php
    $sizeClass = match($size) {
        'lg'    => 'stu-modal-lg',
        'xl'    => 'stu-modal-xl',
        default => '',
    };
@endphp

<div class="stu-modal-backdrop" id="{{ $id }}" role="dialog" aria-modal="true" aria-labelledby="{{ $id }}_title">
    <div class="stu-modal {{ $sizeClass }}">
        <div class="stu-modal-header">
            <h3 class="stu-modal-title" id="{{ $id }}_title">{{ $title }}</h3>
            <button type="button" class="stu-modal-close" data-modal-close aria-label="Đóng cửa sổ">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="stu-modal-body">
            {{ $slot }}
        </div>

        @if($footer)
            <div class="stu-modal-footer">
                {{ $footer }}
            </div>
        @endif
    </div>
</div>
