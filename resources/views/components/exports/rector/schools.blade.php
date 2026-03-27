<form action="{{ route('rector.exports.store') }}" method="POST">
    @csrf

    <input name="type" type="hidden" value="schools">

    <p class="my-4">{{ __('exports.rector_schools_filter_message') }}</p>

    <div class="my-4">
        <x-primary-button>
            {{ __('exports.submit') }}
        </x-primary-button>
    </div>
</form>
