<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Iniciar sesión | BAN RPBI</title>
    @vite([
        'resources/css/app.css',
        'resources/css/pages/login.css',
        'resources/js/app.js',
    ])
</head>

<body class="login-page" style="--login-bg-image: url('{{ asset('images/background.jpeg') }}')">
    <main class="login-shell">
        <div class="login-backdrop" aria-hidden="true"></div>

        <div class="login-panel">
            <div class="login-brand">
                <img
                    class="login-brand__logo"
                    src="{{ asset('images/logo.png') }}"
                    alt="BAN RPBI"
                    width="72"
                    height="72"
                >
                <h1 class="login-brand__title">BAN RPBI</h1>
                <p class="login-brand__subtitle">
                    Sistema de gestión de residuos
                </p>
            </div>

            <div class="login-card">
                <div class="login-card__body">
                    <h2 class="login-card__heading">
                        <i class="ti ti-login" aria-hidden="true"></i>
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
                                @checked(old('remember'))
                            >
                            <label for="remember" class="form-check-label">
                                Mantener sesión iniciada
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 login-submit">
                            <i class="ti ti-login me-1" aria-hidden="true"></i>
                            Iniciar sesión
                        </button>
                    </form>
                </div>
            </div>

            <p class="login-footer">
                Acceso seguro al panel administrativo
            </p>
        </div>
    </main>
</body>
</html>
