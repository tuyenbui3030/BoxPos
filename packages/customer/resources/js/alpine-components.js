/**
 * Customer Management Alpine.js Components
 * 
 * This file contains all Alpine.js components for the Customer package.
 * Components are automatically registered when this script is loaded.
 */

/**
 * Customer Selection Service
 * Handles customer selection logic with clean, testable methods
 */
class CustomerSelectionService {
    static getAllCustomerCheckboxes() {
        return document.querySelectorAll('input[data-customer-id]');
    }
    
    static getAllCustomerIds() {
        return Array.from(this.getAllCustomerCheckboxes())
            .map(checkbox => checkbox.getAttribute('data-customer-id'));
    }
    
    static getTotalCustomerCount() {
        return this.getAllCustomerCheckboxes().length;
    }
    
    static isCustomerSelected(customerId, selectedCustomers) {
        return selectedCustomers.includes(customerId);
    }
    
    static shouldSelectAll(selectedCount, totalCount) {
        return selectedCount === totalCount;
    }
}

/**
 * Main Customer Management Alpine.js Component
 * Follows Clean Code principles with small, focused methods
 */
window.customerManagement = function() {
    return {
        // State
        selectedCustomers: [],
        selectAll: false,
        showFilters: Alpine.$persist(false).as('customer-filters-visible'),
        
        // Lifecycle
        init() {
            this.setupEventListeners();
            this.initializeBootstrapComponents();
            this.logInitialization();
        },
        
        // Private methods
        setupEventListeners() {
            document.addEventListener('livewire:navigated', () => {
                this.clearSelection();
                this.initializeBootstrapComponents();
            });
        },
        
        initializeBootstrapComponents() {
            // Initialize Bootstrap dropdowns
            this.$nextTick(() => {
                const dropdowns = document.querySelectorAll('[data-bs-toggle="dropdown"]');
                dropdowns.forEach(dropdown => {
                    if (window.bootstrap && window.bootstrap.Dropdown) {
                        new window.bootstrap.Dropdown(dropdown);
                    }
                });
            });
        },
        
        logInitialization() {
            console.log('Customer Management Alpine.js component initialized');
        },
        
        updateSelectAllState() {
            const totalCount = CustomerSelectionService.getTotalCustomerCount();
            this.selectAll = CustomerSelectionService.shouldSelectAll(
                this.selectedCustomers.length, 
                totalCount
            );
        },
        
        // Public methods
        toggleAll() {
            this.selectedCustomers = this.selectAll 
                ? CustomerSelectionService.getAllCustomerIds()
                : [];
            
            console.log('Toggle all customers:', this.selectedCustomers);
        },
        
        toggleCustomer(customerId) {
            this.isCustomerSelected(customerId) 
                ? this.removeCustomer(customerId)
                : this.addCustomer(customerId);
            
            this.updateSelectAllState();
            console.log('Toggle customer:', customerId, this.selectedCustomers);
        },
        
        addCustomer(customerId) {
            this.selectedCustomers.push(customerId);
        },
        
        removeCustomer(customerId) {
            const index = this.selectedCustomers.indexOf(customerId);
            if (index > -1) {
                this.selectedCustomers.splice(index, 1);
            }
        },
        
        isCustomerSelected(customerId) {
            return CustomerSelectionService.isCustomerSelected(customerId, this.selectedCustomers);
        },
        
        clearSelection() {
            this.selectedCustomers = [];
            this.selectAll = false;
            console.log('Selection cleared');
        },
        
        getSelectedCount() {
            return this.selectedCustomers.length;
        },
        
        hasSelection() {
            return this.getSelectedCount() > 0;
        },
        
        toggleFilters() {
            this.showFilters = !this.showFilters;
            this.$wire.set('showFilters', this.showFilters);
        },
        
        // Bulk Actions - Clean, async methods with proper error handling
        async exportSelected() {
            if (!this.hasSelection()) {
                this.showSelectionRequiredMessage('export');
                return;
            }
            
            await this.executeBulkAction(
                () => this.$wire.exportSelectedCustomers(this.selectedCustomers),
                'Export failed. Please try again.'
            );
        },
        
        async deleteSelected() {
            if (!this.hasSelection()) {
                this.showSelectionRequiredMessage('delete');
                return;
            }
            
            if (!this.confirmBulkDelete()) {
                return;
            }
            
            await this.executeBulkAction(
                () => this.$wire.deleteSelectedCustomers(this.selectedCustomers),
                'Delete failed. Please try again.'
            );
        },
        
        // Private helper methods
        showSelectionRequiredMessage(action) {
            alert(`Please select customers to ${action}`);
        },
        
        confirmBulkDelete() {
            const count = this.getSelectedCount();
            return confirm(`Are you sure you want to delete ${count} customer(s)?`);
        },
        
        async executeBulkAction(action, errorMessage) {
            try {
                await action();
                this.clearSelection();
            } catch (error) {
                console.error('Bulk action failed:', error);
                alert(errorMessage);
            }
        }
    };
};

/**
 * Date Picker Configuration Service
 * Centralizes date picker configuration following DRY principle
 */
class DatePickerConfig {
    static getDefaultConfig() {
        return {
            singleMode: false,
            numberOfColumns: 2,
            numberOfMonths: 2,
            format: 'YYYY-MM-DD',
            delimiter: ' - ',
            autoApply: true,
            showTooltip: true,
            showWeekNumbers: true,
            lang: 'en-US',
        };
    }
    
    static getButtonConfig() {
        return {
            previousMonth: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1"><path d="M15 6l-6 6l6 6" /></svg>`,
            nextMonth: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1"><path d="M9 6l6 6l-6 6" /></svg>`,
            apply: 'Apply',
            cancel: 'Cancel',
        };
    }
}

