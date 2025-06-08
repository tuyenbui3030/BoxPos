// Customer Management specific JavaScript
document.addEventListener("DOMContentLoaded", function () {
    console.log("Customer Management JavaScript loaded");
    
    // Initialize Litepicker for date filters
    if (window.Litepicker) {
        console.log("Litepicker is available, initializing datepicker");
        const datepickerElement = document.getElementById("datepicker-icon-prepend");
        if (datepickerElement) {
            console.log("Datepicker element found, creating Litepicker instance");
            
            const picker = new window.Litepicker({
                element: datepickerElement,
                singleMode: false,
                numberOfColumns: 2,
                numberOfMonths: 2,
                format: 'YYYY-MM-DD',
                delimiter: ' - ',
                autoApply: true,
                showTooltip: true,
                showWeekNumbers: true,
                lang: 'en-US',
                buttonText: {
                    previousMonth: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1"><path d="M15 6l-6 6l6 6" /></svg>`,
                    nextMonth: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1"><path d="M9 6l6 6l-6 6" /></svg>`,
                    apply: 'Apply',
                    cancel: 'Cancel',
                },
                setup: (picker) => {
                    picker.on('selected', (date1, date2) => {
                        if (date1 && date2) {
                            console.log('Date range selected:', date1.format('YYYY-MM-DD'), 'to', date2.format('YYYY-MM-DD'));
                            
                            // Update the input display value with range
                            datepickerElement.value = `${date1.format('YYYY-MM-DD')} - ${date2.format('YYYY-MM-DD')}`;
                            
                            // Update Livewire properties separately
                            if (window.Livewire) {
                                // Find the Livewire component and update both properties
                                const livewireComponent = window.Livewire.find(datepickerElement.closest('[wire\\:id]')?.getAttribute('wire:id'));
                                if (livewireComponent) {
                                    livewireComponent.set('filterLastTransactionFrom', date1.format('YYYY-MM-DD'));
                                    livewireComponent.set('filterLastTransactionTo', date2.format('YYYY-MM-DD'));
                                }
                            }
                        } else if (date1) {
                            console.log('Single date selected:', date1.format('YYYY-MM-DD'));
                            datepickerElement.value = date1.format('YYYY-MM-DD');
                            
                                                        
                            if (window.Livewire) {
                                // Find the Livewire component and update properties
                                const livewireComponent = window.Livewire.find(datepickerElement.closest('[wire\\:id]')?.getAttribute('wire:id'));
                                if (livewireComponent) {
                                    livewireComponent.set('filterLastTransactionFrom', date1.format('YYYY-MM-DD'));
                                    livewireComponent.set('filterLastTransactionTo', '');
                                }
                            }
                        }
                        
                        // Trigger input event for additional compatibility
                        if (window.Livewire) {
                            datepickerElement.dispatchEvent(new Event('input', { bubbles: true }));
                        }
                    });
                    
                    picker.on('clear', () => {
                        console.log('Date cleared');
                        datepickerElement.value = '';
                        if (window.Livewire) {
                            datepickerElement.dispatchEvent(new Event('input', { bubbles: true }));
                        }
                    });
                }
            });
            
            // Direct event listener for clear button
            document.addEventListener('click', function(event) {
                // Check if the clicked element is the clear filters button
                if (event.target.closest('[wire\\:click="clearFilters"]')) {
                    console.log('Clear filters button clicked, clearing datepicker...');
                    setTimeout(() => {
                        datepickerElement.value = '';
                        if (picker) {
                            picker.clearSelection();
                            console.log('Datepicker cleared via button click');
                        }
                    }, 100); // Small delay to ensure Livewire has processed
                }
            });
            
            // Listen for Livewire events to clear the datepicker
            document.addEventListener('livewire:updated', function (event) {
                // Check if filters were cleared by looking at the component data
                const component = event.detail.component;
                if (component && 
                    component.get('filterLastTransactionFrom') === '' && 
                    component.get('filterLastTransactionTo') === '') {
                    // Clear the datepicker display value
                    datepickerElement.value = '';
                    // Clear the picker's selected dates
                    if (picker) {
                        picker.clearSelection();
                    }
                }
            });
            
            // Alternative: Listen for custom clear event from Livewire
            document.addEventListener('livewire:dispatch', function (event) {
                console.log('Livewire dispatch received:', event.detail);
                if (event.detail.name === 'clearDatePicker') {
                    console.log('Clearing date picker...');
                    datepickerElement.value = '';
                    if (picker) {
                        picker.clearSelection();
                        console.log('Date picker cleared successfully');
                    }
                }
            });
            
            console.log("Litepicker initialized successfully");
        } else {
            console.error("Datepicker element not found");
        }
    } else {
        console.error("Litepicker library not found");
    }
});
