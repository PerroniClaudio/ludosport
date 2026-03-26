<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-background-800 dark:text-background-200 leading-tight">
            {{ __('dashboard.title') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 flex flex-col gap-4">
            @php
                $isMinorPendingApproval = auth()->user()->isMinorPendingApproval();
            @endphp
            @if (auth()->user()->is_user_minor && !auth()->user()->has_user_uploaded_documents)
                <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-background-900 dark:text-background-100">
                        <div class="flex items-start gap-3 text-red-500">
                            <x-lucide-circle-alert class="h-6 w-6 mt-0.5 shrink-0" />
                            <div>
                                <h3 class="text-background-800 dark:text-background-200 text-2xl">
                                    {{ __('dashboard.athlete_minor_documents_required_title') }}
                                </h3>
                                <p class="mt-2 text-sm text-background-700 dark:text-background-300">
                                    {{ auth()->user()->uploaded_documents_path ? __('dashboard.athlete_minor_documents_denied_text') : __('dashboard.athlete_minor_documents_required_text') }}
                                </p>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('users.update-minor-documents', auth()->id()) }}"
                            enctype="multipart/form-data" class="mt-6 lg:w-2/3"
                            x-data="{
                                isSubmitting: false,
                                minorDocumentsError: '',
                                validateMinorDocuments(event) {
                                    const file = event.target.files?.[0];
                                    const maxSize = 10 * 1024 * 1024;

                                    if (!file) {
                                        this.minorDocumentsError = '';
                                        return;
                                    }

                                    if (file.size > maxSize) {
                                        this.minorDocumentsError = '{{ __('auth.minor_documents_too_large') }}';
                                        event.target.value = '';
                                        return;
                                    }

                                    this.minorDocumentsError = '';
                                }
                            }"
                            @submit="if (minorDocumentsError) { return false; } isSubmitting = true">
                            @csrf
                            @method('PUT')

                            <x-input-label for="dashboard_minor_documents" :value="__('auth.minor_documents')" />
                            <input id="dashboard_minor_documents" name="minor_documents" type="file"
                                @change="validateMinorDocuments($event)"
                                class="mt-3 block w-full rounded-lg border border-background-200 bg-white px-4 py-3 text-sm text-background-700 shadow-sm file:mr-4 file:rounded-md file:border-0 file:bg-primary-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-primary-700 hover:file:bg-primary-100 dark:border-background-700 dark:bg-background-900 dark:text-background-200 dark:file:bg-primary-950/50 dark:file:text-primary-300" />
                            <p class="mt-2 text-xs text-background-500 dark:text-background-400">
                                {{ __('auth.minor_documents_help') }}
                            </p>
                            <p x-show="minorDocumentsError" x-text="minorDocumentsError"
                                class="mt-2 text-sm text-red-600 dark:text-red-400"></p>
                            <x-input-error :messages="$errors->get('minor_documents')" class="mt-2" />

                            <div class="mt-4 flex justify-end">
                                <x-primary-button x-bind:disabled="isSubmitting" x-bind:aria-busy="isSubmitting">
                                    <span x-show="!isSubmitting" x-cloak>
                                        {{ __('dashboard.athlete_minor_documents_upload_button') }}
                                    </span>
                                    <span x-show="isSubmitting" x-cloak>{{ __('auth.registering') }}</span>
                                </x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            @if (auth()->user()->is_user_minor && auth()->user()->has_user_uploaded_documents && !auth()->user()->has_admin_approved_minor)
                <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-background-900 dark:text-background-100">
                        <div class="flex items-start gap-3">
                            <x-lucide-clock class="h-6 w-6 mt-0.5 shrink-0 text-yellow-500" />
                            <div>
                                <h3 class="text-background-800 dark:text-background-200 text-2xl">
                                    {{ __('dashboard.athlete_minor_documents_pending_title') }}
                                </h3>
                                <p class="mt-2 text-sm text-background-700 dark:text-background-300">
                                    {{ __('dashboard.athlete_minor_documents_pending_text') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @unless ($isMinorPendingApproval)
                @if (auth()->user()->has_paid_fee)
                    @if (auth()->user()->isFeeExpiring())
                        <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg mb-8">
                            <div class="p-6 text-background-900 dark:text-background-100">
                                <div class="text-red-500 flex items-center gap-1">
                                    <x-lucide-circle-alert class="h-6 w-6" />
                                    {{ __('users.fee_about_expire_text') }}
                                </div>
                            </div>
                        </div>
                    @endif
                @endif


                <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-background-900 dark:text-background-100">
                        <h3 class="text-background-800 dark:text-background-200 text-2xl">
                            {{ __('dashboard.athlete_new_announcements') }}
                        </h3>
                        <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                        @if (collect($announcements)->count() > 0)
                            <p>
                                {{ __('dashboard.athlete_announcements_text', [
                                    'count' => collect($announcements)->count(),
                                ]) }}
                            </p>
                        @else
                            <p>
                                {{ __('dashboard.athlete_no_announcements') }}
                            </p>
                        @endif

                        <div class="flex justify-end">
                            <a href="{{ route('athlete.announcements.index') }}">
                                <x-primary-button>
                                    <x-lucide-arrow-right class="h-6 w-6 text-white" />
                                </x-primary-button>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-background-900 dark:text-background-100">
                        <h3 class="text-background-800 dark:text-background-200 text-2xl">
                            {{ __('dashboard.athlete_upcoming_events') }}
                        </h3>
                        <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

                        @unless (count(collect(auth()->user()->eventResults())) == 0)
                            @foreach (auth()->user()->eventResults()->get() as $eventSubscription)
                                @if (\Carbon\Carbon::parse($eventSubscription->event->start_date)->isFuture())
                                    <div x-data="{
                                    start_date: '{{ $eventSubscription->event->start_date }}',
                                    end_date: '{{ $eventSubscription->event->end_date }}',
                                }"
                                        class="bg-white text-background-800 dark:bg-background-900 rounded dark:text-background-300 p-4 flex flex-col justify-between gap-2">
                                        <p class="text-lg font-semibold group-hover:text-primary-500">
                                            {{ $eventSubscription->event->name }}
                                        </p>
                                        <div class="flex items-center gap-1">
                                            <x-lucide-calendar-days class="w-4 h-4 text-primary-500" />
                                            <div class="flex flex-row gap-2">
                                                <p x-text="new Date(start_date).toLocaleDateString('it-IT', {
                                            hour: 'numeric', 
                                            minute: 'numeric' 
                                        })"
                                                    class="text-xs"></p>
                                                <span class="text-xs"> - </span>
                                                <p x-text="new Date(end_date).toLocaleDateString('it-IT', {
                                            hour: 'numeric', 
                                            minute: 'numeric' 
                                        })"
                                                    class="text-xs"></p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <x-lucide-map-pin class="w-4 h-4 text-primary-500" />
                                            <span class="text-sm font-semibold group-hover:text-primary-500">
                                                {{ $eventSubscription->event->address }} ,
                                                {{ $eventSubscription->event->postal_code }} ,
                                                {{ $eventSubscription->event->city }}
                                            </span>
                                        </div>

                                        <a href="{{ route('event-detail', $eventSubscription->event->slug) }}">
                                            <x-primary-button>
                                                {{ __('website.events_list_button') }}
                                            </x-primary-button>
                                        </a>
                                    </div>
                                @endif
                            @endforeach
                        @endunless
                    </div>
                </div>
            @endunless

        </div>
    </div>
</x-app-layout>
