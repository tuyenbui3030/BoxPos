/**
 * Constants for Customer Management
 * Centralized configuration following Clean Code principles
 */

export const CUSTOMER_CONSTANTS = {
    // DOM Selectors
    SELECTORS: {
        CUSTOMER_CHECKBOX: 'input[data-customer-id]',
        DATE_PICKER: '[x-ref="datepicker"]'
    },
    
    // Event Names
    EVENTS: {
        LIVEWIRE_NAVIGATED: 'livewire:navigated',
        LIVEWIRE_UPDATED: 'livewire:updated',
        CLEAR_DATE_PICKER: 'clearDatePicker'
    },
    
    // Timing
    DELAYS: {
        RETRY_INITIALIZATION: 500,
        FLASH_MESSAGE_TIMEOUT: 5000
    },
    
    // Messages
    MESSAGES: {
        SELECTION_REQUIRED: (action) => `Please select customers to ${action}`,
        DELETE_CONFIRMATION: (count) => `Are you sure you want to delete ${count} customer(s)?`,
        EXPORT_FAILED: 'Export failed. Please try again.',
        DELETE_FAILED: 'Delete failed. Please try again.'
    },
    
    // Date Formats
    DATE_FORMAT: 'YYYY-MM-DD',
    DATE_DELIMITER: ' - ',
    
    // Persistence Keys
    STORAGE_KEYS: {
        FILTERS_VISIBLE: 'customer-filters-visible'
    }
};

// Make available globally for non-module environments
if (typeof window !== 'undefined') {
    window.CUSTOMER_CONSTANTS = CUSTOMER_CONSTANTS;
}
