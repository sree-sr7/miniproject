class ChartManager {
    constructor() {
        this.charts = {
            progress: null,
            weight: null
        };
        this.initCharts();
        this.initThemeListener();
    }

    getChartTheme(darkMode) {
        return {
            color: darkMode ? '#ffffff' : '#333333',
            gridColor: darkMode ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.1)',
            borderColor: '#4CAF50'
        };
    }

    initCharts() {
        const darkMode = document.documentElement.getAttribute('data-theme') === 'dark';
        const theme = this.getChartTheme(darkMode);

        // Progress Chart
        const progressCtx = document.getElementById('progressChart')?.getContext('2d');
        if (progressCtx) {
            this.charts.progress = new Chart(progressCtx, {
                type: 'radar',
                data: {
                    labels: Object.keys(progressData),
                    datasets: [{
                        data: Object.values(progressData),
                        backgroundColor: 'rgba(76,175,80,0.2)',
                        borderColor: theme.borderColor
                    }]
                },
                options: {
                    scales: {
                        r: {
                            grid: { color: theme.gridColor },
                            pointLabels: { color: theme.color }
                        }
                    },
                    plugins: { legend: { display: false } }
                }
            });
        }

        // Weight Chart
        const weightCtx = document.getElementById('weightChart')?.getContext('2d');
        if (weightCtx) {
            this.charts.weight = new Chart(weightCtx, {
                type: 'line',
                data: {
                    labels: weightData.labels,
                    datasets: [{
                        data: weightData.data,
                        borderColor: theme.borderColor,
                        tension: 0.1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            grid: { color: theme.gridColor },
                            ticks: { color: theme.color }
                        },
                        x: {
                            grid: { color: theme.gridColor },
                            ticks: { color: theme.color }
                        }
                    },
                    plugins: { legend: { display: false } }
                }
            });
        }
    }

    updateChartThemes(darkMode) {
        const theme = this.getChartTheme(darkMode);
        
        Object.values(this.charts).forEach(chart => {
            if (!chart) return;
            
            if (chart.config.type === 'radar') {
                chart.options.scales.r.grid.color = theme.gridColor;
                chart.options.scales.r.pointLabels.color = theme.color;
            } else {
                chart.options.scales.x.grid.color = theme.gridColor;
                chart.options.scales.y.grid.color = theme.gridColor;
                chart.options.scales.x.ticks.color = theme.color;
                chart.options.scales.y.ticks.color = theme.color;
            }
            chart.update();
        });
    }

    initThemeListener() {
        document.addEventListener('themeChanged', (e) => {
            this.updateChartThemes(e.detail.darkMode);
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new ChartManager();
});