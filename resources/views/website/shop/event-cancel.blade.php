<x-website-layout>
    <div class="grid grid-cols-12 gap-x-3 px-8 pb-16  container mx-auto max-w-7xl">
        <section class="col-span-12 py-12">
            <div class="bg-background-200 dark:bg-background-800 p-8 rounded-md">
                <h3
                    class="text-4xl font-bold tracking-tighter sm:text-3xl xl:text-4xl/none pb-2 bg-clip-text text-transparent bg-gradient-to-r from-primary-600 to-primary-300 text-center">
                    {{ __('website.shop_event_error') }}
                </h3>
                <p class="text-background-800 dark:text-background-200 text-center">
                    {{ __('website.shop_event_error_text') }}
                </p>
            </div>
        </section>
    </div>
</x-website-layout>
