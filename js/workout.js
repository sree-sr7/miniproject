let selectedExercises = [];

function applyTheme(theme) {
    const isDark = theme === 'dark';
    document.body.classList.toggle('dark-mode', isDark);
    document.documentElement.setAttribute('data-bs-theme', theme);

    const navbar = document.querySelector('.navbar');
    if (isDark) {
        navbar.classList.remove('navbar-light', 'bg-light');
        navbar.classList.add('navbar-dark', 'bg-dark');
    } else {
        navbar.classList.remove('navbar-dark', 'bg-dark');
        navbar.classList.add('navbar-light', 'bg-light');
    }

    // Update checkbox state
    const darkModeToggle = document.getElementById('darkModeToggle');
    if (darkModeToggle) {
        darkModeToggle.checked = isDark;
    }
}

function initializeMuscleGroups() {
    fetch('get_exercises.php?action=get_muscle_groups')
        .then(response => response.json())
        .then(muscleGroups => {
            const muscleGroupsList = document.getElementById('muscleGroupsList');
            muscleGroups.forEach(muscleGroup => {
                const button = document.createElement('button');
                button.className = 'btn btn-outline-primary body-part-btn';
                button.textContent = muscleGroup.MuscleGroupName;
                button.addEventListener('click', () => selectMuscleGroup(muscleGroup.MuscleGroupID, muscleGroup.MuscleGroupName));
                muscleGroupsList.appendChild(button);
            });
        });
}

function selectMuscleGroup(muscleGroupId, muscleGroupName) {
    document.querySelectorAll('.body-part-btn').forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
    document.getElementById('exercisesHeader').textContent = `${muscleGroupName} Exercises`;
    fetch(`get_exercises.php?action=get_exercises&muscle_group_id=${muscleGroupId}`)
        .then(response => response.json())
        .then(exercises => populateExercises(exercises));
}

function populateExercises(exercises) {
    const exercisesList = document.getElementById('exercisesList');
    exercisesList.innerHTML = '';
    exercises.forEach(exercise => {
        const li = document.createElement('li');
        li.className = 'list-group-item exercise-item';
        li.textContent = exercise.ExerciseName;
        li.addEventListener('click', () => toggleExercise(exercise.ExerciseID, exercise.ExerciseName, li));
        if (selectedExercises.some(e => e.id === exercise.ExerciseID)) {
            li.classList.add('selected');
        }
        exercisesList.appendChild(li);
    });
}

function toggleExercise(exerciseId, exerciseName, element) {
    const index = selectedExercises.findIndex(e => e.id === exerciseId);
    if (index !== -1) {
        selectedExercises.splice(index, 1);
        element.classList.remove('selected');
    } else {
        selectedExercises.push({ id: exerciseId, name: exerciseName });
        element.classList.add('selected');
    }
    updateSelectedExercisesList();
}

function updateSelectedExercisesList() {
    const selectedExercisesList = document.getElementById('selectedExercisesList');
    selectedExercisesList.innerHTML = '';
    selectedExercises.forEach(exercise => {
        const li = document.createElement('li');
        li.className = 'list-group-item';
        li.textContent = exercise.name;
        selectedExercisesList.appendChild(li);
    });
}

function clearSelection() {
    selectedExercises = [];
    updateSelectedExercisesList();
    document.querySelectorAll('.exercise-item').forEach(item => item.classList.remove('selected'));
}

function saveWorkout() {
    const workoutName = document.getElementById('workoutNameInput').value.trim();
    if (selectedExercises.length > 0 && workoutName) {
        const workout = { name: workoutName, exercises: selectedExercises };
        console.log('Sending workout data:', workout);
        fetch('save_workouts.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(workout),
        })
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);
            return response.text(); // Get the raw text first
        })
        .then(text => {
            console.log('Raw response:', text);
            try {
                return JSON.parse(text); // Try to parse it as JSON
            } catch (error) {
                throw new Error('Invalid JSON response from server: ' + text);
            }
        })
        .then(data => {
            console.log('Parsed response data:', data);
            if (data.success) {
                alert(`Workout "${workoutName}" saved successfully!`);
                clearSelection();
                document.getElementById('workoutNameInput').value = '';
                updateSavedWorkoutsList();
            } else {
                alert('Error saving workout: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error saving workout: ' + error.message);
        });
    } else {
        alert('Please select at least one exercise and provide a workout name before saving.');
    }
}
function updateSavedWorkoutsList() {
    fetch('load_workouts.php')
        .then(response => response.json())
        .then(workouts => {
            const savedWorkoutsList = document.getElementById('savedWorkoutsList');
            savedWorkoutsList.innerHTML = '';
            workouts.forEach(workout => {
                const li = document.createElement('li');
                li.className = 'list-group-item saved-workout';
                li.textContent = workout.WorkoutName;
                li.addEventListener('click', () => loadSavedWorkout(workout.WorkoutID));
                savedWorkoutsList.appendChild(li);
            });
        });
}

function loadSavedWorkout(workoutId) {
    fetch(`load_workouts.php?workout_id=${workoutId}`)
        .then(response => response.json())
        .then(workout => {
            if (workout) {
                selectedExercises = workout.exercises.map(e => ({ id: e.ExerciseID, name: e.ExerciseName }));
                updateSelectedExercisesList();
                document.getElementById('workoutNameInput').value = workout.WorkoutName;
                alert(`Workout "${workout.WorkoutName}" loaded successfully!`);
            }
        });
}

function generateRandomWorkout() {
    fetch('get_exercises.php?action=get_random_exercises')
        .then(response => response.json())
        .then(exercises => {
            selectedExercises = exercises.map(e => ({ id: e.ExerciseID, name: e.ExerciseName }));
            updateSelectedExercisesList();
            document.getElementById('workoutNameInput').value = 'Random Workout';
            alert('Random workout generated!');
        });
}

function toggleDarkMode() {
    const newTheme = document.body.classList.contains('dark-mode') ? 'light' : 'dark';
    localStorage.setItem('theme', newTheme);
    applyTheme(newTheme);
}

document.addEventListener('DOMContentLoaded', () => {
    // Initialize theme from localStorage or default to light
    const savedTheme = localStorage.getItem('theme') || 'light';
    applyTheme(savedTheme);

    initializeMuscleGroups();
    document.getElementById('clearSelectionBtn').addEventListener('click', clearSelection);
    document.getElementById('saveWorkoutBtn').addEventListener('click', saveWorkout);
    document.getElementById('randomWorkoutBtn').addEventListener('click', generateRandomWorkout);
    document.getElementById('darkModeToggle').addEventListener('change', toggleDarkMode);
    updateSavedWorkoutsList();
});