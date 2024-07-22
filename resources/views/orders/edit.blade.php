<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-background-800 dark:text-background-200 leading-tight">
                {{ __('orders.title') }} #{{ $order->order_number }}
            </h2>

        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 flex flex-col gap-4">
            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-background-900 dark:text-background-100">
                    <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('orders.order_data') }}</h3>
                    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

                    <div class="flex flex-col gap-4 w-1/2" x-data="{
                        created_at: '{{ $order->created_at }}',
                        formattedCreatedAt: function() {
                            return new Date(this.created_at).toLocaleDateString('it-IT', {
                                hour: 'numeric',
                                minute: 'numeric'
                            });
                        }
                    }">
                        <x-form.input name="order_number" label="{{ __('orders.order_number') }}" type="text"
                            required="{{ true }}" value="{{ $order->order_number }}"
                            disabled="{{ true }}" />


                        <x-form.input name="payment_method" label="{{ __('orders.payment_method') }}" type="text"
                            required="{{ true }}" value="{{ $order->payment_method }}"
                            disabled="{{ true }}" />
                        <x-form.input name="total" label="{{ __('orders.total') }}" type="text"
                            value="{{ $order->total }}" required="{{ true }}"
                            disabled="{{ true }}" />
                        <x-form.input name="status" label="{{ __('orders.status') }}" type="text"
                            value="{{ $order->status }}" required="{{ true }}"
                            disabled="{{ true }}" />

                        <div>
                            <p>{{ __('orders.created_at_text') }}</p>
                            <p x-text="formattedCreatedAt"></p>
                        </div>

                    </div>

                </div>
            </div>
            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
                <form class="p-6 text-background-900 dark:text-background-100" method="POST"
                    action="{{ route('orders.update.invoice', $order->id) }}">
                    @csrf
                    <div class="flex items-center justify-between">
                        <h3 class="text-background-800 dark:text-background-200 text-2xl">
                            {{ __('orders.invoice_data') }}
                        </h3>
                        <x-primary-button type="submit">
                            <x-lucide-save class="h-6 w-6 text-white" />

                        </x-primary-button>
                    </div>

                    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                    <div class="grid grid-cols-4 gap-4">

                        <div class="col-span-2">
                            <x-form.input name="name" label="{{ __('fees.invoice_name') }}"
                                value="{{ $order->invoice->name }}" />
                        </div>
                        <div class="col-span-2">
                            <x-form.input name="surname" label="{{ __('fees.invoice_surname') }}"
                                value="{{ $order->invoice->surname }}" />
                        </div>
                        <div class="col-span-3">
                            <x-form.input name="address" label="{{ __('fees.invoice_address') }}"
                                value="{{ $order->invoice->address->address }}" />
                        </div>
                        <div class="col-span-1">
                            <x-form.input name="zip" label="{{ __('fees.invoice_zip') }}"
                                value="{{ $order->invoice->address->zip }}" />
                        </div>
                        <div class="col-span-2">
                            <x-form.input name="city" label="{{ __('fees.invoice_city') }}"
                                value="{{ $order->invoice->address->city }}" />
                        </div>
                        <div class="col-span-2">
                            <x-form.input name="country" label="{{ __('fees.invoice_country') }}"
                                value="{{ $order->invoice->address->country }}" />
                        </div>
                        <div class="col-span-4">
                            <x-form.input name="vat" label="{{ __('fees.invoice_vat') }}"
                                value="{{ $order->invoice->vat }}" />
                        </div>
                    </div>
                </form>
            </div>
            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-background-900 dark:text-background-100">
                    <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('orders.items') }}
                    </h3>
                    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                    <x-table striped="false" :columns="[
                        [
                            'name' => 'ID',
                            'field' => 'id',
                            'columnClasses' => '', // classes to style table th
                            'rowClasses' => '', // classes to style table td
                        ],
                        [
                            'name' => 'Item',
                            'field' => 'product_name',
                            'columnClasses' => '', // classes to style table th
                            'rowClasses' => '', // classes to style table td
                        ],
                        [
                            'name' => 'Quantity',
                            'field' => 'quantity',
                            'columnClasses' => '', // classes to style table th
                            'rowClasses' => '', // classes to style table td
                        ],
                        [
                            'name' => 'Total',
                            'field' => 'total',
                            'columnClasses' => '', // classes to style table th
                            'rowClasses' => '', // classes to style table td
                        ],
                    ]" :rows="$order->items" />
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
