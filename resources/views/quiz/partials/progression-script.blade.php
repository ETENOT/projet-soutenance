{{-- Script du graphique de progression (voir quiz/partials/progression.blade.php) --}}
<script src="{{ URL::asset('build/libs/apexcharts/apexcharts.min.js') }}"></script>
<script>
(function () {
    // Affiche le graphique dans chaque conteneur .js-progression-chart.
    // Un conteneur caché (onglet Quiz non ouvert) a une largeur de 0 : on attend
    // alors l'événement Bootstrap "shown.bs.tab" pour le dessiner à la bonne taille.
    function dessiner(el) {
        if (el.dataset.ready === '1' || el.offsetWidth === 0 || typeof ApexCharts === 'undefined') {
            return;
        }

        var points = [];
        try {
            points = JSON.parse(el.dataset.points || '[]');
        } catch (e) {
            return;
        }
        if (points.length < 2) {
            return;
        }

        el.dataset.ready = '1';

        new ApexCharts(el, {
            chart: {
                type: 'area',
                height: 300,
                fontFamily: 'inherit',
                toolbar: { show: false },
                zoom: { enabled: false }
            },
            series: [{
                name: 'Score',
                data: points.map(function (p) { return p.pourcentage; })
            }],
            colors: ['#405189'],
            stroke: { curve: 'smooth', width: 3 },
            fill: {
                type: 'gradient',
                gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.02, stops: [0, 95, 100] }
            },
            markers: { size: 5, strokeWidth: 2, hover: { size: 7 } },
            dataLabels: {
                enabled: points.length <= 12,
                formatter: function (v) { return Math.round(v) + ' %'; },
                offsetY: -6,
                background: { enabled: false },
                style: { fontSize: '11px' }
            },
            xaxis: {
                categories: points.map(function (p) { return '#' + p.tentative; }),
                title: { text: 'Tentatives (de la plus ancienne à la plus récente)' },
                tooltip: { enabled: false }
            },
            yaxis: {
                min: 0,
                max: 100,
                tickAmount: 5,
                title: { text: 'Score (%)' },
                labels: { formatter: function (v) { return Math.round(v) + ' %'; } }
            },
            annotations: {
                yaxis: [{
                    y: 50,
                    borderColor: '#f7b84b',
                    strokeDashArray: 4,
                    label: {
                        text: 'Moyenne (50 %)',
                        position: 'left',
                        textAnchor: 'start',
                        style: { color: '#fff', background: '#f7b84b', fontSize: '11px' }
                    }
                }]
            },
            grid: { borderColor: 'rgba(128,128,128,0.2)', strokeDashArray: 3 },
            tooltip: {
                custom: function (opts) {
                    var p = points[opts.dataPointIndex];
                    return '<div class="px-3 py-2">' +
                        '<div class="fw-semibold mb-1">Tentative ' + p.tentative + '</div>' +
                        '<div class="text-muted fs-12 mb-1">' + p.date + ' à ' + p.heure + '</div>' +
                        '<div>' + p.score + ' / ' + p.bareme + ' (' + p.pourcentage + ' %)</div>' +
                        '</div>';
                }
            }
        }).render();
    }

    function toutDessiner() {
        document.querySelectorAll('.js-progression-chart').forEach(dessiner);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', toutDessiner);
    } else {
        toutDessiner();
    }
    // Les événements Bootstrap remontent jusqu'au document : on redessine à l'ouverture d'un onglet
    document.addEventListener('shown.bs.tab', toutDessiner);
})();
</script>