@props(['event' => ''])

<div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8" x-data="{}">
    <div class="flex justify-between">
        <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('events.thumbnail') }}</h3>
        <div>
            @php
                $authRole = auth()->user()->getRole();
                $formRoute = $authRole === 'admin' ? 'events.update.thumbnail' : $authRole . '.events.update.thumbnail';
            @endphp
            @if ($authRole === 'admin' || !$event->is_approved)
                <form method="POST" action="{{ route($formRoute, $event->id) }}"
                    enctype="multipart/form-data" x-ref="thumbform">
                    @csrf
                    @method('PUT')

                    <div class="flex flex-col gap-4">
                        <div class="flex flex-col gap-2">
                            <input type="file" name="thumbnail" id="thumbnail" class="hidden"
                                x-on:change="$refs.thumbform.submit()" />
                            <x-primary-button type="button" onclick="document.getElementById('thumbnail').click()">
                                {{ __('events.upload_thumbnail') }}
                            </x-primary-button>
                        </div>
                    </div>
                </form>
            @endif
        </div>
    </div>
    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
    @if ($event->thumbnail)
        <img src="{{ $event->thumbnail }}" alt="{{ $event->name }}" class="w-1/3 rounded-lg">
    @endif

</div>
