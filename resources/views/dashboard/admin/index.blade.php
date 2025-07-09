<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-background-800 dark:text-background-200 leading-tight">
            {{ __('dashboard.admin_title') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="flex flex-col gap-4" x-data="{
                rankRequests: null,
            
                async getPendingRequests() {
                    const response = await fetch('/pending-rank-requests');
                    const data = await response.json();
                    return data;
                },
            
                async init() {
                    this.rankRequests = await this.getPendingRequests();
                }
            }">

                <x-dashboard.admin.user-world-numbers />

                <div class="grid grid-cols-2 gap-4">

                    {{-- <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-background-900 dark:text-background-100">
                            <h3 class="text-background-800 dark:text-background-200 text-2xl">
                                {{ __('dashboard.admin_fees') }}
                            </h3</p>
                            <div class="flex justify-end">
                                <a href="/rank-requests">
                                    <x-primary-button>
                                        <x-lucide-arrow-right class="h-6 w-6 text-white" />
                                    </x-primary-button>
                                </a>
                            </div>
                        </div>
                    </div> --}}

                    <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-background-900 dark:text-background-100">
                            <h3 class="text-background-800 dark:text-background-200 text-2xl">
                                {{ __('dashboard.admin_rank_requests') }}
                            </h3>
                            <p
                                x-text="`{{ __('dashboard.admin_rank_requests_text', ['count' => '${rankRequests}']) }}`">
                            </p>
                            <div class="flex justify-end">
                                <a href="/rank-requests">
                                    <x-primary-button>
                                        <x-lucide-arrow-right class="h-6 w-6 text-white" />
                                    </x-primary-button>
                                </a>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Paga in bulk le fee degli utenti non attivi -->

                {{-- <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-background-900 dark:text-background-100">
                        <h3 class="text-background-800 dark:text-background-200 text-2xl">
                            {{ __('dashboard.rector_bulk_fee') }}
                        </h3>
                        <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                        <p>{{ __('dashboard.rector_bulk_fee_text') }}</p>
                        <div class="flex justify-end ">
                            <a href="{{ route('rector.fees.index') }}">
                                <x-primary-button>
                                    <x-lucide-arrow-right class="h-6 w-6 text-white" />
                                </x-primary-button>
                            </a>
                        </div>
                    </div>
                </div> --}}

                {{-- una sola request per avere tutte le liste annidate. --}}
                <div class="min-h-[635px]"
                    x-data='{
                        nationsData: [],

                        selectedNation: null,
                        selectedAcademy: null,
                        selectedSchool: null,
                        
                        selectedNationData: [],
                        selectedAcademyData: [],
                        selectedSchoolData: [],

                        setSelectedNation(nationId) {
                            this.selectedNation = this.nationsData.find(n => n.id === nationId);
                            this.selectedNationData = this.nationsData.find(n => n.id === nationId).academies;
                        },
                        setSelectedAcademy(academyId) {
                            this.selectedAcademy = this.selectedNation.academies.find(a => a.id === academyId);
                            this.selectedAcademyData = this.selectedNation.academies.find(a => a.id === academyId).schools;
                        },
                        setSelectedSchool(schoolId) {
                            this.selectedSchool = this.selectedAcademy.schools.find(s => s.id === schoolId);
                            this.selectedSchoolData = this.selectedAcademy.schools.find(s => s.id === schoolId)?.courses;
                        },

                        setLevel (level) {
                            switch (level) {
                                case "world":
                                    this.selectedNation = null;
                                    this.selectedAcademy = null;
                                    this.selectedSchool = null;
                                    break;
                                case "nation":
                                    this.selectedAcademy = null;
                                    this.selectedSchool = null;
                                    break;
                                case "academy":
                                    this.selectedSchool = null;
                                    break;
                                default:
                                    break;
                            }
                        },

                        init() {
                            this.$el.addEventListener("usernationgraph-data", (e) => {
                                this.nationsData = e.detail;
                                console.log("nationsData: ", this.nationsData);
                            })

                            this.$el.addEventListener("nation-selected", (e) => {
                                this.setSelectedNation(e.detail);
                            })
                            this.$el.addEventListener("academy-selected", (e) => {
                                this.setSelectedAcademy(e.detail);
                            })
                            this.$el.addEventListener("school-selected", (e) => {
                                this.setSelectedSchool(e.detail);
                            })
                        }
                    }'>
                    <!-- Grafico a torta per vedere la divisione di utenti tra le nazioni + Confronto tra iscritti anno precedente e iscritti anno corrente  -->
                    <template x-if="!selectedNation">
                        <x-dashboard.admin.user-nation-graph
                            @nation-selected.window="setSelectedNation($event.detail)" />
                    </template>

                    <!-- Grafico a torta per vedere la divisione di utenti tra le accademie + Confronto tra iscritti anno precedente e iscritti anno corrente  -->
                    <template x-if="selectedNation && !selectedAcademy">
                        <x-dashboard.admin.user-academy-graph nation="selectedNation"
                            selectedNationData="selectedNationData" nationsData="nationsData"
                            @academy-selected.window="setSelectedAcademy($event.detail)" />
                    </template>

                    <!-- Grafico a torta per vedere la divisione di utenti tra le scuole + Confronto tra iscritti anno precedente e iscritti anno corrente  -->
                    <template x-if="selectedNation && selectedAcademy && !selectedSchool">
                        <x-dashboard.admin.user-school-graph academy="selectedAcademy"
                            selectedAcademyData="selectedAcademyData"
                            @school-selected.window="setSelectedSchool($event.detail)" />
                    </template>

                    <!-- Grafico a torta per vedere la divisione di utenti tra i corsi + Confronto tra iscritti anno precedente e iscritti anno corrente  -->
                    <template x-if="selectedNation && selectedAcademy && selectedSchool">
                        <x-dashboard.admin.user-course-graph school="selectedSchool"
                            selectedSchoolData="selectedSchoolData" />
                    </template>
                </div>


                <!-- Richieste di promozione a Preside delle school -->
                <!-- Richiesta di promozione a cavaliere -->

            </div>


        </div>
    </div>
</x-app-layout>
