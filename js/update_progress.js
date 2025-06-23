document.addEventListener('DOMContentLoaded', () => {
    const progressCategories = {
        'Weight Tracking': {
            chartType: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Weight (kg)',
                    data: [80, 79, 78, 77, 76, 75],
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1
                }]
            },
            description: 'Track your weight changes over time. Consistent progress is key to achieving your fitness goals.'
        },
        'Workout Performance': {
            chartType: 'line',
            data: {
                labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5', 'Week 6'],
                datasets: [{
                    label: 'Bench Press (kg)',
                    data: [60, 62.5, 65, 67.5, 70, 72.5],
                    borderColor: 'rgb(255, 99, 132)',
                    tension: 0.1
                }, {
                    label: 'Squats (kg)',
                    data: [80, 85, 90, 95, 100, 105],
                    borderColor: 'rgb(54, 162, 235)',
                    tension: 0.1
                }]
            },
            description: 'Track your strength gains in key exercises to visualize your performance improvements.'
        },
        'Nutrition Tracking': {
            chartType: 'doughnut',
            data: {
                labels: ['Protein', 'Carbs', 'Fats'],
                datasets: [{
                    label: 'Macronutrient Distribution',
                    data: [30, 50, 20],
                    backgroundColor: [
                        'rgb(255, 99, 132)',
                        'rgb(54, 162, 235)',
                        'rgb(255, 205, 86)'
                    ]
                }]
            },
            description: 'Monitor your macronutrient intake to ensure you\'re fueling your body optimally for your fitness goals.'
        }
    };

    let chartInstance = null;
    let focusChartInstance = null;

    function initializeProgressCategories() {
        const progressCategoriesList = document.getElementById('progressCategoriesList');
        Object.keys(progressCategories).forEach((category, index) => {
            const link = document.createElement('a');
            link.className = 'nav-link' + (index === 0 ? ' active' : '');
            link.href = '#';
            link.textContent = category;
            link.addEventListener('click', (e) => {
                e.preventDefault();
                selectProgressCategory(category);
            });
            progressCategoriesList.appendChild(link);
        });
        selectProgressCategory(Object.keys(progressCategories)[0]);
    }

    function selectProgressCategory(category) {
        document.querySelectorAll('#progressCategoriesList .nav-link').forEach(link => {
            link.classList.remove('active');
        });
        const selectedLink = Array.from(document.querySelectorAll('#progressCategoriesList .nav-link')).find(link => link.textContent === category);
        selectedLink.classList.add('active');

        const progressContent = document.getElementById('progressContent');
        progressContent.innerHTML = '';

        const progressCard = document.createElement('div');
        progressCard.className = 'card progress-card';
        const cardBody = document.createElement('div');
        cardBody.className = 'card-body';
        progressCard.appendChild(cardBody);

        const description = document.createElement('p');
        description.textContent = progressCategories[category].description;
        cardBody.appendChild(description);

        const chartContainer = document.createElement('div');
        chartContainer.className = 'chart-container';
        const canvas = document.createElement('canvas');
        chartContainer.appendChild(canvas);
        cardBody.appendChild(chartContainer);
        progressContent.appendChild(progressCard);

        if (chartInstance) {
            chartInstance.destroy();
        }
        chartInstance = new Chart(canvas, {
            type: progressCategories[category].chartType,
            data: progressCategories[category].data,
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }

    function initializeFocusChart() {
        const focusChartCanvas = document.getElementById('focusChart');
        focusChartInstance = new Chart(focusChartCanvas, {
            type: 'radar',
            data: {
                labels: ['Chest', 'Back', 'Legs', 'Arms', 'Shoulders', 'Core'],
                datasets: [{
                    label: 'Focus Level',
                    data: [0, 0, 0, 0, 0, 0],
                    fill: true,
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderColor: 'rgb(255, 99, 132)',
                    pointBackgroundColor: 'rgb(255, 99, 132)',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: 'rgb(255, 99, 132)'
                }]
            },
            options: {
                elements: {
                    line: {
                        borderWidth: 3
                    }
                },
                scales: {
                    r: {
                        angleLines: {
                            display: false
                        },
                        suggestedMin: 0,
                        suggestedMax: 10
                    }
                }
            }
        });
    }

    function updateFocusChart(focusData) {
        focusChartInstance.data.datasets[0].data = focusData;
        focusChartInstance.update();
    }

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

    const weightForm = document.getElementById('weightForm');
    weightForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const weight = document.getElementById('weightInput').value;
        updateWeight(weight);
    });

    function updateWeight(weight) {
        fetch('update_progress.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=updateWeight&weight=${weight}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Weight updated successfully!');
                // Update the weight tracking chart here
            } else {
                alert('Failed to update weight. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    }

    function fetchFocusLevel() {
        fetch('update_progress.php?action=getFocusLevel')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateFocusChart(data.focusLevel);
            } else {
                console.error('Failed to fetch focus level');
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    initializeProgressCategories();
    initializeFocusChart();
    fetchFocusLevel();
});