/**
 * Theme Switcher for BoxPos
 * Handles theme toggling between light and dark modes
 */

class ThemeSwitcher {
    constructor() {
        this.themeKey = 'theme-preference';
        this.defaultTheme = 'light';
        this.toggleSelector = '[data-bs-toggle="theme"]';
        
        this.init();
    }
    
    /**
     * Initialize theme switcher
     */
    init() {
        // Apply stored theme or default
        this.applyTheme();
        
        // Setup event listeners
        this.setupEventListeners();
    }
    
    /**
     * Apply the stored theme preference
     */
    applyTheme() {
        const storedTheme = localStorage.getItem(this.themeKey) || this.defaultTheme;
        this.setTheme(storedTheme);
    }
    
    /**
     * Set theme to light or dark
     * @param {string} theme - 'light' or 'dark'
     */
    setTheme(theme) {
        if (theme === 'dark' || theme === 'light') {
            document.documentElement.setAttribute('data-bs-theme', theme);
            localStorage.setItem(this.themeKey, theme);
        }
    }
    
    /**
     * Toggle between light and dark themes
     */
    toggleTheme() {
        const currentTheme = document.documentElement.getAttribute('data-bs-theme') || this.defaultTheme;
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        
        this.setTheme(newTheme);
    }
    
    /**
     * Setup event listeners for theme toggle buttons
     */
    setupEventListeners() {
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll(this.toggleSelector).forEach(element => {
                element.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.toggleTheme();
                });
            });
        });
    }
}

// Initialize theme switcher
const themeSwitcher = new ThemeSwitcher();
