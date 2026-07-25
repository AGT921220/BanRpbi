@php
    $checkboxClass = $checkboxClass ?? 'permission-checkbox';
    $accordionId = 'permissions-accordion-'.\Illuminate\Support\Str::random(8);
@endphp

<div class="permissions-panel mt-2" data-accordion-id="{{ $accordionId }}">
    <div class="d-flex align-items-center justify-content-end gap-2 mb-3">
        <button
            type="button"
            class="btn btn-sm btn-outline-secondary btn-expand-all-groups"
            title="Expandir todos los grupos"
        >
            <i class="ti ti-chevrons-down me-1"></i>
            Expandir todos
        </button>
        <button
            type="button"
            class="btn btn-sm btn-outline-secondary btn-collapse-all-groups"
            title="Contraer todos los grupos"
        >
            <i class="ti ti-chevrons-up me-1"></i>
            Contraer todos
        </button>
    </div>

    <div class="permissions-accordion" id="{{ $accordionId }}">
        @foreach ($groupedPermissions as $module => $permissions)
            @php
                $collapseId = $accordionId.'-'.$module;
                $moduleLabel = $moduleLabels[$module] ?? ucfirst(str_replace('_', ' ', $module));
                $hasSelected = collect($permissions)->contains(
                    fn (array $permission): bool => $selectedPermissions->contains($permission['name'])
                        || $lockedPermissions->contains($permission['name'])
                );
            @endphp

            <div class="permission-group border rounded mb-2" data-module="{{ $module }}">
                <div class="permission-group-header d-flex align-items-center gap-2 px-3 py-2">
                    <label
                        class="form-check mb-0"
                        title="Seleccionar todos los permisos del grupo"
                    >
                        <input
                            class="form-check-input permission-group-toggle"
                            type="checkbox"
                            data-group="{{ $module }}"
                            aria-label="Seleccionar todos en {{ $moduleLabel }}"
                        >
                    </label>

                    <button
                        class="btn btn-ghost-secondary permission-group-toggle-btn flex-grow-1 d-flex align-items-center text-start px-2 py-1 {{ $hasSelected ? '' : 'collapsed' }}"
                        type="button"
                        data-target="#{{ $collapseId }}"
                        aria-expanded="{{ $hasSelected ? 'true' : 'false' }}"
                    >
                        <i class="ti permission-group-chevron {{ $hasSelected ? 'ti-chevron-down' : 'ti-chevron-right' }} me-2"></i>
                        <span class="me-2 fw-medium">{{ $moduleLabel }}</span>
                        <span class="badge bg-blue-lt permission-group-count" data-group-count="{{ $module }}">
                            0 / {{ count($permissions) }}
                        </span>
                    </button>
                </div>

                <div
                    id="{{ $collapseId }}"
                    class="permission-group-collapse {{ $hasSelected ? 'is-open' : '' }}"
                    @if (! $hasSelected) hidden @endif
                    @style(['display: none' => ! $hasSelected])
                >
                    <div class="permission-group-body px-3 pb-3 pt-2">
                        <div class="row g-2">
                            @foreach ($permissions as $permission)
                                @php
                                    $isLocked = $lockedPermissions->contains($permission['name']);
                                    $isChecked = $selectedPermissions->contains($permission['name']) || $isLocked;
                                @endphp
                                <div class="col-md-6 col-xl-4">
                                    <label class="form-check {{ $isLocked ? 'text-secondary' : '' }}">
                                        <input
                                            class="form-check-input {{ $checkboxClass }} permission-item"
                                            type="checkbox"
                                            name="{{ $inputName }}"
                                            value="{{ $permission['name'] }}"
                                            data-permission="{{ $permission['name'] }}"
                                            data-group="{{ $module }}"
                                            @checked($isChecked)
                                            @disabled($isLocked)
                                        >
                                        <span class="form-check-label">
                                            {{ $permission['label'] }}
                                            @if ($isLocked)
                                                <span class="badge bg-secondary-lt ms-1">Rol</span>
                                            @endif
                                        </span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
