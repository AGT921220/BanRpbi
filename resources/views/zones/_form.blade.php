@php
    /** @var \App\Models\Zone|null $zone */
    $zone ??= null;
@endphp

<div class="row g-3">
    <div class="col-lg-4">
        <x-form.input
            name="name"
            label="Nombre"
            icon="ti ti-map-pin"
            :value="old('name', $zone?->name)"
            required
        />

        <div class="mb-3">
            <label class="form-label" for="zone-description">Descripción</label>
            <div class="input-icon">
                <span class="input-icon-addon align-items-start pt-2">
                    <i class="ti ti-notes"></i>
                </span>
                <textarea
                    name="description"
                    id="zone-description"
                    class="form-control @error('description') is-invalid @enderror"
                    rows="4"
                    maxlength="2000"
                >{{ old('description', $zone?->description) }}</textarea>
            </div>
            @error('description')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <x-form.input
            name="color"
            type="color"
            label="Color"
            icon="ti ti-palette"
            :value="old('color', $zone?->color ?: '#206bc4')"
        />

        <div class="mb-3">
            <label class="form-check form-switch">
                <input type="hidden" name="is_active" value="0">
                <input
                    class="form-check-input"
                    type="checkbox"
                    name="is_active"
                    value="1"
                    @checked(old('is_active', $zone?->is_active ?? true))
                >
                <span class="form-check-label">Zona activa</span>
            </label>
        </div>

        <div class="alert alert-info" role="alert">
            Haz clic en el mapa para marcar los límites de la zona. Cierra el polígono seleccionando nuevamente el primer punto.
        </div>

        <div class="mb-2">
            <span class="text-secondary">Vértices:</span>
            <strong id="zone-vertex-count">0</strong>
        </div>

        <div id="zone-overlap-warning" class="alert alert-warning d-none" role="alert">
            Esta zona podría superponerse con otra zona existente. Por ahora se permite guardar; la regla definitiva de superposición está pendiente.
        </div>

        @error('geometry')
            <div class="alert alert-danger" role="alert">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-8">
        <div class="btn-list mb-3 flex-wrap">
            <button type="button" class="btn btn-outline-primary" id="zone-draw-btn">
                <i class="ti ti-pencil me-1"></i>
                Dibujar zona
            </button>
            <button type="button" class="btn btn-outline-secondary" id="zone-edit-btn">
                <i class="ti ti-vector-bezier me-1"></i>
                Editar zona
            </button>
            <button type="button" class="btn btn-outline-danger" id="zone-delete-polygon-btn">
                <i class="ti ti-trash me-1"></i>
                Eliminar polígono
            </button>
            <button type="button" class="btn btn-outline-secondary" id="zone-center-btn">
                <i class="ti ti-focus-2 me-1"></i>
                Centrar mapa
            </button>
        </div>

        <div
            id="zone-map"
            class="border rounded"
            style="width: 100%; height: min(70vh, 550px); min-height: 320px;"
        ></div>

        <div id="zone-map-error" class="alert alert-danger mt-3 d-none" role="alert"></div>
    </div>
</div>

<input
    type="hidden"
    name="geometry"
    id="zone-geometry"
    value="{{ old('geometry') ? (is_string(old('geometry')) ? old('geometry') : json_encode(old('geometry'))) : ($zone?->geometry ? json_encode($zone->geometry) : '') }}"
>

<script type="application/json" id="existing-zones-data">
    @json($existingZones ?? [])
</script>

@if ($zone?->geometry)
    <script type="application/json" id="current-zone-geometry">
        @json($zone->geometry)
    </script>
@endif
