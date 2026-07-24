@props([
    'name',
    'label' => null,
    'type' => 'text',
    'icon' => 'ti ti-forms',
    'value' => null,
    'required' => false,
    'disabled' => false,
    'autocomplete' => null,
    'help' => null,
    'id' => null,
])

@php
    $inputId = $id ?? $name;
    $hasError = $errors->has($name);
@endphp

<div class="mb-3">
    @if ($label)
        <label for="{{ $inputId }}" @class(['form-label', 'required' => $required])>
            {{ $label }}
            @if ($help)
                <span class="text-secondary">{{ $help }}</span>
            @endif
        </label>
    @endif

    <div class="input-icon">
        <span class="input-icon-addon">
            <i class="{{ $icon }}"></i>
        </span>

        <input
            {{ $attributes->merge([
                'type' => $type,
                'name' => $name,
                'id' => $inputId,
                'class' => 'form-control'.($hasError ? ' is-invalid' : ''),
            ])->except(['required', 'disabled']) }}
            value="{{ $type === 'password' ? old($name) : old($name, $value) }}"
            @if ($required) required @endif
            @if ($disabled) disabled @endif
            @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
        >
    </div>

    @error($name)
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>
