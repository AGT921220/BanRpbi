@php
    /** @var \App\Models\Contract|null $contract */
    $contract ??= null;
    /** @var \Illuminate\Support\Collection<int, \App\Models\RpbiProfile> $rpbiProfiles */
    $rpbiProfiles ??= collect();
    $frequencyLabels = \App\Models\Contract::frequencyLabels();
    $selectedProfileIds = collect(old(
        'profile_ids',
        $contract?->rpbiProfiles?->pluck('id')->all() ?? [],
    ))->map(static fn ($id): int => (int) $id);
@endphp

<x-form.input
    name="name"
    label="Nombre"
    icon="ti ti-file-description"
    :value="old('name', $contract?->name)"
    required
/>

<x-form.input
    name="duration_months"
    type="number"
    label="Duración (meses)"
    icon="ti ti-calendar"
    :value="old('duration_months', $contract?->duration_months ?? 12)"
    required
    min="1"
    max="120"
/>

<x-form.input
    name="cost"
    type="number"
    label="Costo"
    icon="ti ti-cash"
    :value="old('cost', $contract?->cost ?? '0.00')"
    required
    min="0"
    step="0.01"
/>

<div class="mb-3">
    <label class="form-label required" for="contract-frequency">Frecuencia de recolección</label>
    <div class="input-icon">
        <span class="input-icon-addon">
            <i class="ti ti-recycle"></i>
        </span>
        <select
            name="frequency"
            id="contract-frequency"
            class="form-select @error('frequency') is-invalid @enderror"
            required
        >
            @foreach ($frequencyLabels as $value => $label)
                <option
                    value="{{ $value }}"
                    @selected(old('frequency', $contract?->frequency ?? 'monthly') === $value)
                >
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>
    @error('frequency')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
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

<div class="mb-0">
    <label class="form-label required">Perfiles RPBI</label>
    <p class="text-secondary mb-3">
        Selecciona los tipos de residuos RPBI que aplican a este contrato.
    </p>

    <div class="divide-y">
        @forelse ($rpbiProfiles as $profile)
            <label class="row align-items-center py-2 cursor-pointer" for="contract-profile-{{ $profile->id }}">
                <span class="col-auto">
                    <input
                        type="checkbox"
                        class="form-check-input @error('profile_ids') is-invalid @enderror"
                        name="profile_ids[]"
                        id="contract-profile-{{ $profile->id }}"
                        value="{{ $profile->id }}"
                        @checked($selectedProfileIds->contains((int) $profile->id))
                    >
                </span>
                <span class="col">
                    <span class="fw-bold">
                        {{ $profile->code }} — {{ $profile->name }}
                    </span>
                    <span class="d-block text-secondary">
                        {{ $profile->description }}
                    </span>
                </span>
            </label>
        @empty
            <div class="text-secondary py-3">
                No hay perfiles RPBI configurados.
            </div>
        @endforelse
    </div>
    @error('profile_ids')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>
