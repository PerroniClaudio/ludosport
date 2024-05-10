@props([
    'rows' => [],
    'columns' => [],
    'striped' => false,
    'actionText' => 'Action',
    'tableTextLinkLabel' => 'Link',
    'isDialogTable' => false,
])

<div x-data="{
    columns: {{ collect($columns) }},
    rows: {{ collect($rows) }},
    isStriped: Boolean({{ $striped }}),
    sortColumn: null,
    sortDirection: 'asc',
    sort: function(columnIndex) {
        if (this.sortColumn === columnIndex) {
            this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            this.sortColumn = columnIndex;
            this.sortDirection = 'asc';
        }

        this.rows = [...this.rows].sort((a, b) => {
            const column = this.columns[columnIndex];
            const aValue = String(a[column.field]);
            const bValue = String(b[column.field]);

            if (this.sortDirection === 'asc') {
                return aValue.localeCompare(bValue);
            } else {
                return bValue.localeCompare(aValue);
            }
        });
    },
    searchByValue: function(e) {
        const search = e.target.value.toLowerCase();
        if (search === '') {
            this.rows = {{ collect($rows) }};
        } else {
            this.rows = {{ collect($rows) }}.filter(row => {
                return Object.values(row).some(value => {
                    return String(value).toLowerCase().includes(search);
                });
            });
        }
    },
    page: 1,
    pageLength: 10,
    totalPages: function() {
        return Math.ceil(this.rows.length / this.pageLength);
    },
    paginatedRows: function() {
        const start = (this.page - 1) * this.pageLength;
        const end = start + this.pageLength;
        return this.rows.slice(start, end);
    },

}" x-cloak id="">
    <div class="mb-5 overflow-x-auto bg-white dark:bg-background-900 rounded-lg shadow overflow-y-auto relative {{ $isDialogTable ? "min-h-[600px] flex flex-col justify-between" : "" }}" >
        <div class="flex justify-between items-center p-6">
            <div class="flex items-center justify-end w-full">
                <x-text-input type="text" x-on:input="searchByValue($event)" placeholder="Search..."
                    class="border border-background-100 dark:border-background-700 text-background-500 dark:text-background-300 rounded-lg p-2" />
            </div>
        </div>
        <table
            class="border-collapse table-auto w-full whitespace-no-wrap bg-white dark:bg-background-900 table-striped relative flex-1">

            <thead>
                <tr class="text-left">
                    @isset($tableColumns)
                        {{ $tableColumns }}
                    @else
                        @isset($tableTextLink)
                            <th
                                class="bg-background-100 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 px-6 py-3 text-primary-500 dark:text-primary-400 font-bold tracking-wider uppercase text-xs truncate">
                                {{ $tableTextLinkLabel }}
                            </th>
                        @endisset

                        <template x-for="(column, index) in columns">
                            <th :class="`${column.columnClasses}`"
                                class="bg-background-100 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 px-6 py-3 text-primary-500 dark:text-primary-400">
                                <div class="flex justify-between items-center" @click="sort(index)">
                                    <p class="font-bold tracking-wider uppercase text-xs truncate" x-text="column.name"></p>
                                    <x-lucide-arrow-down-up class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                                </div>
                            </th>
                        </template>

                        {{-- Displays when Custom name slots for action links is shown --}}
                        @isset($tableActions)
                            <th
                                class="bg-background-100 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 px-6 py-3 text-primary-500 dark:text-primary-400 font-bold tracking-wider uppercase text-xs truncate">
                                {{ $actionText }}</th>
                        @endisset
                    @endisset
                </tr>
            </thead>

            <tbody>

                <template x-if="rows.length === 0">
                    @isset($empty)
                        {{ $empty }}
                    @else
                        <tr>
                            <td colspan="100%" class="text-center text-background-500 dark:text-background-300 py-10 px-4 text-sm">
                                No records found
                            </td>
                        </tr>
                    @endisset
                </template>

                <template x-for="(row, rowIndex) in paginatedRows" :key="'row-' + rowIndex">
                    <tr
                        :class="{ 'bg-background-200 dark:bg-background-900': isStriped === true && ((rowIndex + 1) % 2 === 0) }">
                        @isset($tableRows)
                            {{ $tableRows }}
                        @else
                            @isset($tableTextLink)
                                <td
                                    class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap">
                                    {{ $tableTextLink }}
                                </td>
                            @endisset

                            <template x-for="(column, columnIndex) in columns" :key="'column-' + columnIndex">
                                <td :class="`${column.rowClasses}`"
                                    class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap">
                                    <div x-text="`${row[column.field]}`" class="truncate"></div>
                                </td>
                            </template>

                            {{-- Custom name slots for action links --}}
                            @isset($tableActions)
                                <td
                                    class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap">
                                    {{ $tableActions }}
                                </td>
                            @endisset
                        @endisset
                    </tr>
                </template>

            </tbody>

        </table>

        <div class="flex justify-between items-center p-6">
            <div class="flex items-center justify-end w-full">

                <div class="flex items-center">
                    <button x-on:click="page = 1" class="mr-2" x-bind:disabled="page === 1">
                        <x-lucide-chevron-first class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                    </button>
                    <button x-on:click="page = page - 1" class="mr-2" x-bind:disabled="page === 1"
                        :class="{ 'opacity-50 cursor-not-allowed': page === 1 }">
                        <x-lucide-chevron-left class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                    </button>
                    </button>
                    <p class="text-sm text-background-500 dark:text-background-300">Page <span x-text="page"></span> of
                        <span x-text="totalPages()"></span>
                    </p>
                    <button x-on:click="page = page + 1" class="ml-2"
                        :class="{ 'opacity-50 cursor-not-allowed': page === totalPages() }"
                        x-bind:disabled="page === totalPages()">
                        <x-lucide-chevron-right class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                    </button>
                    <button x-on:click="page = totalPages()" class="ml-2" x-bind:disabled="page === totalPages()">
                        <x-lucide-chevron-last class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
