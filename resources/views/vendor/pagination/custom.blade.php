@if ($paginator->hasPages())
<div class="pagination-wrapper">

    {{-- Page info --}}
    <div class="pagination-info">
        Showing {{ $paginator->firstItem() }}
        to {{ $paginator->lastItem() }}
        of {{ $paginator->total() }} results
    </div>

    {{-- Pagination --}}
    <ul class="pagination">

        {{-- Previous --}}
        <li class="{{ $paginator->onFirstPage() ? 'disabled' : '' }}">
            <a href="{{ $paginator->previousPageUrl() ?? '#' }}">«</a>
        </li>

        {{-- Pages --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <li class="disabled"><span>{{ $element }}</span></li>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    <li class="{{ $page == $paginator->currentPage() ? 'active' : '' }}">
                        <a href="{{ $url }}">{{ $page }}</a>
                    </li>
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        <li class="{{ $paginator->hasMorePages() ? '' : 'disabled' }}">
            <a href="{{ $paginator->nextPageUrl() ?? '#' }}">»</a>
        </li>

    </ul>
</div>
@endif
<style>.pagination-wrapper {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 18px;
}

.pagination-info {
    font-size: 12px;
    color: #6b7280;
}

.pagination {
    list-style: none;
    display: flex;
    gap: 6px;
    padding: 0;
    margin: 0;
}

.pagination li a,
.pagination li span {
    min-width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    border: 1px solid #e5e7eb;
    color: #374151;
    text-decoration: none;
}

.pagination li.active a {
    background: #2563eb;
    color: #ffffff;
    border-color: #2563eb;
}

.pagination li.disabled span,
.pagination li.disabled a {
    background: #f3f4f6;
    color: #9ca3af;
    pointer-events: none;
}

.pagination li a:hover {
    background: #eff6ff;
}
</style>