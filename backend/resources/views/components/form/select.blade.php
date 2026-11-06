@props([
    'name' => '',
    'id' => null,
    'label' => null,
    'required' => false,
    'placeholder' => null,
    'hint' => null,
    'disabled' => false,
])

@php
    $selectId = $id ?? $name ?? uniqid('stu_select_');
    $hasError = $errors->has($name);
    $selectClasses = 'stu-select' . ($hasError ? ' is-invalid' : '');
@endphp

<div class="stu-form-group">
    @if($label)
        <x-form.label :for="$selectId" :required="$required">
            {{ $label }}
        </x-form.label>
    @endif

    <select 
        name="{{ $name }}" 
        id="{{ $selectId }}"
        @if($required) required @endif
        @if($disabled) disabled @endif
        {{ $attributes->merge(['class' => $selectClasses]) }}
    >
        @if($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        {{ $slot }}
    </select>

    @if($hint)
        <div class="stu-form-hint">{{ $hint }}</div>
    @endif

    <x-form.error :field="$name" />
</div>
