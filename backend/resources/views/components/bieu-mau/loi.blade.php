@props([
    'field' => null,
    'message' => null,
])

@if($field)
    @error($field)
        <div class="stu-form-error">{{ $message }}</div>
    @enderror
@elseif($message)
    <div class="stu-form-error">{{ $message }}</div>
@endif
