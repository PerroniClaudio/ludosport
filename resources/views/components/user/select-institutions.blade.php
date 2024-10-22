@props([
    'type' => null,
    'user' => null,
    'academies' => [],
    'schools' => [],
    'selectedAcademies' => [],
    'selectedSchools' => []
])
@php
    $authRole = auth()->user()->getRole();
    $modalName = 'select-institutions-' . $type . '-modal';
    $isAcademy = str_contains($type, 'academy');
    $isPersonnel = str_contains($type, 'personnel');
    $selectedAcademyIds = collect($selectedAcademies)->pluck('id')->toArray();    
@endphp
<div class="" x-data="{
  addAcademy(academyId) {
    const formData = new FormData();
    formData.append('user_id', '{{$user->id}}');
    formData.append('academy_id', academyId);
    formData.append('type', '{{$isPersonnel ? 'personnel' : 'athlete'}}');

    fetch(`/users/associate-academy`, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: formData,
      })
      .then(response => response.json())
      .then(data => {
        console.log(data);
        window.location.reload();
      });
  },

  removeAcademy(academyId) {
    const formData = new FormData();
    formData.append('user_id', '{{$user->id}}');
    formData.append('academy_id', academyId);
    formData.append('type', '{{$isPersonnel ? 'personnel' : 'athlete'}}');

    fetch(`/users/remove-academy`, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: formData,
      })
      .then(response => response.json())
      .then(data => {
        console.log(data);
        window.location.reload();
      });
  },
  
  addSchool(schoolId) {
    const formData = new FormData();
    formData.append('user_id', '{{$user->id}}');
    formData.append('school_id', schoolId);
    formData.append('type', '{{$isPersonnel ? 'personnel' : 'athlete'}}');

    fetch(`/users/associate-school`, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: formData,
      })
      .then(response => response.json())
      .then(data => {
        console.log(data);
        window.location.reload();
      });
  },

  removeSchool(schoolId) {
    const formData = new FormData();
    formData.append('user_id', '{{$user->id}}');
    formData.append('school_id', schoolId);
    formData.append('type', '{{$isPersonnel ? 'personnel' : 'athlete'}}');

    fetch(`/users/remove-school`, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: formData,
      })
      .then(response => response.json())
      .then(data => {
        console.log(data);
        window.location.reload();
      });
  },

}">
    <div class="flex justify-between">
      <x-primary-button type="button" x-on:click.prevent="$dispatch('open-modal', '{{ $modalName }}')">
          <x-lucide-edit class="w-5 h-5 text-white" />
      </x-primary-button>
    </div>
    
    <x-modal name="{{ $modalName }}" :show="$errors->customrole->isNotEmpty()" focusable>
        <div class="p-6 flex flex-col gap-2" >
            <h2 class="text-lg font-medium text-background-900 dark:text-background-100">
                {{ __('users.select-institutions-' . $type) }}
            </h2>
            <div>

                <h4 class="text-md font-medium text-background-900 dark:text-background-100">
                    {{ __('users.available_institutions-' . $type) }}</h4>

                {{-- <div class="grid grid-cols-4 gap-2"> --}}
                <div class="">
                    {{-- Penso che una tabella sia meglio. col tasto su ogni riga per aggiungere --}}
                    @if($isAcademy)
                        <x-table striped="false" :columns="[
                          [
                              'name' => 'ID',
                              'field' => 'id',
                              'columnClasses' => '', // classes to style table th
                              'rowClasses' => '', // classes to style table td
                          ],
                          [
                              'name' => 'Name',
                              'field' => 'name',
                              'columnClasses' => '', // classes to style table th
                              'rowClasses' => '', // classes to style table td
                          ],
                        ]" :rows="$academies">
                          <x-slot name="tableActions">
                              {{-- <a x-bind:href="'/users/' + row.id">
                                  <x-lucide-pencil
                                      class="w-5 h-5 text-primary-800 dark:text-primary-500 cursor-pointer" />
                              </a> --}}
                              <x-primary-button type="button" x-on:click="addAcademy(row.id)">
                                <span>
                                  @if($isPersonnel)
                                    {{ __('users.add') }}
                                  @else
                                    @if($selectedAcademies->count() > 0)
                                      {{ __('users.replace') }}
                                    @else
                                      {{ __('users.select') }}
                                    @endif
                                  @endif
                                </span>
                              </x-primary-button>
                          </x-slot>
                        </x-table>
                        @if($isPersonnel)
                          <x-table striped="false" :columns="[
                            [
                                'name' => 'ID',
                                'field' => 'id',
                                'columnClasses' => '', // classes to style table th
                                'rowClasses' => '', // classes to style table td
                            ],
                            [
                                'name' => 'Name',
                                'field' => 'name',
                                'columnClasses' => '', // classes to style table th
                                'rowClasses' => '', // classes to style table td
                            ],
                          ]" :rows="$selectedAcademies">
                            <x-slot name="tableActions">
                                {{-- <a x-bind:href="'/users/' + row.id">
                                    <x-lucide-pencil
                                        class="w-5 h-5 text-primary-800 dark:text-primary-500 cursor-pointer" />
                                </a> --}}
                                <x-primary-button type="button" x-on:click="removeAcademy(row.id)">
                                  <span>{{ __('users.remove') }}</span>
                                </x-primary-button>
                            </x-slot>
                          </x-table>
                        @endif
                    @else
                        <x-table striped="false" :columns="[
                          [
                              'name' => 'ID',
                              'field' => 'id',
                              'columnClasses' => '', // classes to style table th
                              'rowClasses' => '', // classes to style table td
                          ],
                          [
                              'name' => 'Name',
                              'field' => 'name',
                              'columnClasses' => '', // classes to style table th
                              'rowClasses' => '', // classes to style table td
                          ],
                        ]" :rows="$schools">
                          <x-slot name="tableActions">
                              {{-- <a x-bind:href="'/users/' + row.id">
                                  <x-lucide-pencil
                                      class="w-5 h-5 text-primary-800 dark:text-primary-500 cursor-pointer" />
                              </a> --}}
                                <x-primary-button type="button" x-on:click="addSchool(row.id)">
                                  <span>{{ __('users.add') }}</span>
                                </x-primary-button>
                          </x-slot>
                        </x-table>
                        <x-table striped="false" :columns="[
                          [
                              'name' => 'ID',
                              'field' => 'id',
                              'columnClasses' => '', // classes to style table th
                              'rowClasses' => '', // classes to style table td
                          ],
                          [
                              'name' => 'Name',
                              'field' => 'name',
                              'columnClasses' => '', // classes to style table th
                              'rowClasses' => '', // classes to style table td
                          ],
                        ]" :rows="$selectedSchools">
                          <x-slot name="tableActions">
                              {{-- <a x-bind:href="'/users/' + row.id">
                                  <x-lucide-pencil
                                      class="w-5 h-5 text-primary-800 dark:text-primary-500 cursor-pointer" />
                              </a> --}}
                              <x-primary-button type="button" x-on:click="removeSchool(row.id)">
                                <span>{{ __('users.remove') }}</span>
                              </x-primary-button>
                          </x-slot>
                        </x-table>
                    @endif
                </div>

            </div>

            <div class="mt-4" x-show="selectedWeaponForms.length > 0">
              <h4 class="text-md font-medium text-background-900 dark:text-background-100">
                {{ __('users.selected_institutions-' . $type) }}</h4>

                <div class="grid grid-cols-4 gap-2">
                </div>
            </div>
            
        </div>
    </x-modal>
        

</div>