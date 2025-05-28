// System theme preference detector
// Apply this early to prevent flash of wrong theme
(function() {
    // Check localStorage preference first
    const storedTheme = localStorage.getItem('theme-preference');
    if (storedTheme) {
        document.documentElement.setAttribute('data-bs-theme', storedTheme);
        return;
    }
    
    // Otherwise check system preference
    if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
        document.documentElement.setAttribute('data-bs-theme', 'dark');
        localStorage.setItem('theme-preference', 'dark');
    } else {
        document.documentElement.setAttribute('data-bs-theme', 'light');
        localStorage.setItem('theme-preference', 'light');
    }
})();
