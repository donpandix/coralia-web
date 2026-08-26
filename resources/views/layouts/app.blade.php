<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main>
        <div class="coralia-page">{{ $slot }}</div>
    </flux:main>
</x-layouts::app.sidebar>
