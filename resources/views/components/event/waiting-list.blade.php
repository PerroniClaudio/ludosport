@props([
    'waiting_list' => [],
])

<div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-6 text-background-900 dark:text-background-100">

    <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('events.waiting_list') }}
    </h3>
    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

    <x-table striped="false" :columns="[
        [
            'name' => 'User',
            'field' => 'user_id',
            'columnClasses' => '', // classes to style table th
            'rowClasses' => '', // classes to style table td
        ],
        [
            'name' => 'Email',
            'field' => 'user_email',
            'columnClasses' => '', // classes to style table th
            'rowClasses' => '', // classes to style table td
        ],
        [
            'name' => 'Order ID',
            'field' => 'order_id',
            'columnClasses' => '', // classes to style table th
            'rowClasses' => '', // classes to style table td
        ],
        [
            'name' => 'Payment Method',
            'field' => 'payment_method',
            'columnClasses' => '', // classes to style table th
            'rowClasses' => '', // classes to style table td
        ],
        [
            'name' => 'Order Status',
            'field' => 'order_status',
            'columnClasses' => '', // classes to style table th
            'rowClasses' => '', // classes to style table td
        ],
        
    ]" :rows="$waiting_list">

    </x-table>
</div>