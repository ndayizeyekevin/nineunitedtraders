(() => {
    const dataElement = document.getElementById('admin-dashboard-chart-data');
    const trendCanvas = document.getElementById('request-trend-chart');
    const statusCanvas = document.getElementById('request-status-chart');
    const statusCenter = document.querySelector('.admin-chart-center');

    if (!(dataElement instanceof HTMLScriptElement)
        || !(trendCanvas instanceof HTMLCanvasElement)
        || !(statusCanvas instanceof HTMLCanvasElement)) {
        return;
    }

    if (!window.Chart) {
        document.querySelectorAll('.admin-chart-unavailable').forEach((message) => {
            message.hidden = false;
        });
        trendCanvas.hidden = true;
        statusCanvas.hidden = true;
        if (statusCenter) {
            statusCenter.hidden = true;
        }
        return;
    }

    const data = JSON.parse(dataElement.textContent);
    const chartColors = ['#3d9b73', '#e3a34d', '#4c88c5', '#9ca7b3', '#c4ccd5'];
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const isDarkTheme = () => document.documentElement.dataset.adminTheme === 'dark';
    const chartTextColor = () => isDarkTheme() ? '#aab8c8' : '#8793a0';
    const chartGridColor = () => isDarkTheme() ? '#35465a' : '#edf1f5';
    const chartSurfaceColor = () => isDarkTheme() ? '#1b2939' : '#fff';

    window.Chart.defaults.color = chartTextColor();
    window.Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;

    const trendContext = trendCanvas.getContext('2d');
    if (!trendContext) {
        throw new Error('Unable to initialize the quote request trend chart.');
    }
    const trendFill = trendContext.createLinearGradient(0, 0, 0, 300);
    trendFill.addColorStop(0, 'rgba(39, 120, 184, 0.2)');
    trendFill.addColorStop(1, 'rgba(39, 120, 184, 0.01)');

    new window.Chart(trendContext, {
        type: 'line',
        data: {
            labels: data.trendLabels,
            datasets: [{
                label: 'Requests',
                data: data.trendValues,
                borderColor: '#2778b8',
                backgroundColor: trendFill,
                borderWidth: 2.5,
                fill: true,
                tension: 0.38,
                pointRadius: 0,
                pointHoverRadius: 5,
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: '#2778b8',
                pointHoverBorderWidth: 2,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: prefersReducedMotion ? 0 : 650 },
            interaction: { intersect: false, mode: 'index' },
            plugins: {
                legend: { display: false },
                tooltip: {
                    displayColors: false,
                    padding: 11,
                    callbacks: {
                        label: (context) => `${context.parsed.y} request${context.parsed.y === 1 ? '' : 's'}`,
                    },
                },
            },
            scales: {
                x: {
                    grid: { display: false },
                    border: { display: false },
                    ticks: {
                        autoSkip: false,
                        maxRotation: 0,
                        callback: (_, index) => index % 5 === 0 || index === data.trendLabels.length - 1
                            ? data.trendLabels[index]
                            : '',
                    },
                },
                y: {
                    beginAtZero: true,
                    border: { display: false, dash: [3, 4] },
                    grid: { color: chartGridColor(), drawTicks: false },
                    ticks: { precision: 0, maxTicksLimit: 5, padding: 10 },
                },
            },
        },
    });

    if (data.totalRequests === 0) {
        statusCanvas.hidden = true;
        const emptyRing = document.querySelector('.admin-chart-empty-ring');
        if (emptyRing) {
            emptyRing.hidden = false;
        }
    } else {
        new window.Chart(statusCanvas, {
            type: 'doughnut',
            data: {
                labels: data.statusLabels,
                datasets: [{
                    data: data.statusValues,
                    backgroundColor: chartColors,
                    borderColor: chartSurfaceColor(),
                    borderWidth: 3,
                    hoverOffset: 5,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '76%',
                animation: { duration: prefersReducedMotion ? 0 : 650 },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        padding: 11,
                        callbacks: {
                            label: (context) => `${context.label}: ${context.parsed} request${context.parsed === 1 ? '' : 's'}`,
                        },
                    },
                },
            },
        });
    }

    document.addEventListener('admin-theme-change', () => {
        window.Chart.defaults.color = chartTextColor();
        [window.Chart.getChart(trendCanvas), window.Chart.getChart(statusCanvas)].forEach((chart) => {
            if (!chart) {
                return;
            }
            if (chart.options.scales?.x) {
                chart.options.scales.x.ticks.color = chartTextColor();
            }
            if (chart.options.scales?.y) {
                chart.options.scales.y.ticks.color = chartTextColor();
                chart.options.scales.y.grid.color = chartGridColor();
            }
            const dataset = chart.data.datasets[0];
            if (chart.config.type === 'doughnut') {
                dataset.borderColor = chartSurfaceColor();
            }
            chart.update('none');
        });
    });
})();
