<div
    class="modal modal-blur fade"
    id="user-form-modal"
    tabindex="-1"
    aria-labelledby="user-modal-title"
    aria-hidden="true"
>
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered" role="document">
        <div class="modal-content">
            <form
                id="user-admin-form"
                method="POST"
                action="{{ route('users.store') }}"
                data-store-url="{{ route('users.store') }}"
            >
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title" id="user-modal-title">
                        <i class="ti ti-user-plus me-2"></i>
                        Nuevo usuario
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                    @include('users._form')
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-link" data-bs-dismiss="modal">
                        <i class="ti ti-x me-1"></i>
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-device-floppy me-1"></i>
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
