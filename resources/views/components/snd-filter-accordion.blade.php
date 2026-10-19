@props([
    'id',
    'open' => false,
    'title' => __('Filters'),
])

<details id="{{ $id }}" {{ $attributes->class(['card', 'snd-filter-accordion']) }} @if($open) open @endif>
    <summary class="snd-filter-accordion-summary">
        <span class="snd-filter-accordion-title"><x-snd-icon name="filter" />{{ $title }}</span>
        <span class="snd-filter-accordion-hint">
            <span class="snd-filter-show-label">{{ __('Show filters') }}</span>
            <span class="snd-filter-hide-label">{{ __('Hide filters') }}</span>
        </span>
        <x-snd-icon name="chevron-down" class="snd-filter-accordion-chevron" />
    </summary>
    <div class="card-body snd-filter-accordion-body">{{ $slot }}</div>
</details>
