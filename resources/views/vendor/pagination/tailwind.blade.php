@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="stic-pagination-wrapper">
        <div class="stic-pagination-info">
            <span>{{ __('messages.showing', ['default' => 'បង្ហាញ']) }}</span>
            <span class="stic-pagination-highlight">{{ $paginator->firstItem() ?? 0 }}</span>
            <span>{{ __('messages.to', ['default' => 'ដល់']) }}</span>
            <span class="stic-pagination-highlight">{{ $paginator->lastItem() ?? 0 }}</span>
            <span>{{ __('messages.of', ['default' => 'នៃ']) }}</span>
            <span class="stic-pagination-highlight">{{ $paginator->total() }}</span>
            <span>{{ __('messages.total_records', ['default' => 'ទិន្នន័យសរុប']) }}</span>
        </div>

        <div class="stic-pagination-controls">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span class="stic-page-btn stic-page-disabled" aria-disabled="true" aria-label="Previous">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="15 18 9 12 15 6"></polyline></svg>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="stic-page-btn" aria-label="Previous">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="15 18 9 12 15 6"></polyline></svg>
                </a>
            @endif

            {{-- Numbered Page Links --}}
            @foreach ($elements as $element)
                {{-- Three dots separator --}}
                @if (is_string($element))
                    <span class="stic-page-btn stic-page-dots" aria-disabled="true">{{ $element }}</span>
                @endif

                {{-- Array of links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="stic-page-btn stic-page-active" aria-current="page">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="stic-page-btn" aria-label="Page {{ $page }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="stic-page-btn" aria-label="Next">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </a>
            @else
                <span class="stic-page-btn stic-page-disabled" aria-disabled="true" aria-label="Next">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </span>
            @endif
        </div>
    </nav>
@endif
