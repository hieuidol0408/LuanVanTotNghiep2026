@props([
    'type' => 'text',
    'name' => '',
    'id' => null,
    'label' => null,
    'value' => null,
    'required' => false,
    'placeholder' => null,
    'hint' => null,
    'disabled' => false,
])

@php
    $inputId = $id ?? $name ?? uniqid('stu_input_');
    $hasError = $errors->has($name);
    $inputClasses = 'stu-input' . ($hasError ? ' is-invalid' : '');
@endphp

<div class="stu-form-group">
    @if($label)
        <x-bieu-mau.nhan :for="$inputId" :required="$required">
            {{ $label }}
        </x-bieu-mau.nhan>
    @endif

    <input 
        type="{{ $type }}" 
        name="{{ $name }}" 
        id="{{ $inputId }}" 
        value="{{ old($name, $value) }}"
        @if($placeholder) placeholder="{{ $placeholder }}" @endif
        @if($required) required @endif
        @if($disabled) disabled @endif
        {{ $attributes->merge(['class' => $inputClasses]) }}
    >

    @if($hint)
        <div class="stu-form-hint">{{ $hint }}</div>
    @endif

    <x-bieu-mau.loi :field="$name" />
</div>
