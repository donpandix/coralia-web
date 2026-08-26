@extends('layouts.public')

@section('title', 'Coralia — Tu repertorio coral')

@section('content')
    <main>
        <section class="mx-auto grid max-w-7xl items-center gap-14 px-5 py-16 sm:px-8 sm:py-24 lg:grid-cols-[1.05fr_.95fr] lg:px-10 lg:py-32">
            <div class="max-w-2xl">
                <span class="inline-flex items-center gap-2 rounded-full bg-coral-50 px-3 py-1 text-sm font-medium text-coral-800 dark:bg-coral-950/50 dark:text-coral-200">Repertorio simple, ensayos más conectados</span>
                <h1 class="mt-6 text-balance text-4xl font-semibold tracking-tight text-zinc-950 sm:text-5xl lg:text-6xl dark:text-white">Tu repertorio coral, siempre disponible.</h1>
                <p class="mt-6 max-w-xl text-pretty text-lg leading-8 text-zinc-600 dark:text-zinc-300">Partituras, audios de ensayo y material del coro organizados en un solo lugar.</p>
                <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                    <flux:button variant="primary" :href="route('register')" wire:navigate class="min-h-11 sm:w-auto">Crear cuenta</flux:button>
                    <flux:button variant="ghost" :href="route('login')" wire:navigate class="min-h-11 sm:w-auto">Ingresar</flux:button>
                </div>
            </div>

            <div class="relative mx-auto w-full max-w-xl" aria-label="Vista previa de la biblioteca de Coralia">
                <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex items-center gap-2 border-b border-zinc-200 px-5 py-4 dark:border-zinc-800"><span class="size-2 rounded-full bg-coral-500"></span><span class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Mi repertorio</span></div>
                    <div class="divide-y divide-zinc-100 px-5 dark:divide-zinc-800">
                        @foreach ([['Ave Maria', 'Franz Schubert', 'Sacra · Clásica'], ['Duerme negrito', 'Atahualpa Yupanqui', 'Folclore'], ['Carol of the Bells', 'Mykola Leontovych', 'Navidad']] as [$title, $subtitle, $tags])
                            <div class="flex items-start justify-between gap-4 py-5">
                                <div><p class="font-medium text-zinc-950 dark:text-white">{{ $title }}</p><p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $subtitle }}</p><p class="mt-3 text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ $tags }}</p></div>
                                <span class="text-xl text-coral-600" aria-hidden="true">☆</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <section class="border-y border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-900/50">
            <div class="mx-auto max-w-7xl px-5 py-16 sm:px-8 lg:px-10">
                <h2 class="text-2xl font-semibold tracking-tight text-zinc-950 dark:text-white">Todo lo necesario para practicar</h2>
                <div class="mt-10 grid gap-x-10 gap-y-8 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ([['Partituras organizadas', 'Encuentra rápidamente el material del coro.'], ['Audios por cuerda', 'Practica Soprano, Alto, Tenor o Bajo.'], ['Acceso privado', 'Contenido disponible sólo para miembros autorizados.'], ['Disponible donde estés', 'Accede cómodamente desde computador o teléfono.']] as [$title, $text])
                        <article><div class="mb-4 flex size-10 items-center justify-center rounded-xl bg-coral-100 text-coral-800 dark:bg-coral-950 dark:text-coral-200" aria-hidden="true">♪</div><h3 class="font-semibold text-zinc-950 dark:text-white">{{ $title }}</h3><p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-400">{{ $text }}</p></article>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-5 py-16 sm:px-8 lg:px-10">
            <h2 class="text-center text-2xl font-semibold tracking-tight text-zinc-950 dark:text-white">Así de simple</h2>
            <ol class="mx-auto mt-10 grid max-w-4xl gap-8 md:grid-cols-3">
                @foreach (['Únete a tu coro', 'Encuentra tu repertorio', 'Practica con tu partitura y audio'] as $step => $label)
                    <li class="flex items-center gap-4 md:flex-col md:text-center"><span class="flex size-9 shrink-0 items-center justify-center rounded-full border border-coral-300 text-sm font-semibold text-coral-800 dark:border-coral-700 dark:text-coral-200">{{ $step + 1 }}</span><span class="font-medium text-zinc-800 dark:text-zinc-200">{{ $label }}</span></li>
                @endforeach
            </ol>
        </section>
    </main>
@endsection
