@props([
    'icon' => 'inbox',
    'message' => 'No records found.',
    'url' => null,
    'actionLabel' => null,
])

<div class="snd-table-empty">
    <span class="snd-table-empty-icon">
        <x-snd-icon :name="$icon" />
    </span>
    <p>{{ $message }}</p>
    @if($url && $actionLabel)
        <a href="{{ $url }}" class="btn btn-primary">
            <x-snd-icon name="plus" /> {{ $actionLabel }}
        </a>
    @endif
</div>
