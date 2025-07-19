/**
 * Global Alpine.js Components and Utilities
 * Prioritizing Livewire + Alpine.js approach over vanilla JavaScript
 */

// Global keyboard shortcuts
document.addEventListener('alpine:init', () => {

    // Global keyboard shortcuts component
    Alpine.data('keyboardShortcuts', () => ({
        init() {
            this.setupGlobalShortcuts();
        },

        setupGlobalShortcuts() {
            document.addEventListener('keydown', (e) => {
                // Ctrl/Cmd + K for search
                if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                    e.preventDefault();
                    this.$dispatch('open-search-modal');
                }

                // Escape to close modals
                if (e.key === 'Escape') {
                    this.$dispatch('close-all-modals');
                }

                // Ctrl/Cmd + / for help
                if ((e.ctrlKey || e.metaKey) && e.key === '/') {
                    e.preventDefault();
                    this.$dispatch('open-help-modal');
                }
            });
        }
    }));

    // Global notification component
    Alpine.data('notifications', () => ({
        notifications: [],

        init() {
            // Listen for Livewire notifications
            Livewire.on('notify', (data) => {
                this.addNotification(data);
            });
        },

        addNotification(data) {
            const notification = {
                id: Date.now(),
                type: data.type || 'info',
                title: data.title || '',
                message: data.message || '',
                duration: data.duration || 5000
            };

            this.notifications.push(notification);

            // Auto remove after duration
            if (notification.duration > 0) {
                setTimeout(() => {
                    this.removeNotification(notification.id);
                }, notification.duration);
            }
        },

        removeNotification(id) {
            this.notifications = this.notifications.filter(n => n.id !== id);
        }
    }));

    // Global loading state component
    Alpine.data('globalLoading', () => ({
        isLoading: false,
        loadingText: 'Loading...',

        init() {
            console.log('Global loading component initialized');

            // Listen for Livewire loading states
            document.addEventListener('livewire:navigating', () => {
                console.log('Livewire navigating');
                this.startLoading('Navigating...');
            });

            document.addEventListener('livewire:navigated', () => {
                console.log('Livewire navigated');
                this.stopLoading();
            });

            // Listen for Livewire component loading
            document.addEventListener('livewire:loading', () => {
                console.log('Livewire loading');
                this.startLoading('Loading...');
            });

            document.addEventListener('livewire:loaded', () => {
                console.log('Livewire loaded');
                this.stopLoading();
            });

            // Ensure loading is stopped on page load
            this.stopLoading();
        },

        startLoading(text = 'Loading...') {
            console.log('Start loading:', text);
            this.isLoading = true;
            this.loadingText = text;
        },

        stopLoading() {
            console.log('Stop loading');
            this.isLoading = false;
        }
    }));

    // Global modal management
    Alpine.data('modalManager', () => ({
        openModals: [],

        init() {
            // Listen for modal events
            this.$watch('openModals', (value) => {
                document.body.classList.toggle('modal-open', value.length > 0);
            });

            // Close all modals on escape
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.openModals.length > 0) {
                    this.closeTopModal();
                }
            });
        },

        openModal(modalId) {
            if (!this.openModals.includes(modalId)) {
                this.openModals.push(modalId);
            }
        },

        closeModal(modalId) {
            this.openModals = this.openModals.filter(id => id !== modalId);
        },

        closeTopModal() {
            if (this.openModals.length > 0) {
                const topModal = this.openModals[this.openModals.length - 1];
                this.closeModal(topModal);
                this.$dispatch('close-modal', { modalId: topModal });
            }
        },

        closeAllModals() {
            this.openModals = [];
            this.$dispatch('close-all-modals');
        }
    }));

    // Global theme management (works with Livewire ThemeSwitcher)
    Alpine.data('themeManager', () => ({
        theme: document.documentElement.getAttribute('data-bs-theme') || 'light',

        init() {
            // Listen for theme changes from Livewire
            Livewire.on('theme-changed', (event) => {
                this.theme = event.theme;
                this.applyTheme(event.theme);
            });
        },

        applyTheme(theme) {
            document.documentElement.setAttribute('data-bs-theme', theme);

            // Store in localStorage for persistence
            localStorage.setItem('theme', theme);

            // Dispatch event for other components
            this.$dispatch('theme-updated', { theme });
        },

        toggleTheme() {
            const newTheme = this.theme === 'light' ? 'dark' : 'light';
            this.$wire.call('setTheme', newTheme);
        }
    }));

    // Global form utilities
    Alpine.data('formUtils', () => ({
        init() {
            // Auto-focus first input in modals
            this.$watch('$store.modalManager.openModals', (modals) => {
                if (modals.length > 0) {
                    this.$nextTick(() => {
                        const modal = document.querySelector('.modal.show');
                        const firstInput = modal?.querySelector('input, textarea, select');
                        if (firstInput && !firstInput.disabled) {
                            firstInput.focus();
                        }
                    });
                }
            });
        },

        // Confirm before leaving page with unsaved changes
        confirmUnsavedChanges(hasChanges = false) {
            if (hasChanges) {
                return confirm('You have unsaved changes. Are you sure you want to leave?');
            }
            return true;
        },

        // Format currency
        formatCurrency(amount, currency = 'USD') {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: currency
            }).format(amount);
        },

        // Format date
        formatDate(date, options = {}) {
            const defaultOptions = {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            };

            return new Intl.DateTimeFormat('en-US', { ...defaultOptions, ...options })
                .format(new Date(date));
        }
    }));

    // Global utility functions store
    Alpine.store('utils', {
        // Debounce function
        debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        },

        // Generate unique ID
        generateId() {
            return Date.now().toString(36) + Math.random().toString(36).substr(2);
        },

        // Copy to clipboard
        async copyToClipboard(text) {
            try {
                await navigator.clipboard.writeText(text);
                const notificationStore = Alpine.store('notifications');
                if (notificationStore && notificationStore.addNotification) {
                    notificationStore.addNotification({
                        type: 'success',
                        message: 'Copied to clipboard!',
                        duration: 2000
                    });
                }
            } catch (err) {
                console.error('Failed to copy: ', err);
                const notificationStore = Alpine.store('notifications');
                if (notificationStore && notificationStore.addNotification) {
                    notificationStore.addNotification({
                        type: 'error',
                        message: 'Failed to copy to clipboard',
                        duration: 3000
                    });
                }
            }
        }
    });

    // Global store to manage dropdown states and prevent flash
    Alpine.store('dropdowns', {
        closeAllDropdowns() {
            // Remove show class from all dropdown menus
            document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                menu.classList.remove('show');
            });
        }
    });

});

// Global function to ensure all internal links use SPA navigation
document.addEventListener('DOMContentLoaded', function() {
    // Add wire:navigate to all internal links that don't have it
    document.addEventListener('click', function(e) {
        const link = e.target.closest('a');

        if (!link) return;

        const href = link.getAttribute('href');

        // Skip if:
        // - No href
        // - External link
        // - Hash link
        // - Already has wire:navigate
        // - Has onclick handler
        // - Is logout form
        if (!href ||
            href.startsWith('http') ||
            href.startsWith('#') ||
            link.hasAttribute('wire:navigate') ||
            link.hasAttribute('onclick') ||
            link.closest('form')) {
            return;
        }

        // Check if it's an internal route
        const currentHost = window.location.host;
        const linkHost = new URL(href, window.location.origin).host;

        if (linkHost === currentHost) {
            // Prevent default and use Livewire navigation
            e.preventDefault();
            window.Livewire.navigate(href);
        }
    });
});

console.log('Global Alpine.js components and SPA navigation loaded successfully');
