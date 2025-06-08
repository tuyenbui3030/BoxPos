// Improved customer-management.js với wire:stream support
document.addEventListener("DOMContentLoaded", function () {
    console.log("Customer Management JavaScript loaded");
    
    // Initialize Litepicker for date filters
    if (window.Litepicker) {
        console.log("Litepicker is available, initializing datepicker");
        const datepickerElement = document.getElementById("datepicker-icon-prepend");
        if (datepickerElement) {
            console.log("Datepicker element found, creating Litepicker instance");
            
            new window.Litepicker({
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
                    // Optimized update với requestAnimationFrame
                    let updateScheduled = false;
                    
                    const scheduleUpdate = (fromDate, toDate) => {
                        if (!updateScheduled) {
                            updateScheduled = true;
                            requestAnimationFrame(() => {
                                // Update UI immediately
                                const displayValue = toDate ? 
                                    `${fromDate} - ${toDate}` : 
                                    fromDate || '';
                                datepickerElement.value = displayValue;
                                
                                // Batch Livewire updates
                                if (window.Livewire) {
                                    window.dispatchEvent(new CustomEvent('date-range-changed', {
                                        detail: { from: fromDate, to: toDate }
                                    }));
                                }
                                
                                updateScheduled = false;
                            });
                        }
                    };
                    
                    picker.on('selected', (date1, date2) => {
                        if (date1 && date2) {
                            scheduleUpdate(date1.format('YYYY-MM-DD'), date2.format('YYYY-MM-DD'));
                        } else if (date1) {
                            scheduleUpdate(date1.format('YYYY-MM-DD'), '');
                        }
                    });
                    
                    picker.on('clear', () => {
                        scheduleUpdate('', '');
                    });
                }
            });
            
            console.log("Litepicker initialized successfully");
        }
    }
});
