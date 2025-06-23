document.addEventListener('DOMContentLoaded', () => {
    initializeNutritionCategories();
    loadDarkModePreference();
    
    document.getElementById('darkModeToggle').addEventListener('change', () => {
        toggleDarkMode();
        saveDarkModePreference();
    });
});

// Initialize the nutrition categories and populate the sidebar
function initializeNutritionCategories() {
    const nutritionCategoriesList = document.getElementById('nutritionCategoriesList');
    const firstCategory = Object.keys(nutritionCategories)[0];
    
    Object.keys(nutritionCategories).forEach((category, index) => {
        const link = document.createElement('a');
        link.className = 'nav-link' + (index === 0 ? ' active' : '');
        link.href = '#';
        link.textContent = category;
        link.addEventListener('click', (e) => {
            e.preventDefault();
            selectNutritionCategory(category);
        });
        nutritionCategoriesList.appendChild(link);
    });
    
    selectNutritionCategory(firstCategory);
}

// Handle category selection and display corresponding food items
function selectNutritionCategory(category) {
    document.querySelectorAll('#nutritionCategoriesList .nav-link').forEach(link => {
        link.classList.remove('active');
        if (link.textContent === category) {
            link.classList.add('active');
        }
    });
    displayNutritionFoods(category);
}

// Display food items and their details for the selected category
function displayNutritionFoods(category) {
    const nutritionContent = document.getElementById('nutritionContent');
    nutritionContent.innerHTML = '';
    
    nutritionCategories[category].forEach(food => {
        const card = document.createElement('div');
        card.className = 'card food-card';
        card.innerHTML = `
            <div class="card-body">
                <h2 class="card-title">${food.name}</h2>
                <img src="${food.imagePath}" class="food-image img-fluid" alt="${food.name}">
                <h5>Nutrients:</h5>
                <p>${food.nutrients}</p>
                <h5>Description:</h5>
                <p>${food.description}</p>
            </div>
        `;
        nutritionContent.appendChild(card);
    });
}

// Toggle between light and dark modes
function toggleDarkMode() {
    const isDarkMode = document.getElementById('darkModeToggle').checked;
    
    if (isDarkMode) {
        document.documentElement.setAttribute('data-bs-theme', 'dark');
        document.body.classList.add('dark-mode');
    } else {
        document.documentElement.setAttribute('data-bs-theme', 'light');
        document.body.classList.remove('dark-mode');
    }
    
    // Update navbar classes
    const navbar = document.querySelector('.navbar');
    if (isDarkMode) {
        navbar.classList.remove('navbar-light', 'bg-light');
        navbar.classList.add('navbar-dark', 'bg-dark');
    } else {
        navbar.classList.remove('navbar-dark', 'bg-dark');
        navbar.classList.add('navbar-light', 'bg-light');
    }
}

// Save the dark mode preference to local storage
function saveDarkModePreference() {
    const isDarkMode = document.getElementById('darkModeToggle').checked;
    localStorage.setItem('darkMode', isDarkMode);
}

// Load the dark mode preference from local storage on page load
function loadDarkModePreference() {
    const darkModePreference = localStorage.getItem('darkMode');
    const darkModeToggle = document.getElementById('darkModeToggle');
    
    if (darkModePreference === 'true') {
        darkModeToggle.checked = true;
        toggleDarkMode(); // Apply dark mode immediately
    } else {
        darkModeToggle.checked = false;
        toggleDarkMode(); // Ensure light mode is applied
    }
}