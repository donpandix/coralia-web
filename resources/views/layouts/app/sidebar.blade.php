@php
    $currentMembership = app(\App\Support\CurrentOrganization::class)->membership(auth()->user());
    $unreadNotificationCount = auth()->user()->unreadNotifications()->count();
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>@include('partials.head')</head>
    <body class="min-h-screen bg-white dark:bg-zinc-950">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" :href="route('dashboard')" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            @unless (auth()->user()->is_super_admin)
                <div class="px-2 pb-3"><livewire:organization-switcher /></div>
            @endunless

            <flux:sidebar.nav>
                @if (auth()->user()->is_super_admin)
                    <flux:sidebar.group heading="Superadministración" class="grid">
                        <flux:sidebar.item icon="home" :href="route('admin.dashboard')" :current="request()->routeIs('admin.dashboard')" wire:navigate>Dashboard</flux:sidebar.item>
                        <flux:sidebar.item icon="building-office-2" :href="route('admin.organizations')" :current="request()->routeIs('admin.organizations')" wire:navigate>Organizaciones</flux:sidebar.item>
                        <flux:sidebar.item icon="inbox" :href="route('admin.requests')" :current="request()->routeIs('admin.requests')" wire:navigate>Solicitudes</flux:sidebar.item>
                        <flux:sidebar.item icon="users" :href="route('admin.users')" :current="request()->routeIs('admin.users')" wire:navigate>Usuarios</flux:sidebar.item>
                        <flux:sidebar.item icon="tag" :href="route('admin.tags')" :current="request()->routeIs('admin.tags')" wire:navigate>Etiquetas</flux:sidebar.item>
                        <flux:sidebar.item icon="flag" :href="route('admin.reports')" :current="request()->routeIs('admin.reports')" wire:navigate>Reportes</flux:sidebar.item>
                    </flux:sidebar.group>
                @elseif ($currentMembership)
                    <flux:sidebar.group heading="Personal" class="grid">
                        <flux:sidebar.item icon="musical-note" :href="route('library.index')" :current="request()->routeIs('library.*') && request('filter') !== 'favorites'" wire:navigate>Biblioteca</flux:sidebar.item>
                        <flux:sidebar.item icon="star" :href="route('library.index', ['filter' => 'favorites'])" :current="request()->routeIs('library.index') && request('filter') === 'favorites'" wire:navigate>Favoritos</flux:sidebar.item>
                        <flux:sidebar.item icon="bell" :href="route('notifications.index')" :current="request()->routeIs('notifications.*')" wire:navigate>
                            Notificaciones
                            @if ($unreadNotificationCount > 0)<flux:badge size="sm" color="zinc" inset="top bottom">{{ $unreadNotificationCount }}</flux:badge>@endif
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="user-circle" :href="route('profile.edit')" :current="request()->routeIs('profile.edit')" wire:navigate>Perfil</flux:sidebar.item>
                    </flux:sidebar.group>

                    @if ($currentMembership->role === \App\Enums\OrganizationRole::Admin)
                        <flux:sidebar.group heading="Administración" class="grid">
                            <flux:sidebar.item icon="rectangle-stack" :href="route('organization.pieces.index')" :current="request()->routeIs('organization.pieces.*')" wire:navigate>Piezas</flux:sidebar.item>
                            <flux:sidebar.item icon="users" :href="route('organization.members')" :current="request()->routeIs('organization.members')" wire:navigate>Miembros</flux:sidebar.item>
                            <flux:sidebar.item icon="inbox" :href="route('organization.requests')" :current="request()->routeIs('organization.requests')" wire:navigate>Solicitudes</flux:sidebar.item>
                            <flux:sidebar.item icon="user-group" :href="route('organization.groups')" :current="request()->routeIs('organization.groups')" wire:navigate>Grupos</flux:sidebar.item>
                            <flux:sidebar.item icon="building-office" :href="route('organization.settings')" :current="request()->routeIs('organization.settings')" wire:navigate>Organización</flux:sidebar.item>
                        </flux:sidebar.group>
                    @endif
                @endif
            </flux:sidebar.nav>

            <flux:spacer />
            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <flux:header class="border-b border-zinc-200 lg:hidden dark:border-zinc-800">
            <flux:sidebar.toggle icon="bars-2" inset="left" aria-label="Abrir navegación" />
            <div class="min-w-0 flex-1 px-2">@unless(auth()->user()->is_super_admin)<livewire:organization-switcher />@else<span class="text-sm font-medium">Superadministración</span>@endunless</div>
            <flux:dropdown position="top" align="end">
                <flux:profile :initials="auth()->user()->initials()" />
                <flux:menu>
                    <flux:menu.item :href="route('profile.edit')" icon="user-circle" wire:navigate>Perfil</flux:menu.item>
                    <flux:menu.separator />
                    <form method="POST" action="{{ route('logout') }}">@csrf<flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle">Cerrar sesión</flux:menu.item></form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}
        @persist('toast')<flux:toast.group><flux:toast /></flux:toast.group>@endpersist
        @fluxScripts
    </body>
</html>
