@if ($paginator->hasPages())
    <nav aria-label="Pagination Navigation">
        <ul class="pagination">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true" wire:key="paginator-{{ $paginator->getPageName() }}-previous-disabled">
                    <span class="page-link" tabindex="-1" aria-label="@lang('pagination.previous')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1">
                            <path d="M15 6l-6 6l6 6" />
                        </svg>
                    </span>
                </li>
            @else
                <li class="page-item" wire:key="paginator-{{ $paginator->getPageName() }}-previous">
                    <button 
                        wire:click="previousPage('{{ $paginator->getPageName() }}')" 
                        class="page-link" 
                        rel="prev" 
                        aria-label="@lang('pagination.previous')"
                        wire:loading.attr="disabled"
                        type="button"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1">
                            <path d="M15 6l-6 6l6 6" />
                        </svg>
                    </button>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <li class="page-item disabled" aria-disabled="true" wire:key="paginator-{{ $paginator->getPageName() }}-separator-{{ $loop->index }}">
                        <span class="page-link">&hellip;</span>
                    </li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active" aria-current="page" wire:key="paginator-{{ $paginator->getPageName() }}-page-{{ $page }}-current">
                                <span class="page-link">{{ $page }}</span>
                            </li>
                        @else
                            <li class="page-item" wire:key="paginator-{{ $paginator->getPageName() }}-page-{{ $page }}">
                                <button 
                                    wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')" 
                                    class="page-link"
                                    aria-label="Go to page {{ $page }}"
                                    wire:loading.attr="disabled"
                                    type="button"
                                    data-page="{{ $page }}"
                                    data-page-name="{{ $paginator->getPageName() }}"
                                >{{ $page }}</button>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="page-item" wire:key="paginator-{{ $paginator->getPageName() }}-next">
                    <button 
                        wire:click="nextPage('{{ $paginator->getPageName() }}')" 
                        class="page-link" 
                        rel="next" 
                        aria-label="@lang('pagination.next')"
                        wire:loading.attr="disabled"
                        type="button"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1">
                            <path d="M9 6l6 6l-6 6" />
                        </svg>
                    </button>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true" wire:key="paginator-{{ $paginator->getPageName() }}-next-disabled">
                    <span class="page-link" tabindex="-1" aria-label="@lang('pagination.next')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1">
                            <path d="M9 6l6 6l-6 6" />
                        </svg>
                    </span>
                </li>
            @endif
        </ul>
    </nav>

    {{-- Debug information (remove in production) --}}
    @if (config('app.debug'))
        <div class="mt-2 small text-muted">
            Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }} 
            ({{ $paginator->total() }} total items, {{ $paginator->perPage() }} per page)
        </div>
    @endif
@endif