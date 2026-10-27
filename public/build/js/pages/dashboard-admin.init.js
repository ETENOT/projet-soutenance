document.addEventListener('DOMContentLoaded', function () {
    const inscriptionsGraph = document.querySelector('[data-nv-dash-inscriptions]');

    if (inscriptionsGraph) {
        if (typeof ApexCharts === 'undefined') {
            throw new Error('ApexCharts est requis pour afficher les inscriptions par mois.');
        }

        new ApexCharts(inscriptionsGraph, {
            chart: {
                type: 'bar',
                height: 270,
                fontFamily: 'Poppins, sans-serif',
                toolbar: { show: false },
            },
            series: [
                { name: 'Inscriptions', data: JSON.parse(inscriptionsGraph.dataset.inscriptions) },
                { name: 'Payées', data: JSON.parse(inscriptionsGraph.dataset.payees) },
            ],
            colors: ['#155b8a', '#d8332b'],
            plotOptions: {
                bar: {
                    borderRadius: 4,
                    columnWidth: '48%',
                },
            },
            dataLabels: { enabled: false },
            grid: {
                borderColor: '#e8edf4',
                strokeDashArray: 4,
            },
            legend: {
                position: 'top',
                horizontalAlign: 'right',
                fontFamily: 'Poppins, sans-serif',
            },
            xaxis: {
                categories: JSON.parse(inscriptionsGraph.dataset.labels),
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: {
                    style: { colors: '#155b8a', fontFamily: 'Poppins, sans-serif' },
                },
            },
            yaxis: {
                min: 0,
                forceNiceScale: true,
                labels: {
                    style: { colors: '#155b8a', fontFamily: 'Poppins, sans-serif' },
                },
            },
        }).render();
    }

    const paiementsGraph = document.querySelector('[data-nv-dash-paiements]');

    if (paiementsGraph) {
        if (typeof ApexCharts === 'undefined') {
            throw new Error('ApexCharts est requis pour afficher la répartition des paiements.');
        }

        const pourcentage = Number(paiementsGraph.dataset.pourcentage);

        new ApexCharts(paiementsGraph, {
            chart: {
                type: 'donut',
                height: 190,
                fontFamily: 'Poppins, sans-serif',
            },
            series: [
                Number(paiementsGraph.dataset.payees),
                Number(paiementsGraph.dataset.attente),
            ],
            labels: ['Payées', 'En attente'],
            colors: ['#155b8a', '#d8332b'],
            dataLabels: { enabled: false },
            legend: { show: false },
            stroke: { width: 0 },
            plotOptions: {
                pie: {
                    donut: {
                        size: '76%',
                        labels: {
                            show: true,
                            name: { show: false },
                            value: { show: false },
                            total: {
                                show: true,
                                showAlways: true,
                                label: 'Payées',
                                color: '#155b8a',
                                fontFamily: 'Poppins, sans-serif',
                                formatter: () => `${pourcentage}%`,
                            },
                        },
                    },
                },
            },
            tooltip: {
                y: { formatter: value => `${value} inscription(s)` },
            },
        }).render();
    }
});
