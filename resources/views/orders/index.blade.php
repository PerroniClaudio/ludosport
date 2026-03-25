<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-background-800 dark:text-background-200 leading-tight">
                {{ __('orders.title') }}
            </h2>

        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-background-900 dark:text-background-100">
                    
                    <!-- Filtri compatti -->
                    <form method="GET" action="{{ route('orders.index') }}" id="filterForm" class="mb-4 pb-3 border-b border-background-200 dark:border-background-700">
                        <div class="flex flex-col md:flex-row gap-4 items-start mb-2">
                            
                            <!-- Total -->
                            <div class="flex-shrink-0">
                                <label class="block text-xs font-medium mb-1 text-background-600 dark:text-background-400">
                                    Total
                                </label>
                                <div class="flex gap-1.5 items-center">
                                    <input 
                                        type="number" 
                                        name="min_price" 
                                        id="min_price"
                                        step="0.01"
                                        min="0"
                                        value="{{ request('min_price') }}"
                                        onchange="this.form.submit()"
                                        class="w-24 text-xs py-0.5 px-1.5 rounded border-background-300 dark:border-background-600 dark:bg-background-700 dark:text-background-100"
                                        placeholder="Min"
                                    >
                                    <span class="text-xs text-background-400">-</span>
                                    <input 
                                        type="number" 
                                        name="max_price" 
                                        id="max_price"
                                        step="0.01"
                                        min="0"
                                        value="{{ request('max_price') }}"
                                        onchange="this.form.submit()"
                                        class="w-24 text-xs py-0.5 px-1.5 rounded border-background-300 dark:border-background-600 dark:bg-background-700 dark:text-background-100"
                                        placeholder="Max"
                                    >
                                </div>
                            </div>
                            
                            <!-- Created at -->
                            <div class="flex-shrink-0">
                                <label class="block text-xs font-medium mb-1 text-background-600 dark:text-background-400">
                                    Created at
                                </label>
                                <div class="flex gap-1.5 items-center">
                                    <input 
                                        type="date" 
                                        name="min_date" 
                                        id="min_date"
                                        value="{{ request('min_date') }}"
                                        onchange="this.form.submit()"
                                        class="w-32 text-xs py-0.5 px-1.5 rounded border-background-300 dark:border-background-600 dark:bg-background-700 dark:text-background-100"
                                    >
                                    <span class="text-xs text-background-400">-</span>
                                    <input 
                                        type="date" 
                                        name="max_date" 
                                        id="max_date"
                                        value="{{ request('max_date') }}"
                                        onchange="this.form.submit()"
                                        class="w-32 text-xs py-0.5 px-1.5 rounded border-background-300 dark:border-background-600 dark:bg-background-700 dark:text-background-100"
                                    >
                                </div>
                            </div>
                            
                            <!-- Status -->
                            <div class="flex-grow">
                                <label class="block text-xs font-medium mb-1 text-background-600 dark:text-background-400">
                                    Status
                                </label>
                                <div class="flex flex-wrap gap-2">
                                    @foreach([0, 1, 2, 3, 4] as $status)
                                        <label class="flex items-center space-x-1">
                                            <input 
                                                type="checkbox" 
                                                name="status[]" 
                                                value="{{ $status }}"
                                                {{ in_array($status, request('status', [])) ? 'checked' : '' }}
                                                onchange="this.form.submit()"
                                                class="w-3 h-3 rounded border-background-300 dark:border-background-600 dark:bg-background-700 text-primary-600 focus:ring-primary-500"
                                            >
                                            <span class="text-xs">{{ __('orders.status' . $status) }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            
                            <!-- Reset -->
                            <div class="flex-shrink-0 self-end">
                                <a 
                                    href="{{ route('orders.index') }}"
                                    class="inline-block px-2 py-0.5 text-xs bg-background-200 hover:bg-background-300 dark:bg-background-700 dark:hover:bg-background-600 text-background-900 dark:text-background-100 rounded font-medium transition-colors"
                                >
                                    {{ __('orders.reset_filters') }}
                                </a>
                            </div>
                        </div>
                    </form>

                    <x-table striped="false" :columns="[
                        [
                            'name' => 'ID',
                            'field' => 'id',
                        ],
                        [
                            'name' => 'Number',
                            'field' => 'order_number',
                            'columnClasses' => '', // classes to style table th
                            'rowClasses' => '', // classes to style table td
                        ],
                    
                        [
                            'name' => 'User',
                            'field' => 'user_fullname',
                            'columnClasses' => '', // classes to style table th
                            'rowClasses' => '', // classes to style table td
                        ],
                        [
                            'name' => 'Payment method',
                            'field' => 'payment_method',
                            'columnClasses' => '', // classes to style table th
                            'rowClasses' => '', // classes to style table td
                        ],
                        [
                            'name' => 'Total',
                            'field' => 'total',
                            'columnClasses' => '', // classes to style table th
                            'rowClasses' => '', // classes to style table td
                        ],
                        [
                            'name' => 'Status',
                            'field' => 'status',
                            'columnClasses' => '', // classes to style table th
                            'rowClasses' => '', // classes to style table td
                        ],
                        [
                            'name' => 'Created at',
                            'field' => 'created_at',
                            'columnClasses' => '', // classes to style table th
                            'rowClasses' => '', // classes to style table td
                        ],
                        [
                            'name' => 'Actions',
                            'field' => 'id',
                            'columnClasses' => '', // classes to style table th
                            'rowClasses' => '', // classes to style table td
                        ],
                    ]" :rows="$orders">
                        <x-slot name="tableRows">
                            <td class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap"
                                x-text="row.id"></td>
                            <td class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap text-xs"
                                x-text="row.order_number"></td>
                            <td class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap"
                                x-text="row.user_fullname"></td>
                            <td class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap"
                                x-text="row.payment_method"></td>
                            <td class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap"
                                x-text="row.total"></td>
                            <td class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap"
                                x-text="row.status"></td>
                            <td class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap"
                                x-text="new Date(row.created_at).toLocaleDateString('it-IT', {
                                            hour: 'numeric', 
                                            minute: 'numeric' 
                                        })">
                            </td>
                            <td
                                class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap">
                                <a x-bind:href="'/orders/' + row.id">
                                    <x-lucide-pencil
                                        class="w-5 h-5 text-primary-800 dark:text-primary-500 cursor-pointer" />
                                </a>
                            </td>
                        </x-slot>
                        {{-- <x-slot name="tableActions">
                            <a x-bind:href="'/orders/' + row.id">
                                <x-lucide-pencil class="w-5 h-5 text-primary-800 dark:text-primary-500 cursor-pointer" />
                            </a>
                        </x-slot> --}}

                    </x-table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Ricarica la pagina quando si torna indietro dal browser (bfcache)
        // Perchè se l'admin approva un wire transfer deve vedere il dato aggiornato quando torna alla lista degli ordini
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                window.location.reload();
            }
        });
    </script>
    @endpush
</x-app-layout>
