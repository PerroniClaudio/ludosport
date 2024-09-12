<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-background-800 dark:text-background-200 leading-tight">
                {{ __('fees.purchase_fees') }}
            </h2>
        </div>
    </x-slot>


    <div class="py-12" x-data="{
        seniorFees: 0,
        juniorFees: 0,
        seniorFeesPrice: 50,
        juniorFeesPrice: 25,
        totalPrice: 0,
        name: 'Name',
        surname: 'Surname',
        address: 'Address',
        zip: 'Zip',
        city: 'City',
        vat: 'VAT',
        sdi: 'SDI',
        country: '{{ Auth()->user()->nation->name }}',
        addSeniorFees() {
            this.seniorFees++;
    
            if (this.seniorFees > 99) {
                this.seniorFees = 99;
            }
    
            this.calculateTotalPrice()
        },
        minusSeniorFees() {
            if (this.seniorFees > 0) {
                this.seniorFees--;
            }
    
            if (this.seniorFees > 99) {
                this.seniorFees = 99;
            }
    
            this.calculateTotalPrice()
        },
        addJuniorFees() {
            this.juniorFees++;
    
            if (this.juniorFees > 99) {
                this.juniorFees = 99;
            }
    
            this.calculateTotalPrice()
        },
        minusJuniorFees() {
            if (this.juniorFees > 0) {
                this.juniorFees--;
            }
    
            if (this.juniorFees > 99) {
                this.juniorFees = 99;
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
            this.totalPrice = this.seniorFees * this.seniorFeesPrice + this.juniorFees * this.juniorFeesPrice;
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
                    this.country = data.address.country
    
                    let address = JSON.parse(data.address)
    
                    this.address = address.address != '' ? address.address : 'Address'
                    this.zip = address.zip != '' ? address.zip : 'Zip'
                    this.city = address.city != '' ? address.city : 'City'
                    this.country = address.country != '' ? address.country : 'Country'
    
    
                })
        },
        saveInvoiceData() {
            const url = `/rector/invoices/store`
    
            const body = new FormData()
    
            body.append('name', this.name)
            body.append('surname', this.surname)
            body.append('address', this.address)
            body.append('zip', this.zip)
            body.append('city', this.city)
            body.append('country', this.country)
            body.append('vat', this.vat)
            body.append('sdi', this.sdi)
    
    
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
    
            const url = `/rector/fees/stripe/checkout`
            let items = [];
    
            if (this.seniorFees > 0) {
                items.push({
                    'name': 'senior_fee',
                    'quantity': this.seniorFees,
                })
            }
    
            if (this.juniorFees > 0) {
                items.push({
                    'name': 'junior_fee',
                    'quantity': this.juniorFees,
                })
            }
    
            const itemsJson = JSON.stringify(items)
    
            const params = new URLSearchParams({
                'items': itemsJson
            })
    
            window.location.href = `${url}?${params}`
    
        },
        startPaypalCheckout() {
    
            this.saveInvoiceData()
    
    
    
            const url = `/rector/fees/paypal/checkout`
            let items = [];
    
            if (this.seniorFees > 0) {
                items.push({
                    'name': 'senior_fee',
                    'quantity': this.seniorFees,
                })
            }
    
            if (this.juniorFees > 0) {
                items.push({
                    'name': 'junior_fee',
                    'quantity': this.juniorFees,
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
        test() {
            console.log('test')
        },
        init() {
            this.fetchInvoiceData()
        }
    }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 select-none">
            <div class="grid grid-cols-4 gap-4">
                <div class="col-span-3 flex flex-col gap-4">
                    <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
                        <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('fees.fees_amount') }}
                        </h3>
                        <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                        <div class="flex flex-col gap-4">
                            <div class="flex justify-between items-center text-background-800 dark:text-background-200">
                                <p class="text-lg">{{ __('fees.senior_fees') }}</p>
                                <div class="flex w-32">
                                    <div class="bg-background-700 flex flex-col items-center justify-center px-2 rounded-l-md"
                                        @click="addSeniorFees">
                                        <x-lucide-plus
                                            class="h-6 w-6 text-background-800 dark:text-background-200 hover:text-primary-500 cursor-pointer"
                                            id="senior_fees_plus" />
                                    </div>
                                    <div>
                                        <input type="number" name="senior_fees" id="senior_fees_number" value="0"
                                            x-model="seniorFees" x-on:keyup="validateNumber($event)" min="0"
                                            max="99"
                                            class="w-full text-center border-background-300 dark:border-background-700 dark:bg-background-900 dark:text-background-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                    </div>
                                    <div class="bg-background-700 flex flex-col items-center justify-center px-2 rounded-r-md"
                                        @click="minusSeniorFees">
                                        <x-lucide-minus
                                            class="h-6 w-6 text-background-800 dark:text-background-200 hover:text-primary-500 cursor-pointer"
                                            id="senior_fees_plus" />
                                    </div>
                                </div>
                            </div>
                            <div class="flex justify-between items-center text-background-800 dark:text-background-200">
                                <p class="text-lg">{{ __('fees.junior_fees') }}</p>
                                <div class="flex w-32">
                                    <div class="bg-background-700 flex flex-col items-center justify-center px-2 rounded-l-md"
                                        @click="addJuniorFees">
                                        <x-lucide-plus
                                            class="h-6 w-6 text-background-800 dark:text-background-200 hover:text-primary-500 cursor-pointer"
                                            id="junior_fees_plus" />
                                    </div>
                                    <div>
                                        <input type="text" name="junior_fees" id="junior_fees_number" value="0"
                                            x-model="juniorFees" x-on:keyup="validateNumber($event)" min="0"
                                            max="99"
                                            class="w-full text-center border-background-300 dark:border-background-700 dark:bg-background-900 dark:text-background-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                    </div>
                                    <div class="bg-background-700 flex flex-col items-center justify-center px-2 rounded-r-md"
                                        @click="minusJuniorFees">

                                        <x-lucide-minus
                                            class="h-6 w-6 text-background-800 dark:text-background-200 hover:text-primary-500 cursor-pointer"
                                            id="junior_fees_plus" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
                        <div class="flex justify-between items-center">
                            <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('fees.invoice') }}
                            </h3>
                            <div>
                                <x-primary-button @click="saveInvoiceData">
                                    <x-lucide-save class="h-6 w-6 text-white" />
                                </x-primary-button>
                            </div>
                        </div>

                        <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
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
                            <div class="col-span-4">
                                <x-form.input-model name="vat" label="{{ __('fees.invoice_vat') }}" />
                            </div>
                            <div class="col-span-4">
                                <x-form.input-model name="sdi" label="{{ __('fees.invoice_sdi') }}" />
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
                        <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('fees.fees_total') }}
                        </h3>
                        <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                        <p class="text-background-800 dark:text-background-200 text-lg"
                            x-text="'€ ' + totalPrice.toFixed(2)"></p>

                        <div x-show="totalPrice > 0" class="mt-4">
                            <div class="rounded-full bg-blue-500 hover:bg-blue-600 text-white font-bold p-1 text-center cursor-pointer"
                                @click="startPaypalCheckout">

                                <span>PayPal</span>
                            </div>
                        </div>
                        <div x-show="totalPrice > 0" class="mt-4">
                            <div class="rounded-full bg-white hover:bg-gray-200 text-black font-bold p-1 text-center cursor-pointer"
                                @click="startStripeCheckout">

                                <span>Stripe</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
