@if ($paginator->hasPages())
    <nav class="pagination" role="navigation" aria-label="Paginação">
        {{-- Anterior --}}
        @if ($paginator->onFirstPage())
            <span class="page-link disabled" aria-disabled="true">
                <i class="fa-solid fa-chevron-left"></i>
            </span>
        @else
            <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Anterior">
                <i class="fa-solid fa-chevron-left"></i>
            </a>
        @endif

        {{-- Números --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="page-link dots" aria-disabled="true">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="page-link active" aria-current="page">{{ $page }}</span>
                    @else
                        <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Próximo --}}
        @if ($paginator->hasMorePages())
            <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Próximo">
                <i class="fa-solid fa-chevron-right"></i>
            </a>
        @else
            <span class="page-link disabled" aria-disabled="true">
                <i class="fa-solid fa-chevron-right"></i>
            </span>
        @endif
    </nav>
@endif
