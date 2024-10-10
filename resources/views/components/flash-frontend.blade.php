<!-- resources/views/components/flash-message.blade.php -->
@if (session('success'))
    <div id="flash-message" 
      class="alert alert-success"
      {{-- class="fixed bg-primary-500 text-white py-2 px-4 rounded-xl bottom-8 right-32 text-sm" --}}
    >
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div id="flash-message" 
      class="alert alert-danger"
      {{-- class="fixed bg-red-500 text-white py-2 px-4 rounded-xl bottom-8 left-32 text-sm" --}}
    >
        {{ session('error') }}
    </div>
@endif

@if (session('info'))
    <div id="flash-message" 
      class="alert alert-info"
      {{-- class="fixed bg-background-500 text-white py-2 px-4 rounded-xl bottom-8 left-32 text-sm" --}}
    >
        {{ session('info') }}
    </div>
@endif

<script>
    // Nascondi il messaggio dopo 5 secondi
    setTimeout(function() {
        let flashMessageDiv = document.getElementById('flash-message');
        if (flashMessageDiv) {
            flashMessageDiv.style.display = 'none';
        }
    }, 5000);
</script>


{{-- @if (session()->has('success'))

    <div x-data="{ show: true }"
        x-init="setTimeout(() => show = false, 4000)"
        x-show="show"
        class="fixed bg-primary-500 text-white py-2 px-4 rounded-xl bottom-8 right-32 text-sm"
    >
        <p>{{ session('success') }}</p>
    </div>

@endif

@if (session()->has('status'))

    <div x-data="{ show: true }"
        x-init="setTimeout(() => show = false, 4000)"
        x-show="show"
        class="fixed bg-background-500 text-white py-2 px-4 rounded-xl bottom-8 left-32 text-sm"
    >
        <p>{{ session('status') }}</p>
    </div>

@endif

@if (session()->has('error'))

    <div x-data="{ show: true }"
        x-init="setTimeout(() => show = false, 4000)"
        x-show="show"
        class="fixed bg-red-500 text-white py-2 px-4 rounded-xl bottom-8 left-32 text-sm"
    >
        <p>{{ session('error') }}</p>
    </div>

@endif --}}