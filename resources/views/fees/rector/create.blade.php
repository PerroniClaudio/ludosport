<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-background-800 dark:text-background-200 leading-tight">
                {{ __('fees.purchase_fees') }}
            </h2>
        </div>
    </x-slot>


    <div class="py-12" x-data="{
        fees_count: 0,
        fee_price: {{ $fee_price }},
        totalPrice: 0,
        name: '',
        surname: '',
        address: '',
        zip: '',
        city: '',
        vat: '',
        sdi: '',
        fiscal_code: '',
        country: @js(Auth()->user()->nation->name),
        business_name: '',
        is_business: false,
        want_invoice: true,
        invoiceSaved: false,
        invoiceError: '',
        shouldShowPayment: false,
        shouldShowSdi: false,
        shouldShowFiscalCode: false,
        isItaly: function() {
            return ['it', 'italy', 'italia'].includes((this.country || '').trim().toLowerCase())
        },
        updateSdi: function() {
            this.shouldShowFiscalCode = this.isItaly()
            if ((/^IT/.test(this.vat || '')) || this.shouldShowFiscalCode) {
                this.shouldShowSdi = true
            } else {
                this.shouldShowSdi = false
            }
        },
        markInvoiceDirty() {
            this.invoiceSaved = false
            this.invoiceError = ''
            this.updateSdi()
        },
        validateInvoiceForm() {
            this.updateSdi()
            if (!this.$refs.invoiceForm.checkValidity()) {
                this.$refs.invoiceForm.reportValidity()
                return false
            }
            return true
        },
        addFee() {
            this.fees_count++;
    
            if (this.fees_count > 99) {
                this.fees_count = 99;
            }
    
            this.calculateTotalPrice()
        },
        minusFee() {
            if (this.fees_count > 0) {
                this.fees_count--;
            }
    
            if (this.fees_count > 99) {
                this.fees_count = 99;
            }
    
            this.calculateTotalPrice()
        },
        validateNumber(event) {
            if (isNaN(event.target.value)) {
                event.target.value = 0;
            }
    
            if (event.target.value < 0) {
                event.target.value = 0;
            }
    
            if (event.target.value > 99) {
                event.target.value = 99;
            }
        },
        calculateTotalPrice() {
            this.totalPrice = this.fees_count * this.fee_price;
            this.shouldShowPayment = this.totalPrice > 0;
        },
        fetchInvoiceData() {
            const url = `/rector/invoices/user-data/{{ Auth()->user()->id }}`
    
            fetch(url, {
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(res => res.json())
                .then(data => {
    
                    this.name = data.name
                    this.surname = data.surname
                    let address = typeof data.address === 'string' ? JSON.parse(data.address) : data.address
    
                    this.address = address.address != '' ? address.address : ''
                    this.zip = address.zip != '' ? address.zip : ''
                    this.city = address.city != '' ? address.city : ''
                    this.country = address.country != '' ? address.country : ''
                    this.vat = data.vat || ''
                    this.sdi = data.sdi || ''
                    this.fiscal_code = data.fiscal_code || ''
                    this.is_business = data.is_business
                    this.business_name = data.business_name || ''
                    this.updateSdi()
    
    
                })
        },
        async saveInvoiceData() {
            if (!this.validateInvoiceForm()) {
                return false
            }
            const url = `/invoices/store`
    
            const body = new FormData()
    
            body.append('name', this.name)
            body.append('surname', this.surname)
            body.append('address', this.address)
            body.append('zip', this.zip)
            body.append('city', this.city)
            body.append('country', this.country)
            body.append('vat', this.vat)
            body.append('sdi', this.sdi)
            body.append('fiscal_code', this.fiscal_code)
            body.append('is_business', this.is_business)
            body.append('business_name', this.business_name)
            body.append('want_invoice', true)
    
            const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: body
                })
            const data = await response.json()
            this.invoiceSaved = !!data.success
            this.invoiceError = data.success ? '' : (data.error || 'Unable to save invoice data')
            return this.invoiceSaved
        },
        startStripeCheckout() {
            const url = `/rector/fees/stripe/checkout`
            let items = [];
    
            if (this.fees_count > 0) {
                items.push({
                    'name': 'fee',
                    'quantity': this.fees_count,
                })
            }
    
            const itemsJson = JSON.stringify(items)
    
            const params = new URLSearchParams({
                'items': itemsJson
            })
    
            window.location.href = `${url}?${params}`
    
        },
        startPaypalCheckout() {
            const url = `/rector/fees/paypal/checkout`
            let items = [];
    
            if (this.fees_count > 0) {
                items.push({
                    'name': 'fee',
                    'quantity': this.fees_count,
                })
            }
    
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
        },
        startWireCheckout() {
            const url = `/rector/fees/wire-transfer`
            let items = [];
    
            if (this.fees_count > 0) {
                items.push({
                    'name': 'fee',
                    'quantity': this.fees_count,
                })
            }
    
            const itemsJson = JSON.stringify(items)
    
            const params = new URLSearchParams({
                'items': itemsJson
            })
    
            window.location.href = `${url}?${params}`
        },
        init() {
            this.fetchInvoiceData()
            this.$watch('vat', value => {
                this.updateSdi();
            });
            this.$watch('country', value => {
                this.updateSdi();
            });
        }
    }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 select-none">
            <div class="grid grid-cols-4 gap-4">
                <div class="col-span-3 flex flex-col gap-4">
                    <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
                        <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('fees.fees_amount') }}
                        </h3>
                        <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

                        <div class="block mt-4">
                            <label for="is_business" class="inline-flex items-center">
                                <input id="is_business" type="checkbox"
                                    class="rounded dark:bg-background-900 border-background-300 dark:border-background-700 text-primary-600 shadow-sm focus:ring-primary-500 dark:focus:ring-primary-600 dark:focus:ring-offset-background-800"
                                    name="is_business" x-model="is_business" @change="markInvoiceDirty">
                                <span
                                    class="ms-2 text-sm text-background-600 dark:text-background-400">{{ __('fees.is_business') }}</span>
                            </label>
                        </div>

                        <div class="flex justify-between items-center text-background-800 dark:text-background-200">
                            <p class="text-lg">{{ __('fees.fees') }}</p>
                            <div class="flex w-32">
                                <div class="bg-background-700 flex flex-col items-center justify-center px-2 rounded-l-md"
                                    @click="addFee">
                                    <x-lucide-plus
                                        class="h-6 w-6 text-background-800 dark:text-background-200 hover:text-primary-500 cursor-pointer"
                                        id="fees_plus" />
                                </div>
                                <div>
                                    <input type="number" name="fees" id="fees_number" value="0"
                                        x-model="fees_count" x-on:keyup="validateNumber($event)" min="0"
                                        max="99"
                                        class="w-full text-center border-background-300 dark:border-background-700 dark:bg-background-900 dark:text-background-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                </div>
                                <div class="bg-background-700 flex flex-col items-center justify-center px-2 rounded-r-md"
                                    @click="minusFee">
                                    <x-lucide-minus
                                        class="h-6 w-6 text-background-800 dark:text-background-200 hover:text-primary-500 cursor-pointer"
                                        id="fees_minus" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <form x-ref="invoiceForm" @submit.prevent="saveInvoiceData"
                        @input="markInvoiceDirty" @change="markInvoiceDirty"
                        class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
                        <div class="flex justify-between items-center">
                            <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('fees.invoice') }}
                            </h3>
                            <div>
                                <x-primary-button>
                                    <x-lucide-save class="h-6 w-6 text-white" />
                                </x-primary-button>
                            </div>
                        </div>

                        <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                        <div class="grid grid-cols-4 gap-4">
                            <div class="col-span-2">
                                <x-form.input-model name="name" label="{{ '* ' . __('fees.invoice_name') }}" required="true" />
                            </div>
                            <div class="col-span-2">
                                <x-form.input-model name="surname" label="{{ '* ' . __('fees.invoice_surname') }}" required="true" />
                            </div>
                            <div class="col-span-3">
                                <x-form.input-model name="address" label="{{ '* ' . __('fees.invoice_address') }}" required="true" />
                            </div>
                            <div class="col-span-1">
                                <x-form.input-model name="zip" label="{{ '* ' . __('fees.invoice_zip') }}" required="true" />
                            </div>
                            <div class="col-span-2">
                                <x-form.input-model name="city" label="{{ '* ' . __('fees.invoice_city') }}" required="true" />
                            </div>
                            <div class="col-span-2">
                                <x-form.invoice-country-select selectedvalue="{{ Auth()->user()->nation->name }}" required="true" />
                            </div>
                            <div class="col-span-4" x-show="is_business">
                                <x-form.input-model name="business_name" label="{{ __('fees.business_name') }}" />
                            </div>
                            <div class="col-span-4" x-show="is_business">
                                <x-form.input-model name="vat" label="{{ __('fees.invoice_vat') }}" />
                            </div>
                            <div class="col-span-4 md:col-span-2" x-show="shouldShowSdi">
                                <x-form.input-model name="sdi" label="{{ __('fees.invoice_sdi') }}" />
                            </div>
                            <div class="col-span-4 md:col-span-2" x-show="shouldShowFiscalCode">
                                <x-input-label value="{{ '* ' . __('fees.invoice_fiscal_code') }}" />
                                <input name="fiscal_code" type="text" x-model="fiscal_code"
                                    x-bind:required="shouldShowFiscalCode"
                                    class="w-full border-background-300 dark:border-background-700 dark:bg-background-900 dark:text-background-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm" />
                                <x-input-error :messages="$errors->get('fiscal_code')" class="mt-2" />
                            </div>
                        </div>
                        <p x-show="invoiceError" x-text="invoiceError" class="mt-4 text-sm text-error-500"></p>
                    </form>
                </div>
                <div>
                    <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
                        <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('fees.fees_total') }}
                        </h3>
                        <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                        <p class="text-background-800 dark:text-background-200 text-lg"
                            x-text="'€ ' + totalPrice.toFixed(2)"></p>

                        <div x-show="shouldShowPayment && invoiceSaved" class="mt-4">
                            <div class="rounded-full bg-blue-500 hover:bg-blue-600 text-white font-bold p-1 text-center cursor-pointer"
                                @click="startPaypalCheckout">

                                <span>PayPal</span>
                            </div>
                        </div>
                        <div x-show="shouldShowPayment && invoiceSaved" class="mt-4">
                            <div class="rounded-full bg-white hover:bg-gray-200 text-black font-bold p-1 text-center cursor-pointer"
                                @click="startStripeCheckout">

                                <span>Stripe</span>
                            </div>
                        </div>
                        <div x-show="shouldShowPayment && invoiceSaved" class="mt-4">
                            <div class="rounded-full bg-gray-300 hover:bg-gray-500 text-black font-bold p-1 text-center cursor-pointer"
                                @click="startWireCheckout">

                                <span>{{ __('website.wire_transfer') }}</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
