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
