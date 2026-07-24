<div class="mb-3">
    <label for="name" class="form-label required">Nombre</label>
    <input
        type="text"
        name="name"
        id="name"
        class="form-control @error('name') is-invalid @enderror"
        value="{{ old('name', $user->name ?? '') }}"
        required
        autofocus
    >
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="email" class="form-label required">Correo electrónico</label>
    <input
        type="email"
        name="email"
        id="email"
        class="form-control @error('email') is-invalid @enderror"
        value="{{ old('email', $user->email ?? '') }}"
        required
    >
    @error('email')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="password" class="form-label @empty($user) required @endempty">
        Contraseña
        @isset($user)
            <span class="text-secondary">(dejar vacío para no cambiar)</span>
        @endisset
    </label>
    <input
        type="password"
        name="password"
        id="password"
        class="form-control @error('password') is-invalid @enderror"
        @empty($user) required @endempty
        autocomplete="new-password"
    >
    @error('password')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="password_confirmation" class="form-label @empty($user) required @endempty">
        Confirmar contraseña
    </label>
    <input
        type="password"
        name="password_confirmation"
        id="password_confirmation"
        class="form-control"
        @empty($user) required @endempty
        autocomplete="new-password"
    >
</div>

<div class="mb-0">
    <label class="form-label">Roles</label>
    <div class="row g-2">
        @forelse ($roles as $role)
            <div class="col-md-6">
                <label class="form-check">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        name="roles[]"
                        value="{{ $role->name }}"
                        @checked(collect(old('roles', isset($user) ? $user->roles->pluck('name')->all() : []))->contains($role->name))
                    >
                    <span class="form-check-label">{{ $role->name }}</span>
                </label>
            </div>
        @empty
            <div class="col-12">
                <p class="text-secondary mb-0">No hay roles disponibles.</p>
            </div>
        @endforelse
    </div>
    @error('roles')
        <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
</div>
