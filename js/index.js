const modeSwitch = document.getElementById('modeSwitch');
const body = document.body;
let charts = { radarChart: null, weightChart: null };

function destroyCharts() {
    if (charts.radarChart) {
        charts.radarChart.destroy();
        charts.radarChart = null;
    }
    if (charts.weightChart) {
        charts.weightChart.destroy();
        charts.weightChart = null;
    }
}


function createRadarChart() {
    const ctxRadar = document.getElementById('radarChart');
    if (!ctxRadar) {
        console.error('Radar chart canvas element not found');
        return;
    }

    if (charts.radarChart) {
        charts.radarChart.destroy();
        charts.radarChart = null;
    }

    const ctx = ctxRadar.getContext('2d');
    
    try {
        charts.radarChart = new Chart(ctx, {
            type: 'radar',
            data: {
                labels: ['Chest', 'Back', 'Traps', 'Shoulders', 'Triceps', 'Biceps', 'Forearms', 'Legs', 'Calves', 'Glutes', 'Core'],
                datasets: [{
                    label: 'Progress',
                    data: new Array(6).fill(0),
                    backgroundColor: 'rgba(39, 174, 96, 0.2)',
                    borderColor: 'rgba(39, 174, 96, 1)',
                    borderWidth: 1,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: 'rgba(39, 174, 96, 1)',
                    pointBorderWidth: 1,
                    pointHoverBackgroundColor: '#ffffff',
                    pointHoverBorderColor: 'rgba(39, 174, 96, 1)',
                    pointHoverBorderWidth: 2,
                    pointRadius: 3,
                    pointHoverRadius: 4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    r: {
                        angleLines: {
                            display: true,
                            color: 'rgba(255, 255, 255, 0.1)'
                        },
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)',
                            circular: true
                        },
                        pointLabels: {
                            color: '#ffffff',
                            font: {
                                family: "'Inter', sans-serif",
                                size: 12,
                                weight: '400'
                            }
                        },
                        ticks: {
                            display: false,
                            beginAtZero: true,
                            max: 10,
                            stepSize: 2
                        },
                        suggestedMin: 0,
                        suggestedMax: 10
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    } catch (error) {
        console.error('Error creating radar chart:', error);
    }
}

function updateProgressChart(timeRange) {
    const userId = document.body.getAttribute('data-user-id');
    
    console.log(`Updating chart for user ${userId} with time range ${timeRange}`);
    
    if (charts.radarChart) {
        charts.radarChart.data.datasets[0].backgroundColor = 'rgba(200, 200, 200, 0.2)';
        charts.radarChart.update('none');
    }

    const timestamp = new Date().getTime();
    fetch(`includes/get-focus-levels.php?userId=${userId}&timeRange=${timeRange}&t=${timestamp}`, {
        headers: {
            'Accept': 'application/json',
            'Cache-Control': 'no-cache'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(focusLevels => {
        console.log('Received focus levels:', focusLevels);

        if (!Array.isArray(focusLevels)) {
            throw new Error('Invalid data format received');
        }

        // Round the values for the radar chart
        const roundedFocusLevels = focusLevels.map(value => Math.round(value));

        if (charts.radarChart) {
            charts.radarChart.data.datasets[0].data = roundedFocusLevels;
            charts.radarChart.data.datasets[0].backgroundColor = 'rgba(76, 175, 80, 0.2)';
            charts.radarChart.update();
        } else {
            console.error('Radar chart not initialized');
        }

        // Update the muscle focus display with rounded values
        updateMuscleFocusData(roundedFocusLevels);
    })
    .catch(error => {
        console.error('Error updating progress chart:', error);
        if (charts.radarChart) {
            charts.radarChart.data.datasets[0].backgroundColor = 'rgba(76, 175, 80, 0.2)';
            charts.radarChart.update();
        }
    });
}

function createWeightChart() {
    const ctxLine = document.getElementById('weightChart');
    if (!ctxLine) {
        console.error('Weight chart canvas element not found');
        return;
    }

    // Ensure any existing chart is destroyed
    if (charts.weightChart) {
        charts.weightChart.destroy();
    }

    const ctx = ctxLine.getContext('2d');
    if (ctx && weightData && weightData.labels && weightData.data) {
        charts.weightChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: weightData.labels,
                datasets: [{
                    label: 'Weight (kg)',
                    data: weightData.data,
                    borderColor: '#4CAF50',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: false,
                        grid: { color: 'rgba(0, 0, 0, 0.1)' },
                        ticks: { color: '#333333', font: { size: 10 } }
                    },
                    x: {
                        grid: { color: 'rgba(0, 0, 0, 0.1)' },
                        ticks: { color: '#333333', font: { size: 10 } }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.parsed.y + ' kg';
                            }
                        }
                    }
                }
            }
        });
    }
}

function getMuscleGroupData() {
    const muscles = ['Chest', 'Back', 'Traps', 'Shoulders', 'Triceps', 'Biceps', 'Forearms', 'Legs', 'Calves', 'Glutes', 'Core'];
    return muscles.map(muscle => {
        const element = document.querySelector(`[data-muscle="${muscle.toLowerCase()}"]`);
        return element ? parseInt(element.textContent) : 0;
    });
}

function updateChartStyles() {
    const isDarkMode = body.classList.contains('dark-mode');
    const textColor = isDarkMode ? 'rgba(255, 255, 255, 0.8)' : '#666666';
    const gridColor = isDarkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';

    if (charts.radarChart) {
        charts.radarChart.options.scales.r.pointLabels.color = textColor;
        charts.radarChart.options.scales.r.grid.color = gridColor;
        charts.radarChart.options.scales.r.angleLines.color = gridColor;
        charts.radarChart.update();
    }

    if (charts.weightChart) {
        charts.weightChart.options.scales.y.grid.color = gridColor;
        charts.weightChart.options.scales.x.grid.color = gridColor;
        charts.weightChart.options.scales.y.ticks.color = textColor;
        charts.weightChart.options.scales.x.ticks.color = textColor;
        charts.weightChart.update();
    }
}

modeSwitch.addEventListener('change', function() {
    body.classList.toggle('dark-mode');
    document.documentElement.setAttribute('data-bs-theme', body.classList.contains('dark-mode') ? 'dark' : 'light');
    sessionStorage.setItem('dark_mode', body.classList.contains('dark-mode'));
    updateChartStyles();
});

document.addEventListener('DOMContentLoaded', function() {
    // Clear any existing charts
    destroyCharts();
    
    if (verifyChartSetup()) {
        createRadarChart();
        createWeightChart();
        updateChartStyles();
        
        // Set initial active button
        const defaultButton = document.querySelector('.time-selector button[data-range="1W"]');
        if (defaultButton) {
            defaultButton.classList.add('active');
        }
        
        // Initial chart update
        setTimeout(() => {
            updateProgressChart('1W');
        }, 100);
    }
});

// Update time selector click handlers
document.querySelectorAll('.time-selector button').forEach(button => {
    button.addEventListener('click', function() {
        console.log(`Time selector clicked: ${this.getAttribute('data-time')}`);
        
        // Remove active class from all buttons
        document.querySelectorAll('.time-selector button').forEach(btn => 
            btn.classList.remove('active')
        );
        
        // Add active class to clicked button
        this.classList.add('active');
        
        // Update chart
        updateProgressChart(this.getAttribute('data-time'));
    });
});

// Initial chart update on page load
document.addEventListener('DOMContentLoaded', function() {
    if (verifyChartSetup()) {
        createRadarChart();
        updateProgressChart('1W');
    }
});

// Update time selector click handlers
const timeSelectors = document.querySelectorAll('.time-selector button');
timeSelectors.forEach(button => {
    button.addEventListener('click', function() {
        timeSelectors.forEach(btn => btn.classList.remove('active'));
        this.classList.add('active');
        const timeRange = this.textContent.trim();
        updateProgressChart(timeRange);
    });
});

function updateMuscleFocusData(focusLevels) {
    const muscles = ['Chest', 'Back', 'Traps', 'Shoulders', 'Triceps', 'Biceps', 'Forearms', 'Legs', 'Calves', 'Glutes', 'Core'];
    muscles.forEach((muscle, index) => {
        const element = document.querySelector(`[data-muscle="${muscle.toLowerCase()}"]`);
        if (element) {
            element.textContent = Math.round(focusLevels[index]);
        }
    });
}

// Initialize charts on page load
document.addEventListener('DOMContentLoaded', function() {
    // Clear any existing charts first
    destroyCharts();
    
    if (verifyChartSetup()) {
        createRadarChart();
        createWeightChart();
        updateChartStyles();
        // Update chart with initial data (1W by default)
        updateProgressChart('1W');
    }
});

function verifyChartSetup() {
    console.log('Checking chart setup...');
    
    const radarCanvas = document.getElementById('radarChart');
    if (!radarCanvas) {
        console.error('Radar chart canvas not found');
        return false;
    }

    const userId = document.body.getAttribute('data-user-id');
    if (!userId) {
        console.error('User ID not found in body attribute');
        return false;
    }

    const muscleFocusData = document.querySelector('.muscle-focus-data');
    if (!muscleFocusData) {
        console.error('Muscle focus data container not found');
        return false;
    }

    console.log('Chart setup verification complete');
    return true;
}

// Call this when the page loads
document.addEventListener('DOMContentLoaded', function() {
    if (verifyChartSetup()) {
        createRadarChart();
        // Update chart with initial data (1W by default)
        updateProgressChart('1W');
    }
});

function getColorForValue(value) {
    if (value >= 8) return 'rgba(76, 175, 80, 0.2)'; // High priority
    if (value >= 5) return 'rgba(255, 193, 7, 0.2)'; // Medium priority
    return 'rgba(158, 158, 158, 0.2)'; // Low priority
}

const calorieProgress = document.getElementById('calorieProgress');
if (calorieProgress) {
    const consumedCalories = parseInt(calorieProgress.getAttribute('data-consumed'));
    const goalCalories = parseInt(calorieProgress.getAttribute('data-goal'));
    const percentage = Math.min((consumedCalories / goalCalories) * 100, 100);

    calorieProgress.style.width = percentage + '%';
    calorieProgress.textContent = Math.round(percentage) + '%';

    if (percentage > 100) {
        calorieProgress.classList.add('bg-danger');
    } else if (percentage > 80) {
        calorieProgress.classList.add('bg-warning');
    }
}

// Update the feedback form handler
const feedbackForm = document.querySelector('#feedback form');
if (feedbackForm) {
    feedbackForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Get form elements
        const textarea = this.querySelector('textarea');
        const submitButton = this.querySelector('button[type="submit"]');
        const feedbackStatus = document.createElement('div');
        
        // Initial validation
        const feedbackText = textarea.value.trim();
        const userId = document.body.getAttribute('data-user-id');
        
        if (!feedbackText) {
            showFeedbackError('Please enter your feedback before submitting.');
            return;
        }
        
        if (!userId) {
            showFeedbackError('User ID not found. Please try logging in again.');
            return;
        }
        
        // Disable form while submitting
        textarea.disabled = true;
        submitButton.disabled = true;
        
        try {
            // Send feedback to server
            const response = await fetch('includes/submit-feedback.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    userId: userId,
                    feedbackText: feedbackText
                })
            });
            
            // Always parse response as JSON
            const data = await response.json().catch(() => ({
                success: false,
                message: 'Invalid server response'
            }));
            
            if (!response.ok || !data.success) {
                throw new Error(data.message || `Server error: ${response.status}`);
            }
            
            // Show success message
            showFeedbackSuccess('Thank you for your feedback!');
            this.reset();
            
        } catch (error) {
            console.error('Feedback submission error:', error);
            showFeedbackError(error.message || 'Error submitting feedback. Please try again.');
        } finally {
            // Re-enable form
            textarea.disabled = false;
            submitButton.disabled = false;
        }
    });
}

