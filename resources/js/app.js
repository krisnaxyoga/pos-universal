import './bootstrap';
import { createApp } from 'vue';
import Alpine from 'alpinejs';

// Expose Vue globally for inline scripts
window.Vue = { createApp };

window.Alpine = Alpine;
Alpine.start();

// Note: POS app will be mounted by inline script in the Blade template
// This allows for a hybrid approach using Vue without SFC complexity
