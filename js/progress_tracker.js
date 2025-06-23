document.addEventListener('DOMContentLoaded', () => {
  const darkModeToggle = document.getElementById('darkModeToggle');
  const body = document.body;
  const navbar = document.getElementById('navbar');

  darkModeToggle.addEventListener('change', () => {
      if (darkModeToggle.checked) {
          body.classList.add('dark-mode');
          navbar.classList.replace('navbar-light', 'navbar-dark');
          navbar.classList.replace('bg-light', 'bg-dark');
      } else {
          body.classList.remove('dark-mode');
          navbar.classList.replace('navbar-dark', 'navbar-light');
          navbar.classList.replace('bg-dark', 'bg-light');
      }
  });
});
