document.addEventListener('DOMContentLoaded', () => {
    const bodyPartButtons = document.querySelectorAll('.body-part-btn');
    const exercisesList = document.getElementById('exercisesList');
    const selectedExercisesList = document.getElementById('selectedExercisesList');
    const workoutNameInput = document.getElementById('workoutNameInput');
    const saveWorkoutBtn = document.getElementById('saveWorkoutBtn');
    const clearSelectionBtn = document.getElementById('clearSelectionBtn');
    const randomWorkoutBtn = document.getElementById('randomWorkoutBtn');
    const savedWorkoutsList = document.getElementById('savedWorkoutsList');
    const darkModeToggle = document.getElementById('darkModeToggle');

    // Load saved dark mode preference
    function loadDarkMode() {
        const darkMode = localStorage.getItem('darkMode');
        if (darkMode === 'enabled') {
            document.body.classList.add('dark-mode');
            darkModeToggle.checked = true;
        } else {
            document.body.classList.remove('dark-mode');
            darkModeToggle.checked = false;
        }
    }

    // Save dark mode preference
    darkModeToggle.addEventListener('change', () => {
        document.body.classList.toggle('dark-mode', darkModeToggle.checked);
        localStorage.setItem('darkMode', darkModeToggle.checked ? 'enabled' : 'disabled');
    });

    // Load dark mode on page load
    loadDarkMode();

    // Load exercises for selected body part
    bodyPartButtons.forEach(button => {
        button.addEventListener('click', () => loadExercisesForBodyPart(button.dataset.bodyPartId));
    });

    function loadExercisesForBodyPart(bodyPartId) {
        fetch(`http://localhost:3000/api/exercise/${bodyPartId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                displayExercises(data);
            })
            .catch(error => {
                console.error('Error fetching exercises:', error);
            });
    }
    
    

    function displayExercises(exercises) {
        console.log('Exercises fetched:', exercises); // Debugging line
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
        addSelectExerciseListeners(); // Ensure this line is present
    }
    
    
    function addSelectExerciseListeners() {
        document.querySelectorAll('.select-exercise-btn').forEach(button => {
            console.log('Adding event listener for button:', button.dataset.exerciseId); // Log for debugging
            button.addEventListener('click', () => {
                const exerciseId = button.dataset.exerciseId;
                const exerciseName = button.parentElement.firstChild.textContent.trim();
                addExerciseToSelection(exerciseId, exerciseName);
            });
        });
    }
    
    
    function addExerciseToSelection(exerciseId, exerciseName) {
        console.log(`Adding exercise: ${exerciseId}, ${exerciseName}`); // Check if values are correct
        console.log('Currently selected exercises:', [...selectedExercisesList.children].map(li => li.dataset.exerciseId)); // Log current selections
        if ([...selectedExercisesList.children].some(li => li.dataset.exerciseId == exerciseId)) return;
    
        const li = document.createElement('li');
        li.className = 'list-group-item d-flex justify-content-between align-items-center';
        li.textContent = exerciseName;
        li.dataset.exerciseId = exerciseId;
    
        const removeButton = document.createElement('button');
        removeButton.className = 'btn btn-outline-danger btn-sm';
        removeButton.textContent = 'Remove';
        removeButton.addEventListener('click', () => {
            selectedExercisesList.removeChild(li);
        });
    
        li.appendChild(removeButton);
        selectedExercisesList.appendChild(li);
    }
    

    saveWorkoutBtn.addEventListener('click', saveWorkout);
    clearSelectionBtn.addEventListener('click', clearSelectedExercises);
    randomWorkoutBtn.addEventListener('click', generateRandomWorkout);

    function saveWorkout() {
        const workoutName = workoutNameInput.value.trim();
        const exercises = [...selectedExercisesList.children].map(li => ({
            id: li.dataset.exerciseId
        }));
    
        if (!workoutName || exercises.length === 0) {
            alert('Please enter a workout name and select at least one exercise.');
            return;
        }
    
        fetch('includes/save_workout.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ WorkoutName: workoutName, exercises })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert('Workout saved successfully!');
                loadSavedWorkouts();
                clearSelectedExercises();
            } else {
                alert('Failed to save workout: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error saving workout:', error);
            alert('An error occurred while saving the workout. Please try again.');
        });
    }
    
    function clearSelectedExercises() {
        selectedExercisesList.innerHTML = '';
        workoutNameInput.value = '';
    }

    function generateRandomWorkout() {
        // Logic for generating random workouts can be added here
        console.log('Random workout generation not implemented yet.');
    }

    function loadSavedWorkouts() {
        fetch('includes/get_saved_workouts.php')
            .then(response => response.json())
            .then(data => {
                displaySavedWorkouts(data.data);
            })
            .catch(error => console.error('Error loading saved workouts:', error));
    }

    function displaySavedWorkouts(workouts) {
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
    }

    // Initial load of saved workouts
    loadSavedWorkouts();
});