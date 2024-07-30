<x-website-layout>
    <div class="grid grid-cols-12 gap-x-3 px-8 pb-16  container mx-auto max-w-7xl">
        <section class="col-span-12 py-12">
            <h1
                class="text-6xl font-bold tracking-tighter sm:text-5xl xl:text-6xl/none pb-2 bg-clip-text text-transparent bg-gradient-to-r from-primary-600 to-primary-300">
                {{ __('website.events_list_title') }}
            </h1>

            <p class="text-background-800 dark:text-background-200 text-justify">{{ __('website.events_list_text') }}
            </p>
        </section>
    </div>
</x-website-layout>