/**
 * Alpine.js Date Picker Component
 * Clean, modular date picker with proper separation of concerns
 */
window.datePicker = function() {
    return {
        // State
        picker: null,
        isInitialized: false,
        
        // Lifecycle
        init() {
            this.$nextTick(() => {
                this.initializeDatePicker();
                this.setupEventListeners();
            });
        },
        
        // Public methods
        clearDatePicker() {
            this.clearInputValue();
            this.clearPickerSelection();
            console.log('Date picker cleared');
        },
        
        destroy() {
            if (this.picker) {
                this.picker.destroy();
                this.resetState();
            }
        },
        
        // Private methods
        initializeDatePicker() {
            if (this.isInitialized || !this.canInitialize()) {
                this.retryInitialization();
                return;
            }
            
            this.createPicker();
            this.isInitialized = true;
            console.log("Litepicker initialized successfully");
        },
        
        canInitialize() {
            return window.Litepicker && this.$refs.datepicker;
        },
        
        retryInitialization() {
            if (!this.isInitialized) {
                setTimeout(() => this.initializeDatePicker(), 500);
            }
        },
        
        createPicker() {
            console.log("Initializing Litepicker with Alpine.js");
            
            this.picker = new window.Litepicker({
                element: this.$refs.datepicker,
                ...DatePickerConfig.getDefaultConfig(),
                buttonText: DatePickerConfig.getButtonConfig(),
                setup: (picker) => this.setupPickerEvents(picker)
            });
        },
        
        setupPickerEvents(picker) {
            picker.on('selected', (date1, date2) => {
                this.handleDateSelection(date1, date2);
            });
            
            picker.on('clear', () => {
                console.log('Date cleared');
                this.clearDatePicker();
            });
        },
        
        handleDateSelection(date1, date2) {
            if (date1 && date2) {
                this.handleDateRangeSelection(date1, date2);
            } else if (date1) {
                this.handleSingleDateSelection(date1);
            }
        },
        
        handleDateRangeSelection(date1, date2) {
            const fromDate = date1.format('YYYY-MM-DD');
            const toDate = date2.format('YYYY-MM-DD');
            
            console.log('Date range selected:', fromDate, 'to', toDate);
            
            this.$refs.datepicker.value = `${fromDate} - ${toDate}`;
            this.updateLivewireDateRange(fromDate, toDate);
        },
        
        handleSingleDateSelection(date) {
            const formattedDate = date.format('YYYY-MM-DD');
            
            console.log('Single date selected:', formattedDate);
            
            this.$refs.datepicker.value = formattedDate;
            this.updateLivewireDateRange(formattedDate, '');
        },
        
        updateLivewireDateRange(fromDate, toDate) {
            this.$wire.set('filterLastTransactionFrom', fromDate);
            this.$wire.set('filterLastTransactionTo', toDate);
        },
        
        setupEventListeners() {
            this.listenForClearEvents();
            this.listenForLivewireUpdates();
        },
        
        listenForClearEvents() {
            this.$wire.on('clearDatePicker', () => {
                this.clearDatePicker();
            });
        },
        
        listenForLivewireUpdates() {
            document.addEventListener('livewire:updated', () => {
                if (this.shouldClearPicker()) {
                    this.clearDatePicker();
                }
            });
        },
        
        shouldClearPicker() {
            return this.$wire.filterLastTransactionFrom === '' && 
                   this.$wire.filterLastTransactionTo === '';
        },
        
        clearInputValue() {
            if (this.$refs.datepicker) {
                this.$refs.datepicker.value = '';
            }
        },
        
        clearPickerSelection() {
            if (this.picker) {
                this.picker.clearSelection();
            }
        },
        
        resetState() {
            this.picker = null;
            this.isInitialized = false;
        }
    };
};

/**
 * Customer Form Alpine.js Component
 * Clean, focused form handling with separation of concerns
 */
window.customerForm = function() {
    return {
        // State
        isLoading: false,
        showAdvanced: false,
        
        // Lifecycle
        init() {
            console.log('Customer Form Alpine.js component initialized');
        },
        
        // Public methods
        toggleAdvanced() {
            this.showAdvanced = !this.showAdvanced;
        },
        
        async submitForm() {
            await this.executeWithLoading(async () => {
                await this.$wire.save();
            });
        },
        
        resetForm() {
            this.showAdvanced = false;
            this.isLoading = false;
        },
        
        // Private methods
        async executeWithLoading(action) {
            this.startLoading();
            
            try {
                await action();
            } catch (error) {
                console.error('Form submission failed:', error);
            } finally {
                this.stopLoading();
            }
        },
        
        startLoading() {
            this.isLoading = true;
        },
        
        stopLoading() {
            this.isLoading = false;
        }
    }
};

/**
 * Button Loading State Component
 * Simple, reusable loading state management
 */
window.buttonLoader = function(action = null) {
    return {
        // State
        loading: false,
        
        // Lifecycle
        init() {
            this.setupEventListeners(action);
        },
        
        // Public methods
        startLoading() {
            this.loading = true;
        },
        
        stopLoading() {
            this.loading = false;
        },
        
        // Private methods
        setupEventListeners(action) {
            this.listenForLivewireUpdates();
            
            if (action) {
                this.listenForSpecificAction(action);
            }
        },
        
        listenForLivewireUpdates() {
            this.$wire.on('livewire:updated', () => {
                this.stopLoading();
            });
        },
        
        listenForSpecificAction(action) {
            this.$wire.on(`${action}:completed`, () => {
                this.stopLoading();
            });
        }
    }
};

console.log('Customer Package Alpine.js components loaded successfully');
