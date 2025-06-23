document.addEventListener('DOMContentLoaded', () => {
    const darkModeToggle = document.getElementById('darkModeToggle');
    const body = document.body;
    const navbar = document.getElementById('navbar');

    // Function to enable dark mode
    function enableDarkMode() {
        body.classList.add('dark-mode');
        navbar.classList.replace('navbar-light', 'navbar-dark');
        navbar.classList.replace('bg-light', 'bg-dark');
        darkModeToggle.checked = true;
        localStorage.setItem('darkMode', 'enabled');
    }

    // Function to disable dark mode
    function disableDarkMode() {
        body.classList.remove('dark-mode');
        navbar.classList.replace('navbar-dark', 'navbar-light');
        navbar.classList.replace('bg-dark', 'bg-light');
        darkModeToggle.checked = false;
        localStorage.setItem('darkMode', 'disabled');
    }

    // Check for saved dark mode preference
    const darkMode = localStorage.getItem('darkMode');

    // Set initial dark mode state
    if (darkMode === 'enabled') {
        enableDarkMode();
    }

    // Listen for toggle changes
    darkModeToggle.addEventListener('change', () => {
        if (darkModeToggle.checked) {
            enableDarkMode();
        } else {
            disableDarkMode();
        }
    });

    // Event listener for muscle group navigation
    document.querySelectorAll('#bodyPartsList .nav-link').forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const muscleGroupId = this.getAttribute('data-muscle-group');
            loadExercises(muscleGroupId);
        });
    });

    // Function to load exercises based on muscle group
    function loadExercises(muscleGroupId) {
        const exerciseContent = document.getElementById('exerciseContent');
        exerciseContent.innerHTML = '';

        const exercisesByMuscle = exercises[muscleGroupId] || [];
        if (exercisesByMuscle.length === 0) {
            exerciseContent.innerHTML = '<p>No exercises available for this muscle group.</p>';
            return;
        }

        exercisesByMuscle.forEach(exercise => {
            const exerciseCard = document.createElement('div');
            exerciseCard.className = 'exercise-card card mb-3';
            exerciseCard.innerHTML = `
                <div class="card-body">
                    <h5 class="card-title">${exercise.ExerciseName}</h5>
                    <p class="card-text">${exercise.ExerciseDesc}</p>
                    <div class="video-placeholder">
                        <a href="${exercise.Video_url}" target="_blank">Watch Video</a>
                    </div>
                    <p><strong>Muscles Worked:</strong> ${exercise.muscle_worked}</p>
                </div>
            `;
            exerciseContent.appendChild(exerciseCard);
        });
    }
});
