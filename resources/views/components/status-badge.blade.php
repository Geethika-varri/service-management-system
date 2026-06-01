@props(['status', 'type'])

<span class="badge bg-{{ $type }}">
    {{ ucfirst(str_replace('_', ' ', $status)) }}
</span>