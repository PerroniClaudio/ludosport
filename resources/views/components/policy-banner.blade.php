<div x-data="{
    showBanner: false,
    policyUpdatedAt: null,
    selectedCategoryDescription: 'technical',
    hadSavedPreferences: false,
    
    cookieCategories: {
        technical: {
            label: 'Technical',
            description: 'Technical cookies are essential for the website to function properly. They enable basic functionalities such as fonts, page navigation and access to secure areas.',
            required: true
        },
        google_api: {
            label: 'Google APIs',
            description: 'Google APIs are used to enhance website functionality (show maps).',
            required: false
        }
    },

    cookieChoices: {
        technical: true,
        google_api: false
    },

    loadSavedPreferences() {
        // Carica le preferenze salvate da localStorage
        try {
            const policyChoices = JSON.parse(localStorage.getItem('policyChoices') || '{}');
            if (policyChoices.cookie_policy && policyChoices.cookie_policy.categories) {
                this.cookieChoices = { ...policyChoices.cookie_policy.categories };
                this.hadSavedPreferences = true;
                console.log('[policy-banner] Loaded saved preferences:', this.cookieChoices);
            } else {
                this.hadSavedPreferences = false;
            }
        } catch (error) {
            console.error('[policy-banner] Error loading preferences:', error);
            this.hadSavedPreferences = false;
        }
    },

    async init() {
        try {
            const response = await fetch('{{ route("cookie-policy.info") }}');
            const data = await response.json();
            
            if (data.exists && data.updated_at) {
                // Controlla che CookiePolicyManager sia disponibile
                if (!window.CookiePolicyManager) {
                    this.showBanner = true;
                    this.policyUpdatedAt = data.updated_at;
                } else {
                    // Controlla se la policy è stata accettata e se è ancora valida
                    const isAccepted = window.CookiePolicyManager.isPolicyAccepted('cookie_policy', data.updated_at);
                    this.showBanner = !isAccepted;
                    this.policyUpdatedAt = data.updated_at;
                }
            } else {
                // Se nessuna policy nel server, non mostrare banner
                this.showBanner = false;
            }
        } catch (error) {
            console.error('[policy-banner] Error fetching policy info:', error);
            this.showBanner = false;
        }
        
        // Esponi funzione globale per aprire il banner da altri componenti
        window.openCookiePreferences = () => {
            window.dispatchEvent(new CustomEvent('openCookieBanner'));
        };
        
        // Ascolta l'evento per aprire il banner
        const self = this;
        window.addEventListener('openCookieBanner', () => {
            self.showBanner = true;
            self.loadSavedPreferences();
        });
        
        // Ascolta i cambiamenti di localStorage per reagire alle preferenze aggiornate
        window.addEventListener('storage', (e) => {
            if (e.key === 'policyChoices') {
                // Aggiorna le preferenze se cambiano da un'altra tab
            }
        });
    },

    initializeBanner() {
        // Carica le preferenze salvate quando il banner si apre
        if (this.showBanner) {
            this.loadSavedPreferences();
        }
    },

    closeBanner() {
        // Se non c'erano preferenze salvate prima, rifiuta tutto (primo accesso)
        // Se c'erano preferenze, ricaricarle e chiudi (annulla modifiche)
        if (!this.hadSavedPreferences) {
            this.rejectAll();
        } else {
            this.loadSavedPreferences();
            this.showBanner = false;
            console.log('[policy-banner] Closed banner, preferences unchanged');
        }
    },

    confirmChoices() {
        // Salva le scelte attuali (come selezionate dall'utente)
        window.CookiePolicyManager.acceptPolicy('cookie_policy');
        const choices = JSON.parse(localStorage.getItem('policyChoices') || '{}');
        choices.cookie_policy = {
            accepted_at: new Date().toISOString(),
            accepted: true,
            categories: this.cookieChoices
        };
        localStorage.setItem('policyChoices', JSON.stringify(choices));
        this.showBanner = false;
        console.log('[policy-banner] Policy confirmed with choices:', this.cookieChoices);
        
        // Trigger custom event per notificare gli altri componenti
        window.dispatchEvent(new CustomEvent('policyChoicesUpdated', { 
            detail: { choices: this.cookieChoices } 
        }));
    },

    rejectAll() {
        // Rifiuta tutto tranne i cookie tecnici (obbligatori)
        window.CookiePolicyManager.acceptPolicy('cookie_policy');
        const choices = JSON.parse(localStorage.getItem('policyChoices') || '{}');
        const rejectedChoices = {};
        Object.keys(this.cookieCategories).forEach(key => {
            rejectedChoices[key] = this.cookieCategories[key].required;
        });
        
        choices.cookie_policy = {
            accepted_at: new Date().toISOString(),
            accepted: true,
            categories: rejectedChoices
        };
        localStorage.setItem('policyChoices', JSON.stringify(choices));
        this.showBanner = false;
        console.log('[policy-banner] All non-required cookies rejected:', rejectedChoices);
        
        // Trigger custom event per notificare gli altri componenti
        window.dispatchEvent(new CustomEvent('policyChoicesUpdated', { 
            detail: { choices: rejectedChoices } 
        }));
    },

    acceptAll() {
        // Accetta tutto senza considerare le scelte attuali
        window.CookiePolicyManager.acceptPolicy('cookie_policy');
        const choices = JSON.parse(localStorage.getItem('policyChoices') || '{}');
        const acceptedChoices = {};
        Object.keys(this.cookieCategories).forEach(key => {
            acceptedChoices[key] = true;
        });
        
        choices.cookie_policy = {
            accepted_at: new Date().toISOString(),
            accepted: true,
            categories: acceptedChoices
        };
        localStorage.setItem('policyChoices', JSON.stringify(choices));
        this.showBanner = false;
        console.log('[policy-banner] All cookies accepted:', acceptedChoices);
        
        // Trigger custom event per notificare gli altri componenti
        window.dispatchEvent(new CustomEvent('policyChoicesUpdated', { 
            detail: { choices: acceptedChoices } 
        }));
    }
}" x-init="init()">
  <template x-if="showBanner">
    <div>
      <div id="policy-overlay" class="fixed top-0 left-0 w-screen h-screen bg-black z-30 opacity-50"></div>
      <div id="policy-wrap" class="flex justify-center items-end fixed top-0 left-0 w-screen h-screen pb-24 z-50 ">
        <div id="policy-container" class="relative p-8 bg-white dark:bg-background-800 dark:text-background-50 rounded opacity-100 flex flex-col gap-4 w-10/12 md:w-3/5 xl:w-1/2 ">
          <button @click="closeBanner()" class="absolute top-4 right-4 text-background-400 hover:text-background-600 dark:hover:text-background-300 transition">
            <x-lucide-x class="w-5 h-5" />
          </button>

          <div>
            <h2 class="text-xl font-semibold">
              {{ __('website.cookies_banner_title') }}
            </h2>
            <p class="text-sm mt-2">{{ __('website.cookies_banner_text') }}</p>
          </div>

          <a href="{{ route('cookie-policy.show') }}" class="text-sm  text-primary-400 hover:text-primary-600">
            {{ __('website.cookies_policy_link') ?? 'Read Cookie Policy' }}
          </a>

          <!-- Cookie Categories Checkboxes -->
          <div class="flex flex-wrap gap-3 border-t border-background-200 dark:border-background-700 pt-4">
            <template x-for="(category, key) in cookieCategories" :key="key">
              <label class="flex items-center gap-1 cursor-pointer">
                <input 
                  type="checkbox" 
                  x-model="cookieChoices[key]"
                  :disabled="category.required"
                  class="w-4 h-4 rounded"
                />
                <span class="text-sm font-medium" :class="category.required && 'opacity-50'">
                  <span x-text="category.label"></span>
                  <template x-if="category.required">
                    <span class="text-xs text-background-500 dark:text-background-400">({{ __('website.cookies_required') ?? 'Required' }})</span>
                  </template>
                </span>
              </label>
            </template>
          </div>

          {{-- Cookie Categories Description
          <div class="bg-background-700 dark:bg-background-700/50 p-4 rounded">
            Description Tabs
            <div class="flex gap-2 mb-3 overflow-x-auto pb-2">
              <template x-for="(category, key) in cookieCategories" :key="key">
                <button 
                  @click="selectedCategoryDescription = key"
                  :class="selectedCategoryDescription === key ? 'bg-primary-600 text-white' : 'bg-background-200 dark:bg-background-600 text-background-700 dark:text-background-300'"
                  class="px-3 py-1 text-sm font-medium rounded whitespace-nowrap transition"
                >
                  <span x-text="category.label"></span>
                </button>
              </template>
            </div>
            
            Description Content
            <p class="text-sm ">
              <template x-for="(category, key) in cookieCategories" :key="key">
                <template x-if="selectedCategoryDescription === key">
                  <span x-text="category.description"></span>
                </template>
              </template>
            </p>
          </div> --}}

          <div class="flex gap-3 justify-center flex-wrap">
            <x-secondary-button @click="rejectAll()" class="px-4 py-2 text-sm text-background-700 dark:text-background-300 hover:bg-background-100 dark:hover:bg-background-700 rounded transition">
              {{ __('website.cookies_reject_all') ?? 'Reject All' }}
            </x-secondary-button>
            <x-primary-button @click="confirmChoices">
              {{ __('website.cookies_confirm_choices') ?? 'Confirm Choices' }}
            </x-primary-button>
            <x-primary-button @click="acceptAll" class="bg-green-600 hover:bg-green-700">
              {{ __('website.cookies_accept_all') ?? 'Accept All' }}
            </x-primary-button>
          </div>
        </div>
      </div>
    </div>
  </template>
</div>