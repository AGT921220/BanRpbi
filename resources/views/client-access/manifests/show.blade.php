<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Manifiesto | BAN RPBI</title>
    @vite(['resources/css/app.css', 'resources/js/modules/manifests/publicManifest.js'])
</head>

<body>
    <div id="public-manifest" data-url="{{ route('client-access.manifests.show', $manifestPublicUuid) }}"
        data-loader="Creando tu manifiesto"></div>
    <iframe id="manifest-frame" title="Manifiesto" style="width: 100%; height: 100vh; border: 0;"></iframe>
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1090;" aria-live="polite"
        aria-atomic="true"></div>
</body>

</html>
