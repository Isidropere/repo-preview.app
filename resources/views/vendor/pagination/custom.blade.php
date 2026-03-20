@if ($paginator->total() > 0)
<nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex flex-col sm:flex-row items-center justify-between gap-4 py-4">
    {{-- Información de Resultados --}}
    <div class="text-sm text-gray-700 dark:text-gray-300">
        {{ __('pagination.showing') }}
        <span class="font-semibold">{{ $paginator->firstItem() }}</span>
        {{ __('pagination.to') }}
        <span class="font-semibold">{{ $paginator->lastItem() }}</span>
        {{ __('pagination.of') }}
        <span class="font-semibold">{{ $paginator->total() }}</span>
        {{ __('pagination.results') }}
    </div>

    {{-- Controles de Paginación --}}
    <div class="inline-flex rounded-md shadow-sm overflow-hidden border border-gray-300 dark:border-gray-600">
        {{-- Botón Anterior --}}
        @if ($paginator->onFirstPage())
            <span class="px-3 py-2 text-sm text-gray-400 bg-gray-100 dark:bg-gray-700 cursor-not-allowed">
                ← {{ __('pagination.previous') }}
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="px-3 py-2 text-sm text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                ← {{ __('pagination.previous') }}
            </a>
        @endif

        {{-- Números --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="px-3 py-2 text-sm text-gray-400 bg-white dark:bg-gray-800 cursor-default">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="px-3 py-2 text-sm font-bold text-blue-600 bg-blue-50 dark:bg-blue-900 dark:text-blue-300">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="px-3 py-2 text-sm text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Botón Siguiente --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="px-3 py-2 text-sm text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                {{ __('pagination.next') }} →
            </a>
        @else
            <span class="px-3 py-2 text-sm text-gray-400 bg-gray-100 dark:bg-gray-700 cursor-not-allowed">
                {{ __('pagination.next') }} →
            </span>
        @endif
    </div>
</nav>
@endif
