<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Iniciar sesión | BAN RPBI</title>
    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])
</head>

<body class="bg-light">
    <main class="min-vh-100 d-flex align-items-center justify-content-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-sm-10 col-md-7 col-lg-5 col-xl-4">

                    <div class="text-center mb-4">
                        <h1 class="h2 mb-1">BAN RPBI</h1>
                        <p class="text-secondary mb-0">
                            Sistema de gestión de residuos
                        </p>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-body p-4">
                            <h2 class="h4 text-center mb-4">
                                <i class="ti ti-login me-1"></i>
                                Iniciar sesión
                            </h2>

                            @if ($errors->any())
                                <div class="alert alert-danger" role="alert">
                                    <ul class="mb-0 ps-3">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if (session('status'))
                                <div class="alert alert-success" role="alert">
                                    {{ session('status') }}
                                </div>
                            @endif

                            <form method="POST" action="{{ route('login') }}">
                                @csrf

                                <x-form.input
                                    name="email"
                                    label="Correo electrónico"
                                    type="email"
                                    icon="ti ti-mail"
                                    :value="old('email')"
                                    required
                                    autofocus
                                    autocomplete="email"
                                />

                                <x-form.input
                                    name="password"
                                    label="Contraseña"
                                    type="password"
                                    icon="ti ti-lock"
                                    required
                                    autocomplete="current-password"
                                />

                                <div class="form-check mb-4">
                                    <input
                                        id="remember"
                                        type="checkbox"
                                        name="remember"
                                        class="form-check-input"
                                    >
                                    <label for="remember" class="form-check-label">
                                        Mantener sesión iniciada
                                    </label>
                                </div>

                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="ti ti-login me-1"></i>
                                    Iniciar sesión
                                </button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </main>
</body>
</html>
