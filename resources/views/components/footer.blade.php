<footer class="bg-background-100 dark:bg-background-950">
    <div class="container px-6 py-8 mx-auto">
        <div class="flex flex-col items-center text-center">
            <a href="#">
                <x-application-logo class="block h-9 w-9" />
            </a>

            <p class="max-w-md mx-auto mt-4 text-background-500 dark:text-background-400">
                {{ __('website.footer_text_1') }}
                <a class="external text-primary-300 hover:text-primary-500" href="https://www.lamadiluce.it" target="_blank">
                    {{ __('website.footer_link_text_1') }}
                </a>
            </p>
            <p class="max-w-md mx-auto mt-4 text-background-500 dark:text-background-400">
                {{ __('website.footer_text_2') }}
                <a class="external text-primary-300 hover:text-primary-500" href="https://www.udemy.com/course/lightsaber-combat-by-ludosport-master-form-1/?couponCode=24T4MT120424" target="_blank">
                    {{ __('website.footer_link_text_2') }}
                </a>
            </p>
            <p class="max-w-md mx-auto mt-4 text-background-500 dark:text-background-400">
                {{ __('website.footer_text_3') }}
                <a class="external text-primary-300 hover:text-primary-500" href="https://www.ludosport.net" target="_blank">
                    {{ __('website.footer_link_text_3') }}
                </a>
            </p>
            <a class="text-background-800 hover:text-primary" href="/cookie-policy">Cookie policy</a>
        
        </div>

        <hr class="my-10 border-background-200 dark:border-background-700" />

        <div class="flex flex-col items-center sm:flex-row sm:justify-between">
            <p class="text-xs text-background-500 dark:text-background-400 text-center">
                {{ __('website.footer_text_coypright') }}</p>


        </div>
    </div>
</footer>
