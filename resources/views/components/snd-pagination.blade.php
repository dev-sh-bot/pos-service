@props(['paginator'])

@if ($paginator->hasPages())
    <div class="snd-pagination">
        <p class="snd-pagination-summary">
            {{ __('Showing') }}
            <strong>{{ $paginator->firstItem() ?? 0 }}</strong>
            {{ __('to') }}
            <strong>{{ $paginator->lastItem() ?? 0 }}</strong>
            {{ __('of') }}
            <strong>{{ $paginator->total() }}</strong>
            {{ __('results') }}
        </p>

        <div class="snd-pagination-nav">
            {!! $paginator->render() !!}
        </div>
    </div>
@endif
