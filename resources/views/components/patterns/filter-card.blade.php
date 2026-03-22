@props([
    'title' => 'Filtros',
    'description' => null,
    'compact' => true,
    'tone' => 'base',
])

@php
    $bodyClass = $compact ? 'card-body p-4' : 'card-body p-5';

    $toneClass = match ($tone) {
        'primary' => 'border-primary/20 bg-primary/5',
        'info' => 'border-info/20 bg-info/5',
        default => 'border-base-content/10 bg-base-100',
    };
@endphp

<div {{ $attributes->class(['card border shadow-sm', $toneClass]) }}>
    <div class="{{ $bodyClass }}">
        <div class="flex items-start justify-between gap-3 mb-3">
            <div>
                <h2 class="card-title text-base">{{ $title }}</h2>
                @if ($description)
                    <p class="text-xs text-base-content/65">{{ $description }}</p>
                @endif
            </div>

            @isset($actions)
                <div class="flex items-center gap-2">
                    {{ $actions }}
                </div>
            @endisset
        </div>

        <div class="grid gap-3">
            {{ $slot }}
        </div>
    </div>
</div>
