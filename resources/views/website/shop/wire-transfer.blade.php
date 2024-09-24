<x-website-layout>
    <div class="grid grid-cols-12 gap-x-3 px-8 pb-16  container mx-auto max-w-7xl">
        <section class="col-span-12 py-12">
            <h1
                class="text-6xl font-bold tracking-tighter sm:text-5xl xl:text-6xl/none pb-2 bg-clip-text text-transparent bg-gradient-to-r from-primary-600 to-primary-300">
                {{ __('website.wire_transfer') }}
            </h1>

            <div
                class="bg-white dark:bg-background-800 text-background-800 dark:text-background-200 overflow-hidden shadow-sm sm:rounded-lg p-8">
                <p>{{ __('website.wire_transfer_instructions') }}</p>

                <div class="mt-6">
                    <h2 class="text-2xl font-semibold">{{ __('website.bank_details') }}</h2>
                    <ul class="mt-4 space-y-2">
                        <li><strong>{{ __('website.bank_name') }}:</strong> {{ config('app.wire_transfer.bank') }}</li>
                        <li><strong>{{ __('website.account_name') }}:</strong> {{ config('app.wire_transfer.holder') }}
                        </li>
                        <li><strong>{{ __('website.bic') }}:</strong> {{ config('app.wire_transfer.bic') }}</li>
                        <li><strong>{{ __('website.iban') }}:</strong> {{ config('app.wire_transfer.iban') }}</li>
                    </ul>
                </div>


                <div class="mt-6">
                    <h2 class="text-2xl font-semibold">{{ __('website.transfer_details') }}</h2>
                    <p class="mt-4">{{ __('website.transfer_amount') }}: € {{ number_format($order->total, 2) }}</p>
                    <p class="mt-2">{{ __('website.order_id') }}: <span
                            class="font-bold text-primary-500">{{ $order->order_number }}</span></p>
                </div>

                <div class="mt-6">
                    <p>{{ __('website.wire_transfer_additional_info') }}</p>
                </div>

                <div class="mt-6">
                    <p>{{ __('website.thank_you') }}</p>
                </div>
            </div>
        </section>
    </div>
</x-website-layout>
