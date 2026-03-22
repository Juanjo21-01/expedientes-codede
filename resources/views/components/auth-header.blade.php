@props([
    'title' => '',
    'description' => '',
])

<div class="space-y-2 text-center">
    @if (filled($title))
        <h2 class="text-2xl font-bold tracking-tight text-base-content">{{ $title }}</h2>
    @endif

    @if (filled($description))
        <p class="mx-auto max-w-prose text-sm leading-relaxed text-base-content/70">{{ $description }}</p>
    @endif
</div>
