@php
    $checkboxClass = $checkboxClass ?? 'permission-checkbox';
    $accordionId = 'permissions-accordion-'.\Illuminate\Support\Str::random(8);
@endphp

<div class="accordion" id="{{ $accordionId }}">
    @foreach ($groupedPermissions as $module => $permissions)
        @php
            $collapseId = $accordionId.'-'.$module;
            $moduleLabel = $moduleLabels[$module] ?? ucfirst(str_replace('_', ' ', $module));
            $hasSelected = collect($permissions)->contains(
                fn (array $permission): bool => $selectedPermissions->contains($permission['name'])
                    || $lockedPermissions->contains($permission['name'])
            );
        @endphp

        <div class="accordion-item permission-group" data-module="{{ $module }}">
            <h2 class="accordion-header">
                <div class="d-flex align-items-center gap-2 px-3 py-2 border-bottom bg-light">
                    <label
                        class="form-check mb-0"
                        onclick="event.stopPropagation()"
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
                        class="accordion-button {{ $hasSelected ? '' : 'collapsed' }} flex-grow-1 py-2 shadow-none"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#{{ $collapseId }}"
                        aria-expanded="{{ $hasSelected ? 'true' : 'false' }}"
                        aria-controls="{{ $collapseId }}"
                    >
                        <span class="me-2">{{ $moduleLabel }}</span>
                        <span class="badge bg-blue-lt permission-group-count" data-group-count="{{ $module }}">
                            0 / {{ count($permissions) }}
                        </span>
                    </button>
                </div>
            </h2>

            <div
                id="{{ $collapseId }}"
                class="accordion-collapse collapse {{ $hasSelected ? 'show' : '' }}"
                data-bs-parent=""
            >
                <div class="accordion-body">
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

@once
    @push('scripts')
    <script>
        (function () {
            function updateGroupState(groupName) {
                const items = document.querySelectorAll(`.permission-item[data-group="${groupName}"]`);
                const toggle = document.querySelector(`.permission-group-toggle[data-group="${groupName}"]`);
                const counter = document.querySelector(`.permission-group-count[data-group-count="${groupName}"]`);

                if (!items.length || !toggle) {
                    return;
                }

                const enabledItems = Array.from(items).filter((item) => !item.disabled);
                const checkedItems = Array.from(items).filter((item) => item.checked);
                const checkedEnabled = enabledItems.filter((item) => item.checked);

                if (counter) {
                    counter.textContent = `${checkedItems.length} / ${items.length}`;
                }

                if (enabledItems.length === 0) {
                    toggle.checked = checkedItems.length === items.length;
                    toggle.indeterminate = false;
                    toggle.disabled = true;
                    return;
                }

                toggle.disabled = false;
                toggle.checked = checkedEnabled.length === enabledItems.length;
                toggle.indeterminate = checkedEnabled.length > 0 && checkedEnabled.length < enabledItems.length;
            }

            function refreshAllGroups() {
                document.querySelectorAll('.permission-group-toggle').forEach((toggle) => {
                    updateGroupState(toggle.dataset.group);
                });
            }

            document.addEventListener('change', function (event) {
                const target = event.target;

                if (target.classList.contains('permission-group-toggle')) {
                    const groupName = target.dataset.group;
                    document
                        .querySelectorAll(`.permission-item[data-group="${groupName}"]`)
                        .forEach((item) => {
                            if (!item.disabled) {
                                item.checked = target.checked;
                            }
                        });
                    updateGroupState(groupName);
                    return;
                }

                if (target.classList.contains('permission-item')) {
                    updateGroupState(target.dataset.group);
                }
            });

            document.addEventListener('permissions:refresh-groups', refreshAllGroups);

            refreshAllGroups();
        })();
    </script>
    @endpush
@endonce
