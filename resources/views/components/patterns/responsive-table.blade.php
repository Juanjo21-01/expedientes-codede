@props([
    'title' => null,
    'count' => null,
    'compact' => true,
    'tone' => 'base',
])

@php
    $tableClass = $compact ? 'table table-sm' : 'table';

    $toneClass = match ($tone) {
        'primary' => 'border-primary/20',
        'info' => 'border-info/20',
        default => 'border-base-content/10',
    };
@endphp

<div {{ $attributes->class(['card bg-base-100 shadow-sm border', $toneClass]) }}>
    <div class="card-body p-0">
        @if ($title || isset($actions))
            <div class="border-b border-base-content/10 px-4 py-3 flex items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    @if ($title)
                        <h3 class="font-semibold text-sm">{{ $title }}</h3>
                    @endif
                    @if (!is_null($count))
                        <span class="badge badge-sm badge-primary badge-soft">{{ $count }}</span>
                    @endif
                </div>

                @isset($actions)
                    <div class="flex items-center gap-2">
                        {{ $actions }}
                    </div>
                @endisset
            </div>
        @endif

        <div class="overflow-x-auto">
            <table class="{{ $tableClass }}">
                @isset($head)
                    <thead>
                        {{ $head }}
                    </thead>
                @endisset

                <tbody>
                    {{ $slot }}
                </tbody>

                @isset($foot)
                    <tfoot>
                        {{ $foot }}
                    </tfoot>
                @endisset
            </table>
        </div>
    </div>
</div>
