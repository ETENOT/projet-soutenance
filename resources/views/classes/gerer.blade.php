@extends('layouts.master')

@section('title', 'Gérer la classe ' . $classe->nom)

@section('content')

@component('components.breadcrumb')
    @slot('li_1')
        @if ($classe->session)
            <a href="{{ route('admin.sessions.show', $classe->session) }}">{{ $classe->session->nom }}</a>
        @else
            <a href="{{ route('admin.classes.index') }}">Classes</a>
        @endif
    @endslot
    @slot('title')
        {{ $classe->nom }}
    @endslot
@endcomponent

@php
    $ferme = $classe->estFermee();
    $nbInscrits = $classe->inscriptions->count();
    $nbPayes = $classe->inscriptions->filter(fn ($i) => $i->paiement)->count();
    $placesRestantes = max(0, $classe->capacite_max - $nbInscrits);

    // Jours déjà exclus ; si la validation vient d'échouer, on remet ce qui avait été saisi
    $erreurJours = $errors->has('jours_exclus') || $errors->has('jours_exclus.*');
    $joursActuels = $erreurJours
        ? collect(old('jours_exclus', []))->filter()->unique()->sort()->values()->all()
        : $classe->datesExclues()->all();

    // URL de la page sans certains paramètres (sert au "×" de chaque étiquette)
    $sans = fn (array $cles) => route('admin.cours.classes.gerer', array_merge([$cours, $classe], request()->except(array_merge($cles, ['page']))));
    $paiementsChoisis = array_values(array_intersect(['paye', 'impaye'], (array) request('paiement', [])));
    $libellesPaiement = ['paye' => 'Payés', 'impaye' => 'Impayés'];

    // Étiquettes des filtres actifs : [texte, paramètres à retirer, icône]
    $etiquettes = [];
    if ($paiementsChoisis) {
        $etiquettes[] = [implode(' / ', array_map(fn ($p) => $libellesPaiement[$p], $paiementsChoisis)), ['paiement'], 'ri-bank-card-line'];
    }
    if (request()->filled('date_du')) {
        $etiquettes[] = ['Inscrit depuis le ' . \Illuminate\Support\Carbon::parse(request('date_du'))->format('d/m/Y'), ['date_du'], 'ri-calendar-event-line'];
    }
    if (request()->filled('date_au')) {
        $etiquettes[] = ['Inscrit jusqu\'au ' . \Illuminate\Support\Carbon::parse(request('date_au'))->format('d/m/Y'), ['date_au'], 'ri-calendar-event-line'];
    }
    $rechercheActive = request()->filled('q') || count($etiquettes) > 0;

    // Fenêtre à rouvrir : les jours après une erreur de validation ; l'ajout après une erreur du serveur
    $reouvrir = $erreurJours ? 'jours' : (session('error') ? (explode(':', (string) session('reouvrir'))[1] ?? null) : null);
@endphp

