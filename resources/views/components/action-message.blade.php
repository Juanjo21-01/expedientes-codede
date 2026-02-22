@props(['on'])

<div x-data="{ shown: false, timeout: null }"
    x-on:{{ $on }}.window="
        shown = true;
        clearTimeout(timeout);
        timeout = setTimeout(() => shown = false, 2000);
    "
    x-show="shown" x-transition style="display: none;" {{ $attributes }}>
    {{ $slot }}
</div>
