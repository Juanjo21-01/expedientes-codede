@props(['status'])

@if ($status)
    <div class="alert alert-success text-sm" {{ $attributes }}>
        <span>{{ $status }}</span>
    </div>
@endif
