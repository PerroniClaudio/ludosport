<button
    {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 bg-white dark:bg-background-800 border border-background-300 dark:border-background-500 rounded-md font-semibold text-xs text-background-700 dark:text-background-300 uppercase tracking-widest shadow-sm hover:bg-background-50 dark:hover:bg-background-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-background-800 disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
