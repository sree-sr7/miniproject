// progress.js
document.addEventListener('DOMContentLoaded', function() {
    // DOM elements
    const darkModeToggle = document.getElementById('darkModeToggle');
    const body = document.body;
    const navbar = document.getElementById('navbar');

    // Function to enable dark mode
    function enableDarkMode() {
        body.classList.add('dark-mode');
        navbar.classList.remove('navbar-light', 'bg-light');
        navbar.classList.add('navbar-dark', 'bg-dark');
        darkModeToggle.checked = true;
        localStorage.setItem('darkMode', 'enabled');
        
        // Update form controls background
        updateFormControlsBackground('#333333');
    }

    // Function to disable dark mode
    function disableDarkMode() {
        body.classList.remove('dark-mode');
        navbar.classList.remove('navbar-dark', 'bg-dark');
        navbar.classList.add('navbar-light', 'bg-light');
        darkModeToggle.checked = false;
        localStorage.setItem('darkMode', 'disabled');
        
        // Reset form controls background
        updateFormControlsBackground('');
    }

    // Function to update form controls background
    function updateFormControlsBackground(color) {
        const formControls = document.querySelectorAll('.form-control, .form-select');
        formControls.forEach(control => {
            control.style.backgroundColor = color;
        });
    }

    // Function to get system dark mode preference
    function getSystemPreference() {
        return window.matchMedia && 
               window.matchMedia('(prefers-color-scheme: dark)').matches;
    }

    // Initialize dark mode
    function initializeDarkMode() {
        const savedPreference = localStorage.getItem('darkMode');
        if (savedPreference === 'enabled') {
            enableDarkMode();
        } else if (savedPreference === 'disabled') {
            disableDarkMode();
        } else if (getSystemPreference()) {
            enableDarkMode();
        }
    }

    // Listen for system dark mode changes
    const darkModeMediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
    darkModeMediaQuery.addListener((e) => {
        if (!localStorage.getItem('darkMode')) {
            if (e.matches) {
                enableDarkMode();
            } else {
                disableDarkMode();
            }
        }
    });

    // Initialize dark mode
    initializeDarkMode();

    // Listen for dark mode toggle
    darkModeToggle.addEventListener('change', function() {
        if (this.checked) {
            enableDarkMode();
        } else {
            disableDarkMode();
        }
    });

    // Handle form control focus in dark mode
    const formControls = document.querySelectorAll('.form-control, .form-select');
    formControls.forEach(control => {
        control.addEventListener('focus', function() {
            if (body.classList.contains('dark-mode')) {
                this.style.backgroundColor = '#333333';
            }
        });
        
        control.addEventListener('blur', function() {
            if (body.classList.contains('dark-mode')) {
                this.style.backgroundColor = '#333333';
            } else {
                this.style.backgroundColor = '';
            }
        });
    });
});