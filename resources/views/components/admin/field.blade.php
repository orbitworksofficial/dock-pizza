@props([
    'name',
    'label' => null,
    'hint' => null,
    'required' => false,
    'counter' => null,   // ['warn' => 60, 'max' => 70] to show a live counter
    'value' => null,
])

@php
    $hasError = $errors->has($name);
    // Dot notation for array fields: faqs.0.question -> faqs[0][question]
    $id = str_replace(['.', '_'], ['-', '-'], $name);
@endphp

<div class="field {{ $hasError ? 'has-error' : '' }}"
     @if($counter) x-data="charCounter(@js(old($name, $value ?? '')), {{ $counter['warn'] }}, {{ $counter['max'] }})" @endif>

    @if($label)
        @if($counter)
            <div class="field__labelrow">
                <label class="field__label" for="{{ $id }}">
                    {{ $label }}@if($required)<span class="req">*</span>@endif
                </label>
                <span class="field__counter" :class="counterClass"
                      x-text="`${count} / {{ $counter['warn'] }}`"></span>
            </div>
        @else
            <label class="field__label" for="{{ $id }}">
                {{ $label }}@if($required)<span class="req">*</span>@endif
            </label>
        @endif
    @endif

    {{ $slot }}

    {{-- Errors render inline, next to the field they belong to --}}
    @error($name)
        <div class="field__error">
            <i class="fa-solid fa-circle-exclamation" style="margin-top:1px;"></i>
            <span>{{ $message }}</span>
        </div>
    @enderror

    @if($hint)
        <div class="field__hint">{!! $hint !!}</div>
    @endif
</div>
