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
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
            <div>
                <a href="/">
                    <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
