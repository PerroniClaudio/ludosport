@php
    $isWaitingList = $event->isWaitingList();
    $stripeUrl = "";
    $paypalUrl = "";
    if ($isWaitingList) {
        $stripeUrl = route('shop.event.stripe-preauth', ['event' => $event]);
        $paypalUrl = route('shop.event.paypal-preauth', ['event' => $event]);
    } else {
        $stripeUrl = route('shop.events.stripe-checkout', ['event' => $event]);
        $paypalUrl = route('shop.events.paypal-checkout', ['event' => $event]);
    }
@endphp

<x-website-layout>
    <div class="grid grid-cols-12 gap-x-3 px-8 pb-16  container mx-auto max-w-7xl">
        <section class="col-span-12 py-12">
            <div x-data="{
                birthday: '',
                totalPrice: {{ $event->price }},
                name: '{{$invoice->name ? $invoice->name : 'Name'}}',
                surname: '{{$invoice->surname ? $invoice->surname : 'Surname'}}',
                address: '{{(json_decode($invoice->address)->address ?? false) ? json_decode($invoice->address)->address : 'Address'}}',
                zip: '{{(json_decode($invoice->address)->zip ?? false) ? json_decode($invoice->address)->zip : 'Zip'}}',
                city: '{{(json_decode($invoice->address)->city ?? false) ? json_decode($invoice->address)->city : 'City'}}',
                country: '{{(json_decode($invoice->address)->country ?? false) ? json_decode($invoice->address)->country : ''}}',
                vat: '{{$invoice->vat ? $invoice->vat : 'VAT'}}',
                sdi: '{{$invoice->sdi ? $invoice->sdi : 'SDI'}}',
                business_name: '{{ __('fees.insert_business_name') }}',
                is_business: '{{$invoice->is_business ? true : false}}' == 'true' ? true : false,
                want_invoice: '{{$invoice->want_invoice ? true : false}}' == 'true' ? true : false,
                shouldShowPayment: false,
                shouldShowSdi: false,
                updateSdi: function() {
                    if ((/^IT/.test(this.vat)) || (this.country.toLowerCase() === 'italy') || (this.country.toLowerCase() === 'italia')) {
                        this.shouldShowSdi = true
                    } else {
                        this.shouldShowSdi = false
                    }
                },
                shouldShowPayment: true,
                event_id: '{{ $event->id }}',
                saveInvoiceData() {
                    const url = `/invoices/update`
            
                    const body = new FormData()
            
                    body.append('invoice_id', {{$invoice->id}})
                    body.append('name', this.name)
                    body.append('surname', this.surname)
                    body.append('address', this.address)
                    body.append('zip', this.zip)
                    body.append('city', this.city)
                    body.append('country', this.country)
                    body.append('vat', this.vat)
                    body.append('sdi', this.sdi)
                    body.append('is_business', this.is_business)
                    body.append('business_name', this.business_name)
                    body.append('want_invoice', this.want_invoice)
            
                    fetch(url, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: body
                        })
                        .then(res => res.json())
                        .then(data => {
                            console.log(data)
                        })
                },
                startStripeCheckout() {
                    this.saveInvoiceData()
            
                    const url = `{{ $stripeUrl }}`
                    let items = [];
            
                    items.push({
                        'name': 'event_participation',
                        'quantity': 1,
                    })
            
                    const itemsJson = JSON.stringify(items)
            
                    const params = new URLSearchParams({
                        'items': itemsJson
                    })
            
                    window.location.href = `${url}?${params}`
            
                },
                startPaypalCheckout() {
                    this.saveInvoiceData()
            
                    const url = `{{ $paypalUrl }}`
                    let items = [];
            
                    items.push({
                        'name': 'event_participation',
                        'quantity': 1,
                    })
            
            
                    const itemsJson = JSON.stringify(items)
            
                    let fd = new FormData()
                    fd.append('items', itemsJson)
            
                    fetch(url, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: fd
                        })
                        .then(res => res.json())
                        .then(data => {
                            window.location.href = data.url
                        })
                }
            }">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 select-none">
                    <div class="grid grid-cols-4 gap-4">
                        <div class="col-span-3 flex flex-col gap-4">
                            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
                                <h1
                                    class="font-semibold text-3xl text-background-800 dark:text-background-200 leading-tight">
                                    {{ $event->name }}</h1>
                                <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                                <div>
                                    <p class="text-background-800 dark:text-background-200">1x
                                        {{ __('website.event_participation_checkout_text') }}</p>
                                    @if ($isWaitingList)
                                        <p class="mt-2 text-error-500">
                                            {{ __('website.event_waiting_list_checkout_text') }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
                                <div class="flex justify-between items-center">
                                    <h3 class="text-background-800 dark:text-background-200 text-2xl">
                                        {{ __('fees.invoice') }}
                                    </h3>
                                    <div>
                                        <x-primary-button @click="saveInvoiceData">
                                            <x-lucide-save class="h-6 w-6 text-white" />
                                        </x-primary-button>
                                    </div>
                                </div>

                                <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

                                <div class="block mt-4">
                                    <label for="want_invoice" class="inline-flex items-center">
                                        <input id="want_invoice" type="checkbox"
                                            class="rounded dark:bg-background-900 border-background-300 dark:border-background-700 text-primary-600 shadow-sm focus:ring-primary-500 dark:focus:ring-primary-600 dark:focus:ring-offset-background-800"
                                            name="want_invoice" x-model="want_invoice">
                                        <span
                                            class="ms-2 text-sm text-background-600 dark:text-background-400">{{ __('fees.want_invoice') }}</span>
                                    </label>
                                </div>

                                <div class="block mt-4">
                                    <label for="is_business" class="inline-flex items-center">
                                        <input id="is_business" type="checkbox"
                                            class="rounded dark:bg-background-900 border-background-300 dark:border-background-700 text-primary-600 shadow-sm focus:ring-primary-500 dark:focus:ring-primary-600 dark:focus:ring-offset-background-800"
                                            name="is_business" x-model="is_business">
                                        <span
                                            class="ms-2 text-sm text-background-600 dark:text-background-400">{{ __('fees.is_business') }}</span>
                                    </label>
                                </div>

                                <div class="grid grid-cols-4 gap-4">
                                    <div class="col-span-2">
                                        <x-form.input-model name="name" label="{{ __('fees.invoice_name') }}" />
                                    </div>
                                    <div class="col-span-2">
                                        <x-form.input-model name="surname" label="{{ __('fees.invoice_surname') }}" />
                                    </div>
                                    <div class="col-span-3">
                                        <x-form.input-model name="address" label="{{ __('fees.invoice_address') }}" />
                                    </div>
                                    <div class="col-span-1">
                                        <x-form.input-model name="zip" label="{{ __('fees.invoice_zip') }}" />
                                    </div>
                                    <div class="col-span-2">
                                        <x-form.input-model name="city" label="{{ __('fees.invoice_city') }}" />
                                    </div>
                                    <div class="col-span-2">
                                        <x-form.input-model name="country" label="{{ __('fees.invoice_country') }}" />
                                    </div>
                                    <div class="col-span-4" x-show="is_business">
                                        <x-form.input-model name="business_name"
                                            label="{{ __('fees.business_name') }}" />
                                    </div>
                                    <div class="col-span-4" x-show="is_business">
                                        <x-form.input-model name="vat" label="{{ __('fees.invoice_vat') }}" />
                                    </div>
                                    <div class="col-span-4" x-show="shouldShowSdi">
                                        <x-form.input-model name="sdi" label="{{ __('fees.invoice_sdi') }}" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
                                <h3 class="text-background-800 dark:text-background-200 text-2xl">
                                    {{ __('website.total_price') }}
                                </h3>
                                <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                                <p class="text-background-800 dark:text-background-200 text-lg"
                                    x-text="'€ ' + totalPrice.toFixed(2)"></p>

                                <div x-show="shouldShowPayment" class="mt-4">
                                    <div @click="startPaypalCheckout"
                                        class="rounded-full bg-blue-500 hover:bg-blue-600 text-white font-bold p-1 text-center cursor-pointer">
                                        <span>PayPal</span>
                                    </div>
                                </div>
                                {{-- Al momento non è ancora implementata la preautorizzazione con stripe --}}
                                @if(!$isWaitingList) 
                                    <div x-show="shouldShowPayment" class="mt-4">
                                        <div class="rounded-full bg-white hover:bg-gray-200 text-black font-bold p-1 text-center cursor-pointer"
                                            @click="startStripeCheckout">

                                            <span>Stripe</span>
                                        </div>
                                    </div>
                                @endif
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-website-layout>
