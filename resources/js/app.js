import './bootstrap';

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
