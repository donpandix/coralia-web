@props(['title', 'description' => null])

<div {{ $attributes->merge(['class' => 'flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between']) }}>
    <div><flux:heading size="xl" level="1">{{ $title }}</flux:heading>@if ($description)<flux:text class="mt-1">{{ $description }}</flux:text>@endif</div>
    @if (trim($slot))<div class="flex shrink-0 items-center gap-2">{{ $slot }}</div>@endif
</div>
