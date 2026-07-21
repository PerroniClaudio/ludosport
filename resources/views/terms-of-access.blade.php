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
            @else
                <p class="text-center text-background-600 dark:text-background-400">
                    Terms of Access are not available.
                </p>
            @endif

            @if ($terms)
                <div class="mt-12 flex flex-col gap-4 rounded-lg bg-background-100 p-6 shadow-sm dark:bg-background-800 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold">Download Terms of Access</h2>
                        <p class="mt-1 text-sm text-background-500 dark:text-background-400">
                            Version {{ $terms->version }} — {{ $terms->original_name }}
                        </p>
                    </div>
                    <a href="{{ route('terms-of-access.download') }}"
                        class="inline-flex items-center justify-center gap-2 px-4 py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-lg transition duration-150">
                        <x-lucide-download class="h-5 w-5" />
                        Download PDF
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-website-layout>
