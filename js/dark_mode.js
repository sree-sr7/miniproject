// dark-mode.js
document.addEventListener('DOMContentLoaded', () => {
    const darkModeToggle = document.getElementById('darkModeToggle');
    
    // Apply dark mode based on saved preference
    function applyDarkMode(isDark) {
        document.documentElement.setAttribute('data-bs-theme', isDark ? 'dark' : 'light');
        document.body.classList.toggle('dark-mode', isDark);
        const navbar = document.querySelector('.navbar');
        if (navbar) {
            navbar.className = `navbar navbar-expand-lg ${isDark ? 'navbar-dark bg-dark' : 'navbar-light bg-light'}`;
        }
    }

    // Check saved preference when page loads
    const isDarkMode = document.cookie.includes('darkMode=true');
    applyDarkMode(isDarkMode);
    darkModeToggle.checked = isDarkMode;

    // Handle toggle changes
    darkModeToggle.addEventListener('change', (e) => {
        const isDark = e.target.checked;
        applyDarkMode(isDark);
        document.cookie = `darkMode=${isDark}; path=/; max-age=31536000`;
    });
});