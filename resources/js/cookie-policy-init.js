/**
 * Cookie Policy Initialization
 * Controlla se la cookie policy è stata accettata al caricamento della pagina
 */

import CookiePolicyManager from './cookie-policy-manager';

// Route che non necessitano di accettazione della cookie policy
// const exemptRoutes = [
//     'cookie-policy.show',
//     'cookie-policy.accept',
//     'cookie-policy.decline',
//     'logout',
//     'login',
//     'register',
//     'homepage',
//     'schools-map',
//     'rankings-website',
//     'shop',
//     'user-search',
//     'events-list',
//     'school-profile',
//     'academy-profile',
//     'website-users-show',
//     'privacy-policy',
// ];

/**
 * Controlla se l'utente è autenticato leggendo dal DOM
 */
// function isUserAuthenticated() {
//     // Controlla se è presente il form di logout (presente solo se autenticato)
//     const isAuth = document.querySelector('form[action*="logout"]') !== null;
    
//     console.log('isUserAuthenticated:', isAuth);
//     return isAuth;
// }

/**
 * Estrae la rotta attuale dal DOM o da window location
 */
// function getCurrentRoute() {
//     // Prova a trovare metadati di rotta nel DOM
//     const routeMeta = document.querySelector('meta[name="current-route"]');
//     if (routeMeta) {
//         return routeMeta.content;
//     }
    
//     // Fallback: usa il pathname
//     return window.location.pathname;
// }

/**
 * Controlla se la rotta attuale è esente dal controllo cookie policy
 */
// function isExemptRoute() {
//     const currentRoute = getCurrentRoute();
//     return exemptRoutes.some(route => currentRoute.includes(route));
// }

// export function initializeCookiePolicies() {
//     console.log('initializeCookiePolicies called');
    
//     // Se non siamo su una rotta protetta, non fare nulla
//     if (isExemptRoute()) {
//         console.log('Exempt route detected, skipping cookie policy check');
//         return;
//     }

//     // Se siamo autenticati, controlla il cookie policy da localStorage
//     if (isUserAuthenticated()) {
//         console.log('User authenticated, checking cookie policy');
//         // NOTA: Non fare reindirizzamento automatico
//         // Il controllo e il banner si gestiscono manualmente sulla pagina /cookie-policy
//         // checkAndEnforceCookiePolicy();
//     } else {
//         console.log('User not authenticated, skipping cookie policy check');
//     }

//     if (window.CookiePolicyManager) {
//         console.log('Cookie Policy Manager initialized');
//     }
// }

/**
 * Verifica se la cookie policy è stata accettata e applicala
 */
// async function checkAndEnforceCookiePolicy() {
//     console.log('[checkAndEnforceCookiePolicy] Starting check');
    
//     try {
//         // Step 1: Leggi policyChoices da localStorage
//         const storedChoices = localStorage.getItem('policyChoices');
//         console.log('[checkAndEnforceCookiePolicy] storedChoices:', storedChoices);
        
//         let needsRedirect = false;
        
//         if (!storedChoices) {
//             // Se non esiste localStorage, richiedi accettazione
//             console.log('[checkAndEnforceCookiePolicy] No policyChoices in localStorage');
//             needsRedirect = true;
//         } else {
//             // Se esiste, verifica che abbia accepted_at
//             try {
//                 const choices = JSON.parse(storedChoices);
//                 const cookieChoice = choices.cookie_policy;
                
//                 if (!cookieChoice || !cookieChoice.accepted_at) {
//                     // Ha policyChoices ma non ha accepted_at, eliminalo e richiedi accettazione
//                     console.log('[checkAndEnforceCookiePolicy] policyChoices exists but missing accepted_at, clearing it');
//                     localStorage.removeItem('policyChoices');
//                     needsRedirect = true;
//                 } else {
//                     // Ha accepted_at, confronta con server
//                     console.log('[checkAndEnforceCookiePolicy] policyChoices has accepted_at:', cookieChoice.accepted_at);
                    
//                     // Recupera info dal server
//                     const response = await fetch('/api/cookie-policy/info');
//                     const data = await response.json();
                    
//                     console.log('[checkAndEnforceCookiePolicy] Server data:', data);

//                     if (!data.exists || !data.has_content || !data.updated_at) {
//                         // Nessuna policy sul server o vuota, consenti accesso
//                         console.log('[checkAndEnforceCookiePolicy] No valid policy on server');
//                         return;
//                     }

//                     // Confronta le date
//                     const isAccepted = CookiePolicyManager.isPolicyAccepted('cookie_policy', data.updated_at);
//                     console.log('[checkAndEnforceCookiePolicy] isPolicyAccepted result:', isAccepted);
                    
//                     if (!isAccepted) {
//                         needsRedirect = true;
//                     }
//                 }
//             } catch (e) {
//                 console.error('[checkAndEnforceCookiePolicy] Error parsing localStorage:', e);
//                 localStorage.removeItem('policyChoices');
//                 needsRedirect = true;
//             }
//         }

//         if (needsRedirect) {
//             // Redirige alla pagina di accettazione
//             console.log('[checkAndEnforceCookiePolicy] Redirecting to /cookie-policy');
//             window.location.href = '/cookie-policy';
//         } else {
//             console.log('[checkAndEnforceCookiePolicy] Policy accepted, allowing access');
//         }
//     } catch (error) {
//         console.error('[checkAndEnforceCookiePolicy] Error:', error);
//         // Se c'è un errore, permetti il passaggio
//     }
// }

// Esporta il manager globalmente
window.CookiePolicyManager = CookiePolicyManager;

// Inizializza al caricamento
// if (document.readyState === 'loading') {
//     document.addEventListener('DOMContentLoaded', () => {
//         initializeCookiePolicies();
//     });
// } else {
//     initializeCookiePolicies();
// }
