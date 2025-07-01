<div x-data="{
    defaultPolicyChoices: {
        'functional': true,
    },
    policyChoices: null,
    isPolicyChoicesSet: false,

    setPolicyChoices: function () {
        localStorage.setItem('policyChoices', JSON.stringify(this.defaultPolicyChoices));
        this.isPolicyChoicesSet = true;
        console.log('Setting policy choices');
        console.log(this.isPolicyChoicesSet);
    },
    init() {
        this.policyChoices = localStorage.getItem('policyChoices');
        this.isPolicyChoicesSet = !!this.policyChoices;
        console.log('Policy banner initialized');
        console.log(this.isPolicyChoicesSet);
    }
}">
  <template x-if="!isPolicyChoicesSet">
    <div>
      <div id="policy-overlay" class="fixed top-0 left-0 w-screen h-screen bg-black z-30 opacity-50"></div>
      <div id="policy-wrap" class="flex justify-center items-end fixed top-0 left-0 w-screen h-screen pb-24 z-50 ">
        <div id="policy-container" class="relative p-8 bg-white dark:bg-background-800 dark:text-background-50 rounded opacity-100 flex flex-col gap-2 w-10/12 md:w-3/5 xl:w-1/2 ">
          <h2 class="text-xl font-semibold">
            {{ __('website.cookies_banner_title') }}
          </h2>
          <p>{{__('website.cookies_banner_text')}}</p>
          
          {{-- <div class="text-xs md:text-sm">
            <a href="http://" target="_blank" rel="noopener noreferrer" class="font-semibold text-primary-400 hover:text-primary-600">Cookie Policy</a> <span class="font-light">|</span>
            <a href="http://" target="_blank" rel="noopener noreferrer" class="font-semibold text-primary-400 hover:text-primary-600">Privacy Policy</a> 
          </div> --}}

          <div class="flex justify-center">
            <x-primary-button @click="setPolicyChoices">
              {{-- Questo se ci saranno scelte diventerà il submit con le impostazioni dell'utente --}}
              {{ __('website.cookies_banner_accept') }}
            </x-primary-button>
          </div>
          <button @click="setPolicyChoices"> 
            {{-- Questo se ci saranno scelte diventerà il submit con le impostazioni di default --}}
            <x-lucide-x class="w-4 h-4 absolute top-2 right-2" />
          </button>
        </div>
      </div>
    </div>
  </template>
</div>