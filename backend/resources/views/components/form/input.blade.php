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
        <x-form.label :for="$inputId" :required="$required">
            {{ $label }}
        </x-form.label>
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

    <x-form.error :field="$name" />
</div>
