/**
 * Enhanced Horizontal Navigation for BoxPos
 * Based on Tabler's horizontal layout
 */

document.addEventListener('DOMContentLoaded', function() {
    // Mark the body as horizontal layout
    document.body.setAttribute('data-layout', 'horizontal');
    
    /**
     * Handle menu overflow by creating "More" dropdown for items that don't fit
     * This adapts the horizontal menu for various screen sizes
     */
    const handleHorizontalMenuOverflow = function() {
        // Only run this on desktop where horizontal layout applies
        if (window.innerWidth < 768) return;
        
        const navbar = document.querySelector('.navbar-expand-md .navbar-collapse .navbar-nav');
        if (!navbar) return;
        
        // Calculate available width
        const containerWidth = navbar.parentElement.clientWidth;
        let usedWidth = 0;
        let overflowItems = [];
        
        // Reset any previous "more" dropdown setup
        const existingOverflow = document.querySelector('.horizontal-overflow-menu');
        if (existingOverflow) {
            // Move any items from overflow back to main menu
            const overflowedItems = existingOverflow.querySelectorAll('.dropdown-menu .nav-item');
            overflowedItems.forEach(item => {
                navbar.appendChild(item);
            });
            
            existingOverflow.remove();
        }
        
        // Measure each menu item
        const menuItems = Array.from(navbar.querySelectorAll('.nav-item:not(.horizontal-overflow-menu)'));
        
        // Calculate which items fit and which don't
        menuItems.forEach(item => {
            usedWidth += item.clientWidth;
            
            // If this item pushes us over the edge, add it to overflow
            if (usedWidth > containerWidth - 100) { // Allow space for the "More" dropdown
                overflowItems.push(item);
            }
        });
        
        // If we have overflow items, create the "More" dropdown
        if (overflowItems.length > 0) {
            // Create the more dropdown
            const overflowMenu = document.createElement('li');
            overflowMenu.className = 'nav-item dropdown horizontal-overflow-menu';
            overflowMenu.innerHTML = `
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" role="button" aria-expanded="false">
                    <span class="nav-link-title">More</span>
                </a>
                <div class="dropdown-menu">
                </div>
            `;
            
            const dropdownMenu = overflowMenu.querySelector('.dropdown-menu');
            
            // Move overflow items to dropdown
            overflowItems.forEach(item => {
                // For dropdown items, we need to adjust their styling when in the overflow menu
                if (item.classList.contains('dropdown')) {
                    // Convert a dropdown to a submenu
                    const link = item.querySelector('.nav-link');
                    const menu = item.querySelector('.dropdown-menu');
                    
                    const newItem = document.createElement('div');
                    newItem.className = 'dropend';
                    newItem.innerHTML = `
                        <a class="dropdown-item dropdown-toggle" href="${link.getAttribute('href') || '#'}" data-bs-toggle="dropdown">
                            ${link.innerHTML}
                        </a>
                        <div class="dropdown-menu">
                            ${menu.innerHTML}
                        </div>
                    `;
                    
                    dropdownMenu.appendChild(newItem);
                } else {
                    // Convert regular nav item to dropdown item
                    const link = item.querySelector('.nav-link');
                    const newItem = document.createElement('a');
                    newItem.className = 'dropdown-item';
                    newItem.href = link.getAttribute('href') || '#';
                    newItem.innerHTML = link.innerHTML;
                    
                    dropdownMenu.appendChild(newItem);
                }
                
                item.remove();
            });
            
            // Add the overflow menu
            navbar.appendChild(overflowMenu);
        }
    };
    
    // Run on load and resize
    handleHorizontalMenuOverflow();
    window.addEventListener('resize', handleHorizontalMenuOverflow);
    
    // Ensure dropdowns work properly in horizontal layout
    const nestedDropdowns = document.querySelectorAll('.dropend .dropdown-toggle');
    nestedDropdowns.forEach(dropdown => {
        dropdown.addEventListener('click', function(e) {
            e.stopPropagation();
            e.preventDefault();
            
            const parentDropend = this.closest('.dropend');
            const submenu = parentDropend.querySelector('.dropdown-menu');
            
            if (submenu.classList.contains('show')) {
                submenu.classList.remove('show');
            } else {
                document.querySelectorAll('.dropend .dropdown-menu.show').forEach(menu => {
                    menu.classList.remove('show');
                });
                submenu.classList.add('show');
            }
        });
    });
});
