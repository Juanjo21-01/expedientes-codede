@props([
    'title' => '',
    'description' => '',
])

<div class="space-y-2 text-center">
    @if (filled($title))
        <h2 class="text-2xl font-bold text-base-content">{{ $title }}</h2>
    @endif

    @if (filled($description))
        <p class="text-sm text-base-content/70">{{ $description }}</p>
    @endif
</div>
