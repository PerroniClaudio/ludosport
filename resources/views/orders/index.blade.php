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
                    allOrders: [],
                    filteredOrders: [],
                    sorting: {
                        sort_by: null,
                        sort_direction: null
                    },
                    filters: {
                        min_price: '',
                        max_price: '',
                        min_date: '',
                        max_date: '',
                        status: [],
                        search: ''
                    },
                    page: 1,
                    pageLength: 10,
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
                                this.sorting.sort_by = null;
                                this.sorting.sort_direction = null;
                            } else {
                                this.sorting.sort_direction = 'asc';
                            }
                        } else {
                            this.sorting.sort_by = field;
                            this.sorting.sort_direction = 'asc';
                        }
                        this.applySortAndSearch();
                    },
                   applySortAndSearch() {
                        let result = [...this.allOrders];
                        
                        // Ricerca
                        if (this.filters.search) {
                            const search = this.filters.search.toLowerCase();
                            result = result.filter(order => {
                                return Object.values(order).some(value => {
                                    return String(value).toLowerCase().includes(search);
                                });
                            });
                        }
                        
                        // Ordinamento
                        if (this.sorting.sort_by && this.sorting.sort_direction) {
                            result.sort((a, b) => {
                                let aValue = a[this.sorting.sort_by];
                                let bValue = b[this.sorting.sort_by];
                                
                                // Gestione numeri
                                if (this.sorting.sort_by === 'total' || this.sorting.sort_by === 'id') {
                                    aValue = parseFloat(aValue);
                                    bValue = parseFloat(bValue);
                                    return this.sorting.sort_direction === 'asc' ? aValue - bValue : bValue - aValue;
                                }
                                
                                // Gestione date
                                if (this.sorting.sort_by === 'created_at') {
                                    aValue = new Date(aValue);
                                    bValue = new Date(bValue);
                                    return this.sorting.sort_direction === 'asc' ? aValue - bValue : bValue - aValue;
                                }
                                
                                // Gestione stringhe (case-insensitive)
                                aValue = String(aValue).toLowerCase();
                                bValue = String(bValue).toLowerCase();
                                return this.sorting.sort_direction === 'asc' 
                                    ? aValue.localeCompare(bValue)
                                    : bValue.localeCompare(aValue);
                            });
                        }
                        
                        this.filteredOrders = result;
                        this.page = 1;
                    },
                    async loadOrders() {
                        this.loading = true;
                        try {
                            const params = new URLSearchParams();
                            if (this.filters.min_price) params.append('min_price', this.filters.min_price);
                            if (this.filters.max_price) params.append('max_price', this.filters.max_price);
                            if (this.filters.min_date) params.append('min_date', this.filters.min_date);
                            if (this.filters.max_date) params.append('max_date', this.filters.max_date);
                            this.filters.status.forEach(status => params.append('status[]', status));
                            
                            const response = await fetch('{{ route('orders.data') }}?' + params.toString());
                            const result = await response.json();
                            
                            this.allOrders = result.data;
                            this.applySortAndSearch();
                        } catch (error) {
                            console.error('Error loading orders:', error);
                        } finally {
                            this.loading = false;
                        }
                    },
                    applyFilters() {
                        this.loadOrders();
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
                        this.loadOrders(1);
                    },
                    totalPages() {
                        return Math.ceil(this.filteredOrders.length / this.pageLength);
                    },
                    paginatedOrders() {
                        const start = (this.page - 1) * this.pageLength;
                        const end = start + this.pageLength;
                        return this.filteredOrders.slice(start, end);
                    },
                    goToPage(page) {
                        if (page >= 1 && page <= this.totalPages()) {
                            this.page = page;
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
                                        @change="applyFilters()"
                                        step="0.01"
                                        min="0"
                                        class="w-24 text-xs py-0.5 px-1.5 rounded border-background-300 dark:border-background-600 dark:bg-background-700 dark:text-background-100"
                                        placeholder="Min"
                                    >
                                    <span class="text-xs text-background-400">-</span>
                                    <input 
                                        type="number" 
                                        x-model="filters.max_price"
                                        @change="applyFilters()"
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
                                        @change="applyFilters()"
                                        class="w-32 text-xs py-0.5 px-1.5 rounded border-background-300 dark:border-background-600 dark:bg-background-700 dark:text-background-100"
                                    >
                                    <span class="text-xs text-background-400">-</span>
                                    <input 
                                        type="date" 
                                        x-model="filters.max_date"
                                        @change="applyFilters()"
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
                                                @change="toggleStatus({{ $status }}); applyFilters()"
                                                class="w-3 h-3 rounded border-background-300 dark:border-background-600 dark:bg-background-700 text-primary-600 focus:ring-primary-500"
                                            >
                                            <span class="text-xs">{{ __('orders.status' . $status) }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            
                            <!-- Reset -->
                            <div class="flex-shrink-0 self-end">
                                <button 
                                    @click="resetFilters()"
                                    type="button"
                                    class="inline-block px-2 py-0.5 text-xs bg-background-200 hover:bg-background-300 dark:bg-background-700 dark:hover:bg-background-600 text-background-900 dark:text-background-100 rounded font-medium transition-colors"
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
                                    @input.debounce.500ms="applySortAndSearch()"
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
                                    <template x-for="order in paginatedOrders()" :key="order.id">
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
                                    <template x-if="filteredOrders.length === 0">
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
                                    :disabled="page === 1"
                                    class="mr-2"
                                    :class="{ 'opacity-50 cursor-not-allowed': page === 1 }"
                                >
                                    <x-lucide-chevron-first class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                                </button>
                                <button
                                    @click="goToPage(page - 1)"
                                    :disabled="page === 1"
                                    class="mr-2"
                                    :class="{ 'opacity-50 cursor-not-allowed': page === 1 }"
                                >
                                    <x-lucide-chevron-left class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                                </button>
                                <span class="text-sm text-background-500 dark:text-background-300">Pagina <span x-text="page"></span> di <span x-text="totalPages()"></span></span>
                                <button
                                    @click="goToPage(page + 1)"
                                    :disabled="page === totalPages()"
                                    class="ml-2"
                                    :class="{ 'opacity-50 cursor-not-allowed': page === totalPages() }"
                                >
                                    <x-lucide-chevron-right class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                                </button>
                                <button
                                    @click="goToPage(totalPages())"
                                    :disabled="page === totalPages()"
                                    class="ml-2"
                                    :class="{ 'opacity-50 cursor-not-allowed': page === totalPages() }"
                                >
                                    <x-lucide-chevron-last class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                                </button>
                            </div>
                            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm text-background-700 dark:text-background-300">
                                        Mostrando
                                        <span class="font-medium" x-text="((page - 1) * pageLength) + 1"></span>
                                        -
                                        <span class="font-medium" x-text="Math.min(page * pageLength, filteredOrders.length)"></span>
                                        di
                                        <span class="font-medium" x-text="filteredOrders.length"></span>
                                        risultati
                                    </p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button
                                        @click="goToPage(1)"
                                        :disabled="page === 1"
                                        class=""
                                        :class="{ 'opacity-50 cursor-not-allowed': page === 1 }"
                                    >
                                        <x-lucide-chevron-first class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                                    </button>
                                    <button
                                        @click="goToPage(page - 1)"
                                        :disabled="page === 1"
                                        class=""
                                        :class="{ 'opacity-50 cursor-not-allowed': page === 1 }"
                                    >
                                        <x-lucide-chevron-left class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                                    </button>
                                    <span class="text-sm text-background-500 dark:text-background-300">Pagina <span x-text="page"></span> di <span x-text="totalPages()"></span></span>
                                    <button
                                        @click="goToPage(page + 1)"
                                        :disabled="page === totalPages()"
                                        class=""
                                        :class="{ 'opacity-50 cursor-not-allowed': page === totalPages() }"
                                    >
                                        <x-lucide-chevron-right class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                                    </button>
                                    <button
                                        @click="goToPage(totalPages())"
                                        :disabled="page === totalPages()"
                                        class=""
                                        :class="{ 'opacity-50 cursor-not-allowed': page === totalPages() }"
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
