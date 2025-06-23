// Mode switching logic
const modeSwitch = document.getElementById('modeSwitch');
const body = document.body;

// Initialize an object to store the chart instances
const charts = {
    userChart: null,
    nutritionChart: null,
    exerciseChart: null
};

function setMode(isDarkMode) {
    if (isDarkMode) {
        body.classList.remove('light-mode');
        body.classList.add('dark-mode');
        document.documentElement.setAttribute('data-bs-theme', 'dark');
        modeSwitch.checked = true;
    } else {
        body.classList.remove('dark-mode');
        body.classList.add('light-mode');
        document.documentElement.setAttribute('data-bs-theme', 'light');
        modeSwitch.checked = false;
    }
    localStorage.setItem('darkMode', isDarkMode);
    updateChartStyles();
}
document.addEventListener('DOMContentLoaded', function() {
    const savedMode = localStorage.getItem('darkMode');
    if (savedMode !== null) {
        setMode(savedMode === 'true');
    }
});

// Mode switch event listener
modeSwitch.addEventListener('change', function () {
    setMode(this.checked);
});

function updateChartStyles() {
    const isDarkMode = body.classList.contains('dark-mode');
    const textColor = isDarkMode ? '#ffffff' : '#333333';
    const gridColor = isDarkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';

    // Loop through each chart in the charts object
    Object.values(charts).forEach(chart => {
        if (chart) {
            // Update the chart's color options
            chart.options.scales.y.grid.color = gridColor;
            chart.options.scales.x.grid.color = gridColor;
            chart.options.scales.y.ticks.color = textColor;
            chart.options.scales.x.ticks.color = textColor;
            // Update the chart to reflect the changes
            chart.update();
        }
    });
}

// Nutrition Data Chart
const ctxNutrition = document.getElementById('nutritionChart').getContext('2d');

// Create a new Chart instance for nutrition data
charts.nutritionChart = new Chart(ctxNutrition, {
    type: 'pie', // Pie chart type
    data: {
        labels: ['Proteins', 'Carbs', 'Fats'], // Labels for the pie slices
        datasets: [{
            data: [30, 50, 20], // Example data for the slices
            backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56'], // Background colors for slices
            hoverBackgroundColor: ['#FF6384', '#36A2EB', '#FFCE56'] // Background colors on hover
        }]
    },
    options: {
        responsive: true, // Make the chart responsive
        maintainAspectRatio: false, // Prevent aspect ratio from being maintained
        plugins: {
            legend: {
                position: 'bottom' // Display the legend at the bottom
            }
        }
    }
});

// Exercise Data Chart
const ctxExercise = document.getElementById('exerciseChart').getContext('2d');

// Create a new Chart instance for exercise data
charts.exerciseChart = new Chart(ctxExercise, {
    type: 'line', // Line chart type
    data: {
        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'], // Labels for the data points
        datasets: [{
            label: 'Minutes of Exercise', // Label for the dataset
            data: [30, 45, 60, 30, 45, 90, 60], // Example data
            fill: false, // Don't fill the area under the line
            borderColor: '#4CAF50', // Line color
            tension: 0.1 // Line tension
        }]
    },
    options: {
        responsive: true, // Make the chart responsive
        maintainAspectRatio: false, // Prevent aspect ratio from being maintained
        scales: {
            y: {
                beginAtZero: true, // Start y-axis at zero
                grid: {
                    color: 'rgba(0, 0, 0, 0.1)' // Grid line color
                },
                ticks: {
                    color: '#333333', // Tick color
                    font: {
                        size: 10 // Tick font size
                    }
                }
            },
            x: {
                grid: {
                    color: 'rgba(0, 0, 0, 0.1)' // Grid line color
                },
                ticks: {
                    color: '#333333', // Tick color
                    font: {
                        size: 10 // Tick font size
                    }
                }
            }
        }
    }
});


// Function to update nutrition data
function updateNutritionData() {
    const form = document.getElementById('nutritionForm');
    const formData = new FormData(form);

    // Send the form data to the server using Fetch API
    fetch('admin.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json()) // Parse the response as JSON
        .then(data => {
            // Check if the update was successful
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Nutrition data updated successfully!',
                    confirmButtonColor: '#4CAF50'
                });
                // Optionally, you can reset the form fields here
                form.reset();
            } else {
                // If there was an error, display the error message
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Error updating nutrition data: ' + data.message,
                    confirmButtonColor: '#d33'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error); // Log any network or parsing errors
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'An error occurred while updating nutrition data.',
                confirmButtonColor: '#d33'
            });
        });
}

// Function to update exercise data
// Update the updateExerciseData function
function updateExerciseData() {
    const form = document.getElementById('exerciseForm');
    const formData = new FormData(form);

    fetch('admin.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Exercise data updated successfully!',
                confirmButtonColor: '#4CAF50'
            });
            form.reset();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Error updating exercise data: ' + data.message,
                confirmButtonColor: '#d33'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'An error occurred while updating exercise data.',
            confirmButtonColor: '#d33'
        });
    });
}

// Event listeners for form submissions
document.getElementById('nutritionForm').addEventListener('submit', function (e) {
    e.preventDefault(); // Prevent default form submission behavior
    updateNutritionData(); // Call the function to update nutrition data
});

document.getElementById('exerciseForm').addEventListener('submit', function (e) {
    e.preventDefault(); // Prevent default form submission behavior
    updateExerciseData(); // Call the function to update exercise data
});

// Initial call to set the correct chart styles on