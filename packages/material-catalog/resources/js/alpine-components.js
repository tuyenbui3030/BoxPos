/**
 * Material Catalog Alpine.js Components
 * Following customer-management pattern for consistent UI/UX
 */

/**
 * Material Units Management Component
 */
window.materialUnitsManagement = function() {
    return {
        // State
        showFilters: Alpine.$persist(false).as('material-units-filters-visible'),
        
        // Lifecycle
        init() {
            this.setupEventListeners();
            this.initializeBootstrapComponents();
        },

        // Private methods
        setupEventListeners() {
            document.addEventListener('livewire:navigated', () => {
                this.initializeBootstrapComponents();
            });
        },
        
        initializeBootstrapComponents() {
            // Initialize Bootstrap tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Using native HTML selects - no initialization needed
        },

        // Tom Select removed - using native HTML selects
    }
};

/**
 * Material Categories Management Component
 */
window.materialCategoriesManagement = function() {
    return {
        // State
        showFilters: Alpine.$persist(false).as('material-categories-filters-visible'),
        expandedCategories: [],
        
        // Lifecycle
        init() {
            this.setupEventListeners();
            this.initializeBootstrapComponents();
        },
        
        // Private methods
        setupEventListeners() {
            document.addEventListener('livewire:navigated', () => {
                this.initializeBootstrapComponents();
            });
        },
        
        initializeBootstrapComponents() {
            // Initialize Bootstrap tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        },
        
        // Category tree methods
        toggleCategory(categoryId) {
            const index = this.expandedCategories.indexOf(categoryId);
            if (index > -1) {
                this.expandedCategories.splice(index, 1);
            } else {
                this.expandedCategories.push(categoryId);
            }
        },
        
        isCategoryExpanded(categoryId) {
            return this.expandedCategories.includes(categoryId);
        }
    }
};

/**
 * Building Materials Management Component
 */
window.buildingMaterialsManagement = function() {
    return {
        // State
        showFilters: Alpine.$persist(false).as('building-materials-filters-visible'),
        selectedMaterials: [],
        selectAll: false,
        
        // Lifecycle
        init() {
            this.setupEventListeners();
            this.initializeBootstrapComponents();
        },
        
        // Private methods
        setupEventListeners() {
            document.addEventListener('livewire:navigated', () => {
                this.clearSelection();
                this.initializeBootstrapComponents();
            });
        },
        
        initializeBootstrapComponents() {
            // Initialize Bootstrap tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        },
        
        // Selection methods
        toggleMaterial(materialId) {
            const index = this.selectedMaterials.indexOf(materialId);
            if (index > -1) {
                this.selectedMaterials.splice(index, 1);
            } else {
                this.selectedMaterials.push(materialId);
            }
            this.updateSelectAllState();
        },
        
        toggleAll() {
            if (this.selectAll) {
                // Select all visible materials
                const checkboxes = document.querySelectorAll('input[data-material-id]');
                this.selectedMaterials = Array.from(checkboxes).map(cb => cb.dataset.materialId);
            } else {
                // Deselect all
                this.selectedMaterials = [];
            }
        },
        
        updateSelectAllState() {
            const checkboxes = document.querySelectorAll('input[data-material-id]');
            const totalVisible = checkboxes.length;
            const selectedVisible = this.selectedMaterials.filter(id => 
                Array.from(checkboxes).some(cb => cb.dataset.materialId === id)
            ).length;
            
            this.selectAll = totalVisible > 0 && selectedVisible === totalVisible;
        },
        
        clearSelection() {
            this.selectedMaterials = [];
            this.selectAll = false;
        },
        
        hasSelection() {
            return this.selectedMaterials.length > 0;
        },
        
        getSelectedCount() {
            return this.selectedMaterials.length;
        },
        
        // Bulk actions
        deleteSelected() {
            if (this.hasSelection()) {
                if (confirm(`Bạn có chắc chắn muốn xóa ${this.getSelectedCount()} vật liệu đã chọn?`)) {
                    Livewire.dispatch('bulk-delete-materials', { ids: this.selectedMaterials });
                    this.clearSelection();
                }
            }
        }
    }
};

/**
 * Image Preview Component
 */
window.imagePreview = function() {
    return {
        previewUrl: null,
        
        previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.previewUrl = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        },
        
        clearPreview() {
            this.previewUrl = null;
        }
    }
};

/**
 * Color Picker Component
 */
window.colorPicker = function(initialColor = '#206bc4') {
    return {
        color: initialColor,
        
        setColor(color) {
            this.color = color;
            this.$dispatch('color-changed', { color: color });
        }
    }
};
