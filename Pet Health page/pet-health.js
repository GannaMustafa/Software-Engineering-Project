const cctx = document.getElementById('chronicChart');
if (cctx) {
    const data = window.PET_DATA?.chronicLogs || { labels: [], water_intake: [], insulin: [] };

    new Chart(cctx, {
        type: 'bar',
        data: {
            labels: data.labels.length ? data.labels : ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Water (ml)',
                data: data.water_intake.length ? data.water_intake : [0, 0, 0, 0, 0, 0, 0],
                backgroundColor: '#94CDD3',
                borderRadius: 6
            },
            {
                label: 'Insulin (u)',
                data: data.insulin.length ? data.insulin : [0, 0, 0, 0, 0, 0, 0],
                backgroundColor: '#9BC870',
                borderRadius: 6,
                yAxisID: 'y2'
            }]
        },
        options: {
            plugins: { legend: { position: 'bottom' } },
            scales: {
                y: { grid: { color: '#eef1ef' }, beginAtZero: true },
                y2: { position: 'right', grid: { display: false }, min: 0, max: 8, beginAtZero: true },
                x: { grid: { display: false } }
            }
        }
    });
}

function initRealWeightChart(data, alert) {
    const ctx = document.getElementById('weightChart');
    if (!ctx) return;

    if (window.weightChartInstance) {
        window.weightChartInstance.destroy();
    }

    const labels = data.map(d => {
        const date = new Date(d.date);
        return date.toLocaleDateString('en-US', { month: 'short' });
    });
    const weights = data.map(d => d.weight);
    const bcsValues = data.map(d => d.bcs).filter(v => v !== null);

    window.weightChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Weight (kg)',
                    data: weights,
                    borderColor: '#6BB5A8',
                    backgroundColor: 'rgba(107,181,168,0.18)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 3,
                    pointRadius: 4,
                    pointBackgroundColor: '#6BB5A8'
                },
                ...(bcsValues.length > 0 ? [{
                    label: 'BCS (1-9)',
                    data: data.map(d => d.bcs),
                    borderColor: '#9BC870',
                    borderDash: [6, 4],
                    tension: 0.4,
                    borderWidth: 2,
                    pointRadius: 3,
                    yAxisID: 'y2',
                    hidden: false
                }] : [])
            ]
        },
        options: {
            plugins: {
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            let label = context.dataset.label || '';
                            if (label) label += ': ';
                            if (context.parsed.y !== null) {
                                label += context.parsed.y + (context.datasetIndex === 0 ? ' kg' : '/9');
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: { grid: { color: '#eef1ef' }, beginAtZero: false, title: { display: true, text: 'Weight (kg)' } },
                y2: { position: 'right', min: 1, max: 9, grid: { display: false }, title: { display: true, text: 'BCS Score' } },
                x: { grid: { display: false } }
            }
        }
    })
};
document.addEventListener('DOMContentLoaded', () => {
    const petData = window.PET_DATA || { weightLogs: [], alert: null };

    if (petData.weightLogs?.length > 0) {
        initRealWeightChart(petData.weightLogs, petData.alert);
    }
});