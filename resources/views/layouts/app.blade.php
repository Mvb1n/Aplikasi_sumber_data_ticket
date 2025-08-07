<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Sumber_Data') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts & Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('styles')
        @stack('scripts')
    </head>
    <body class="font-sans antialiased">
        <div x-data="{ isSidebarOpen: true }" class="flex min-h-screen bg-gray-100">

            <!-- Sidebar -->
            <aside 
                class="bg-gray-800 text-gray-200 flex flex-col fixed inset-y-0 left-0 z-30 transition-all duration-300"
                :class="isSidebarOpen ? 'w-64' : 'w-20'"
            >
                <!-- Logo di Sidebar -->
                <div class="flex items-center h-16 flex-shrink-0 border-b border-gray-700" :class="isSidebarOpen ? 'justify-start px-4' : 'justify-center'">
                    <a href="{{ route('dashboard') }}">
                        <div class="flex items-center">
                            <svg class="h-10 w-10 text-indigo-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                            <span class="ml-3 font-semibold text-lg whitespace-nowrap overflow-hidden transition-all duration-200"
                                  :class="isSidebarOpen ? 'w-32 opacity-100' : 'w-0 opacity-0'">
                                Sumber Data
                            </span>
                        </div>
                    </a>
                </div>

                <!-- Menu Navigasi di Sidebar -->
                <nav class="flex-1 py-4 overflow-y-auto">
                    {{-- PERUBAHAN: Menghapus properti :is-open --}}
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" :sidebar="true">
                    <x-slot name="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
                    </x-slot>
                    {{ __('Dashboard') }}
                    </x-nav-link>
                    <x-nav-link :href="route('assets.create')" :active="request()->routeIs('assets.create')" :sidebar="true">
                        <x-slot name="icon">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </x-slot>
                        {{ __('Input Aset') }}
                    </x-nav-link>
                    <x-nav-link :href="route('incidents.create')" :active="request()->routeIs('incidents.create')" :sidebar="true">
                        <x-slot name="icon">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"></path></svg>
                        </x-slot>
                        {{ __('Lapor Insiden') }}
                    </x-nav-link>
                    <x-nav-link :href="route('sync-logs.index')" :active="request()->routeIs('sync-logs.index')" :sidebar="true">
                        <x-slot name="icon">
                            {{-- Ikon untuk log --}}
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </x-slot>
                        {{ __('Log Sinkronisasi') }}
                    </x-nav-link>
                    <x-nav-link :href="route('assets.index')" :active="request()->routeIs('assets.index')" :sidebar="true">
                        <x-slot name="icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" /></svg>
                        </x-slot>
                        {{ __('Manajemen Aset') }}
                    </x-nav-link>
                    <x-nav-link :href="route('incidents.index')" :active="request()->routeIs('incidents.index')" :sidebar="true">
                        <x-slot name="icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6"><path stroke-linecap="round" stroke-linejoin="round" d="M6.429 9.75 2.25 12l4.179 2.25m0-4.5 5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L21.75 12l-4.179 2.25m0 0 4.179 2.25L12 21.75 2.25 16.5l4.179-2.25m11.142 0-5.571 3-5.571-3" /></svg>
                        </x-slot>
                        {{ __('Manajemen Laporan') }}
                    </x-nav-link>
                    
                </nav>
            </aside>

            <!-- Konten Utama -->
            <div class="flex-1 flex flex-col transition-all duration-300" :class="isSidebarOpen ? 'lg:ml-64' : 'lg:ml-20'">
                <!-- Top Bar -->
                @include('layouts.navigation')

                <!-- Page Heading -->
                @if (isset($header))
                    <header class="bg-white shadow">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endif

                <!-- Page Content -->
                <main>
                    {{ $slot }}
                </main>
            </div>
        </div>
        @stack('scripts')
    </body>
</html>