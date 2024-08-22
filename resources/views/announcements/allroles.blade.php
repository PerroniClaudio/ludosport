<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-background-800 dark:text-background-200 leading-tight">
                {{ __('announcements.title') }}
            </h2>

        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="grid grid-cols-12 gap-4" x-data="{
                    announcements: {{ $announcements }},
                    seenAnnouncements: {{ $seen_announcements }},
                    selectedAnnouncement: {{ $active_announcement }},
                    csrf: '{{ csrf_token() }}',
                    shouldShowAsNew: function(announcement) {
                        console.log(this.seenAnnouncements)
                
                        //Return true if announcement is not in seenAnnouncements
                
                        let isSeen = this.seenAnnouncements.find(element => element.id == announcement.id);
                
                        return !isSeen;
                    },
                    setSelectedAnnouncement: function(announcement) {
                        fetch(`announcements/${announcement.id}/seen`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrf
                            },
                            body: JSON.stringify({
                                _method: 'POST',
                            })
                        }).then(response => {
                            if (response.ok) {
                                this.seenAnnouncements.push(announcement);
                                this.selectedAnnouncement = this.announcements.find(element => element.id == announcement.id);
                            }
                        });
                    },
                    shouldShowSelectedItem: function(announcement) {
                        if (this.selectedAnnouncement != null) {
                            return this.selectedAnnouncement.id == announcement.id;
                        }
                
                        return false;
                    }
                
                }">
                    <div class="col-span-4 border-r dark:border-background-700 flex flex-col pb-4">
                        @if ($announcements->isEmpty())
                            <div class="text-center p-4">
                                <p>{{ __('announcements.no_announcements') }}</p>
                            </div>
                        @else
                            <template x-for="announcement in announcements" :key="announcement.id">

                                <div
                                    :class="{
                                    
                                        'text-primary-500': shouldShowSelectedItem(announcement),
                                        'text-background-800 dark:text-background-200': !shouldShowSelectedItem(
                                            announcement),
                                    
                                    }">
                                    <div x-show="shouldShowAsNew(announcement)"
                                        x-on:click="setSelectedAnnouncement(announcement)"
                                        class="cursor-pointer p-2 border-b dark:border-background-700 flex items-center justify-between">
                                        <div class="flex flex-col gap-1">
                                            <p x-text="announcement.object" class="font-bold"></p>
                                            <p x-text="new Date(announcement.created_at).toLocaleDateString('it-IT', {
                                                hour: 'numeric', 
                                                minute: 'numeric' 
                                            })"
                                                class="font-bold text-xs">
                                            </p>
                                        </div>
                                        <x-lucide-chevron-right class="h-6 w-6 font-bold" />
                                    </div>

                                    <div x-show="!shouldShowAsNew(announcement)"
                                        x-on:click="setSelectedAnnouncement(announcement)"
                                        x-on:click="selectedAnnouncement = announcement.id"
                                        class="cursor-pointer p-2 border-b dark:border-background-700 flex items-center justify-between">
                                        <div class="flex flex-col gap-1">
                                            <p x-text="announcement.object" class=""></p>
                                            <p x-text="new Date(announcement.created_at).toLocaleDateString('it-IT', {
                                                hour: 'numeric', 
                                                minute: 'numeric' 
                                            })"
                                                class=" text-xs">
                                            </p>
                                        </div>
                                        <x-lucide-chevron-right class="h-6 w-6" />

                                    </div>
                                </div>


                            </template>
                        @endif
                    </div>
                    <div class="col-span-8 text-background-800 dark:text-background-200 p-8">
                        <div x-show="!selectedAnnouncement" class="min-h-[55vh] flex flex-col justify-between">
                            <div>
                                <h3 class="text-2xl text-background-800 dark:text-background-200">
                                    {{ __('announcements.no_selected_announcement') }}</h3>
                                <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                                <div class="flex-1">
                                    <p>{{ __('announcements.no_selected_announcement_info') }}</p>
                                </div>
                            </div>

                        </div>
                        <div x-show="selectedAnnouncement" class="min-h-[55vh] flex flex-col justify-between">
                            <div>
                                <h3 x-text="selectedAnnouncement.object"
                                    class="text-2xl text-background-800 dark:text-background-200"></h3>
                                <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                            </div>
                            <div class="flex-1">
                                <p x-text="selectedAnnouncement.content"></p>
                            </div>
                            <div class="flex justify-end">
                                <p
                                    x-text="new Date(selectedAnnouncement.created_at).toLocaleDateString('it-IT', {
                                        hour: 'numeric', 
                                        minute: 'numeric' 
                                    })">
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
