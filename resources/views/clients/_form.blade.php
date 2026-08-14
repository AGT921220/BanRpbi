@php
    /** @var \App\Models\Client|null $client */
    $client ??= null;
@endphp

<x-form.input
    name="name"
    label="Nombre"
    icon="ti ti-user"
    :value="old('name', $client?->name)"
    required
/>

<x-form.input
    name="parentarl_surname"
    label="Apellido paterno"
    icon="ti ti-signature"
    :value="old('parentarl_surname', $client?->parentarl_surname)"
    required
/>

<x-form.input
    name="email"
    type="email"
    label="Correo electrónico"
    icon="ti ti-mail"
    :value="old('email', $client?->email)"
    autocomplete="email"
    required
/>

<x-form.input
    name="phone"
    type="tel"
    label="Teléfono"
    icon="ti ti-phone"
    :value="old('phone', $client?->phone)"
    autocomplete="tel"
    required
/>

<x-form.input
    name="company"
    label="Empresa"
    icon="ti ti-building"
    :value="old('company', $client?->company)"
    required
/>

<x-form.input
    name="nra"
    label="NRA"
    icon="ti ti-license"
    :value="old('nra', $client?->nra)"
    help="(Número de registro ambiental)"
    required
/>

<x-form.input
    name="rfc"
    label="RFC"
    icon="ti ti-id"
    :value="old('rfc', $client?->rfc)"
    maxlength="13"
    style="text-transform: uppercase"
    required
/>

<hr class="my-4">

<div class="mb-3">
    <h4 class="mb-1">
        <i class="ti ti-map-pin me-1"></i>
        Dirección
    </h4>
    <p class="text-secondary mb-0">
        Busca la dirección con Google Maps para autocompletar los campos.
    </p>
</div>

<div class="mb-3">
    <label class="form-label" for="client-address-search">Buscar dirección</label>
    <div class="input-icon">
        <span class="input-icon-addon">
            <i class="ti ti-search"></i>
        </span>
        <input
            type="text"
            id="client-address-search"
            class="form-control"
            placeholder="Escribe una dirección y selecciona una sugerencia"
            autocomplete="off"
        >
    </div>
    <div class="form-hint">Puedes buscar calles, colonias o residenciales. Al seleccionar una sugerencia se llenarán los campos de dirección.</div>
    <div id="client-address-search-error" class="alert alert-warning mt-2 d-none" role="alert"></div>
    @if (old('maps_url', $client?->maps_url))
        <div class="form-hint mt-1">
            <a href="{{ old('maps_url', $client?->maps_url) }}" target="_blank" rel="noopener noreferrer">
                Ver ubicación en Google Maps
            </a>
        </div>
    @endif
</div>

<input type="hidden" name="maps_url" id="client-maps-url" value="{{ old('maps_url', $client?->maps_url) }}">
<input type="hidden" name="maps_place_id" id="client-maps-place-id" value="{{ old('maps_place_id', $client?->maps_place_id) }}">
<input type="hidden" name="latitude" id="client-latitude" value="{{ old('latitude', $client?->latitude) }}">
<input type="hidden" name="longitude" id="client-longitude" value="{{ old('longitude', $client?->longitude) }}">

<x-form.input
    name="street"
    id="client-street"
    label="Calle"
    icon="ti ti-road"
    :value="old('street', $client?->street)"
    required
/>

<div class="row">
    <div class="col-md-6">
        <x-form.input
            name="num_ext"
            id="client-num-ext"
            label="Número exterior"
            icon="ti ti-door"
            :value="old('num_ext', $client?->num_ext)"
        />
    </div>
    <div class="col-md-6">
        <x-form.input
            name="num_int"
            id="client-num-int"
            label="Número interior"
            icon="ti ti-door-enter"
            :value="old('num_int', $client?->num_int)"
        />
    </div>
</div>

<x-form.input
    name="postal_code"
    id="client-postal-code"
    label="Código postal"
    icon="ti ti-mail-opened"
    :value="old('postal_code', $client?->postal_code)"
    maxlength="10"
    required
/>

<x-form.input
    name="colony"
    id="client-colony"
    label="Colonia"
    icon="ti ti-building-community"
    :value="old('colony', $client?->colony)"
/>

<x-form.input
    name="city"
    id="client-city"
    label="Municipio"
    icon="ti ti-building"
    :value="old('city', $client?->city?->name)"
/>

<x-form.input
    name="state"
    id="client-state"
    label="Estado"
    icon="ti ti-map-2"
    :value="old('state', $client?->state?->name)"
/>

<script type="application/json" id="states-cities-catalog">
    @json($statesCities ?? [])
</script>
