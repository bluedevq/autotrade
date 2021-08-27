@if ($paginator->hasPages())
    <div class="pagination">
        @if ($paginator->onFirstPage())
            <a href="javascript:void(0)" class="btn btn-outline-default-custom disabled"><span class="fas fa-arrow-left"></span>&nbsp;Trang trước</a>&nbsp;
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="btn btn-outline-default-custom"><span class="fas fa-arrow-left"></span>&nbsp;Trang trước</a>&nbsp;
        @endif
        @foreach ($elements as $element)
            @if (is_string($element))
                <span>{{ $element }}</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <a href="javascript:void(0)" class="btn btn-primary-custom"><span class="current">{{ $page }}</span></a>&nbsp;
                    @else
                        <a href="{{ $url }}" class="btn btn-outline-default-custom">{{ $page }}</a>&nbsp;
                    @endif
                @endforeach
            @endif
        @endforeach
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="btn btn-outline-default-custom">Trang sau&nbsp;<span class="fas fa-arrow-right"></span></a>
        @else
            <a href="javascript:void(0)" class="btn btn-outline-default-custom disabled">Trang sau&nbsp;<span class="fas fa-arrow-right"></span></a>
        @endif
    </div>
@endif
