/**
 * Cookie Policy Management - LocalStorage Handler
 * Gestisce l'accettazione dei cookie policy lato client
 */

class CookiePolicyManager {
    static STORAGE_KEY = 'policyChoices';

    /**
     * Struttura del localStorage:
     * {
     *   cookie_policy: {
     *     accepted_at: "2026-03-20T10:00:00Z",
     *     accepted: true
     *   }
     * }
     */

    /**
     * Ottiene le preferenze salvate dal localStorage
     */
    static getStoredChoices() {
        try {
            const stored = localStorage.getItem(this.STORAGE_KEY);
            return stored ? JSON.parse(stored) : {};
        } catch (error) {
            console.error('Error reading from localStorage:', error);
            return {};
        }
    }

    /**
     * Salva le preferenze nel localStorage
     */
    static saveChoice(policyType, accepted) {
        try {
            const choices = this.getStoredChoices();
            choices[policyType] = {
                accepted_at: new Date().toISOString(),
                accepted: accepted,
            };
            localStorage.setItem(this.STORAGE_KEY, JSON.stringify(choices));
            return true;
        } catch (error) {
            console.error('Error saving to localStorage:', error);
            return false;
        }
    }

    /**
     * Controlla se la policy è stata accettata e se è ancora valida
     * 
     * Logica:
     * 1. Se non esiste localStorage o accepted è false → NOT ACCEPTED
     * 2. Se esiste localStorage ma NON ha accepted_at → NOT ACCEPTED (retrocompatibilità)
     * 3. Se esiste localStorage e ha accepted_at, confronta con server:
     *    - Se accepted_at > updated_at (utente ha accettato dopo l'ultimo update) → ACCEPTED
     *    - Se accepted_at <= updated_at (server aggiornato dopo accettazione) → NOT ACCEPTED
     */
    static isPolicyAccepted(policyType, serverUpdatedAt) {
        const choices = this.getStoredChoices();
        const choice = choices[policyType];

        console.log(`[isPolicyAccepted] Checking ${policyType}`, {
            hasChoice: !!choice,
            choice: choice,
            serverUpdatedAt: serverUpdatedAt
        });

        // Step 1: Se non esiste nessun record o non è marcata come accettata
        if (!choice || !choice.accepted) {
            console.log(`[isPolicyAccepted] No choice or not accepted`);
            return false;
        }

        // Step 2: Se manca la data di accettazione, considera non valida (retrocompatibilità)
        if (!choice.accepted_at) {
            console.log(`[isPolicyAccepted] Missing accepted_at, considering invalid`);
            return false;
        }

        // Step 3: Se non c'è una data di aggiornamento dal server, non possiamo verificare
        if (!serverUpdatedAt) {
            console.log(`[isPolicyAccepted] No serverUpdatedAt provided`);
            return false;
        }

        // Confronta le date
        const acceptedDate = new Date(choice.accepted_at);
        const updatedDate = new Date(serverUpdatedAt);
        
        // Controlla se le date sono valide
        if (isNaN(acceptedDate.getTime()) || isNaN(updatedDate.getTime())) {
            console.log(`[isPolicyAccepted] Invalid dates`);
            return false;
        }
        
        // La policy è valida solo se l'utente ha accettato DOPO l'ultimo aggiornamento del server
        // accettato_at > updated_at = ACCETTATO
        const isValid = acceptedDate > updatedDate;
        console.log(`[isPolicyAccepted] Date comparison: acceptedDate(${acceptedDate.toISOString()}) > updatedDate(${updatedDate.toISOString()}) = ${isValid}`);
        
        return isValid;
    }

    /**
     * Accetta una policy
     */
    static acceptPolicy(policyType) {
        return this.saveChoice(policyType, true);
    }

    /**
     * Rifiuta una policy
     */
    static declinePolicy(policyType) {
        try {
            const choices = this.getStoredChoices();
            if (choices[policyType]) {
                choices[policyType].accepted = false;
                choices[policyType].accepted_at = new Date().toISOString();
            }
            localStorage.setItem(this.STORAGE_KEY, JSON.stringify(choices));
            return true;
        } catch (error) {
            console.error('Error declining policy:', error);
            return false;
        }
    }

    /**
     * Pulisce le preferenze di una policy
     */
    static clearChoice(policyType) {
        try {
            const choices = this.getStoredChoices();
            delete choices[policyType];
            localStorage.setItem(this.STORAGE_KEY, JSON.stringify(choices));
            return true;
        } catch (error) {
            console.error('Error clearing choice:', error);
            return false;
        }
    }

    /**
     * Pulisce tutte le preferenze
     */
    static clearAll() {
        try {
            localStorage.removeItem(this.STORAGE_KEY);
            return true;
        } catch (error) {
            console.error('Error clearing all choices:', error);
            return false;
        }
    }
}

export default CookiePolicyManager;
