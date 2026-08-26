@props(['title', 'description' => null, 'icon' => 'musical-note'])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center rounded-xl border border-dashed border-zinc-300 px-5 py-12 text-center dark:border-zinc-700']) }}>
    <span class="flex size-11 items-center justify-center rounded-full bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400"><flux:icon :name="$icon" class="size-5" /></span>
    <h2 class="mt-4 font-medium text-zinc-900 dark:text-white">{{ $title }}</h2>
    @if ($description)<p class="mt-1 max-w-md text-sm text-zinc-500 dark:text-zinc-400">{{ $description }}</p>@endif
    @if (trim($slot))<div class="mt-5">{{ $slot }}</div>@endif
</div>
