/**
 * Horizontal Navigation Enhancement
 * Improves the interaction with the horizontal navbar on mobile devices
 */

document.addEventListener('DOMContentLoaded', function() {
    // Handle dropdown menus on mobile
    const dropdownToggleElems = document.querySelectorAll('.dropdown-toggle');
    const isMobile = window.innerWidth < 768;
    
    dropdownToggleElems.forEach(function(dropdown) {
        // On mobile, first click opens dropdown, second click navigates
        if (isMobile) {
            dropdown.addEventListener('click', function(e) {
                const parentDropdown = this.closest('.dropdown');
                const isOpen = parentDropdown.classList.contains('show');
                
                // If dropdown has a URL and not just "#"
                if (this.getAttribute('href') && this.getAttribute('href') !== '#' && isOpen) {
                    return true;
                }
                
                if (!isOpen) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    // Close other open dropdowns
                    document.querySelectorAll('.dropdown.show').forEach(function(openDropdown) {
                        if (openDropdown !== parentDropdown) {
                            openDropdown.classList.remove('show');
                            openDropdown.querySelector('.dropdown-menu').classList.remove('show');
                        }
                    });
                    
                    // Open this dropdown
                    parentDropdown.classList.add('show');
                    this.setAttribute('aria-expanded', 'true');
                    this.nextElementSibling.classList.add('show');
                    return false;
                }
            });
        }
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown-menu') && !e.target.closest('.dropdown-toggle')) {
            document.querySelectorAll('.dropdown.show').forEach(function(dropdown) {
                dropdown.classList.remove('show');
                dropdown.querySelector('.dropdown-menu').classList.remove('show');
                dropdown.querySelector('.dropdown-toggle').setAttribute('aria-expanded', 'false');
            });
        }
    });
    
    // Active menu item tracking based on current URL
    const currentPath = window.location.pathname;
    document.querySelectorAll('.navbar-nav a').forEach(function(navLink) {
        const href = navLink.getAttribute('href');
        if (href && (href === currentPath || currentPath.startsWith(href) && href !== '/')) {
            navLink.closest('.nav-item').classList.add('active');
            
            // If it's in a dropdown, also activate parent
            const parentDropdown = navLink.closest('.dropdown');
            if (parentDropdown) {
                parentDropdown.classList.add('active');
            }
        }
    });
});
