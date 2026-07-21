<x-website-layout>
    <div class="w-full px-4 py-12 sm:px-6 lg:px-8 bg-white dark:bg-background-900">
        <div class="max-w-5xl mx-auto min-h-[60vh]">
            <h1 class="text-5xl font-bold mb-8 text-primary-600 dark:text-primary-400">
                Terms of Access
            </h1>

            @if ($content)
                @if ($terms)
                    <p class="text-background-500 dark:text-background-400 mb-4">
                        Last updated: <strong>{{ $terms->created_at->format('d/m/Y') }}</strong>
                    </p>
                @endif

                <div class="prose dark:prose-invert max-w-none">
                    {!! $content !!}
                </div>

                @if ($terms)
                    <div class="mt-12 pt-8 border-t border-background-300 dark:border-background-600 text-center">
                        <a href="{{ route('terms-of-access.download') }}"
                            class="inline-flex items-center px-4 py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-lg transition duration-150">
                            Download PDF
                        </a>
                    </div>
                @endif
            @else
                <p class="text-center text-background-600 dark:text-background-400">
                    Terms of Access are not available.
                </p>
            @endif
        </div>
    </div>
</x-website-layout>
