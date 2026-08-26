<!DOCTYPE html>
<html lang="es">
    <head>
        @include('partials.head')
        <title>@yield('title', 'Coralia')</title>
    </head>
    <body class="min-h-screen bg-white text-zinc-900 antialiased dark:bg-zinc-950 dark:text-zinc-100">
        <header class="border-b border-zinc-200/80 dark:border-zinc-800">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-5 sm:px-8 lg:px-10">
                <x-app-logo :href="route('home')" wire:navigate />
                <nav class="flex items-center gap-1" aria-label="Navegación principal">
                    @auth
                        <flux:button variant="ghost" :href="route('dashboard')" wire:navigate>Ir a Coralia</flux:button>
                    @else
                        <flux:button variant="ghost" :href="route('login')" wire:navigate>Ingresar</flux:button>
                        <flux:button variant="primary" :href="route('register')" wire:navigate class="hidden sm:inline-flex">Crear cuenta</flux:button>
                    @endauth
                </nav>
            </div>
        </header>
        @yield('content')
        <footer class="border-t border-zinc-200 dark:border-zinc-800">
            <div class="mx-auto flex max-w-7xl flex-col gap-5 px-5 py-8 text-sm text-zinc-500 sm:px-8 md:flex-row md:items-center md:justify-between lg:px-10 dark:text-zinc-400">
                <p>© {{ now()->year }} Coralia.</p>
                <nav class="flex flex-wrap gap-x-5 gap-y-2" aria-label="Información legal">
                    <a href="{{ route('about') }}" class="hover:text-zinc-900 dark:hover:text-white">Acerca de</a><a href="{{ route('privacy') }}" class="hover:text-zinc-900 dark:hover:text-white">Privacidad</a><a href="{{ route('terms') }}" class="hover:text-zinc-900 dark:hover:text-white">Términos</a><a href="{{ route('contact') }}" class="hover:text-zinc-900 dark:hover:text-white">Contacto</a>
                </nav>
            </div>
        </footer>
        @fluxScripts
    </body>
</html>
