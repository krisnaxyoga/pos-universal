<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $appSettings['app_name'] ?? config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
            <div class="flex flex-col items-center">
                <a href="/" class="flex flex-col items-center space-y-2">
                    @if(isset($appSettings['app_logo']) && $appSettings['app_logo'] && Storage::disk('public')->exists($appSettings['app_logo']))
                        <img src="{{ Storage::url($appSettings['app_logo']) }}" alt="{{ $appSettings['app_name'] ?? config('app.name') }}" class="w-20 h-20 object-contain">
                    @else
                        <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-cash-register text-white text-2xl"></i>
                        </div>
                    @endif
                    <h1 class="text-xl font-bold text-gray-700">{{ $appSettings['app_name'] ?? config('app.name', 'POS System') }}</h1>
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
