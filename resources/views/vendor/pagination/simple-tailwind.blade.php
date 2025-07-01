@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex justify-between">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span
                class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-background-500 bg-white border border-background-300 cursor-default leading-5 rounded-md dark:text-background-600 dark:bg-background-800 dark:border-background-600">
                {!! __('pagination.previous') !!}
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-background-700 bg-white border border-background-300 leading-5 rounded-md hover:text-background-500 focus:outline-none focus:ring ring-background-300 focus:border-blue-300 active:bg-background-100 active:text-background-700 transition ease-in-out duration-150 dark:bg-background-800 dark:border-background-600 dark:text-background-300 dark:focus:border-blue-700 dark:active:bg-background-700 dark:active:text-background-300">
                {!! __('pagination.previous') !!}
            </a>
        @endif

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-background-700 bg-white border border-background-300 leading-5 rounded-md hover:text-background-500 focus:outline-none focus:ring ring-background-300 focus:border-blue-300 active:bg-background-100 active:text-background-700 transition ease-in-out duration-150 dark:bg-background-800 dark:border-background-600 dark:text-background-300 dark:focus:border-blue-700 dark:active:bg-background-700 dark:active:text-background-300">
                {!! __('pagination.next') !!}
            </a>
        @else
            <span
                class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-background-500 bg-white border border-background-300 cursor-default leading-5 rounded-md dark:text-background-600 dark:bg-background-800 dark:border-background-600">
                {!! __('pagination.next') !!}
            </span>
        @endif
    </nav>
@endif
