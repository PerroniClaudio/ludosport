@if (session()->has('success'))
    <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show"
        class="fixed bg-primary-500 text-white py-2 px-4 rounded-xl top-8 right-32 text-sm">
        <p>{{ session('success') }}</p>
    </div>
@endif

@if (session()->has('status'))
    <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show"
        class="fixed bg-background-500 text-white py-2 px-4 rounded-xl top-8 left-32 text-sm">
        <p>{{ session('status') }}</p>
    </div>
@endif

@if (session()->has('error'))
    <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show"
        class="fixed bg-red-500 text-white py-2 px-4 rounded-xl top-8 left-32 text-sm">
        <p>{{ session('error') }}</p>
    </div>
@endif


<div class="hidden text-white py-2 px-4 rounded-xl top-8 text-sm z-50" id="custom-error-message">
    <p></p>
</div>

<script>
    class FlashMessage {
        constructor(element, timeout = 4000) {
            this.element = element;
            this.timeout = timeout;
            this.show();
        }

        show() {
            this.element.classList.remove('hidden');
            this.element.classList.add('fixed');
            setTimeout(() => this.hide(), this.timeout);
        }

        hide() {
            this.element.classList.remove('fixed');
            this.element.classList.add('hidden');
        }

        static displayCustomMessage(message, timeout = 4000) {
            const customErrorMessage = document.getElementById('custom-error-message');
            customErrorMessage.querySelector('p').textContent = message;
            customErrorMessage.classList.add('bg-primary-500');
            customErrorMessage.classList.add('right-32');
            new FlashMessage(customErrorMessage, timeout);
        }

        static displayError(message, timeout = 4000) {
            const errorElement = document.getElementById('custom-error-message');
            errorElement.querySelector('p').textContent = message;
            errorElement.classList.add('bg-red-500');
            errorElement.classList.add('left-32');
            new FlashMessage(errorElement, timeout);
        }
    }
</script>
