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

    // Couleur d'une classe selon son état (vert = en cours, rouge = ma classe, bleu = à venir).
    // Donnée directement à FullCalendar : le thème Velzon écrase sinon la couleur par du bleu.
    function couleurs(classNames) {
        let couleur = '#1c7fc4';
        if (classNames.indexOf('nv-cal-en-cours') !== -1) { couleur = '#0ab39c'; }
        if (classNames.indexOf('nv-cal-inscrit') !== -1) { couleur = '#d8332b'; }
        return { backgroundColor: couleur, borderColor: couleur };
    }

    // Transforme une classe en éléments de calendrier
    function eclater(classe) {
        const premierJour = classe.start;
        const dernierJour = veille(classe.end);
        const couleur = classe.classNames || [];
        // Titre court dans la case (le nom du cours) ; le détail complet est dans l'infobulle
        const nomCours = classe.title.split(' · ')[0];
        const commun = {
            backgroundColor: couleurs(couleur).backgroundColor,
            borderColor: couleurs(couleur).borderColor,
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

    // Échappe le texte avant de l'insérer dans le HTML d'une barre
    function echapper(texte) {
        const div = document.createElement('div');
        div.textContent = texte;
        return div.innerHTML;
    }

    // Mode simple : deux barres par classe, sans période teintée.
    //  - « Début » : barre pleine le premier jour ;
    //  - « Fin »   : barre blanche, texte coloré, le dernier jour (même couleur que le début).
    // Chaque barre a deux lignes : « Début · cours » puis le nom de la classe (c'est lui qui
    // distingue plusieurs classes d'un même cours, ex. « TEST 2 »). Dates et lieu : infobulle.
    function simplifier(classe) {
        const morceaux = classe.title.split(' · ');
        // « Microsoft » est retiré pour que « Word », « Excel »… tiennent dans la case
        const nomCours = morceaux[0].replace(/^Microsoft\s+/i, '');

        // Nom de la classe sans le nom du cours répété au début (« Word - Session… » -> « Session… »)
        let nomClasse = morceaux.slice(1).join(' · ');
        const brut = morceaux[0];
        if (nomClasse.toLowerCase().indexOf(brut.toLowerCase()) === 0) {
            const reste = nomClasse.slice(brut.length).replace(/^[\s\-–—:]+/, '');
            if (reste) { nomClasse = reste; }
        }

        const couleur = couleurs(classe.classNames || []).backgroundColor;
        const premierJour = classe.start;
        const dernierJour = veille(classe.end);
        const commun = {
            allDay: true,
            display: 'block',   // barre pleine : le thème colorerait sinon tout en bleu
            url: classe.url,
        };
        const props = function (libelle) {
            return Object.assign({}, classe.extendedProps, { ligne1: libelle + ' · ' + nomCours, ligne2: nomClasse });
        };

        const memeJour = premierJour === dernierJour;
        const debut = Object.assign({}, commun, {
            title: nomCours + ' · ' + nomClasse,
            start: premierJour,
            backgroundColor: couleur,
            borderColor: couleur,
            classNames: (classe.classNames || []).concat('nv-cal-debut'),
            extendedProps: props(memeJour ? 'Début et fin' : 'Début'),
        });

        if (memeJour) {
            return [debut];
        }

        const fin = Object.assign({}, commun, {
            title: nomCours + ' · ' + nomClasse,
            start: dernierJour,
            backgroundColor: '#ffffff',
            borderColor: couleur,
            textColor: couleur,
            classNames: (classe.classNames || []).concat('nv-cal-fin'),
            extendedProps: props('Fin'),
        });

        return [debut, fin];
    }

    document.querySelectorAll('.js-calendrier-classes').forEach(function (element) {
        const classes = JSON.parse(element.dataset.evenements || '[]');
        const simple = element.dataset.simple === '1';
        const evenements = classes.reduce(function (liste, classe) {
            return liste.concat(simple ? simplifier(classe) : eclater(classe));
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
            dayMaxEvents: simple ? 2 : 3,   // Accueil : deux repères par jour, puis un lien « +N ».
            headerToolbar: { left: 'prev,next', center: 'title', right: 'today' },
            buttonText: { today: "Aujourd'hui" },
            moreLinkText: function (nombre) { return '+' + nombre; },
            moreLinkHint: function (nombre) {
                return nombre === 1
                    ? 'Afficher le repère supplémentaire'
                    : 'Afficher les ' + nombre + ' repères supplémentaires';
            },
            noEventsText: 'Aucune classe ce mois-ci',
            allDayText: 'Journée',
            events: evenements,

            // Rotation du téléphone / redimensionnement : on adapte la vue
            windowResize: function () {
                if (calendrier.view.type !== vueAdaptee()) {
                    calendrier.changeView(vueAdaptee());
                }
            },

            // Mode simple : barre à deux lignes (« Début · Word » puis le nom de la classe)
            eventContent: simple ? function (info) {
                const p = info.event.extendedProps;
                return { html: '<div class="px-1 text-truncate fw-semibold">' + echapper(p.ligne1 || '') + '</div>'
                    + '<div class="px-1 text-truncate fs-12">' + echapper(p.ligne2 || '') + '</div>' };
            } : undefined,

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