@props(['title', 'subtitle' => null, 'compact' => false, 'tone' => 'primary', 'badge' => null])

@php
    $bodyClass = $compact ? 'card-body p-4 sm:p-5' : 'card-body p-5 sm:p-6';

    $toneClasses = match ($tone) {
        'info' => [
            'wrap' => 'border-info/20 from-info/10',
            'icon' => 'border-info/20 bg-info/15 text-info',
            'badge' => 'badge-info',
        ],
        'warning' => [
            'wrap' => 'border-warning/20 from-warning/10',
            'icon' => 'border-warning/20 bg-warning/15 text-warning',
            'badge' => 'badge-warning',
        ],
        'accent' => [
            'wrap' => 'border-accent/20 from-accent/10',
            'icon' => 'border-accent/20 bg-accent/15 text-accent',
            'badge' => 'badge-accent',
        ],
        default => [
            'wrap' => 'border-primary/20 from-primary/10',
            'icon' => 'border-primary/20 bg-primary/15 text-primary',
            'badge' => 'badge-primary',
        ],
    };
@endphp

<div {{ $attributes->class(['card border bg-linear-to-br via-base-100 to-base-100 shadow-sm', $toneClasses['wrap']]) }}>
    <div class="{{ $bodyClass }}">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div class="min-w-0">
                <div class="flex items-start gap-3">
                    @isset($icon)
                        <div class="avatar placeholder shrink-0">
                            <div
                                class="h-11 w-11 rounded-box border {{ $toneClasses['icon'] }} flex items-center justify-center">
                                {{ $icon }}
                            </div>
                        </div>
                    @endisset

                    <div class="min-w-0">
                        <h1 class="text-2xl font-bold tracking-tight leading-tight">{{ $title }}</h1>
                        @if ($subtitle)
                            <p class="mt-1 text-sm text-base-content/70">{{ $subtitle }}</p>
                        @endif
                        {{ $slot }}
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 self-start lg:self-center">
                @if ($badge)
                    <span class="badge badge-soft {{ $toneClasses['badge'] }}">{{ $badge }}</span>
                @endif

                @isset($actions)
                    <div class="flex flex-wrap items-center gap-2 lg:justify-end">
                        {{ $actions }}
                    </div>
                @endisset
            </div>
        </div>
    </div>
</div>
