document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM fully loaded and parsed');
    const bodyPartButtons = document.querySelectorAll('.body-part-btn');
    const exercisesList = document.getElementById('exercisesList');
    const selectedExercisesList = document.getElementById('selectedExercisesList');
    const workoutNameInput = document.getElementById('workoutNameInput');
    const saveWorkoutBtn = document.getElementById('saveWorkoutBtn');
    const clearSelectionBtn = document.getElementById('clearSelectionBtn');
    const randomWorkoutBtn = document.getElementById('randomWorkoutBtn');
    const savedWorkoutsList = document.getElementById('savedWorkoutsList');
    const darkModeToggle = document.getElementById('darkModeToggle');

    console.log('All DOM elements selected');

    // Load saved dark mode preference
    function loadDarkMode() {
        console.log('Loading dark mode preference');
        const darkMode = localStorage.getItem('darkMode');
        if (darkMode === 'enabled') {
            document.body.classList.add('dark-mode');
            darkModeToggle.checked = true;
        } else {
            document.body.classList.remove('dark-mode');
            darkModeToggle.checked = false;
        }
        console.log('Dark mode state:', darkMode);
    }

    // Save dark mode preference
    darkModeToggle.addEventListener('change', () => {
        console.log('Dark mode toggle changed');
        document.body.classList.toggle('dark-mode', darkModeToggle.checked);
        localStorage.setItem('darkMode', darkModeToggle.checked ? 'enabled' : 'disabled');
        console.log('Dark mode set to:', darkModeToggle.checked);
    });

    // Load dark mode on page load
    loadDarkMode();

    // Load exercises for selected body part using AJAX
    bodyPartButtons.forEach(button => {
        button.addEventListener('click', () => {
            console.log('Body part button clicked:', button.dataset.bodyPartId);
            loadExercisesForBodyPart(button.dataset.bodyPartId);
        });
    });

    function loadExercisesForBodyPart(bodyPartId) {
        console.log('Loading exercises for body part:', bodyPartId);
        
        const xhr = new XMLHttpRequest();
        xhr.open('GET', `includes/get_exercises.php?bodyPartId=${bodyPartId}`, true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                console.log('Exercises data received:', response);
                if (response.status === 'success') {
                    displayExercises(response.data);
                } else {
                    exercisesList.innerHTML = `<li class="list-group-item text-danger">Error loading exercises: ${response.message || 'Unknown error'}</li>`;
                }
            } else if (xhr.readyState === 4) {
                exercisesList.innerHTML = `<li class="list-group-item text-danger">Error loading exercises: ${xhr.status}</li>`;
            }
        };
        xhr.send();
    }

    function displayExercises(exercises) {
        console.log('Displaying exercises:', exercises);
        exercisesList.innerHTML = '';
        exercises.forEach(exercise => {
            const li = document.createElement('li');
            li.className = 'list-group-item d-flex justify-content-between align-items-center';
            li.innerHTML = `
                ${exercise.ExerciseName}
                <button class="btn btn-outline-success btn-sm select-exercise-btn" data-exercise-id="${exercise.ExerciseID}">Select</button>
            `;
            exercisesList.appendChild(li);
        });
        console.log('Exercises added to DOM');
        addSelectExerciseListeners();
    }

    function addSelectExerciseListeners() {
        console.log('Adding select exercise listeners');
        document.querySelectorAll('.select-exercise-btn').forEach(button => {
            button.removeEventListener('click', selectExerciseHandler);
            button.addEventListener('click', selectExerciseHandler);
            console.log('Listener added to button:', button.dataset.exerciseId);
        });
    }

    function selectExerciseHandler(event) {
        console.log('Select exercise button clicked');
        const button = event.currentTarget;
        const exerciseId = button.dataset.exerciseId;
        const exerciseName = button.parentElement.firstChild.textContent.trim();
        console.log('Exercise selected:', exerciseId, exerciseName);
        addExerciseToSelection(exerciseId, exerciseName);
    }

    function addExerciseToSelection(exerciseId, exerciseName) {
        console.log(`Adding exercise to selection: ${exerciseId}, ${exerciseName}`);
        if ([...selectedExercisesList.children].some(li => li.dataset.exerciseId == exerciseId)) {
            console.log('Exercise already in selection, not adding');
            return;
        }

        const li = document.createElement('li');
        li.className = 'list-group-item d-flex justify-content-between align-items-center';
        li.textContent = exerciseName;
        li.dataset.exerciseId = exerciseId;

        const removeButton = document.createElement('button');
        removeButton.className = 'btn btn-outline-danger btn-sm';
        removeButton.textContent = 'Remove';
        removeButton.addEventListener('click', () => {
            console.log('Remove button clicked');
            selectedExercisesList.removeChild(li);
        });

        li.appendChild(removeButton);
        selectedExercisesList.appendChild(li);
        console.log('Exercise added to selection');
    }

    saveWorkoutBtn.addEventListener('click', saveWorkout);
    clearSelectionBtn.addEventListener('click', clearSelectedExercises);
    randomWorkoutBtn.addEventListener('click', generateRandomWorkout);

    // Save the workout using AJAX
    function saveWorkout() {
        console.log('Saving workout');
        const workoutName = workoutNameInput.value.trim();
        const exercises = [...selectedExercisesList.children].map(li => ({
            id: li.dataset.exerciseId
        }));

        if (!workoutName || exercises.length === 0) {
            console.log('Invalid workout data');
            alert('Please enter a workout name and select at least one exercise.');
            return;
        }

        console.log('Workout data:', { WorkoutName: workoutName, exercises });

        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'includes/save_workout.php', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                console.log('Save workout response:', response);
                if (response.status === 'success') {
                    alert('Workout saved successfully!');
                    loadSavedWorkouts();
                    clearSelectedExercises();
                } else {
                    alert('Failed to save workout: ' + (response.message || 'Unknown error'));
                }
            } else if (xhr.readyState === 4) {
                console.error('Error saving workout:', xhr.status);
                alert('An error occurred while saving the workout. Please try again.');
            }
        };

        xhr.send(JSON.stringify({ WorkoutName: workoutName, exercises }));
    }

    function clearSelectedExercises() {
        console.log('Clearing selected exercises');
        selectedExercisesList.innerHTML = '';
        workoutNameInput.value = '';
    }

    function generateRandomWorkout() {
        console.log('Random workout generation requested');
        // Logic for generating random workouts can be added here
        console.log('Random workout generation not implemented yet.');
    }

    // Load saved workouts using AJAX
    function loadSavedWorkouts() {
        console.log('Loading saved workouts');
        
        const xhr = new XMLHttpRequest();
        xhr.open('GET', 'includes/get_saved_workouts.php', true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                console.log('Saved workouts data:', response);
                displaySavedWorkouts(response.data);
            } else if (xhr.readyState === 4) {
                console.error('Error loading saved workouts:', xhr.status);
            }
        };
        xhr.send();
    }

    function displaySavedWorkouts(workouts) {
        console.log('Displaying saved workouts:', workouts);
        savedWorkoutsList.innerHTML = '';
        if (workouts.length === 0) {
            savedWorkoutsList.innerHTML = '<li class="list-group-item text-muted">No saved workouts</li>';
        } else {
            workouts.forEach(workout => {
                const li = document.createElement('li');
                li.className = 'list-group-item';
                li.textContent = workout.WorkoutName;
                savedWorkoutsList.appendChild(li);
            });
        }
        console.log('Saved workouts displayed');
    }

    // Initial load of saved workouts
    loadSavedWorkouts();
});
