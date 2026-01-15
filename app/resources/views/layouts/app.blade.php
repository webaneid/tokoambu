<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @php
            $storeName = \App\Models\Setting::get('store_name', config('app.name', 'Laravel'));
            $faviconMediaId = \App\Models\Setting::get('favicon_media_id');
            $faviconMedia = $faviconMediaId ? \App\Models\Media::find($faviconMediaId) : null;
        @endphp

        <title>{{ $storeName }}</title>

        @if($faviconMedia)
            <link rel="icon" type="image/x-icon" href="{{ $faviconMedia->url }}">
            <link rel="shortcut icon" type="image/x-icon" href="{{ $faviconMedia->url }}">
        @endif

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/scss/app.scss', 'resources/js/app.js'])

        <!-- Additional Styles -->
        @stack('styles')
    </head>
    <body class="font-sans antialiased">
        @php($errors = $errors ?? new \Illuminate\Support\ViewErrorBag)
        @include('layouts.navigation')

        <!-- Additional Scripts -->
        @stack('scripts')
    </body>
</html>
