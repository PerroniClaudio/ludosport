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
    sortNestedColumn:null,
    sortDirection: 'asc',
    sort: function(columnIndex, isNested = false, nestedIndex = null) {
        if(!isNested){
            sortNestedColumn = null;
    
            if (this.sortColumn === columnIndex) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortColumn = columnIndex;
                this.sortDirection = 'asc';
            }
            this.rows = [...this.rows].sort((a, b) => {
                const column = this.columns[columnIndex];
                const aValue = a[column.field];
                const bValue = b[column.field];

                if (!isNaN(aValue) && !isNaN(bValue)) {
                    if (this.sortDirection === 'asc') {
                        return aValue - bValue;
                    } else {
                        return bValue - aValue;
                    }
                } else {
                    const aStr = String(aValue);
                    const bStr = String(bValue);

                    if (this.sortDirection === 'asc') {
                        return aStr.localeCompare(bStr);
                    } else {
                        return bStr.localeCompare(aStr);
                    }
                }
            });
        } else {
            if (this.sortColumn == columnIndex && this.sortNestedColumn == nestedIndex) {
                console.log('Saaame');
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                console.log('Not same', this.sortColumn, columnIndex, this.sortNestedColumn, nestedIndex);
                this.sortColumn = columnIndex;
                this.sortNestedColumn = nestedIndex;
                this.sortDirection = 'asc';
            }
            this.rows = [...this.rows].sort((a, b) => {
                const column = this.columns[columnIndex].nestedColumns[nestedIndex];
                const aValue = a[column.field];
                const bValue = b[column.field];

                if (!isNaN(aValue) && !isNaN(bValue)) {
                    if (this.sortDirection === 'asc') {
                        return aValue - bValue;
                    } else {
                        return bValue - aValue;
                    }
                } else {
                    const aStr = String(aValue);
                    const bStr = String(bValue);

                    if (this.sortDirection === 'asc') {
                        return aStr.localeCompare(bStr);
                    } else {
                        return bStr.localeCompare(aStr);
                    }
                }
            });
        }

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
        this.page = 1; // Reset to first page after search
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

    <p x-text="rows.count"></p>

    <div
        class="mb-5 bg-white dark:bg-background-900 rounded-lg shadow relative {{ $isDialogTable ? 'min-h-[600px] flex flex-col justify-between' : '' }}">
        <div class="flex justify-between items-center p-6">
            <div class="flex items-center justify-end w-full">
                <x-text-input type="text" x-on:input="searchByValue($event)" placeholder="Search..."
                    class="border border-background-100 dark:border-background-700 text-background-500 dark:text-background-300 rounded-lg p-2" />
            </div>
        </div>
        <div class="overflow-x-auto overflow-y-auto">

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
                                    class="bg-background-100 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 px-6 py-3 text-primary-500 dark:text-primary-400 ">
                                    <template x-if="!column.nestedColumns">
                                        <div class="flex justify-between items-center" :class="{ 'cursor-pointer': !column.dontSort }" x-on:click="!column.dontSort && sort(index)">
                                            <p class="font-bold tracking-wider uppercase text-xs truncate" x-text="column.name"></p>
                                            <template x-if="!column.dontSort">
                                                <x-lucide-arrow-down-up class="w-4 h-4 text-primary-500 dark:text-primary-400 hover:opacity-70" />
                                            </template>
                                        </div>
                                    </template>
                                    <template x-if="column.nestedColumns">
                                        <div class="flex">
                                            <template x-for="(nestedColumn, nestedIndex) in column.nestedColumns" :key="'nested-' + nestedIndex">
                                                <div :class="`${nestedColumn.columnClasses}`" class="flex justify-between items-center" x-on:click="!nestedColumn.dontSort && sort(index, true, nestedIndex)">
                                                    <span class="font-bold tracking-wider uppercase text-xs truncate" x-text="nestedColumn.name"></span>
                                                    <template x-if="!nestedColumn.dontSort">
                                                        <x-lucide-arrow-down-up class="w-4 h-4 text-primary-500 dark:text-primary-400 hover:opacity-70" />
                                                    </template>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
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
                                <td colspan="100%"
                                    class="text-center text-background-500 dark:text-background-300 py-10 px-4 text-sm">
                                    No records found
                                </td>
                            </tr>
                        @endisset
                    </template>
    
                    <template x-for="(row, rowIndex) in paginatedRows" :key="'row-' + rowIndex">
                        <tr
                            :class="{ 'relative bg-background-200 dark:bg-background-900': isStriped === true && ((rowIndex + 1) % 2 === 0) }">
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

        </div>

        <div class="flex justify-between items-center p-6">
            <div class="flex items-center justify-end w-full">

                <div class="flex items-center">
                    <button type="button" x-on:click="page = 1" class="mr-2" x-bind:disabled="page === 1">
                        <x-lucide-chevron-first class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                    </button>
                    <button type="button" x-on:click="page = page - 1" class="mr-2" x-bind:disabled="page === 1"
                        :class="{ 'opacity-50 cursor-not-allowed': page === 1 }">
                        <x-lucide-chevron-left class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                    </button>
                    </button>
                    <p class="text-sm text-background-500 dark:text-background-300">Page <span x-text="page"></span> of
                        <span x-text="totalPages()"></span>
                    </p>
                    <button type="button" x-on:click="page = page + 1" class="ml-2"
                        :class="{ 'opacity-50 cursor-not-allowed': page === totalPages() }"
                        x-bind:disabled="page === totalPages()">
                        <x-lucide-chevron-right class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                    </button>
                    <button type="button" x-on:click="page = totalPages()" class="ml-2"
                        x-bind:disabled="page === totalPages()">
                        <x-lucide-chevron-last class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
