import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Import Cookie Policy Manager
import CookiePolicyManager from './cookie-policy-manager';
window.CookiePolicyManager = CookiePolicyManager;

// Initialize Cookie Policies
import './cookie-policy-init';
