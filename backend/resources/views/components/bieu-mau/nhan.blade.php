@props([
    'for' => null,
    'required' => false,
])

<label @if($for) for="{{ $for }}" @endif {{ $attributes->merge(['class' => 'stu-label']) }}>
    <span>{{ $slot }}</span>
    @if($required)
        <span class="stu-required" title="Trường bắt buộc nhập">*</span>
    @endif
</label>
