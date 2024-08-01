<footer class="bg-background-100 dark:bg-background-950">
    <div class="container px-6 py-8 mx-auto">
        <div class="flex flex-col items-center text-center">
            <a href="#">
                <x-application-logo class="block h-9 w-auto" />
            </a>

            <p class="max-w-md mx-auto mt-4 text-background-500 dark:text-background-400">
                {{ __('website.footer_text_1') }}</p>
            <p class="max-w-md mx-auto mt-4 text-background-500 dark:text-background-400">
                {{ __('website.footer_text_2') }}</p>


        </div>

        <hr class="my-10 border-background-200 dark:border-background-700" />

        <div class="flex flex-col items-center sm:flex-row sm:justify-between">
            <p class="text-xs text-background-500 dark:text-background-400 text-center">
                {{ __('website.footer_text_coypright') }}</p>


        </div>
    </div>
</footer>
