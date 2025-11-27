<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? 'Admin Panel' }} - {{ config('app.name') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Chart.js -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        @stack('styles')
    </head>
    <body class="font-sans antialiased bg-gray-100">
        <div class="min-h-screen flex">
            <!-- Sidebar -->
            <aside class="w-64 bg-gray-900 text-white flex-shrink-0 hidden md:block">
                <div class="p-6">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-2">
                        <svg class="w-8 h-8 text-indigo-400" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path>
                        </svg>
                        <span class="text-xl font-bold">Admin Panel</span>
                    </a>
                </div>
                
                <nav class="mt-4">
                    <a href="{{ route('admin.dashboard') }}" 
                       class="flex items-center px-6 py-3 {{ request()->routeIs('admin.dashboard') ? 'bg-gray-800 border-l-4 border-indigo-500' : 'hover:bg-gray-800' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        Dashboard
                    </a>
                    
                    <a href="{{ route('admin.users.index') }}" 
                       class="flex items-center px-6 py-3 {{ request()->routeIs('admin.users.*') ? 'bg-gray-800 border-l-4 border-indigo-500' : 'hover:bg-gray-800' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        Users
                    </a>
                    
                    <a href="{{ route('admin.posts.index') }}" 
                       class="flex items-center px-6 py-3 {{ request()->routeIs('admin.posts.*') ? 'bg-gray-800 border-l-4 border-indigo-500' : 'hover:bg-gray-800' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                        </svg>
                        Posts
                    </a>
                    
                    <a href="{{ route('admin.comments.index') }}" 
                       class="flex items-center px-6 py-3 {{ request()->routeIs('admin.comments.*') ? 'bg-gray-800 border-l-4 border-indigo-500' : 'hover:bg-gray-800' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                        Comments
                    </a>
                    
                    <div class="border-t border-gray-800 my-4"></div>
                    
                    <a href="{{ route('posts.index') }}" 
                       class="flex items-center px-6 py-3 hover:bg-gray-800 text-gray-400 hover:text-white">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                        View Site
                    </a>
                </nav>
            </aside>

            <!-- Main Content -->
            <div class="flex-1 flex flex-col">
                <!-- Top Bar -->
                <header class="bg-white shadow-sm border-b border-gray-200">
                    <div class="px-6 py-4 flex items-center justify-between">
                        <h1 class="text-xl font-semibold text-gray-800">
                            {{ $header ?? 'Dashboard' }}
                        </h1>
                        
                        <div class="flex items-center space-x-4">
                            <span class="text-sm text-gray-600">{{ auth()->user()->name }}</span>
                            <img class="w-8 h-8 rounded-full object-cover" 
                                 src="{{ auth()->user()->avatar_url }}" 
                                 alt="{{ auth()->user()->name }}">
                        </div>
                    </div>
                </header>

                <!-- Flash Messages -->
                @if(session('success'))
                    <div class="mx-6 mt-4">
                        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                            {{ session('success') }}
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mx-6 mt-4">
                        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                            {{ session('error') }}
                        </div>
                    </div>
                @endif

                <!-- Page Content -->
                <main class="flex-1 p-6">
                    {{ $slot }}
                </main>
            </div>
        </div>

        @stack('scripts')
    </body>
</html>

