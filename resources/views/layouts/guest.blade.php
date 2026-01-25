<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>CODEDE - @yield('title', 'Iniciar Sesión')</title>

    <!-- Icon -->
    <link rel="icon" href="{{ asset('img/icono.png') }}" type="image/png">

    {{-- Scripts --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="bg-base-200 min-h-screen flex items-center justify-center">
    {{ $slot }}

    @livewireScripts
</body>

</html>
