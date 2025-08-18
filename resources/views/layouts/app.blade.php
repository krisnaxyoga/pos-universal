<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#1f2937">

        <title>{{ $appSettings['app_name'] ?? config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        
        <!-- Icons -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <style>
            body { font-family: 'Inter', sans-serif; }
            
            /* Mobile optimizations */
            @media (max-width: 768px) {
                .mobile-full { width: 100vw; margin-left: calc(-50vw + 50%); }
                .mobile-padding { padding-left: 1rem; padding-right: 1rem; }
            }
            
            /* Custom scrollbar */
            ::-webkit-scrollbar { width: 6px; }
            ::-webkit-scrollbar-track { background: #f1f1f1; }
            ::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 3px; }
            ::-webkit-scrollbar-thumb:hover { background: #a8a8a8; }
            
            /* Performance optimizations */
            * {
                will-change: auto;
            }
            
            /* Smooth transitions - reduced for better performance */
            input, select, textarea, button { transition: all 0.2s ease; }
            
            /* Instant page loads - no fade effects */
            body, html {
                opacity: 1 !important;
                visibility: visible !important;
            }
            
            /* Disable all page transition effects */
            body.loading, html.loading {
                opacity: 1 !important;
                pointer-events: auto !important;
            }
            
            /* Remove any loading overlays */
            .loading-overlay, .page-loader {
                display: none !important;
            }
            
            /* Enhanced Glass Effects - optimized for performance */
            .glass { 
                backdrop-filter: blur(8px);
                background: rgba(255, 255, 255, 0.9);
                border: 1px solid rgba(255, 255, 255, 0.3);
                box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            }
            
            .dark .glass { 
                backdrop-filter: blur(8px);
                background: rgba(17, 24, 39, 0.9);
                border: 1px solid rgba(255, 255, 255, 0.1);
                box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
            }
            
            /* Light Mode Color Improvements */
            .text-gray-900 { color: #111827 !important; }
            .text-gray-800 { color: #1f2937 !important; }
            .text-gray-700 { color: #374151 !important; }
            .text-gray-600 { color: #4b5563 !important; }
            .text-gray-500 { color: #6b7280 !important; }
            .text-gray-400 { color: #9ca3af !important; }
            
            /* Dark Mode Color Improvements */
            .dark .text-gray-900 { color: #f9fafb !important; }
            .dark .text-gray-800 { color: #f3f4f6 !important; }
            .dark .text-gray-700 { color: #e5e7eb !important; }
            .dark .text-gray-600 { color: #d1d5db !important; }
            .dark .text-gray-500 { color: #9ca3af !important; }
            .dark .text-gray-400 { color: #6b7280 !important; }
            
            /* Enhanced Text Readability */
            .text-primary { color: #111827 !important; }
            .text-secondary { color: #4b5563 !important; }
            .text-muted { color: #6b7280 !important; }
            
            .dark .text-primary { color: #f9fafb !important; }
            .dark .text-secondary { color: #d1d5db !important; }
            .dark .text-muted { color: #9ca3af !important; }
            
            /* Form Elements Enhanced */
            input, select, textarea {
                color: #111827 !important;
                background-color: rgba(255, 255, 255, 0.9) !important;
                border-color: #d1d5db !important;
            }
            
            .dark input, .dark select, .dark textarea {
                color: #f9fafb !important;
                background-color: rgba(31, 41, 55, 0.9) !important;
                border-color: #4b5563 !important;
            }
            
            /* Button Enhancements */
            .btn-primary {
                background: linear-gradient(135deg, #3b82f6, #1d4ed8);
                border: none;
                box-shadow: 0 4px 14px rgba(59, 130, 246, 0.3);
                transition: all 0.3s ease;
            }
            
            .btn-primary:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
            }
            
            /* Navigation Enhancements - optimized */
            nav {
                backdrop-filter: blur(6px);
                background: rgba(255, 255, 255, 0.95) !important;
                border-bottom: 1px solid rgba(0, 0, 0, 0.1) !important;
            }
            
            .dark nav {
                background: rgba(17, 24, 39, 0.95) !important;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
            }
            
            /* Card & Content Areas */
            .glass p, .glass span, .glass div, .glass h1, .glass h2, .glass h3, .glass h4, .glass h5, .glass h6 {
                color: #111827 !important;
            }
            
            .dark .glass p, .dark .glass span, .dark .glass div, .dark .glass h1, .dark .glass h2, .dark .glass h3, .dark .glass h4, .dark .glass h5, .dark .glass h6 {
                color: #f9fafb !important;
            }
            
            /* Table Improvements */
            table {
                background: rgba(255, 255, 255, 0.7);
                border-radius: 12px;
                overflow: hidden;
            }
            
            .dark table {
                background: rgba(17, 24, 39, 0.7);
            }
            
            table td, table th {
                color: #111827 !important;
                border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            }
            
            .dark table td, .dark table th {
                color: #f9fafb !important;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            }
            
            /* Hover Effects */
            .hover-lift:hover {
                transform: translateY(-2px);
                transition: transform 0.2s ease;
            }
            
            /* Scrollbar Improvements */
            .dark ::-webkit-scrollbar-track { background: #374151; }
            .dark ::-webkit-scrollbar-thumb { background: #6b7280; }
            .dark ::-webkit-scrollbar-thumb:hover { background: #9ca3af; }
            
            /* Badge & Status Improvements */
            .badge {
                padding: 4px 12px;
                border-radius: 20px;
                font-size: 0.75rem;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            
            /* Animation Improvements - removed fade-in for better performance */
            
            /* Focus Improvements */
            input:focus, select:focus, textarea:focus, button:focus {
                outline: none;
                ring: 2px;
                ring-color: #3b82f6;
                ring-opacity: 0.5;
            }
        </style>
    </head>
    <body class="font-sans antialiased h-full bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 dark:from-gray-900 dark:via-blue-900 dark:to-indigo-900">
        <div class="min-h-screen flex flex-col">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="glass border-b border-white/20 shadow-sm sticky top-16 z-40">
                    <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Mobile-friendly notifications -->
            @if(session('success') || session('error'))
                <div class="fixed top-20 left-4 right-4 z-50 md:left-auto md:right-6 md:w-96">
                    @if(session('success'))
                        <div class="mb-4 bg-green-500 text-white px-4 py-3 rounded-lg shadow-lg flex items-center animate-pulse">
                            <i class="fas fa-check-circle mr-3"></i>
                            <span>{{ session('success') }}</span>
                            <button onclick="this.parentElement.remove()" class="ml-auto text-green-200 hover:text-white">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mb-4 bg-red-500 text-white px-4 py-3 rounded-lg shadow-lg flex items-center animate-pulse">
                            <i class="fas fa-exclamation-circle mr-3"></i>
                            <span>{{ session('error') }}</span>
                            <button onclick="this.parentElement.remove()" class="ml-auto text-red-200 hover:text-white">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Page Content -->
            <main class="flex-1 max-w-7xl mx-auto w-full py-6 px-4 sm:px-6 lg:px-8">
                {{ $slot }}
            </main>

        </div>

        <!-- Auto-hide notifications after 5 seconds -->
        <script>
            setTimeout(function() {
                const notifications = document.querySelectorAll('.animate-pulse');
                notifications.forEach(function(notification) {
                    notification.style.opacity = '0';
                    setTimeout(function() {
                        notification.remove();
                    }, 300);
                });
            }, 5000);
        </script>
    </body>
</html>