<div class="container-fluid pb-4">

    @if ($ferme)
        <div class="alert alert-secondary py-2">
            <i class="ri-lock-line me-1"></i>Classe fermée : les inscrits et les jours ne sont plus modifiables.
        </div>
    @endif

    {{-- Statistiques --}}
    <div class="row g-2 mb-3">
        @foreach ([
            ['ri-group-line', 'primary', $nbInscrits . ' / ' . $classe->capacite_max, $placesRestantes === 0 ? 'Places (complet)' : 'Places occupées'],
            ['ri-bank-card-line', 'success', $nbPayes, 'Inscriptions payées'],
            ['ri-time-line', 'warning', $nbInscrits - $nbPayes, 'Inscriptions impayées'],
            ['ri-calendar-close-line', 'info', count($joursActuels), 'Jours non comptabilisés'],
        ] as [$icone, $teinte, $valeur, $libelle])
            <div class="col-sm-6 col-xl-3">
                <div class="card border shadow-sm h-100 mb-0">
                    <div class="card-body d-flex align-items-center gap-2 py-2 px-3">
                        <span class="avatar-sm flex-shrink-0">
                            <span class="avatar-title rounded-3 bg-{{ $teinte }}-subtle text-{{ $teinte }} fs-4"><i class="{{ $icone }}"></i></span>
                        </span>
                        <div>
                            <h5 class="fw-bold mb-0">{{ $valeur }}</h5>
                            <p class="text-muted mb-0 fs-12">{{ $libelle }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- ===== Barre d'actions groupées : cachée tant qu'aucun inscrit n'est coché.
         Les cases du tableau y sont rattachées par form="formGroupe". ===== --}}
    <form id="formGroupe" method="POST" action="{{ route('admin.cours.classes.inscrits.actions', [$cours, $classe]) }}">
        @csrf
        <input type="hidden" name="action" id="champActionGroupee" value="">
        <div id="barreActions" class="card border shadow-sm mb-3 d-none">
            <div class="card-body d-flex flex-wrap align-items-center gap-2">
                <strong><span id="nbSelection">0</span> inscrit(s) sélectionné(s)</strong>
                <button type="button" class="btn btn-success" data-action-groupee="payer">
                    <i class="ri-bank-card-line me-1"></i>Enregistrer le paiement
                </button>
                <button type="button" class="btn btn-danger" data-action-groupee="retirer">
                    <i class="ri-user-unfollow-line me-1"></i>Retirer de la classe
                </button>
                <button type="button" id="annulerSelection" class="btn btn-outline-secondary">Désélectionner</button>
            </div>
        </div>
    </form>

    {{-- Boutons à gauche, recherche à droite --}}
    <div class="d-flex flex-wrap align-items-start justify-content-between gap-2 mb-3">
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-primary fw-semibold" data-bs-toggle="modal" data-bs-target="#modalAjout"
                    @disabled($ferme || $placesRestantes === 0)
                    @if ($ferme) title="Classe fermée" @elseif ($placesRestantes === 0) title="Classe complète : retirez un inscrit pour libérer une place" @endif>
                <i class="ri-user-add-line me-1"></i>Ajouter des utilisateurs
            </button>
            <button type="button" class="btn btn-light border shadow-sm" data-bs-toggle="modal" data-bs-target="#modalJours">
                <i class="ri-calendar-close-line text-primary me-1"></i>Jours non comptabilisés
            </button>
        </div>

        <form method="GET" action="{{ route('admin.cours.classes.gerer', [$cours, $classe]) }}" class="col-12 col-md-8 col-lg-6 col-xl-5 ms-md-auto">
            <div class="d-flex align-items-start gap-2">
                <div class="form-control h-auto d-flex flex-wrap align-items-center gap-1 py-1 shadow-sm flex-grow-1">
                    <i class="ri-search-line text-primary me-1"></i>

                    @foreach ($etiquettes as [$texte, $retirer, $icone])
                        <span class="badge bg-primary-subtle text-primary d-inline-flex align-items-center gap-1 fs-12 py-2">
                            <i class="{{ $icone }}"></i><span class="text-truncate" title="{{ $texte }}">{{ $texte }}</span>
                            <a href="{{ $sans($retirer) }}" class="text-primary ms-1" title="Retirer ce filtre" aria-label="Retirer le filtre {{ $texte }}">
                                <i class="ri-close-line"></i>
                            </a>
                        </span>
                    @endforeach

                    <input type="text" name="q" value="{{ request('q') }}"
                           class="form-control border-0 shadow-none flex-grow-1 w-auto p-1"
                           placeholder="Rechercher un inscrit (nom ou e-mail)…" aria-label="Rechercher un inscrit">
                </div>

                <button type="button" class="btn btn-light border shadow-sm flex-shrink-0"
                        data-bs-toggle="modal" data-bs-target="#modalFiltres" aria-label="Ouvrir les filtres">
                    <i class="ri-equalizer-line text-primary me-1"></i>Filtres
                    @if (count($etiquettes))
                        <span class="badge bg-primary ms-1">{{ count($etiquettes) }}</span>
                    @endif
                </button>
            </div>

            <div class="modal fade" id="modalFiltres" tabindex="-1" aria-labelledby="titreFiltres" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title fw-semibold" id="titreFiltres">
                                    <i class="ri-equalizer-line text-primary me-2"></i>Filtres des inscrits
                                </h5>
                                <p class="text-muted small mb-0">Choisissez vos critères, puis appliquez-les à la liste.</p>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                        </div>

                        <div class="modal-body">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="card h-100 mb-0">
                                        <div class="card-body">
                                            <h6 class="fw-semibold mb-3"><i class="ri-bank-card-line text-primary me-1"></i>Paiement</h6>
                                            @foreach ($libellesPaiement as $valeur => $libelle)
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" name="paiement[]" value="{{ $valeur }}"
                                                           id="paiement-{{ $valeur }}" @checked(in_array($valeur, $paiementsChoisis, true))>
                                                    <label class="form-check-label" for="paiement-{{ $valeur }}">{{ $libelle }}</label>
                                                </div>
                                            @endforeach
                                            <p class="text-muted fs-13 mb-0 mt-3">Les deux cochés : tous les inscrits s'affichent.</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="card h-100 mb-0">
                                        <div class="card-body">
                                            <h6 class="fw-semibold mb-3"><i class="ri-calendar-event-line text-primary me-1"></i>Date d'inscription</h6>
                                            <label for="filtre-date-du" class="form-label small mb-1">Du</label>
                                            <input type="date" name="date_du" id="filtre-date-du" class="form-control form-control-sm mb-3" value="{{ request('date_du') }}">
                                            <label for="filtre-date-au" class="form-label small mb-1">Au</label>
                                            <input type="date" name="date_au" id="filtre-date-au" class="form-control form-control-sm" value="{{ request('date_au') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer justify-content-between">
                            <a href="{{ route('admin.cours.classes.gerer', [$cours, $classe]) }}" class="btn btn-outline-secondary">
                                <i class="ri-refresh-line me-1"></i>Réinitialiser
                            </a>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-check-line me-1"></i>Appliquer les filtres
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <button type="submit" class="visually-hidden" tabindex="-1" aria-hidden="true">Rechercher</button>
        </form>
    </div>

    {{-- Inscrits --}}
    <div class="card border shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0 table-liste">
                <thead>
                    <tr class="table-light">
                        <th class="col-coche"><input type="checkbox" id="toutSelectionner" class="form-check-input" aria-label="Tout sélectionner" @disabled($ferme)></th>
                        <th>Nom</th>
                        <th>E-mail</th>
                        <th class="col-date">Inscrit le</th>
                        <th class="text-end col-actions-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($inscriptions as $inscription)
                        @php $nomInscrit = $inscription->user?->name ?? 'Compte supprimé'; @endphp
                        <tr>
                            <td>
                                <input type="checkbox" name="ids[]" value="{{ $inscription->id }}" form="formGroupe"
                                       class="form-check-input case-inscription" aria-label="Sélectionner {{ $nomInscrit }}" @disabled($ferme)>
                            </td>
                            <td class="fw-medium" title="{{ $nomInscrit }}">{{ $nomInscrit }}</td>
                            <td class="text-muted" title="{{ $inscription->user?->email }}">{{ $inscription->user?->email ?? '—' }}</td>
                            <td>{{ $inscription->created_at?->format('d/m/Y') }}</td>
                            <td class="text-end">
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
                                        <form action="{{ route('admin.cours.classes.inscrits.destroy', [$cours, $classe, $inscription->user]) }}" method="POST" class="m-0"
                                              data-confirmation="{{ $inscription->paiement ? 'Retirer ' . $nomInscrit . ' de la classe ? Son inscription est payée : le paiement enregistré sera supprimé avec elle.' : 'Retirer ' . $nomInscrit . ' de la classe ?' }}"
                                              data-confirmation-bouton="Retirer"
                                              data-confirmation-style="danger">
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
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">
                                <i class="ri-group-line display-5 d-block mb-2"></i>
                                {{ $rechercheActive ? 'Aucun inscrit ne correspond à votre recherche.' : "Aucun inscrit pour l'instant." }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($inscriptions->count() > 0)
            <div class="card-footer bg-transparent">
                <small class="text-muted">{{ $inscriptions->count() }} inscrit(s)</small>
            </div>
        @endif
    </div>
</div>

{{-- ===== Ajouter des utilisateurs : liste à gauche, sélection à droite ===== --}}
<div class="modal fade" id="modalAjout" tabindex="-1" aria-hidden="true"
     data-url-recherche="{{ route('admin.cours.classes.candidats', [$cours, $classe]) }}"
     data-restantes="{{ $placesRestantes }}">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('admin.cours.classes.inscrits.store', [$cours, $classe]) }}" method="POST" class="js-form-ajout">
                @csrf
                <div class="modal-header">
                    <div class="overflow-hidden flex-grow-1 me-3">
                        <h5 class="modal-title">Ajouter des utilisateurs</h5>
                        <p class="text-muted small mb-0">Inscrits avec le paiement enregistré au comptoir</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>

                <div class="modal-body">
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
                                    <input type="checkbox" class="form-check-input js-tout-cocher" id="toutCocher">
                                    <label class="form-check-label" for="toutCocher">Tout cocher (liste affichée)</label>
                                </div>
                                <span class="text-muted fs-13"><span class="js-affiches">0</span> affiché(s)</span>
                            </div>

                            <div class="border rounded js-defilement gestion-colonne">
                                <div class="list-group list-group-flush js-liste"></div>
                                <p class="text-muted text-center py-4 mb-0 js-message">Chargement…</p>
                            </div>
                        </div>

                        {{-- Sélection --}}
                        <div class="col-lg-5">
                            <div class="d-flex justify-content-between align-items-center mb-2 gestion-ligne-haute">
                                <h6 class="mb-0 fw-semibold">Sélection</h6>
                                <span class="badge bg-primary-subtle text-primary fs-12">
                                    <span class="js-compteur">0</span> / {{ $placesRestantes }} place(s)
                                </span>
                            </div>
                            <div class="mb-2 text-muted fs-13 gestion-ligne-basse">Les personnes cochées apparaissent ici.</div>

                            <div class="border rounded bg-light-subtle gestion-colonne">
                                <ul class="list-group list-group-flush js-selection mb-0"></ul>
                                <p class="text-muted text-center py-4 mb-0 js-selection-vide">Aucun utilisateur sélectionné.</p>
                            </div>
                        </div>
                    </div>
                    <div class="js-champs"></div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary js-bouton-ajout" disabled>
                        <i class="ri-user-add-line me-1"></i><span class="js-bouton-texte">Ajouter à la classe</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ===== Jours non comptabilisés ===== --}}
