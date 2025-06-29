import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Import and initialize Bootstrap 5 (required for Tabler)
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

// Bootstrap initialization will be handled by Alpine.js components
// This provides better integration with Livewire and reactive updates

// Global Alpine.js directive for Bootstrap tooltips
document.addEventListener('alpine:init', () => {
    Alpine.directive('tooltip', (el, { expression }) => {
        if (window.bootstrap && window.bootstrap.Tooltip) {
            new window.bootstrap.Tooltip(el, {
                title: expression || el.getAttribute('title')
            });
        }
    });

    Alpine.directive('popover', (el, { expression }) => {
        if (window.bootstrap && window.bootstrap.Popover) {
            new window.bootstrap.Popover(el, {
                content: expression || el.getAttribute('data-bs-content')
            });
        }
    });
});
