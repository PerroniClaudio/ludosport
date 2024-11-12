<div class="flex flex-col gap-4">
    <x-table striped="false" :columns="[
        [
            'name' => 'Name',
            'field' => 'name',
            'columnClasses' => '', // classes to style table th
            'rowClasses' => '', // classes to style table td
        ],
        [
            'name' => 'Nation',
            'field' => 'nation_name',
            'columnClasses' => '', // classes to style table th
            'rowClasses' => '', // classes to style table td
        ],
    ]" :rows="$academies">
        <x-slot name="tableActions">
            <a x-bind:href="'/academies/' + row.id">
                <x-lucide-pencil class="w-5 h-5 text-primary-800 dark:text-primary-500 cursor-pointer" />
            </a>
        </x-slot>
    </x-table>
</div>
