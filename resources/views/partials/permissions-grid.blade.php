<div class="row g-3">
    @foreach ($groupedPermissions as $module => $permissions)
        <div class="col-12">
            <div class="card card-sm">
                <div class="card-header">
                    <h4 class="card-title">{{ $moduleLabels[$module] ?? ucfirst(str_replace('_', ' ', $module)) }}</h4>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        @foreach ($permissions as $permission)
                            @php
                                $isLocked = $lockedPermissions->contains($permission['name']);
                                $isChecked = $selectedPermissions->contains($permission['name']) || $isLocked;
                            @endphp
                            <div class="col-md-6 col-xl-4">
                                <label class="form-check {{ $isLocked ? 'text-secondary' : '' }}">
                                    <input
                                        class="form-check-input {{ $checkboxClass ?? 'permission-checkbox' }}"
                                        type="checkbox"
                                        name="{{ $inputName }}"
                                        value="{{ $permission['name'] }}"
                                        data-permission="{{ $permission['name'] }}"
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
