@extends('layouts.app')

@section('title', 'Test')
@section('page-title', 'Test')

@section('content')
    <div class="container-xl">
     
        
<input
    type="hidden"
    id="route-locations"
    value='@json($locations)'
>

<div
    id="map"
    style="width: 100%; height: 600px;"
></div>




    </div>
@endsection

@push('scripts')
    @vite('resources/js/test.js')
        <script
        src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&callback=initMap"
        async
        defer
    ></script>
@endpush
