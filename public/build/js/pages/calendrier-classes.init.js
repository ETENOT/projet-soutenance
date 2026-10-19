// Initialise les petits calendriers de classes (.js-calendrier-classes).
// Chaque classe reçue (voir Classe::versEvenementCalendrier) devient TROIS éléments lisibles :
//  - un point « Début » le premier jour,
//  - un point « Fin » le dernier jour,
//  - une période teintée en couleur claire entre les deux (pas de longue barre).
document.addEventListener('DOMContentLoaded', function () {

    // Jour précédent d'une date AAAA-MM-JJ (la fin FullCalendar d'une classe est exclusive)
    function veille(date) {
        const jour = new Date(date + 'T00:00:00');
        jour.setDate(jour.getDate() - 1);
        const mois = String(jour.getMonth() + 1).padStart(2, '0');
        const numero = String(jour.getDate()).padStart(2, '0');
        return jour.getFullYear() + '-' + mois + '-' + numero;
    }

    // Transforme une classe en éléments de calendrier
    function eclater(classe) {
        const premierJour = classe.start;
        const dernierJour = veille(classe.end);
        const couleur = classe.classNames || [];
        // Titre court dans la case (le nom du cours) ; le détail complet est dans l'infobulle
        const nomCours = classe.title.split(' · ')[0];
        const commun = {
            allDay: true,
            display: 'list-item',
            url: classe.url,
            extendedProps: classe.extendedProps,
        };

        // Période entre le début et la fin, en teinte claire
        const elements = [{
            start: premierJour,
            end: classe.end,
            display: 'background',
            classNames: couleur,
        }];

        if (premierJour === dernierJour) {
            // Classe d'un seul jour
            elements.push(Object.assign({}, commun, {
                title: 'Début et fin · ' + nomCours,
                start: premierJour,
                classNames: couleur.concat('nv-cal-debut'),
            }));
        } else {
            elements.push(Object.assign({}, commun, {
                title: 'Début · ' + nomCours,
                start: premierJour,
                classNames: couleur.concat('nv-cal-debut'),
            }));
            elements.push(Object.assign({}, commun, {
                title: 'Fin · ' + nomCours,
                start: dernierJour,
                classNames: couleur.concat('nv-cal-fin'),
            }));
        }

        return elements;
    }

    document.querySelectorAll('.js-calendrier-classes').forEach(function (element) {
        const classes = JSON.parse(element.dataset.evenements || '[]');
        const evenements = classes.reduce(function (liste, classe) {
            return liste.concat(eclater(classe));
        }, []);

        // Petit écran : les cases du mois sont trop étroites pour lire les libellés -> vue liste
        const vueAdaptee = function () {
            return window.innerWidth < 576 ? 'listMonth' : 'dayGridMonth';
        };

        const calendrier = new FullCalendar.Calendar(element, {
            initialView: vueAdaptee(),
            locale: 'fr',                 // mois et jours en français (via Intl du navigateur)
            firstDay: 1,                  // la semaine commence le lundi
            height: 'auto',
            fixedWeekCount: false,
            dayMaxEvents: 3,              // "+2" au-delà de 3 éléments le même jour
            headerToolbar: { left: 'prev,next', center: 'title', right: 'today' },
            buttonText: { today: "Aujourd'hui" },
            moreLinkText: function (nombre) { return '+' + nombre; },
            noEventsText: 'Aucune classe ce mois-ci',
            allDayText: 'Journée',
            events: evenements,

            // Rotation du téléphone / redimensionnement : on adapte la vue
            windowResize: function () {
                if (calendrier.view.type !== vueAdaptee()) {
                    calendrier.changeView(vueAdaptee());
                }
            },

            // Détail de la classe au survol (cours, dates, lieu)
            eventDidMount: function (info) {
                if (info.event.extendedProps.infobulle) {
                    info.el.title = info.event.extendedProps.infobulle;
                }
            },
        });

        calendrier.render();
    });
});