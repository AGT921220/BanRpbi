<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel') | BAN RPBI</title>
    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])
    @stack('styles')
</head>

<body>

    <div class="page">
      <!-- BEGIN NAVBAR  -->
       @include('partials.dashboard.header')
      <!-- END NAVBAR  -->
      <div class="page-wrapper">
        <!-- BEGIN PAGE HEADER -->
        <div class="page-header d-print-none" aria-label="Page header">
          <div class="container-xl">
            <div class="row g-2 align-items-center">
              <div class="col">
                <!-- Page pre-title -->
                <h2 class="page-title">Dashboard</h2>
              </div>
              <!-- Page title actions -->
            </div>
          </div>
        </div>
        <!-- END PAGE HEADER -->
        <!-- BEGIN PAGE BODY -->
        <div class="page-body">
          {{-- <div class="container-xl"> --}}
            @yield('content')
          {{-- </div> --}}
        </div>
        <!-- END PAGE BODY -->
        <!--  BEGIN FOOTER  -->
        @include('partials.dashboard.footer')
        <!--  END FOOTER  -->
      </div>
    </div>
    @stack('scripts')
</body>
</html>

