class ThemeManager {
    constructor() {
        this.darkModeToggle = document.getElementById('darkModeToggle');
        this.html = document.documentElement;
        this.init();
    }

    init() {
        this.loadTheme();
        this.darkModeToggle.addEventListener('change', () => this.toggleTheme());
        document.addEventListener('DOMContentLoaded', () => this.loadTheme());
    }

    loadTheme() {
        const darkMode = localStorage.getItem('darkMode') === 'true';
        this.html.setAttribute('data-theme', darkMode ? 'dark' : 'light');
        this.darkModeToggle.checked = darkMode;
        document.dispatchEvent(new CustomEvent('themeChanged', { detail: { darkMode } }));
    }

    toggleTheme() {
        const darkMode = this.darkModeToggle.checked;
        this.html.setAttribute('data-theme', darkMode ? 'dark' : 'light');
        localStorage.setItem('darkMode', darkMode);
        document.dispatchEvent(new CustomEvent('themeChanged', { detail: { darkMode } }));
    }
}

const themeManager = new ThemeManager();
