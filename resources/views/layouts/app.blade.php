<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Partify') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <title>Partify</title>
        <link rel="icon" type="image/png" href="{{ asset('images/partify.png') }}">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <style>
            /* Partify Kék - A logód alapján */
            :root {
                --partify-blue: #0066FF; /* Élénk kék */
                --partify-dark: #0f172a; /* Mély sötétkék/fekete */
            }
            .text-brand-blue { color: var(--partify-blue); }
            .bg-brand-blue { background-color: var(--partify-blue); }
            
            [x-cloak] { display: none !important; }
        </style>
    </head>
    <body class="font-sans antialiased bg-gray-900 text-gray-100">
        <div class="min-h-screen bg-gray-900">
            @include('layouts.navigation')

            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>