@props(['status'])

@if ($status)
    <div class="alert alert-success border border-success/20 text-sm shadow-sm" {{ $attributes }}>
        <span>{{ $status }}</span>
    </div>
@endif
