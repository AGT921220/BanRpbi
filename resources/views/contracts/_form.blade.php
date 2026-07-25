@php
    /** @var \App\Models\Contract|null $contract */
    $contract ??= null;
    $frequencyLabels = \App\Models\Contract::frequencyLabels();
@endphp

<x-form.input
    name="name"
    label="Nombre"
    icon="ti ti-file-description"
    :value="old('name', $contract?->name)"
    required
/>

<x-form.input
    name="duration_months"
    type="number"
    label="Duración (meses)"
    icon="ti ti-calendar"
    :value="old('duration_months', $contract?->duration_months ?? 12)"
    required
    min="1"
    max="120"
/>

<div class="mb-3">
    <label class="form-label required" for="contract-frequency">Frecuencia de recolección</label>
    <div class="input-icon">
        <span class="input-icon-addon">
            <i class="ti ti-recycle"></i>
        </span>
        <select
            name="frequency"
            id="contract-frequency"
            class="form-select @error('frequency') is-invalid @enderror"
            required
        >
            @foreach ($frequencyLabels as $value => $label)
                <option
                    value="{{ $value }}"
                    @selected(old('frequency', $contract?->frequency ?? 'monthly') === $value)
                >
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>
    @error('frequency')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label class="form-label" for="contract-notes">Notas</label>
    <div class="input-icon">
        <span class="input-icon-addon align-items-start pt-2">
            <i class="ti ti-notes"></i>
        </span>
        <textarea
            name="notes"
            id="contract-notes"
            class="form-control @error('notes') is-invalid @enderror"
            rows="3"
            maxlength="2000"
        >{{ old('notes', $contract?->notes) }}</textarea>
    </div>
    @error('notes')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>
