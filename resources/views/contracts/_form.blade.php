@php
    /** @var \App\Models\Contract|null $contract */
    $contract ??= null;
    $clients ??= collect();
    $statusLabels = \App\Models\Contract::statusLabels();
    $frequencyLabels = \App\Models\Contract::frequencyLabels();
@endphp

<x-form.input
    name="folio"
    label="Folio"
    icon="ti ti-hash"
    :value="old('folio', $contract?->folio)"
    required
/>

<div class="mb-3">
    <label class="form-label required" for="contract-client-id">Cliente</label>
    <div class="input-icon">
        <span class="input-icon-addon">
            <i class="ti ti-building"></i>
        </span>
        <select
            name="client_id"
            id="contract-client-id"
            class="form-select @error('client_id') is-invalid @enderror"
            required
        >
            <option value="">Selecciona un cliente</option>
            @foreach ($clients as $clientOption)
                <option
                    value="{{ $clientOption->id }}"
                    @selected((string) old('client_id', $contract?->client_id) === (string) $clientOption->id)
                >
                    {{ $clientOption->name }} {{ $clientOption->parentarl_surname }}
                    @if ($clientOption->company)
                        — {{ $clientOption->company }}
                    @endif
                </option>
            @endforeach
        </select>
    </div>
    @error('client_id')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>

<x-form.input
    name="name"
    label="Nombre del contrato"
    icon="ti ti-file-description"
    :value="old('name', $contract?->name)"
    required
/>

<div class="row">
    <div class="col-md-6">
        <x-form.input
            name="starts_at"
            type="date"
            label="Fecha de inicio"
            icon="ti ti-calendar"
            :value="old('starts_at', $contract?->starts_at?->format('Y-m-d'))"
            required
        />
    </div>
    <div class="col-md-6">
        <x-form.input
            name="ends_at"
            type="date"
            label="Fecha de finalización"
            icon="ti ti-calendar-event"
            :value="old('ends_at', $contract?->ends_at?->format('Y-m-d'))"
            required
        />
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label class="form-label required" for="contract-status">Estado</label>
            <div class="input-icon">
                <span class="input-icon-addon">
                    <i class="ti ti-flag"></i>
                </span>
                <select
                    name="status"
                    id="contract-status"
                    class="form-select @error('status') is-invalid @enderror"
                    required
                >
                    @foreach ($statusLabels as $value => $label)
                        <option
                            value="{{ $value }}"
                            @selected(old('status', $contract?->status ?? 'draft') === $value)
                        >
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            @error('status')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label class="form-label" for="contract-frequency">Frecuencia de recolección</label>
            <div class="input-icon">
                <span class="input-icon-addon">
                    <i class="ti ti-recycle"></i>
                </span>
                <select
                    name="collection_frequency"
                    id="contract-frequency"
                    class="form-select @error('collection_frequency') is-invalid @enderror"
                >
                    <option value="">Sin definir</option>
                    @foreach ($frequencyLabels as $value => $label)
                        <option
                            value="{{ $value }}"
                            @selected(old('collection_frequency', $contract?->collection_frequency) === $value)
                        >
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            @error('collection_frequency')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
    </div>
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
