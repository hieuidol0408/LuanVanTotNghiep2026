@props([
    'type' => 'info',
    'title' => null,
    'message' => null,
    'dismissible' => true,
])

@php
    $typeClass = match($type) {
        'success' => 'stu-alert-success',
        'warning' => 'stu-alert-warning',
        'danger'  => 'stu-alert-danger',
        default   => 'stu-alert-info',
    };
@endphp

<div {{ $attributes->merge(['class' => "stu-alert {$typeClass}"]) }} role="alert">
    <div class="stu-alert-icon">
        @if($type === 'success')
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        @elseif($type === 'warning')
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        @elseif($type === 'danger')
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        @else
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        @endif
    </div>

    <div class="stu-alert-content">
        @if($title)
            <div class="stu-alert-title">{{ $title }}</div>
        @endif
        <div>{{ $message ?? $slot }}</div>
    </div>

    @if($dismissible)
        <button type="button" onclick="this.closest('.stu-alert').remove()" style="background:none;border:none;cursor:pointer;color:inherit;opacity:0.7;padding:2px;" aria-label="Đóng thông báo">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    @endif
</div>
