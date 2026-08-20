@php
    /** @var \App\Models\Driver|null $driver */
    $driver ??= null;
    /** @var \Illuminate\Support\Collection<int, \App\Models\User> $users */
    $users ??= collect();
@endphp

<div class="mb-3">
    <label class="form-label required" for="driver-user-id">Usuario</label>
    <div class="input-icon">
        <span class="input-icon-addon">
            <i class="ti ti-user-circle"></i>
        </span>
        <select
            name="user_id"
            id="driver-user-id"
            class="form-select @error('user_id') is-invalid @enderror"
            required
        >
            <option value="">Selecciona un usuario</option>
            @foreach ($users as $user)
                <option
                    value="{{ $user->id }}"
                    @selected((string) old('user_id', $driver?->user_id) === (string) $user->id)
                >
                    {{ $user->name }} ({{ $user->email }})
                </option>
            @endforeach
        </select>
    </div>
    @error('user_id')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>

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