// Helper functions for feedback status
function showFeedbackError(message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'alert alert-danger mt-3';
    errorDiv.textContent = message;
    
    const form = document.querySelector('#feedback form');
    const existingAlert = form.querySelector('.alert');
    if (existingAlert) {
        existingAlert.remove();
    }
    
    form.appendChild(errorDiv);
    setTimeout(() => errorDiv.remove(), 5000);
}

function showFeedbackSuccess(message) {
    const successDiv = document.createElement('div');
    successDiv.className = 'alert alert-success mt-3';
    successDiv.textContent = message;
    
    const form = document.querySelector('#feedback form');
    const existingAlert = form.querySelector('.alert');
    if (existingAlert) {
        existingAlert.remove();
    }
    
    form.appendChild(successDiv);
    setTimeout(() => successDiv.remove(), 5000);
}

// Initialize tooltips
const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
tooltipTriggerList.map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

document.addEventListener('DOMContentLoaded', function() {
    const darkModeFromStorage = sessionStorage.getItem('dark_mode');

    if (darkModeFromStorage === 'true') {
        body.classList.add('dark-mode');
        document.documentElement.setAttribute('data-bs-theme', 'dark');
        if (modeSwitch) modeSwitch.checked = true;
    }

    createRadarChart();
    createWeightChart();
    updateChartStyles();
});

window.addEventListener('resize', function() {
    if (charts.radarChart) charts.radarChart.resize();
    if (charts.weightChart) charts.weightChart.resize();
});