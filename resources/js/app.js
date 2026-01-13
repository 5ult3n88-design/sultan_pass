import './bootstrap';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

// Expose Chart globally so inline Blade scripts can use it
// (e.g., the examinee performance dashboard)
// eslint-disable-next-line no-undef
window.Chart = Chart;

const themeStorageKey = 'pass-theme';
const root = document.documentElement;

const updateToggleUI = (theme) => {
    const label = theme === 'dark' ? 'Normal mode' : 'Dark mode';
    const icon = theme === 'dark' ? '🌙' : '☀️';

    document.querySelectorAll('[data-theme-toggle-label]').forEach((el) => (el.textContent = label));
    document.querySelectorAll('[data-theme-toggle-icon]').forEach((el) => (el.textContent = icon));
};

const applyTheme = (theme) => {
    const normalized = theme === 'light' ? 'light' : 'dark';
    root.dataset.theme = normalized;
    updateToggleUI(normalized);
};

const preferredTheme = () => {
    const stored = localStorage.getItem(themeStorageKey);
    if (stored === 'light' || stored === 'dark') {
        return stored;
    }

    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
};

// Apply theme immediately (before DOM is ready) to prevent flash
applyTheme(preferredTheme());

const initTheme = () => {
    // Ensure theme is applied
    applyTheme(preferredTheme());

    // Listen for system theme changes (only if user hasn't set a preference)
    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
    const handleSystemThemeChange = (event) => {
        if (!localStorage.getItem(themeStorageKey)) {
            applyTheme(event.matches ? 'dark' : 'light');
            setTimeout(initCharts, 100);
        }
    };
    
    // Modern browsers
    if (mediaQuery.addEventListener) {
        mediaQuery.addEventListener('change', handleSystemThemeChange);
    } else {
        // Fallback for older browsers
        mediaQuery.addListener(handleSystemThemeChange);
    }

    // Set up toggle buttons using event delegation to avoid duplicate listeners
    document.body.addEventListener('click', (e) => {
        const toggleButton = e.target.closest('[data-theme-toggle]');
        if (toggleButton) {
            e.preventDefault();
            const currentTheme = root.dataset.theme || 'dark';
            const nextTheme = currentTheme === 'dark' ? 'light' : 'dark';
            localStorage.setItem(themeStorageKey, nextTheme);
            applyTheme(nextTheme);
            setTimeout(initCharts, 100);
        }
    });
};

// Initialize fully when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTheme);
} else {
    initTheme();
}

// Chart instances storage
let chartInstances = {};

// Initialize charts
const initCharts = () => {
    const isDark = root.dataset.theme === 'dark';
    const textColor = isDark ? '#C6C6C6' : '#414042';
    const gridColor = isDark ? 'rgba(182, 138, 53, 0.1)' : 'rgba(182, 138, 53, 0.1)';
    const goldColors = ['#B68A35', '#A67A2A', '#8F6A24', '#785A1F', '#614A19'];
    
    // Pie Chart - User Roles Distribution
    const pieCtx = document.getElementById('userRolesChart');
    if (pieCtx) {
        if (chartInstances.userRolesChart) {
            chartInstances.userRolesChart.destroy();
        }
        const pieData = JSON.parse(pieCtx.dataset.chartData || '{"labels":[],"values":[]}');
        chartInstances.userRolesChart = new Chart(pieCtx, {
            type: 'pie',
            data: {
                labels: pieData.labels.length > 0 ? pieData.labels : ['No data'],
                datasets: [{
                    data: pieData.values.length > 0 ? pieData.values : [1],
                    backgroundColor: pieData.values.length > 0 ? goldColors : ['#C6C6C6'],
                    borderColor: isDark ? '#414042' : '#FFFFFF',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: textColor,
                            padding: 15,
                            font: { size: 12 }
                        }
                    },
                    tooltip: {
                        backgroundColor: isDark ? '#414042' : '#FFFFFF',
                        titleColor: textColor,
                        bodyColor: textColor,
                        borderColor: '#B68A35',
                        borderWidth: 1
                    }
                }
            }
        });
    }
    
    // Bar Chart - Assessments by Status
    const barCtx = document.getElementById('assessmentsBarChart');
    if (barCtx) {
        if (chartInstances.assessmentsBarChart) {
            chartInstances.assessmentsBarChart.destroy();
        }
        const barData = JSON.parse(barCtx.dataset.chartData || '{"labels":[],"values":[]}');
        chartInstances.assessmentsBarChart = new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: barData.labels.length > 0 ? barData.labels : ['No data'],
                datasets: [{
                    label: 'Assessments',
                    data: barData.values.length > 0 ? barData.values : [0],
                    backgroundColor: barData.values.length > 0 ? '#B68A35' : '#C6C6C6',
                    borderColor: '#A67A2A',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: isDark ? '#414042' : '#FFFFFF',
                        titleColor: textColor,
                        bodyColor: textColor,
                        borderColor: '#B68A35',
                        borderWidth: 1
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: textColor,
                            stepSize: 1
                        },
                        grid: {
                            color: gridColor
                        }
                    },
                    x: {
                        ticks: {
                            color: textColor
                        },
                        grid: {
                            color: gridColor
                        }
                    }
                }
            }
        });
    }
};

// Initialize charts when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCharts);
} else {
    initCharts();
}
