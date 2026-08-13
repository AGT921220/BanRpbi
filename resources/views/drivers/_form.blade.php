@php
    /** @var \App\Models\Driver|null $driver */
    $driver ??= null;
    /** @var \Illuminate\Support\Collection<int, \App\Models\Zone> $zones */
    $zones ??= collect();
@endphp

<x-form.input
    name="name"
    label="Nombre"
    icon="ti ti-user"
    :value="old('name', $driver?->name)"
    required
/>

<x-form.input
    name="parentarl_surname"
    label="Apellido paterno"
    icon="ti ti-signature"
    :value="old('parentarl_surname', $driver?->parentarl_surname)"
    required
/>

<x-form.input
    name="maternal_surname"
    label="Apellido materno"
    icon="ti ti-signature"
    :value="old('maternal_surname', $driver?->maternal_surname)"
    required
/>

<x-form.input
    name="phone"
    type="tel"
    label="Teléfono"
    icon="ti ti-phone"
    :value="old('phone', $driver?->phone)"
    autocomplete="tel"
    required
/>

<div class="mb-3">
    <label class="form-label required" for="driver-zone-id">Zona</label>
    <div class="input-icon">
        <span class="input-icon-addon">
            <i class="ti ti-map-2"></i>
        </span>
        <select
            name="zone_id"
            id="driver-zone-id"
            class="form-select @error('zone_id') is-invalid @enderror"
            required
        >
            <option value="">Selecciona una zona</option>
            @foreach ($zones as $zone)
                <option
                    value="{{ $zone->id }}"
                    @selected((string) old('zone_id', $driver?->zone_id) === (string) $zone->id)
                >
                    {{ $zone->name }}
                </option>
            @endforeach
        </select>
    </div>
    @error('zone_id')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>
