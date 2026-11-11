@props([
    'name' => '',
    'id' => null,
    'label' => null,
    'value' => null,
    'rows' => 4,
    'required' => false,
    'placeholder' => null,
    'hint' => null,
    'disabled' => false,
])

@php
    $textareaId = $id ?? $name ?? uniqid('stu_textarea_');
    $hasError = $errors->has($name);
    $classes = 'stu-textarea' . ($hasError ? ' is-invalid' : '');
@endphp

<div class="stu-form-group">
    @if($label)
        <x-bieu-mau.nhan :for="$textareaId" :required="$required">
            {{ $label }}
        </x-bieu-mau.nhan>
    @endif

    <textarea 
        name="{{ $name }}" 
        id="{{ $textareaId }}" 
        rows="{{ $rows }}"
        @if($placeholder) placeholder="{{ $placeholder }}" @endif
        @if($required) required @endif
        @if($disabled) disabled @endif
        {{ $attributes->merge(['class' => $classes]) }}
    >{{ old($name, $value) }}</textarea>

    @if($hint)
        <div class="stu-form-hint">{{ $hint }}</div>
    @endif

    <x-bieu-mau.loi :field="$name" />
</div>
