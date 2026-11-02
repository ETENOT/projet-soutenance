{{--
    Fenêtre « Gérer la classe » de la liste des classes (admin) : deux onglets
      - Inscrits : jauge des places, liste, paiement, retrait, ajout d'utilisateurs (liste à cocher + sélection) ;
      - Jours non comptabilisés : jours exclus du taux de présence.
    À inclure dans la page, après la liste : @include('classes.gestion')
    Variable utilisée : $classes (les classes de la page, avec inscriptions.user, inscriptions.paiement et joursExclus).
--}}

{{-- Modèles cachés : un par classe (un <template> n'est pas dans la page, donc aucun identifiant en double) --}}
@foreach ($classes as $classe)
    @continue(! $classe->cours)
    @php
        $ferme = $classe->estFermee();
        $nbInscrits = $classe->inscriptions->count();
        $placesRestantes = max(0, $classe->capacite_max - $nbInscrits);
        $pourcentage = $classe->capacite_max > 0 ? min(100, (int) round($nbInscrits / $classe->capacite_max * 100)) : 0;
        // Jours déjà exclus ; si la validation vient d'échouer pour CETTE classe, on remet ce qui avait été saisi
        $joursActuels = (string) old('_classe') === (string) $classe->id
            ? collect(old('jours_exclus', []))->filter()->unique()->sort()->values()->all()
            : $classe->datesExclues()->all();
    @endphp
    <template id="gestion-{{ $classe->id }}">
        <div class="gestion-racine"
             data-url-recherche="{{ route('admin.cours.classes.candidats', [$classe->cours, $classe]) }}"
             data-restantes="{{ $placesRestantes }}"
             data-min="{{ $classe->date_debut->toDateString() }}"
             data-max="{{ $classe->date_fin->toDateString() }}"
             data-jours='@json($joursActuels)'
             data-ferme="{{ $ferme ? 1 : 0 }}">

            {{-- Confirmation (retrait, paiement) : remplace la vue principale, pas de fenêtre par-dessus --}}
            <div class="js-confirmation d-none">
                <p class="mb-3 js-confirmation-message"></p>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-light js-confirmation-annuler">Annuler</button>
                    <button type="button" class="btn btn-danger js-confirmation-valider">Confirmer</button>
                </div>
            </div>

            <div class="js-vue-principale">
                <ul class="nav nav-tabs mb-3" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button type="button" class="nav-link active" data-bs-toggle="tab"
                                data-bs-target="#gestion-inscrits-{{ $classe->id }}" role="tab" id="onglet-inscrits-{{ $classe->id }}">
                            Inscrits <span class="badge bg-primary-subtle text-primary ms-1">{{ $nbInscrits }}/{{ $classe->capacite_max }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button type="button" class="nav-link" data-bs-toggle="tab"
                                data-bs-target="#gestion-jours-{{ $classe->id }}" role="tab" id="onglet-jours-{{ $classe->id }}">
                            Jours non comptabilisés
                            @if (count($joursActuels))
                                <span class="badge bg-warning-subtle text-warning ms-1">{{ count($joursActuels) }}</span>
                            @endif
                        </button>
                    </li>
                </ul>

                @if ($ferme)
                    <div class="alert alert-secondary py-2">
                        <i class="ri-lock-line me-1"></i>Classe fermée : les inscrits et les jours ne sont plus modifiables.
                    </div>
                @endif

                <div class="tab-content">
                    {{-- ===== Inscrits ===== --}}
                    <div class="tab-pane fade show active" id="gestion-inscrits-{{ $classe->id }}" role="tabpanel">

                        {{-- Jauge des places --}}
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-semibold">Places</span>
                            <span class="text-muted fs-13">
                                <strong class="text-body">{{ $nbInscrits }}</strong> / {{ $classe->capacite_max }} occupée(s)
                                @if ($placesRestantes === 0)
                                    <span class="badge bg-danger-subtle text-danger ms-1">Complet</span>
                                @else
                                    <span class="badge bg-success-subtle text-success ms-1">{{ $placesRestantes }} restante(s)</span>
                                @endif
                            </span>
                        </div>
                        <div class="progress mb-3" style="height: 8px;">
                            <div class="progress-bar {{ $placesRestantes === 0 ? 'bg-danger' : 'bg-success' }}" role="progressbar"
                                 style="width: {{ $pourcentage }}%;" aria-valuenow="{{ $pourcentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>

                        {{-- Tableau des inscrits : hauteur limitée, il défile dans son cadre --}}
                        <div class="border rounded overflow-hidden">
                            <div class="table-responsive" style="max-height: 240px; overflow-y: auto;">
                                <table class="table table-sm table-hover align-middle mb-0 table-inscrits table-liste">
                                    <thead class="table-light" style="position: sticky; top: 0; z-index: 1;">
                                        <tr>
                                            <th>Inscrit</th>
                                            <th>Paiement</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($classe->inscriptions as $inscription)
                                            @php $nomInscrit = $inscription->user?->name ?? 'Compte supprimé'; @endphp
                                            <tr>
                                                <td title="{{ $nomInscrit }} — {{ $inscription->user?->email }}">
                                                    <span class="fw-medium">{{ $nomInscrit }}</span><br>
                                                    <span class="text-muted fs-12">{{ $inscription->user?->email ?? '—' }}</span>
                                                </td>
                                                <td>
                                                    @if ($inscription->paiement)
                                                        <span class="badge bg-success-subtle text-success">Payé</span>
                                                        <div class="text-muted fs-12 texte-coupe" title="{{ $inscription->paiement->libelle_mode }}">
                                                            {{ number_format($inscription->paiement->montant, 0, ',', ' ') }} fcfa · {{ $inscription->paiement->libelle_mode }}
                                                        </div>
                                                    @else
                                                        <span class="badge bg-warning-subtle text-warning">Impayé</span>
                                                    @endif
                                                </td>
                                                <td class="text-end inscrit-actions">
                                                    <div class="d-flex justify-content-end gap-1">
                                                        @if ($inscription->paiement)
                                                            <a href="{{ route('paiements.recu', $inscription->paiement) }}" target="_blank"
                                                               class="btn btn-sm btn-soft-primary" title="Voir le reçu">
                                                                <i class="ri-file-text-line"></i>
                                                            </a>
                                                        @elseif (! $ferme)
                                                            <form action="{{ route('paiements.store', $inscription) }}" method="POST" class="m-0"
                                                                  data-confirmation="Enregistrer le paiement de {{ $nomInscrit }} ?"
                                                                  data-confirmation-bouton="Enregistrer le paiement"
                                                                  data-confirmation-style="success">
                                                                @csrf
                                                                <button type="submit" class="btn btn-sm btn-soft-success" title="Enregistrer le paiement">
                                                                    <i class="ri-bank-card-line"></i>
                                                                </button>
                                                            </form>
                                                        @endif

                                                        @if (! $ferme && $inscription->user)
                                                            <form action="{{ route('admin.cours.classes.inscrits.destroy', [$classe->cours, $classe, $inscription->user]) }}" method="POST" class="m-0"
                                                                  data-confirmation="{{ $inscription->paiement ? 'Retirer ' . $nomInscrit . ' de la classe ? Son inscription est payée : le paiement enregistré sera supprimé avec elle.' : 'Retirer ' . $nomInscrit . ' de la classe ?' }}"
                                                                  data-confirmation-bouton="Retirer">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-soft-danger" title="Retirer de la classe">
                                                                    <i class="ri-user-unfollow-line"></i>
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="3" class="text-center text-muted py-4">Aucun inscrit pour l'instant.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- ===== Ajout : liste à gauche, sélection à droite ===== --}}
                        @unless ($ferme)
                            <form action="{{ route('admin.cours.classes.inscrits.store', [$classe->cours, $classe]) }}" method="POST" class="js-form-ajout mt-3">
                                @csrf
                                <div class="card border shadow-sm mb-0">
                                    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                                        <h6 class="card-title mb-0 fw-semibold">
                                            <i class="ri-user-add-line text-primary me-1"></i>Ajouter des utilisateurs
                                        </h6>
                                        <span class="text-muted fs-12">Inscrits avec le paiement enregistré au comptoir</span>
                                    </div>

                                    <div class="card-body">
                                        @if ($placesRestantes === 0)
                                            <p class="text-danger mb-0">
                                                <i class="ri-error-warning-line me-1"></i>La classe est complète : retirez un inscrit pour libérer une place.
                                            </p>
                                        @else
                                            <div class="row g-3">
                                                {{-- Utilisateurs disponibles --}}
                                                <div class="col-lg-7">
                                                    <div class="input-group mb-2">
                                                        <span class="input-group-text bg-transparent"><i class="ri-search-line text-primary"></i></span>
                                                        <input type="text" class="form-control js-recherche" autocomplete="off"
                                                               placeholder="Filtrer par nom ou e-mail…" aria-label="Filtrer les utilisateurs">
                                                    </div>

                                                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                                                        <div class="form-check mb-0">
                                                            <input type="checkbox" class="form-check-input js-tout-cocher" id="toutCocher-{{ $classe->id }}">
                                                            <label class="form-check-label" for="toutCocher-{{ $classe->id }}">Tout cocher (liste affichée)</label>
                                                        </div>
                                                        <span class="text-muted fs-13"><span class="js-affiches">0</span> affiché(s)</span>
                                                    </div>

                                                    <div class="border rounded js-defilement" style="height: 260px; overflow-y: auto;">
                                                        <div class="list-group list-group-flush js-liste"></div>
                                                        <p class="text-muted text-center py-4 mb-0 js-message">Chargement…</p>
                                                    </div>
                                                </div>

                                                {{-- Sélection --}}
                                                <div class="col-lg-5">
                                                    <div class="d-flex justify-content-between align-items-center mb-2" style="min-height: 38px;">
                                                        <h6 class="mb-0 fw-semibold">Sélection</h6>
                                                        <span class="badge bg-primary-subtle text-primary fs-12">
                                                            <span class="js-compteur">0</span> / {{ $placesRestantes }} place(s)
                                                        </span>
                                                    </div>
                                                    {{-- Même alignement que « Tout cocher » à gauche --}}
                                                    <div class="mb-2 text-muted fs-13" style="min-height: 24px;">Les personnes cochées apparaissent ici.</div>

                                                    <div class="border rounded bg-light-subtle" style="height: 260px; overflow-y: auto;">
                                                        <ul class="list-group list-group-flush js-selection mb-0"></ul>
                                                        <p class="text-muted text-center py-4 mb-0 js-selection-vide">Aucun utilisateur sélectionné.</p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="js-champs"></div>
                                            <div class="d-flex justify-content-end mt-3">
                                                <button type="submit" class="btn btn-primary js-bouton-ajout" disabled>
                                                    <i class="ri-user-add-line me-1"></i><span class="js-bouton-texte">Ajouter à la classe</span>
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </form>
                        @endunless
                    </div>

                    {{-- ===== Jours non comptabilisés ===== --}}
                    <div class="tab-pane fade" id="gestion-jours-{{ $classe->id }}" role="tabpanel">
                        <form action="{{ route('admin.cours.classes.jours-exclus', [$classe->cours, $classe]) }}" method="POST" class="js-form-jours" data-ferme="{{ $ferme ? 1 : 0 }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="_classe" value="{{ $classe->id }}">

                            <p class="text-muted fs-13">
                                Jour férié, séance annulée, formateur absent… Ces jours ne comptent ni comme présence ni comme absence.
                                Dimanches exclus d'office ; la période va du {{ $classe->date_debut->format('d/m/Y') }} au {{ $classe->date_fin->format('d/m/Y') }}.
                            </p>

                            @unless ($ferme)
                                <div class="input-group mb-2">
                                    <input type="date" class="form-control js-jour-saisie"
                                           min="{{ $classe->date_debut->toDateString() }}" max="{{ $classe->date_fin->toDateString() }}">
                                    <button type="button" class="btn btn-outline-primary js-jour-ajouter">
                                        <i class="ri-add-line me-1"></i>Ajouter ce jour
                                    </button>
                                </div>
                            @endunless

                            <div class="d-flex flex-wrap gap-2 js-jours-liste"></div>
                            <div class="text-danger fs-12 mt-1 js-jours-message"></div>
                            <p class="text-muted fs-13 js-jours-vide d-none mb-0">Aucun jour exclu : tous les jours de formation comptent.</p>

                            @unless ($ferme)
                                <button type="submit" class="btn btn-primary mt-3">
                                    <i class="ri-save-line me-1"></i>Enregistrer les jours
                                </button>
                            @endunless
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </template>
@endforeach

{{-- ===== Fenêtre « Gérer la classe » ===== --}}
<div class="modal fade" id="modalGestion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div class="min-w-0 flex-grow-1 me-3">
                    <div class="small text-primary fw-semibold">Gérer la classe</div>
                    {{-- Titre sur une seule ligne : un nom très long est coupé avec « … » (texte complet au survol) --}}
                    <h5 class="modal-title fw-bold mb-0 text-truncate" id="gestionTitre"></h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body" id="gestionCorps"></div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const fenetre = document.getElementById('modalGestion');
    const corps = document.getElementById('gestionCorps');

    // Crée un élément avec ses classes et son texte (le texte est inséré tel quel : pas de HTML injecté)
    function el(balise, classes, texte) {
        const e = document.createElement(balise);
        if (classes) e.className = classes;
        if (texte !== undefined) e.textContent = texte;
        return e;
    }

    // ------------------------------------------------------------------
    // Confirmation à la place de la vue principale (retrait d'un inscrit, paiement)
    // ------------------------------------------------------------------
    function initialiserConfirmation(racine) {
        const vue = racine.querySelector('.js-vue-principale');
        const panneau = racine.querySelector('.js-confirmation');
        const valider = racine.querySelector('.js-confirmation-valider');
        let formulaire = null;

        racine.querySelectorAll('form[data-confirmation]').forEach(function (f) {
            f.addEventListener('submit', function (e) {
                e.preventDefault();
                formulaire = f;
                panneau.querySelector('.js-confirmation-message').textContent = f.dataset.confirmation;
                valider.textContent = f.dataset.confirmationBouton || 'Confirmer';
                valider.className = 'btn js-confirmation-valider btn-' + (f.dataset.confirmationStyle || 'danger');
                vue.classList.add('d-none');
                panneau.classList.remove('d-none');
            });
        });

        racine.querySelector('.js-confirmation-annuler').addEventListener('click', function () {
            formulaire = null;
            panneau.classList.add('d-none');
            vue.classList.remove('d-none');
        });

        valider.addEventListener('click', function () {
            // submit() ne redéclenche pas l'événement « submit » : pas de nouvelle demande
            if (formulaire) formulaire.submit();
        });
    }

    // ------------------------------------------------------------------
    // Onglet Inscrits : liste des utilisateurs disponibles (à gauche) + sélection (à droite)
    // ------------------------------------------------------------------
    function initialiserAjout(racine) {
        const form = racine.querySelector('.js-form-ajout');
        const champRecherche = racine.querySelector('.js-recherche');
        if (!form || !champRecherche) return;   // classe fermée ou complète

        const restantes = parseInt(racine.dataset.restantes, 10);
        const liste = racine.querySelector('.js-liste');
        const message = racine.querySelector('.js-message');
        const champs = racine.querySelector('.js-champs');
        const compteur = racine.querySelector('.js-compteur');
        const affiches = racine.querySelector('.js-affiches');
        const toutCocher = racine.querySelector('.js-tout-cocher');
        const bouton = racine.querySelector('.js-bouton-ajout');
        const texteBouton = racine.querySelector('.js-bouton-texte');
        const defilement = racine.querySelector('.js-defilement');
        const listeSelection = racine.querySelector('.js-selection');
        const selectionVide = racine.querySelector('.js-selection-vide');

        let tous = [];                 // tous les utilisateurs disponibles
        let visibles = [];             // ceux qui correspondent au filtre
        const selection = new Set();   // ids cochés (conservés quand on change le filtre)

        function parId(id) {
            return tous.find(u => u.id === id);
        }

        // Panneau de droite : les personnes sélectionnées, avec un bouton pour chacune
        function dessinerSelection() {
            listeSelection.replaceChildren();
            selectionVide.classList.toggle('d-none', selection.size > 0);

            selection.forEach(function (id) {
                const u = parId(id);
                if (!u) return;

                const li = el('li', 'list-group-item d-flex align-items-center gap-2 bg-transparent');
                const texte = el('div', 'min-w-0 flex-grow-1');
                const nom = el('div', 'fw-medium texte-coupe', u.name);
                nom.title = u.name;
                const mail = el('small', 'text-muted texte-coupe d-block', u.email);
                mail.title = u.email;
                texte.append(nom, mail);

                const retirer = el('button', 'btn btn-sm btn-soft-danger flex-shrink-0');
                retirer.type = 'button';
                retirer.title = 'Retirer de la sélection';
                retirer.setAttribute('aria-label', 'Retirer ' + u.name + ' de la sélection');
                retirer.appendChild(el('i', 'ri-close-line'));
                retirer.addEventListener('click', function () {
                    selection.delete(id);
                    dessiner();   // décoche aussi la case dans la liste de gauche
                });

                li.append(texte, retirer);
                listeSelection.appendChild(li);
            });
        }

        function majResume() {
            compteur.textContent = selection.size;
            affiches.textContent = visibles.length;
            bouton.disabled = selection.size === 0;
            texteBouton.textContent = selection.size > 0
                ? 'Ajouter (' + selection.size + ') à la classe'
                : 'Ajouter à la classe';

            const cochables = visibles.filter(u => selection.has(u.id)).length;
            toutCocher.checked = visibles.length > 0 && cochables === visibles.length;
            toutCocher.indeterminate = cochables > 0 && cochables < visibles.length;

            // Champs cachés envoyés au serveur
            champs.replaceChildren();
            selection.forEach(function (id) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'user_ids[]';
                input.value = id;
                champs.appendChild(input);
            });

            dessinerSelection();
        }

        function dessiner() {
            const terme = champRecherche.value.trim().toLowerCase();
            visibles = tous.filter(u => !terme || (u.name + ' ' + u.email).toLowerCase().includes(terme));

            liste.replaceChildren();
            message.textContent = visibles.length ? ''
                : (tous.length ? 'Aucun utilisateur ne correspond.' : 'Aucun utilisateur disponible à inscrire.');
            message.classList.toggle('d-none', visibles.length > 0);

            visibles.forEach(function (u) {
                const etiquette = el('label', 'list-group-item list-group-item-action d-flex align-items-center gap-2 mb-0');
                etiquette.style.cursor = 'pointer';

                const caseU = document.createElement('input');
                caseU.type = 'checkbox';
                caseU.className = 'form-check-input mt-0 flex-shrink-0';
                caseU.checked = selection.has(u.id);
                caseU.addEventListener('change', function () {
                    if (caseU.checked) {
                        if (selection.size >= restantes) {
                            caseU.checked = false;
                            message.textContent = 'Classe pleine : seulement ' + restantes + ' place(s) disponible(s).';
                            message.classList.remove('d-none');
                            return;
                        }
                        selection.add(u.id);
                    } else {
                        selection.delete(u.id);
                    }
                    majResume();
                });

                const texte = el('span', 'min-w-0 flex-grow-1');
                const nom = el('span', 'fw-medium texte-coupe d-block', u.name);
                nom.title = u.name;
                const mail = el('small', 'text-muted texte-coupe d-block', u.email);
                mail.title = u.email;
                texte.append(nom, mail);

                etiquette.append(caseU, texte);
                liste.appendChild(etiquette);
            });

            majResume();
        }

        // Tout cocher : les utilisateurs affichés, dans la limite des places restantes
        toutCocher.addEventListener('change', function () {
            if (toutCocher.checked) {
                let refuses = 0;
                visibles.forEach(function (u) {
                    if (selection.has(u.id)) return;
                    if (selection.size < restantes) { selection.add(u.id); } else { refuses++; }
                });
                if (refuses > 0) {
                    message.textContent = 'Classe pleine : seulement ' + restantes + ' place(s) disponible(s), ' + refuses + ' utilisateur(s) non coché(s).';
                    message.classList.remove('d-none');
                }
            } else {
                visibles.forEach(u => selection.delete(u.id));
            }
            const position = defilement.scrollTop;
            dessiner();
            defilement.scrollTop = position;
        });

        // Entrée dans le champ de filtre : ne valide pas le formulaire
        champRecherche.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') e.preventDefault();
        });
        champRecherche.addEventListener('input', dessiner);

        // Une seule requête : la liste complète des utilisateurs disponibles
        fetch(racine.dataset.urlRecherche, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (r) {
                if (!r.ok) { throw new Error('http ' + r.status); }
                return r.json();
            })
            .then(function (donnees) {
                tous = donnees;
                dessiner();
            })
            .catch(function () {
                message.textContent = 'Le chargement des utilisateurs a échoué. Fermez puis rouvrez la fenêtre.';
            });
    }

    // ------------------------------------------------------------------
    // Onglet Jours non comptabilisés
    // ------------------------------------------------------------------
    function initialiserJours(racine) {
        const form = racine.querySelector('.js-form-jours');
        const liste = racine.querySelector('.js-jours-liste');
        const message = racine.querySelector('.js-jours-message');
        const vide = racine.querySelector('.js-jours-vide');
        const saisie = racine.querySelector('.js-jour-saisie');
        const verrouille = racine.dataset.ferme === '1';
        const min = racine.dataset.min;
        const max = racine.dataset.max;
        let jours = JSON.parse(racine.dataset.jours || '[]');

        // "2026-10-07" -> "mer. 07/10/2026"
        function libelle(iso) {
            const [a, m, j] = iso.split('-').map(Number);
            return new Date(a, m - 1, j).toLocaleDateString('fr-FR', {
                weekday: 'short', day: '2-digit', month: '2-digit', year: 'numeric',
            });
        }

        // Ajoute la date saisie à la liste. Retourne false si elle est refusée (message affiché).
        function ajouterSaisie() {
            message.textContent = '';
            const iso = saisie ? saisie.value : '';
            if (!iso) return true;   // rien à ajouter

            const [a, m, j] = iso.split('-').map(Number);
            if (new Date(a, m - 1, j).getDay() === 0) {
                message.textContent = "Ce jour tombe un dimanche : il n'est de toute façon pas compté.";
                return false;
            }
            if (iso < min || iso > max) {
                message.textContent = 'Ce jour est en dehors de la période de la classe.';
                return false;
            }
            if (!jours.includes(iso)) {
                jours.push(iso);
                dessiner();
            }
            saisie.value = '';
            return true;
        }

        // Redessine les badges et les champs cachés envoyés au serveur
        function dessiner() {
            jours.sort();
            liste.replaceChildren();
            vide.classList.toggle('d-none', jours.length > 0);

            jours.forEach(function (iso) {
                const badge = el('span', 'badge bg-warning-subtle text-warning fs-12 d-inline-flex align-items-center badge-fermable', libelle(iso));

                const cache = document.createElement('input');
                cache.type = 'hidden';
                cache.name = 'jours_exclus[]';
                cache.value = iso;
                badge.appendChild(cache);

                if (!verrouille) {
                    const retirer = el('button', 'btn-close ms-2');
                    retirer.type = 'button';
                    retirer.setAttribute('aria-label', 'Retirer ce jour');
                    retirer.addEventListener('click', function () {
                        jours = jours.filter(function (x) { return x !== iso; });
                        dessiner();
                    });
                    badge.appendChild(retirer);
                }
                liste.appendChild(badge);
            });
        }

        if (saisie) {
            racine.querySelector('.js-jour-ajouter').addEventListener('click', ajouterSaisie);
            saisie.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') { e.preventDefault(); ajouterSaisie(); }
            });
            // Date choisie mais pas « ajoutée » : on l'ajoute à l'enregistrement ; si elle est refusée, on bloque l'envoi
            form.addEventListener('submit', function (e) {
                if (!ajouterSaisie()) e.preventDefault();
            });
        }

        dessiner();
    }

    // ------------------------------------------------------------------
    // Ouverture de la fenêtre : copie du modèle de la classe, puis initialisation
    // ------------------------------------------------------------------
    fenetre.addEventListener('show.bs.modal', function (e) {
        const bouton = e.relatedTarget;
        if (!bouton) return;

        const titre = document.getElementById('gestionTitre');
        titre.textContent = bouton.dataset.titre;
        titre.title = bouton.dataset.titre;   // texte complet au survol si le titre est coupé
        corps.replaceChildren(document.getElementById(bouton.dataset.gestion).content.cloneNode(true));

        const racine = corps.querySelector('.gestion-racine');
        initialiserConfirmation(racine);
        initialiserAjout(racine);
        initialiserJours(racine);

        // Onglet demandé (après une action : on revient là où on était)
        const onglet = bouton.dataset.onglet;
        if (onglet) {
            const lanceur = corps.querySelector('#onglet-' + onglet + '-' + bouton.dataset.gestion.replace('gestion-', ''));
            if (lanceur) bootstrap.Tab.getOrCreateInstance(lanceur).show();
        }
        delete bouton.dataset.onglet;
    });

    // Après une action (ajout, retrait, jours) ou une erreur de validation : on rouvre la fenêtre de la classe
    @php
        $reouvrir = session('reouvrir') ?: ($errors->has('jours_exclus') || $errors->has('jours_exclus.*') ? old('_classe') . ':jours' : null);
    @endphp
    const reouvrir = @json($reouvrir);
    if (reouvrir) {
        const [idClasse, onglet] = reouvrir.split(':');
        const bouton = document.querySelector('[data-gestion="gestion-' + idClasse + '"]');
        if (bouton) {
            bouton.dataset.onglet = onglet || 'inscrits';
            bouton.click();
        }
    }
});
</script>