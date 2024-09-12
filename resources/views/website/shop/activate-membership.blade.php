<x-website-layout>
    <div class="grid grid-cols-12 gap-x-3 px-8 pb-16  container mx-auto max-w-7xl">
        <section class="col-span-12 py-12">
            <div x-data="{
                birthday: '',
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
                country: '',
                shouldShowPayment: false,
                differenzaInAnni(dataInizio, dataFine) {
                    var anni = dataFine.getFullYear() - dataInizio.getFullYear();
                    var meseFine = dataFine.getMonth();
                    var giornoFine = dataFine.getDate();
                    var meseInizio = dataInizio.getMonth();
                    var giornoInizio = dataInizio.getDate();
            
                    if (meseFine < meseInizio || (meseFine === meseInizio && giornoFine < giornoInizio)) {
                        anni--;
                    }
                    return anni;
                },
                calculateFeePrice() {
                    let date = new Date(this.birthday)
                    let age = Math.abs(this.differenzaInAnni(new Date(), date))
            
                    if (age < 99) {
            
                        if (age >= 16) {
                            this.seniorFees = 1
                            this.juniorFees = 0
            
                            this.totalPrice = this.seniorFeesPrice
            
                        } else {
                            this.seniorFees = 0
                            this.juniorFees = 1
            
                            this.totalPrice = this.juniorFeesPrice
            
                        }
            
                        this.shouldShowPayment = true
            
                    } else {
                        this.seniorFees = 0
                        this.juniorFees = 0
                        this.totalPrice = 0
                        this.shouldShowPayment = false
                    }
            
                },
                fetchInvoiceData() {
                    const url = `/shop/invoices/user-data/{{ Auth()->user()->id }}`
            
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
            
                    const url = `/shop/fees/stripe/checkout`
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
            
                    const url = `/shop/fees/paypal/checkout`
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
                init() {
                    this.fetchInvoiceData()
                }
            }">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 select-none">
                    <div class="grid grid-cols-4 gap-4">
                        <div class="col-span-3 flex flex-col gap-4">
                            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8">

                                <div>
                                    <label for="seniorFees" class="text-background-800 dark:text-background-200">
                                        {{ __('website.birthday') }}
                                    </label>
                                    <input type="date" name="birthday" id="seniorFees" x-model="birthday"
                                        x-on:input="calculateFeePrice"
                                        class="w-full border-background-300 dark:border-background-700 dark:bg-background-900 dark:text-background-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm" />
                                </div>

                                <p class="text-background-800 dark:text-background-200 mt-4">
                                    {{ __('website.membership_age') }}
                                </p>

                                <p class="text-background-800 dark:text-background-200 mt-4">
                                    {{ __('website.membership_expiration_text') }}
                                </p>
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
                                <h3 class="text-background-800 dark:text-background-200 text-2xl">
                                    {{ __('fees.fees_total') }}
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
                                <div x-show="shouldShowPayment" class="mt-4">
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
        </section>
    </div>
</x-website-layout>
