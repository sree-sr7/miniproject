document.addEventListener('DOMContentLoaded', function() {
    const modeSwitch = document.getElementById('modeSwitch');
    const body = document.body;

    // Function to set the theme
    function setTheme(theme) {
        if (theme === 'dark') {
            body.classList.remove('light-mode');
            body.classList.add('dark-mode');
            modeSwitch.checked = true;
        } else {
            body.classList.remove('dark-mode');
            body.classList.add('light-mode');
            modeSwitch.checked = false;
        }
        console.log('Theme applied:', theme); // Debugging
        console.log('Body class list:', body.classList); // Debugging
    }

    // Check for saved theme preference or use default
    const savedTheme = localStorage.getItem('theme') || 'light';
    console.log('Saved theme:', savedTheme); // Debugging
    setTheme(savedTheme);

    // Theme switch event listener
    modeSwitch.addEventListener('change', function() {
        if (this.checked) {
            setTheme('dark');
            localStorage.setItem('theme', 'dark');
        } else {
            setTheme('light');
            localStorage.setItem('theme', 'light');
        }
        console.log('Theme switched to:', this.checked ? 'dark' : 'light'); // Debugging
    });
});
