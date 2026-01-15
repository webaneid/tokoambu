@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="storefront-pagination">
        {{-- Previous/Next Text Links --}}
        <div class="storefront-pagination__text-links">
            @if ($paginator->onFirstPage())
                <span class="storefront-pagination__text-link storefront-pagination__text-link--disabled">« Previous</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="storefront-pagination__text-link">« Previous</a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="storefront-pagination__text-link">Next »</a>
            @else
                <span class="storefront-pagination__text-link storefront-pagination__text-link--disabled">Next »</span>
            @endif
        </div>

        {{-- Pagination Buttons --}}
        <div class="storefront-pagination__buttons">
            {{-- Previous Arrow --}}
            @if ($paginator->onFirstPage())
                <span class="storefront-pagination__button storefront-pagination__button--disabled" aria-disabled="true">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                    </svg>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="storefront-pagination__button">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                    </svg>
                </a>
            @endif

            {{-- Page Numbers --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span class="storefront-pagination__dots">{{ $element }}</span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="storefront-pagination__button storefront-pagination__button--active" aria-current="page">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="storefront-pagination__button">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Arrow --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="storefront-pagination__button">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
                    </svg>
                </a>
            @else
                <span class="storefront-pagination__button storefront-pagination__button--disabled" aria-disabled="true">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
                    </svg>
                </span>
            @endif
        </div>
    </nav>
@endif
