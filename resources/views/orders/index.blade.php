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
            
            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg" 
                 x-data="{
                    loading: false,
                    orders: [],
                    pagination: {
                        current_page: 1,
                        last_page: 1,
                        per_page: 25,
                        total: 0,
                        from: 0,
                        to: 0
                    },
                    sorting: {
                        sort_by: 'created_at',
                        sort_direction: 'desc'
                    },
                    filters: {
                        min_price: '',
                        max_price: '',
                        min_date: '',
                        max_date: '',
                        status: [],
                        search: ''
                    },
                    toggleStatus(statusValue) {
                        const index = this.filters.status.indexOf(statusValue);
                        if (index > -1) {
                            this.filters.status.splice(index, 1);
                        } else {
                            this.filters.status.push(statusValue);
                        }
                    },
                    sortBy(field) {
                        if (this.sorting.sort_by === field) {
                            if (this.sorting.sort_direction === 'asc') {
                                this.sorting.sort_direction = 'desc';
                            } else if (this.sorting.sort_direction === 'desc') {
                                this.sorting.sort_by = 'created_at';
                                this.sorting.sort_direction = 'desc';
                            } else {
                                this.sorting.sort_direction = 'asc';
                            }
                        } else {
                            this.sorting.sort_by = field;
                            this.sorting.sort_direction = 'asc';
                        }
                        this.loadOrders();
                    },
                    async loadOrders(page = 1) {
                        this.loading = true;
                        try {
                            const params = new URLSearchParams();
                            params.append('page', page);
                            params.append('per_page', this.pagination.per_page);
                            
                            // Filtri
                            if (this.filters.min_price) params.append('min_price', this.filters.min_price);
                            if (this.filters.max_price) params.append('max_price', this.filters.max_price);
                            if (this.filters.min_date) params.append('min_date', this.filters.min_date);
                            if (this.filters.max_date) params.append('max_date', this.filters.max_date);
                            this.filters.status.forEach(status => params.append('status[]', status));
                            
                            // Ricerca
                            if (this.filters.search) params.append('search', this.filters.search);
                            
                            // Sorting
                            if (this.sorting.sort_by) params.append('sort_by', this.sorting.sort_by);
                            if (this.sorting.sort_direction) params.append('sort_direction', this.sorting.sort_direction);
                            
                            const response = await fetch('{{ route('orders.data') }}?' + params.toString());
                            const result = await response.json();
                            
                            this.orders = result.data;
                            this.pagination = {
                                current_page: result.current_page,
                                last_page: result.last_page,
                                per_page: result.per_page,
                                total: result.total,
                                from: result.from,
                                to: result.to
                            };
                        } catch (error) {
                            console.error('Error loading orders:', error);
                        } finally {
                            this.loading = false;
                        }
                    },
                    applyFilters() {
                        this.loadOrders(1);
                    },
                    resetFilters() {
                        this.filters = {
                            min_price: '',
                            max_price: '',
                            min_date: '',
                            max_date: '',
                            status: [],
                            search: ''
                        };
                        this.sorting = {
                            sort_by: 'created_at',
                            sort_direction: 'desc'
                        };
                        this.loadOrders(1);
                    },
                    goToPage(page) {
                        if (page >= 1 && page <= this.pagination.last_page) {
                            this.loadOrders(page);
                        }
                    }
                 }"
                 x-init="loadOrders()">
                <div class="p-6 text-background-900 dark:text-background-100">
                    
                    <!-- Filtri compatti -->
                    <div id="filterForm" class="mb-4 pb-3 border-b border-background-200 dark:border-background-700">
                        <div class="flex flex-col md:flex-row gap-4 items-start mb-2">
                            
                            <!-- Total -->
                            <div class="flex-shrink-0">
                                <label class="block text-xs font-medium mb-1 text-background-600 dark:text-background-400">
                                    Total
                                </label>
                                <div class="flex gap-1.5 items-center">
                                    <input 
                                        type="number" 
                                        x-model="filters.min_price"
                                        step="0.01"
                                        min="0"
                                        class="w-24 text-xs py-0.5 px-1.5 rounded border-background-300 dark:border-background-600 dark:bg-background-700 dark:text-background-100"
                                        placeholder="Min"
                                    >
                                    <span class="text-xs text-background-400">-</span>
                                    <input 
                                        type="number" 
                                        x-model="filters.max_price"
                                        step="0.01"
                                        min="0"
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
                                        x-model="filters.min_date"
                                        class="w-32 text-xs py-0.5 px-1.5 rounded border-background-300 dark:border-background-600 dark:bg-background-700 dark:text-background-100"
                                    >
                                    <span class="text-xs text-background-400">-</span>
                                    <input 
                                        type="date" 
                                        x-model="filters.max_date"
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
                                                value="{{ $status }}"
                                                @change="toggleStatus({{ $status }})"
                                                class="w-3 h-3 rounded border-background-300 dark:border-background-600 dark:bg-background-700 text-primary-600 focus:ring-primary-500"
                                            >
                                            <span class="text-xs">{{ __('orders.status' . $status) }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            
                            <!-- Buttons -->
                            <div class="flex-shrink-0 self-end flex gap-2">
                                <button 
                                    @click="applyFilters()"
                                    type="button"
                                    class="inline-block px-3 py-1 text-xs bg-primary-600 hover:bg-primary-700 dark:bg-primary-500 dark:hover:bg-primary-600 text-white rounded font-medium transition-colors"
                                >
                                    {{ __('orders.apply_filters') }}
                                </button>
                                <button 
                                    @click="resetFilters()"
                                    type="button"
                                    class="inline-block px-3 py-1 text-xs bg-background-200 hover:bg-background-300 dark:bg-background-700 dark:hover:bg-background-600 text-background-900 dark:text-background-100 rounded font-medium transition-colors"
                                >
                                    {{ __('orders.reset_filters') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Loading indicator -->
                    <div x-show="loading" class="text-center py-4">
                        <span class="text-background-600 dark:text-background-400">{{ __('Loading...') }}</span>
                    </div>

                    <!-- Tabella ordini -->
                    <div x-show="!loading" class="bg-white dark:bg-background-900 rounded-lg shadow overflow-hidden">
                        <div class="overflow-x-auto">
                            <!-- Search -->
                            <div class="flex items-center justify-end w-full p-6">
                                <x-text-input 
                                    type="text" 
                                    x-model="filters.search"
                                    @input.debounce.500ms="loadOrders(1)"
                                    placeholder="Search..."
                                    class="border border-background-100 dark:border-background-700 text-background-500 dark:text-background-300 rounded-lg p-2" 
                                />
                            </div>
                            <table class="min-w-full divide-y divide-background-200 dark:divide-background-700">
                                <thead class="bg-background-50 dark:bg-background-800">
                                    <tr>
                                        <th class="bg-background-100 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 px-6 py-3 text-primary-500 dark:text-primary-400">
                                            <div class="flex justify-between items-center cursor-pointer" @click="sortBy('id')">
                                                <span class="font-bold tracking-wider uppercase text-xs truncate">ID</span>
                                                <template x-if="sorting.sort_by !== 'id'">
                                                    <svg class="w-4 h-4 text-primary-500 dark:text-primary-400 hover:opacity-70" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 16 4 4 4-4"></path><path d="M7 20V4"></path><path d="m21 8-4-4-4 4"></path><path d="M17 4v16"></path></svg>
                                                </template>
                                                <template x-if="sorting.sort_by === 'id' && sorting.sort_direction === 'asc'">
                                                    <svg class="w-4 h-4 text-primary-500 dark:text-primary-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="m3 16 4 4 4-4"></path><path d="M7 20V4"></path></svg>
                                                </template>
                                                <template x-if="sorting.sort_by === 'id' && sorting.sort_direction === 'desc'">
                                                    <svg class="w-4 h-4 text-primary-500 dark:text-primary-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="m21 8-4-4-4 4"></path><path d="M17 4v16"></path></svg>
                                                </template>
                                            </div>
                                        </th>
                                        <th class="bg-background-100 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 px-6 py-3 text-primary-500 dark:text-primary-400">
                                            <div class="flex justify-between items-center cursor-pointer" @click="sortBy('order_number')">
                                                <span class="font-bold tracking-wider uppercase text-xs truncate">Number</span>
                                                <template x-if="sorting.sort_by !== 'order_number'">
                                                    <svg class="w-4 h-4 text-primary-500 dark:text-primary-400 hover:opacity-70" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 16 4 4 4-4"></path><path d="M7 20V4"></path><path d="m21 8-4-4-4 4"></path><path d="M17 4v16"></path></svg>
                                                </template>
                                                <template x-if="sorting.sort_by === 'order_number' && sorting.sort_direction === 'asc'">
                                                    <svg class="w-4 h-4 text-primary-500 dark:text-primary-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="m3 16 4 4 4-4"></path><path d="M7 20V4"></path></svg>
                                                </template>
                                                <template x-if="sorting.sort_by === 'order_number' && sorting.sort_direction === 'desc'">
                                                    <svg class="w-4 h-4 text-primary-500 dark:text-primary-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="m21 8-4-4-4 4"></path><path d="M17 4v16"></path></svg>
                                                </template>
                                            </div>
                                        </th>
                                        <th class="bg-background-100 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 px-6 py-3 text-primary-500 dark:text-primary-400">
                                            <div class="flex justify-between items-center cursor-pointer" @click="sortBy('user_fullname')">
                                                <span class="font-bold tracking-wider uppercase text-xs truncate">User</span>
                                                <template x-if="sorting.sort_by !== 'user_fullname'">
                                                    <svg class="w-4 h-4 text-primary-500 dark:text-primary-400 hover:opacity-70" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 16 4 4 4-4"></path><path d="M7 20V4"></path><path d="m21 8-4-4-4 4"></path><path d="M17 4v16"></path></svg>
                                                </template>
                                                <template x-if="sorting.sort_by === 'user_fullname' && sorting.sort_direction === 'asc'">
                                                    <svg class="w-4 h-4 text-primary-500 dark:text-primary-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="m3 16 4 4 4-4"></path><path d="M7 20V4"></path></svg>
                                                </template>
                                                <template x-if="sorting.sort_by === 'user_fullname' && sorting.sort_direction === 'desc'">
                                                    <svg class="w-4 h-4 text-primary-500 dark:text-primary-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="m21 8-4-4-4 4"></path><path d="M17 4v16"></path></svg>
                                                </template>
                                            </div>
                                        </th>
                                        <th class="bg-background-100 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 px-6 py-3 text-primary-500 dark:text-primary-400">
                                            <div class="flex justify-between items-center cursor-pointer" @click="sortBy('payment_method')">
                                                <span class="font-bold tracking-wider uppercase text-xs truncate">Payment method</span>
                                                <template x-if="sorting.sort_by !== 'payment_method'">
                                                    <svg class="w-4 h-4 text-primary-500 dark:text-primary-400 hover:opacity-70" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 16 4 4 4-4"></path><path d="M7 20V4"></path><path d="m21 8-4-4-4 4"></path><path d="M17 4v16"></path></svg>
                                                </template>
                                                <template x-if="sorting.sort_by === 'payment_method' && sorting.sort_direction === 'asc'">
                                                    <svg class="w-4 h-4 text-primary-500 dark:text-primary-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="m3 16 4 4 4-4"></path><path d="M7 20V4"></path></svg>
                                                </template>
                                                <template x-if="sorting.sort_by === 'payment_method' && sorting.sort_direction === 'desc'">
                                                    <svg class="w-4 h-4 text-primary-500 dark:text-primary-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="m21 8-4-4-4 4"></path><path d="M17 4v16"></path></svg>
                                                </template>
                                            </div>
                                        </th>
                                        <th class="bg-background-100 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 px-6 py-3 text-primary-500 dark:text-primary-400">
                                            <div class="flex justify-between items-center cursor-pointer" @click="sortBy('total')">
                                                <span class="font-bold tracking-wider uppercase text-xs truncate">Total</span>
                                                <template x-if="sorting.sort_by !== 'total'">
                                                    <svg class="w-4 h-4 text-primary-500 dark:text-primary-400 hover:opacity-70" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 16 4 4 4-4"></path><path d="M7 20V4"></path><path d="m21 8-4-4-4 4"></path><path d="M17 4v16"></path></svg>
                                                </template>
                                                <template x-if="sorting.sort_by === 'total' && sorting.sort_direction === 'asc'">
                                                    <svg class="w-4 h-4 text-primary-500 dark:text-primary-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="m3 16 4 4 4-4"></path><path d="M7 20V4"></path></svg>
                                                </template>
                                                <template x-if="sorting.sort_by === 'total' && sorting.sort_direction === 'desc'">
                                                    <svg class="w-4 h-4 text-primary-500 dark:text-primary-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="m21 8-4-4-4 4"></path><path d="M17 4v16"></path></svg>
                                                </template>
                                            </div>
                                        </th>
                                        <th class="bg-background-100 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 px-6 py-3 text-primary-500 dark:text-primary-400">
                                            <div class="flex justify-between items-center cursor-pointer" @click="sortBy('status')">
                                                <span class="font-bold tracking-wider uppercase text-xs truncate">Status</span>
                                                <template x-if="sorting.sort_by !== 'status'">
                                                    <svg class="w-4 h-4 text-primary-500 dark:text-primary-400 hover:opacity-70" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 16 4 4 4-4"></path><path d="M7 20V4"></path><path d="m21 8-4-4-4 4"></path><path d="M17 4v16"></path></svg>
                                                </template>
                                                <template x-if="sorting.sort_by === 'status' && sorting.sort_direction === 'asc'">
                                                    <svg class="w-4 h-4 text-primary-500 dark:text-primary-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="m3 16 4 4 4-4"></path><path d="M7 20V4"></path></svg>
                                                </template>
                                                <template x-if="sorting.sort_by === 'status' && sorting.sort_direction === 'desc'">
                                                    <svg class="w-4 h-4 text-primary-500 dark:text-primary-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="m21 8-4-4-4 4"></path><path d="M17 4v16"></path></svg>
                                                </template>
                                            </div>
                                        </th>
                                        <th class="bg-background-100 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 px-6 py-3 text-primary-500 dark:text-primary-400">
                                            <div class="flex justify-between items-center cursor-pointer" @click="sortBy('created_at')">
                                                <span class="font-bold tracking-wider uppercase text-xs truncate">Created at</span>
                                                <template x-if="sorting.sort_by !== 'created_at'">
                                                    <svg class="w-4 h-4 text-primary-500 dark:text-primary-400 hover:opacity-70" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 16 4 4 4-4"></path><path d="M7 20V4"></path><path d="m21 8-4-4-4 4"></path><path d="M17 4v16"></path></svg>
                                                </template>
                                                <template x-if="sorting.sort_by === 'created_at' && sorting.sort_direction === 'asc'">
                                                    <svg class="w-4 h-4 text-primary-500 dark:text-primary-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="m3 16 4 4 4-4"></path><path d="M7 20V4"></path></svg>
                                                </template>
                                                <template x-if="sorting.sort_by === 'created_at' && sorting.sort_direction === 'desc'">
                                                    <svg class="w-4 h-4 text-primary-500 dark:text-primary-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="m21 8-4-4-4 4"></path><path d="M17 4v16"></path></svg>
                                                </template>
                                            </div>
                                        </th>
                                        <th class="bg-background-100 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 px-6 py-3 text-primary-500 dark:text-primary-400 font-bold tracking-wider uppercase text-xs truncate">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-background-900 divide-y divide-background-200 dark:divide-background-700">
                                    <template x-for="order in orders" :key="order.id">
                                        <tr class="hover:bg-background-50 dark:hover:bg-background-800">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-background-900 dark:text-background-100" x-text="order.id"></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-background-900 dark:text-background-100" x-text="order.order_number"></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-background-900 dark:text-background-100" x-text="order.user_fullname"></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-background-900 dark:text-background-100" x-text="order.payment_method"></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-background-900 dark:text-background-100" x-text="order.total_formatted"></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-background-900 dark:text-background-100" x-text="order.status"></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-background-900 dark:text-background-100" 
                                                x-text="order.created_at_formatted">
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <a :href="'/orders/' + order.id">
                                                    <x-lucide-pencil class="w-5 h-5 text-primary-800 dark:text-primary-500 cursor-pointer" />
                                                </a>
                                            </td>
                                        </tr>
                                    </template>
                                    <template x-if="orders.length === 0">
                                        <tr>
                                            <td colspan="8" class="px-6 py-8 text-center text-background-500 dark:text-background-400">
                                                Nessun ordine trovato
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Paginazione -->
                        <div class="bg-white dark:bg-background-900 px-4 py-3 flex items-center justify-between border-t border-background-200 dark:border-background-700 sm:px-6">
                            <div class="flex-1 flex justify-between sm:hidden">
                                <button
                                    @click="goToPage(1)"
                                    :disabled="pagination.current_page === 1"
                                    class="mr-2"
                                    :class="{ 'opacity-50 cursor-not-allowed': pagination.current_page === 1 }"
                                >
                                    <x-lucide-chevron-first class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                                </button>
                                <button
                                    @click="goToPage(pagination.current_page - 1)"
                                    :disabled="pagination.current_page === 1"
                                    class="mr-2"
                                    :class="{ 'opacity-50 cursor-not-allowed': pagination.current_page === 1 }"
                                >
                                    <x-lucide-chevron-left class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                                </button>
                                <span class="text-sm text-background-500 dark:text-background-300">Pagina <span x-text="pagination.current_page"></span> di <span x-text="pagination.last_page"></span></span>
                                <button
                                    @click="goToPage(pagination.current_page + 1)"
                                    :disabled="pagination.current_page === pagination.last_page"
                                    class="ml-2"
                                    :class="{ 'opacity-50 cursor-not-allowed': pagination.current_page === pagination.last_page }"
                                >
                                    <x-lucide-chevron-right class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                                </button>
                                <button
                                    @click="goToPage(pagination.last_page)"
                                    :disabled="pagination.current_page === pagination.last_page"
                                    class="ml-2"
                                    :class="{ 'opacity-50 cursor-not-allowed': pagination.current_page === pagination.last_page }"
                                >
                                    <x-lucide-chevron-last class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                                </button>
                            </div>
                            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm text-background-700 dark:text-background-300">
                                        Mostrando
                                        <span class="font-medium" x-text="pagination.from || 0"></span>
                                        -
                                        <span class="font-medium" x-text="pagination.to || 0"></span>
                                        di
                                        <span class="font-medium" x-text="pagination.total"></span>
                                        risultati
                                    </p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button
                                        @click="goToPage(1)"
                                        :disabled="pagination.current_page === 1"
                                        class=""
                                        :class="{ 'opacity-50 cursor-not-allowed': pagination.current_page === 1 }"
                                    >
                                        <x-lucide-chevron-first class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                                    </button>
                                    <button
                                        @click="goToPage(pagination.current_page - 1)"
                                        :disabled="pagination.current_page === 1"
                                        class=""
                                        :class="{ 'opacity-50 cursor-not-allowed': pagination.current_page === 1 }"
                                    >
                                        <x-lucide-chevron-left class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                                    </button>
                                    <span class="text-sm text-background-500 dark:text-background-300">Pagina <span x-text="pagination.current_page"></span> di <span x-text="pagination.last_page"></span></span>
                                    <button
                                        @click="goToPage(pagination.current_page + 1)"
                                        :disabled="pagination.current_page === pagination.last_page"
                                        class=""
                                        :class="{ 'opacity-50 cursor-not-allowed': pagination.current_page === pagination.last_page }"
                                    >
                                        <x-lucide-chevron-right class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                                    </button>
                                    <button
                                        @click="goToPage(pagination.last_page)"
                                        :disabled="pagination.current_page === pagination.last_page"
                                        class=""
                                        :class="{ 'opacity-50 cursor-not-allowed': pagination.current_page === pagination.last_page }"
                                    >
                                        <x-lucide-chevron-last class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
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