<div class="modal fade" id="modalJours" tabindex="-1" aria-hidden="true"
     data-min="{{ $classe->date_debut->toDateString() }}"
     data-max="{{ $classe->date_fin->toDateString() }}"
     data-jours='@json($joursActuels)'
     data-ferme="{{ $ferme ? 1 : 0 }}">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('admin.cours.classes.jours-exclus', [$cours, $classe]) }}" method="POST" class="js-form-jours">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Jours non comptabilisés</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>

                <div class="modal-body">
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
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ $ferme ? 'Fermer' : 'Annuler' }}</button>
                    @unless ($ferme)
                        <button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i>Enregistrer les jours</button>
                    @endunless
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ===== Confirmation (retrait d'un inscrit, paiement) ===== --}}
<div class="modal fade" id="modalConfirmation" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0" id="confirmationMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-danger" id="confirmationValider">Confirmer</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Crée un élément avec ses classes et son texte (le texte est inséré tel quel : pas de HTML injecté)
    function el(balise, classes, texte) {
        const e = document.createElement(balise);
        if (classes) e.className = classes;
        if (texte !== undefined) e.textContent = texte;
        return e;
    }


    // ------------------------------------------------------------------
    // Confirmation avant un retrait ou un paiement (fenêtre unique)
    // ------------------------------------------------------------------
    const fenetreConfirmation = document.getElementById('modalConfirmation');
    const boutonConfirmer = document.getElementById('confirmationValider');
    let formulaireEnAttente = null;

    document.querySelectorAll('form[data-confirmation]').forEach(function (f) {
        f.addEventListener('submit', function (e) {
            e.preventDefault();
            formulaireEnAttente = f;
            document.getElementById('confirmationMessage').textContent = f.dataset.confirmation;
            boutonConfirmer.textContent = f.dataset.confirmationBouton || 'Confirmer';
            boutonConfirmer.className = 'btn btn-' + (f.dataset.confirmationStyle || 'danger');
            bootstrap.Modal.getOrCreateInstance(fenetreConfirmation).show();
        });
    });

    boutonConfirmer.addEventListener('click', function () {
        // submit() ne redéclenche pas l'événement « submit » : pas de nouvelle demande
        if (formulaireEnAttente) formulaireEnAttente.submit();
    });

    // ------------------------------------------------------------------
    // Sélection multiple des inscrits : la barre d'actions apparaît dès qu'un inscrit est coché
    // ------------------------------------------------------------------
    const casesInscrits = Array.from(document.querySelectorAll('.case-inscription:not(:disabled)'));
    const toutesInscrits = document.getElementById('toutSelectionner');
    const barreActions = document.getElementById('barreActions');

    function majSelectionInscrits() {
        const nb = casesInscrits.filter(c => c.checked).length;
        document.getElementById('nbSelection').textContent = nb;
        barreActions.classList.toggle('d-none', nb === 0);
        toutesInscrits.checked = nb > 0 && nb === casesInscrits.length;
        toutesInscrits.indeterminate = nb > 0 && nb < casesInscrits.length;
    }

    casesInscrits.forEach(c => c.addEventListener('change', majSelectionInscrits));
    toutesInscrits.addEventListener('change', function () {
        casesInscrits.forEach(c => c.checked = toutesInscrits.checked);
        majSelectionInscrits();
    });

    document.getElementById('annulerSelection').addEventListener('click', function () {
        casesInscrits.forEach(c => c.checked = false);
        majSelectionInscrits();
    });

    // Retirer / enregistrer le paiement des inscrits cochés : on passe par la fenêtre de confirmation existante
    document.querySelectorAll('[data-action-groupee]').forEach(function (bouton) {
        bouton.addEventListener('click', function () {
            const nb = casesInscrits.filter(c => c.checked).length;
            if (nb === 0) return;

            const retirer = bouton.dataset.actionGroupee === 'retirer';
            document.getElementById('champActionGroupee').value = bouton.dataset.actionGroupee;
            formulaireEnAttente = document.getElementById('formGroupe');
            document.getElementById('confirmationMessage').textContent = retirer
                ? 'Retirer ' + nb + ' inscrit(s) de la classe ? Les paiements déjà enregistrés de ces inscrits seront supprimés avec leur inscription.'
                : 'Enregistrer le paiement de ' + nb + ' inscrit(s) ? Les inscriptions déjà payées sont ignorées.';
            boutonConfirmer.textContent = retirer ? 'Retirer' : 'Enregistrer le paiement';
            boutonConfirmer.className = 'btn btn-' + (retirer ? 'danger' : 'success');
            bootstrap.Modal.getOrCreateInstance(fenetreConfirmation).show();
        });
    });

    majSelectionInscrits();

    // ------------------------------------------------------------------
    // Ajouter des utilisateurs : liste à gauche, sélection à droite
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

        let chargement = false;        // vrai pendant la requête
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
            if (chargement) { message.textContent = 'Chargement…'; } else {
                message.textContent = visibles.length ? ''
                    : (tous.length ? 'Aucun utilisateur ne correspond.' : 'Aucun utilisateur disponible à inscrire.');
            }
            message.classList.toggle('d-none', visibles.length > 0);

            visibles.forEach(function (u) {
                const etiquette = el('label', 'list-group-item list-group-item-action d-flex align-items-center gap-2 mb-0');

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

        // À chaque ouverture de la fenêtre : sélection vide, puis une seule requête pour la liste des utilisateurs disponibles
        racine.addEventListener('show.bs.modal', function () {
            selection.clear();
            champRecherche.value = '';
            tous = [];
            chargement = true;
            dessiner();

            fetch(racine.dataset.urlRecherche, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(function (r) {
                    if (!r.ok) { throw new Error('http ' + r.status); }
                    return r.json();
                })
                .then(function (donnees) {
                    tous = donnees;
                    chargement = false;
                    dessiner();
                })
                .catch(function () {
                    chargement = false;
                    message.textContent = 'Le chargement des utilisateurs a échoué. Rechargez la page.';
                    message.classList.remove('d-none');
                });
        });
    }


    // ------------------------------------------------------------------
    // Jours non comptabilisés
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

        // Fenêtre fermée sans enregistrer : on revient aux jours réellement enregistrés
        racine.addEventListener('hidden.bs.modal', function () {
            jours = JSON.parse(racine.dataset.jours || '[]');
            message.textContent = '';
            if (saisie) saisie.value = '';
            dessiner();
        });

        dessiner();
    }

    const fenetreAjout = document.getElementById('modalAjout');
    const fenetreJours = document.getElementById('modalJours');
    initialiserAjout(fenetreAjout);
    initialiserJours(fenetreJours);

    // Après une erreur : on rouvre la fenêtre concernée (la saisie est conservée)
    const reouvrir = @json($reouvrir);
    if (reouvrir === 'jours') {
        bootstrap.Modal.getOrCreateInstance(fenetreJours).show();
    } else if (reouvrir === 'inscrits') {
        bootstrap.Modal.getOrCreateInstance(fenetreAjout).show();
    }
});
</script>
@endsection