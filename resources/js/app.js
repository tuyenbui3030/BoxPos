import './bootstrap';

// Import Alpine.js
import Alpine from 'alpinejs';
// Make Alpine available globally
window.Alpine = Alpine;
// Start Alpine
Alpine.start();

// Import Tabler JavaScript
import '@tabler/core/dist/js/tabler.min.js';

// Theme switching is now handled by Livewire ThemeSwitcher component

// Initialize Tabler-specific JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize any Tabler components that require JavaScript activation
    // (This is in addition to Bootstrap components initialized in bootstrap.js)
    
    // Example: Initialize Tabler dropdowns if present
    document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(function(element) {
        if (!element.classList.contains('dropdown-toggle')) {
            element.classList.add('dropdown-toggle');
        }
    });
});
