@extends('layouts.master')

@section('title', 'Gestion des classes')

@section('content')

{{-- ===== Fil d'Ariane ===== --}}
@component('components.breadcrumb')
    @slot('li_1')
        <a href="{{ route('dashboard') }}">Accueil</a>
    @endslot
    @slot('title')
        Classes
    @endslot
@endcomponent

@php
    // Retour d'une validation qui a échoué : "_mode" (ajout ou modification) dit quelle fenêtre rouvrir
    $modeAvecErreur = $errors->any() ? old('_mode') : null;
@endphp

<div class="container-fluid pb-4">

    {{-- ===== Statistiques ===== --}}
    <div class="row g-2 mb-3">
        <div class="col-sm-6 col-xl-3">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center gap-2 py-2 px-3">
                    <span class="avatar-sm flex-shrink-0">
                        <span class="avatar-title rounded-3 bg-success-subtle text-success fs-4"><i class="ri-play-circle-line"></i></span>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-0">{{ $stats['en_cours'] }}</h5>
                        <p class="text-muted mb-0 fs-12">Classes en cours</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center gap-2 py-2 px-3">
                    <span class="avatar-sm flex-shrink-0">
                        <span class="avatar-title rounded-3 bg-warning-subtle text-warning fs-4"><i class="ri-calendar-event-line"></i></span>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-0">{{ $stats['a_venir'] }}</h5>
                        <p class="text-muted mb-0 fs-12">Classes à venir</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center gap-2 py-2 px-3">
                    <span class="avatar-sm flex-shrink-0">
                        <span class="avatar-title rounded-3 bg-info-subtle text-info fs-4"><i class="ri-hourglass-line"></i></span>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-0">{{ $stats['terminees'] }}</h5>
                        <p class="text-muted mb-0 fs-12">Classes terminées</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center gap-2 py-2 px-3">
                    <span class="avatar-sm flex-shrink-0">
                        <span class="avatar-title rounded-3 bg-primary-subtle text-primary fs-4"><i class="ri-book-open-line"></i></span>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-0">{{ $stats['formations'] }}</h5>
                        <p class="text-muted mb-0 fs-12">Cours concernés</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Erreurs de filtre (période à l'envers, nombre négatif...) ===== --}}
    @if ($errors->any() && ! $modeAvecErreur)
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $erreur)
                    <li>{{ $erreur }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ===== Étiquettes des filtres actifs ===== --}}
    @php
        // URL de la liste sans certains paramètres (sert au "×" de chaque étiquette)
        $sans = fn (array $cles) => route('admin.classes.index', request()->except(array_merge($cles, ['page'])));

        $libellesEtat = ['en_cours' => 'En cours', 'a_venir' => 'À venir', 'terminee' => 'Terminée'];
        $libellesGroupe = [
            'formation' => 'Cours', 'formateur' => 'Formateur', 'etat' => 'Statut',
            'categorie' => 'Catégorie', 'lieu' => 'Lieu',
        ];

        // Étiquettes des filtres actifs : [texte, paramètres à retirer, icône]
        $etiquettes = [];

        if ($etatsChoisis) {
            $etiquettes[] = ['Statut : ' . collect($etatsChoisis)->map(fn ($e) => $libellesEtat[$e])->implode(' ou '), ['etat'], 'ri-flag-line'];
        }
        if (request()->boolean('completes')) {
            $etiquettes[] = ['Complètes', ['completes'], 'ri-group-line'];
        }
        if (request()->boolean('incompletes')) {
            $etiquettes[] = ['Incomplètes', ['incompletes'], 'ri-group-line'];
        }
        if (request()->boolean('avec_formateur')) {
            $etiquettes[] = ['Avec formateur', ['avec_formateur'], 'ri-user-star-line'];
        }
        if (request()->boolean('sans_formateur') || request('formateur_id') === 'aucun') {
            $etiquettes[] = ['Sans formateur', ['sans_formateur', 'formateur_id'], 'ri-user-unfollow-line'];
        } elseif (request()->filled('formateur_id')) {
            $etiquettes[] = ['Formateur : ' . ($tousLesFormateurs->firstWhere('id', (int) request('formateur_id'))->name ?? '—'), ['formateur_id'], 'ri-user-star-line'];
        }
        if (request()->filled('cours_id')) {
            $etiquettes[] = ['Cours : ' . ($tousLesCours->firstWhere('id', (int) request('cours_id'))->titre ?? '—'), ['cours_id'], 'ri-book-open-line'];
        }
        if (request()->filled('categorie')) {
            $categorieChoisie = $toutesLesCategories->firstWhere('id', request('categorie'));
            $etiquettes[] = ['Catégorie : ' . ($categorieChoisie?->nom ?? request('categorie')), ['categorie'], 'ri-price-tag-3-line'];
        }
        if (request()->filled('lieu')) {
            $etiquettes[] = ['Lieu : ' . request('lieu'), ['lieu'], 'ri-map-pin-line'];
        }
        if (request()->filled('date_du') || request()->filled('date_au')) {
            $du = request()->filled('date_du') ? \Carbon\Carbon::parse(request('date_du'))->format('d/m/Y') : '…';
            $au = request()->filled('date_au') ? \Carbon\Carbon::parse(request('date_au'))->format('d/m/Y') : '…';
            $etiquettes[] = ['Période : ' . $du . ' → ' . $au, ['date_du', 'date_au'], 'ri-calendar-line'];
        }
        if (request()->filled('inscrits_min') || request()->filled('inscrits_max')) {
            $etiquettes[] = ['Inscrits : ' . (request('inscrits_min') ?: '0') . ' à ' . (request('inscrits_max') ?: '∞'), ['inscrits_min', 'inscrits_max'], 'ri-group-line'];
        }
        if ($groupe) {
            $etiquettes[] = ['Regroupé par ' . mb_strtolower($libellesGroupe[$groupe]), ['groupe'], 'ri-stack-line'];
        }
    @endphp

    {{-- ===== Barre d'actions groupées : cachée tant qu'aucune classe n'est cochée.
         Les cases du tableau y sont rattachées par form="formActions". ===== --}}
    <form id="formActions" method="POST" action="{{ route('admin.classes.actions') }}">
        @csrf
        <div id="barreActions" class="card border shadow-sm mb-3 d-none">
            <div class="card-body d-flex flex-wrap align-items-center gap-2">
                <strong><span id="nbSelection">0</span> classe(s) sélectionnée(s)</strong>

                <select name="action" id="actionChoisie" class="form-select w-auto" aria-label="Action à appliquer">
                    <option value="">Choisir une action…</option>
                    <option value="affecter_formateur">Affecter un formateur</option>
                    <option value="retirer_formateur">Retirer le formateur</option>
                    <option value="fermer">Fermer les classes</option>
                    <option value="supprimer">Supprimer les classes</option>
                </select>

                {{-- Visible seulement pour l'action "Affecter un formateur" --}}
                <select name="formateur_id" id="formateurCible" class="form-select w-auto d-none" aria-label="Formateur à affecter">
                    <option value="">Choisir le formateur…</option>
                    @foreach ($tousLesFormateurs as $formateur)
                        <option value="{{ $formateur->id }}">{{ $formateur->name }}</option>
                    @endforeach
                </select>

                <button type="submit" class="btn btn-primary">
                    <i class="ri-check-line me-1"></i>Appliquer
                </button>
                <button type="button" id="annulerSelection" class="btn btn-outline-secondary">Désélectionner</button>
            </div>
        </div>
    </form>

    {{-- ===== Ligne d'outils : bouton de création à gauche, recherche + filtres à droite ===== --}}
    <div class="d-flex flex-wrap align-items-start justify-content-between gap-2 mb-3">

        <button type="button" class="btn btn-primary fw-semibold"
                data-bs-toggle="modal" data-bs-target="#modalClasse" data-mode="ajout">
            <i class="ri-add-line me-1"></i>Créer une classe
        </button>

        <form method="GET" action="{{ route('admin.classes.index') }}" class="col-12 col-md-8 col-lg-6 col-xl-5 ms-md-auto">
            <div class="d-flex align-items-start gap-2">
                <div class="form-control h-auto d-flex flex-wrap align-items-center gap-1 py-1 shadow-sm flex-grow-1">
                    <i class="ri-search-line text-primary me-1"></i>

                    @foreach ($etiquettes as [$texte, $retirer, $icone])
                        <span class="badge bg-primary-subtle text-primary d-inline-flex align-items-center gap-1 fs-12 py-2">
                            <i class="{{ $icone }}"></i>{{ $texte }}
                            <a href="{{ $sans($retirer) }}" class="text-primary ms-1" title="Retirer ce filtre" aria-label="Retirer le filtre {{ $texte }}">
                                <i class="ri-close-line"></i>
                            </a>
                        </span>
                    @endforeach

                    <input type="text" name="q" value="{{ request('q') }}"
                           class="form-control border-0 shadow-none flex-grow-1 w-auto p-1"
                           placeholder="Rechercher une classe, un cours, un lieu…" aria-label="Rechercher une classe">
                </div>

                <button type="button" class="btn btn-light border shadow-sm flex-shrink-0"
                        data-bs-toggle="modal" data-bs-target="#modalFiltres" aria-label="Ouvrir les filtres">
                    <i class="ri-equalizer-line text-primary me-1"></i>
                    Filtres
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
                                    <i class="ri-equalizer-line text-primary me-2"></i>Filtres des classes
                                </h5>
                                <p class="text-muted small mb-0">Choisissez vos critères, puis appliquez-les à la liste.</p>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                        </div>

                        <div class="modal-body">
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <div class="card h-100 mb-0">
                                        <div class="card-body">
                                            <h6 class="fw-semibold mb-3"><i class="ri-filter-3-line text-primary me-1"></i>État et inscriptions</h6>

                                            @foreach ($libellesEtat as $valeur => $libelle)
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" name="etat[]" value="{{ $valeur }}"
                                                           id="filtre-etat-{{ $valeur }}" @checked(in_array($valeur, $etatsChoisis, true))>
                                                    <label class="form-check-label" for="filtre-etat-{{ $valeur }}">{{ $libelle }}</label>
                                                </div>
                                            @endforeach

                                            <hr>

                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" name="completes" value="1" id="filtre-completes"
                                                       @checked(request()->boolean('completes'))>
                                                <label class="form-check-label" for="filtre-completes">Complètes</label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" name="incompletes" value="1" id="filtre-incompletes"
                                                       @checked(request()->boolean('incompletes'))>
                                                <label class="form-check-label" for="filtre-incompletes">Incomplètes</label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" name="avec_formateur" value="1" id="filtre-avec-formateur"
                                                       @checked(request()->boolean('avec_formateur'))>
                                                <label class="form-check-label" for="filtre-avec-formateur">Avec formateur</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="sans_formateur" value="1" id="filtre-sans-formateur"
                                                       @checked(request()->boolean('sans_formateur') || request('formateur_id') === 'aucun')>
                                                <label class="form-check-label" for="filtre-sans-formateur">Sans formateur</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="card h-100 mb-0">
                                        <div class="card-body">
                                            <h6 class="fw-semibold mb-3"><i class="ri-stack-line text-primary me-1"></i>Regrouper par</h6>

                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="radio" name="groupe" value="" id="groupe-aucun"
                                                       @checked(! $groupe)>
                                                <label class="form-check-label" for="groupe-aucun">Aucun regroupement</label>
                                            </div>
                                            @foreach ($libellesGroupe as $valeur => $libelle)
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="radio" name="groupe" value="{{ $valeur }}"
                                                           id="groupe-{{ $valeur }}" @checked($groupe === $valeur)>
                                                    <label class="form-check-label" for="groupe-{{ $valeur }}">{{ $libelle }}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="card mb-0">
                                        <div class="card-body">
                                            <h6 class="fw-semibold mb-3"><i class="ri-list-settings-line text-primary me-1"></i>Autres critères</h6>

                                            <label for="filtre-cours" class="form-label small mb-1">Cours</label>
                                            <select name="cours_id" id="filtre-cours" class="form-select form-select-sm mb-3" aria-label="Filtrer par cours">
                                                <option value="">Tous les cours</option>
                                                @foreach ($tousLesCours as $unCours)
                                                    <option value="{{ $unCours->id }}" @selected(request('cours_id') == $unCours->id)>{{ $unCours->titre }}</option>
                                                @endforeach
                                            </select>

                                            <label for="filtre-formateur" class="form-label small mb-1">Formateur</label>
                                            <select name="formateur_id" id="filtre-formateur" class="form-select form-select-sm mb-3" aria-label="Filtrer par formateur">
                                                <option value="">Tous les formateurs</option>
                                                <option value="aucun" @selected(request('formateur_id') === 'aucun')>— Sans formateur —</option>
                                                @foreach ($tousLesFormateurs as $formateur)
                                                    <option value="{{ $formateur->id }}" @selected((string) request('formateur_id') === (string) $formateur->id)>{{ $formateur->name }}</option>
                                                @endforeach
                                            </select>

                                            <label for="filtre-categorie" class="form-label small mb-1">Catégorie</label>
                                            <select name="categorie" id="filtre-categorie" class="form-select form-select-sm mb-3" aria-label="Filtrer par catégorie">
                                                <option value="">Toutes les catégories</option>
                                                @foreach ($toutesLesCategories as $categorie)
                                                    <option value="{{ $categorie->id }}" @selected((string) request('categorie') === (string) $categorie->id)>{{ $categorie->nom }}</option>
                                                @endforeach
                                            </select>

                                            <label for="filtre-lieu" class="form-label small mb-1">Lieu</label>
                                            <select name="lieu" id="filtre-lieu" class="form-select form-select-sm mb-3" aria-label="Filtrer par lieu">
                                                <option value="">Tous les lieux</option>
                                                @foreach ($tousLesLieux as $lieu)
                                                    <option value="{{ $lieu }}" @selected(request('lieu') === $lieu)>{{ $lieu }}</option>
                                                @endforeach
                                            </select>

                                            <label class="form-label small mb-1">Période</label>
                                            <div class="row g-2 mb-3">
                                                <div class="col-6">
                                                    <input type="date" name="date_du" value="{{ request('date_du') }}" class="form-control form-control-sm"
                                                           aria-label="Début de la période">
                                                </div>
                                                <div class="col-6">
                                                    <input type="date" name="date_au" value="{{ request('date_au') }}" class="form-control form-control-sm"
                                                           aria-label="Fin de la période">
                                                </div>
                                            </div>

                                            <label class="form-label small mb-1">Nombre d'inscrits</label>
                                            <div class="row g-2">
                                                <div class="col-6">
                                                    <input type="number" name="inscrits_min" min="0" value="{{ request('inscrits_min') }}"
                                                           class="form-control form-control-sm" placeholder="Minimum" aria-label="Inscrits minimum">
                                                </div>
                                                <div class="col-6">
                                                    <input type="number" name="inscrits_max" min="0" value="{{ request('inscrits_max') }}"
                                                           class="form-control form-control-sm" placeholder="Maximum" aria-label="Inscrits maximum">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer justify-content-between">
                            <a href="{{ route('admin.classes.index') }}" class="btn btn-outline-secondary">
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

    {{-- ===== Tableau des classes ===== --}}
    <div class="card border shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0 table-classes">
                <thead>
                    <tr class="table-light">
                        <th>
                            <input type="checkbox" id="toutSelectionner" class="form-check-input" aria-label="Tout sélectionner">
                        </th>
                        <th>Cours</th>
                        <th>Classe</th>
                        <th>Formateur</th>
                        <th>Période / statut</th>
                        <th>Inscrits</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @php
                        // dernier groupe affiché : sert à n'écrire l'en-tête qu'au changement de groupe
                        $groupePrecedent = null;
                    @endphp
                    @forelse ($classes as $classe)
                        {{-- En-tête de groupe (Regrouper par) --}}
                        @if ($groupe)
                            @php
                                $cle = $cleGroupe($classe);
                                $libelleGroupe = match ($groupe) {
                                    'formation' => $classe->cours?->titre ?? 'Cours supprimé',
                                    'formateur' => $classe->formateur?->name ?? 'Sans formateur',
                                    'etat' => $libellesEtat[$classe->etat] ?? $classe->etat,
                                    'categorie' => $classe->cours?->categorie?->nom ?: 'Sans catégorie',
                                    'lieu' => $classe->lieu,
                                };
                            @endphp
                            @if ($cle !== $groupePrecedent)
                                @php $groupePrecedent = $cle; @endphp
                                <tr class="table-active">
                                    <td colspan="7" class="fw-semibold">
                                        <i class="ri-stack-line text-primary me-1"></i>{{ $libelleGroupe }}
                                        <span class="badge bg-primary-subtle text-primary ms-1">{{ $comptesGroupes[$cle] ?? 0 }}</span>
                                    </td>
                                </tr>
                            @endif
                        @endif

                        @php
                            $etat = $classe->etat;
                            $badge = match ($etat) {
                                'en_cours' => ['bg-success-subtle text-success', 'En cours'],
                                'a_venir'  => ['bg-warning-subtle text-warning', 'À venir'],
                                default    => ['bg-info-subtle text-info', 'Terminée'],
                            };
                            $complete = $classe->inscriptions_count >= $classe->capacite_max;
                        @endphp

                        <tr>
                            <td>
                                <input type="checkbox" name="ids[]" value="{{ $classe->id }}" form="formActions"
                                       class="form-check-input case-classe" aria-label="Sélectionner {{ $classe->nom }}">
                            </td>

                            <td class="fw-semibold cellule-tronquee-fixe" title="{{ $classe->cours?->titre }}">{{ $classe->cours?->titre ?? 'Cours supprimé' }}</td>
                            <td class="fw-medium cellule-tronquee-fixe" title="{{ $classe->nom }}">{{ $classe->nom }}</td>

                            <td>
                                @if ($classe->formateur)
                                    <i class="ri-user-star-line text-primary me-1"></i>{{ $classe->formateur->name }}
                                @else
                                    <span class="text-muted">Non affecté</span>
                                @endif
                            </td>

                            <td>
                                <div class="d-flex flex-column align-items-start gap-1">
                                    <span class="classe-dates">
                                        {{ $classe->date_debut->format('d/m/Y') }}
                                        <i class="ri-arrow-right-line text-muted mx-1"></i>
                                        {{ $classe->date_fin->format('d/m/Y') }}
                                    </span>
                                    <span class="badge {{ $badge[0] }}">{{ $badge[1] }}</span>
                                </div>
                            </td>

                            <td>
                                <span class="fw-semibold {{ $complete ? 'text-danger' : '' }}">
                                    {{ $classe->inscriptions_count }} / {{ $classe->capacite_max }}
                                </span>
                                @if ($complete)
                                    <span class="badge bg-danger-subtle text-danger ms-1">Complète</span>
                                @endif
                            </td>

                            <td class="text-end classe-actions">
                                <div class="d-flex justify-content-end gap-1">
                                    @if ($classe->cours)
                                        <button type="button" class="btn btn-sm btn-soft-primary" title="Voir les détails"
                                                data-bs-toggle="modal" data-bs-target="#modalDetail"
                                                data-detail="detail-{{ $classe->id }}"
                                                data-titre="{{ $classe->nom }}">
                                            <i class="ri-eye-line"></i>
                                        </button>

                                        <button type="button" class="btn btn-sm btn-soft-secondary" title="Modifier la classe"
                                                data-bs-toggle="modal" data-bs-target="#modalClasse"
                                                data-mode="modif"
                                                data-action="{{ route('admin.cours.classes.update', [$classe->cours, $classe]) }}"
                                                data-nom="{{ $classe->nom }}"
                                                data-capacite="{{ $classe->capacite_max }}"
                                                data-debut="{{ $classe->date_debut->format('Y-m-d') }}"
                                                data-fin="{{ $classe->date_fin->format('Y-m-d') }}"
                                                data-formateur="{{ $classe->formateur_id }}"
                                                data-lieu="{{ $classe->lieu }}"
                                                data-lien="{{ $classe->lieu_lien }}">
                                            <i class="ri-edit-line"></i>
                                        </button>

                                        <button type="button" class="btn btn-sm btn-soft-danger" title="Supprimer la classe"
                                                data-bs-toggle="modal" data-bs-target="#modalSuppression"
                                                data-action="{{ route('admin.cours.classes.destroy', [$classe->cours, $classe]) }}"
                                                data-nom="{{ $classe->nom }}"
                                                data-inscrits="{{ $classe->inscriptions_count }}"
                                                data-payes="{{ $classe->inscriptions->filter->estPayee()->count() }}">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="ri-group-line display-5 d-block mb-2"></i>
                                Aucune classe ne correspond à votre recherche.
                                @if ($etiquettes || request()->filled('q'))
                                    <a href="{{ route('admin.classes.index') }}" class="d-block mt-2">Réinitialiser les filtres</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($classes->total() > 0)
            <div class="card-footer bg-transparent d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <small class="text-muted">
                    Affichage de {{ $classes->firstItem() }} à {{ $classes->lastItem() }}
                    sur {{ $classes->total() }} résultat(s)
                </small>
                {{ $classes->links() }}
            </div>
        @endif
    </div>

    {{-- Contenus des fiches « Voir » : le script les copie dans la fenêtre --}}
    @foreach ($classes as $classe)
        <div id="detail-{{ $classe->id }}" class="d-none">
            <div class="row g-4">
                <div class="col-md-5">
                    <a href="{{ $classe->lieu_url }}" target="_blank" rel="noopener noreferrer"
                       class="d-block overflow-hidden rounded-3 mb-3">
                        <img src="{{ $classe->lieu_image_url }}" alt="Photo du lieu : {{ $classe->lieu }}"
                             class="detail-image">
                    </a>
                    <div class="d-flex align-items-start gap-2">
                        <i class="ri-map-pin-line text-primary fs-5"></i>
                        <div>
                            <div class="fw-semibold">{{ $classe->lieu }}</div>
                            <a href="{{ $classe->lieu_url }}" target="_blank" rel="noopener noreferrer" class="small">
                                Voir sur Google Maps <i class="ri-external-link-line"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-7">
                    <div class="row g-3 mb-4">
                        <div class="col-sm-6">
                            <div class="bg-light rounded-3 p-3 h-100">
                                <div class="small text-muted mb-1"><i class="ri-book-open-line me-1"></i>Cours</div>
                                <div class="fw-semibold">{{ $classe->cours?->titre ?? '—' }}</div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="bg-light rounded-3 p-3 h-100">
                                <div class="small text-muted mb-1"><i class="ri-calendar-line me-1"></i>Période</div>
                                <div class="fw-semibold">{{ $classe->date_debut->format('d/m/Y') }} → {{ $classe->date_fin->format('d/m/Y') }}</div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="bg-light rounded-3 p-3 h-100">
                                <div class="small text-muted mb-1"><i class="ri-user-star-line me-1"></i>Formateur</div>
                                <div class="fw-semibold">{{ $classe->formateur?->name ?? 'Non affecté' }}</div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="bg-light rounded-3 p-3 h-100">
                                <div class="small text-muted mb-1"><i class="ri-group-line me-1"></i>Inscrits</div>
                                <div class="fw-semibold">{{ $classe->inscriptions_count }} / {{ $classe->capacite_max }}</div>
                            </div>
                        </div>
                        <div class="col-12">
                            <span class="badge bg-primary-subtle text-primary">{{ $classe->etat_libelle }}</span>
                            @if ($classe->estFermee())
                                <span class="small text-muted ms-1">Fermée le {{ $classe->fermee_le->format('d/m/Y') }}</span>
                            @endif
                        </div>
                    </div>

                    <h6 class="fw-semibold mb-2">Liste des inscrits</h6>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr><th>Inscrit</th><th>Courriel</th><th>Paiement</th></tr>
                            </thead>
                            <tbody>
                                @forelse ($classe->inscriptions as $inscription)
                                    <tr>
                                        <td>{{ $inscription->user?->name ?? 'Compte supprimé' }}</td>
                                        <td>{{ $inscription->user?->email ?? '—' }}</td>
                                        <td>{{ $inscription->estPayee() ? 'Payé' : 'En attente' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-muted">Aucun inscrit.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @if ($classe->cours && ! $classe->estFermee() && $classe->peutEtreFermee())
                <form action="{{ route('admin.cours.classes.fermer', [$classe->cours, $classe]) }}" method="POST"
                      class="mt-3"
                      data-confirmation="Fermer cette classe ? Les taux de présence seront figés et les attestations calculées. Action définitive."
                      data-confirmation-bouton="Fermer la classe">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-danger">Fermer la classe</button>
                </form>
            @endif
        </div>
    @endforeach
</div>

{{-- ===== Fenêtre « Voir » ===== --}}
<div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <div class="small text-primary fw-semibold text-uppercase">Fiche de la classe</div>
                    <h5 class="modal-title fw-bold mb-0" id="detailTitre"></h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body" id="detailCorps"></div>
        </div>
    </div>
</div>

{{-- ===== Fenêtre de confirmation de suppression ===== --}}
<div class="modal fade" id="modalSuppression" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="formSuppression" method="POST" action="#">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title">Supprimer la classe</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Voulez-vous vraiment supprimer la classe <strong id="suppressionNom"></strong> ?</p>
                    <p class="text-danger mb-2 d-none" id="suppressionAlerte"></p>
                    <p class="text-muted mb-0">Cette action est définitive.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">Supprimer</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ===== Confirmation de la suppression groupée ===== --}}
<div class="modal fade" id="modalSuppressionGroupee" tabindex="-1" aria-labelledby="titreSuppressionGroupee" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-3">
                    <span class="avatar-sm">
                        <span class="avatar-title rounded-circle bg-danger-subtle text-danger fs-4">
                            <i class="ri-delete-bin-line"></i>
                        </span>
                    </span>
                    <div>
                        <h5 class="modal-title fw-bold mb-1" id="titreSuppressionGroupee">Confirmer la suppression</h5>
                        <p class="text-muted small mb-0">Cette action ne pourra pas être annulée.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body py-4">
                <div class="rounded-3 bg-light p-3 text-center">
                    <div class="display-6 fw-bold text-danger" id="nbSuppressionGroupee">0</div>
                    <div class="text-muted">classe(s) sélectionnée(s)</div>
                </div>
                <p class="mb-0 mt-3">
                    Voulez-vous vraiment supprimer définitivement les classes sélectionnées ?
                </p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-danger" id="confirmerSuppressionGroupee">
                    <i class="ri-delete-bin-line me-1"></i>Supprimer les classes
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ===== Fenêtre de confirmation générique (fermeture de classe) ===== --}}
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

{{-- ===== Fenêtre « Créer / Modifier » ===== --}}
<div class="modal fade" id="modalClasse" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formClasse" method="POST" action="#" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_method" value="PUT" id="champMethode" disabled>
                <input type="hidden" name="_mode" id="champMode" value="{{ old('_mode') }}">
                <input type="hidden" name="_action" id="champAction" value="{{ old('_action') }}">

                <div class="modal-header">
                    <h5 class="modal-title" id="classeTitre">Créer une classe</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3" id="blocCours">
                        <label for="champCours" class="form-label">Cours</label>
                        <select name="cours_id" id="champCours" class="form-select" required>
                            <option value="">— Choisir —</option>
                            @foreach ($tousLesCours as $unCours)
                                <option value="{{ $unCours->id }}" @selected((string) old('cours_id') === (string) $unCours->id)>
                                    {{ $unCours->titre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="champNom" class="form-label">Nom de la classe</label>
                        <input type="text" name="nom" id="champNom" class="form-control" value="{{ old('nom') }}" required>
                    </div>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <label for="champDebut" class="form-label">Début</label>
                            <input type="date" name="date_debut" id="champDebut" class="form-control"
                                   value="{{ old('date_debut') }}" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label for="champFin" class="form-label">Fin</label>
                            <input type="date" name="date_fin" id="champFin" class="form-control"
                                   value="{{ old('date_fin') }}" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-4 mb-3">
                            <label for="champCapacite" class="form-label">Capacité</label>
                            <input type="number" name="capacite_max" id="champCapacite" min="1" max="255"
                                   class="form-control" value="{{ old('capacite_max', 10) }}" required>
                        </div>
                        <div class="col-8 mb-3">
                            <label for="champFormateur" class="form-label">Formateur</label>
                            <select name="formateur_id" id="champFormateur" class="form-select">
                                <option value="">— Aucun —</option>
                                @foreach ($tousLesFormateurs as $formateur)
                                    <option value="{{ $formateur->id }}" @selected((string) old('formateur_id') === (string) $formateur->id)>
                                        {{ $formateur->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="champLieu" class="form-label">Lieu</label>
                        <input type="text" name="lieu" id="champLieu" class="form-control"
                               value="{{ old('lieu', \App\Models\Classe::LIEU_PAR_DEFAUT) }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="champLien" class="form-label">Lien Google Maps (facultatif)</label>
                        <input type="url" name="lieu_lien" id="champLien" class="form-control"
                               placeholder="https://maps.app.goo.gl/..." value="{{ old('lieu_lien') }}">
                    </div>

                    <div class="mb-0">
                        <label for="champPhoto" class="form-label">Photo du lieu (facultatif)</label>
                        <input type="file" name="lieu_image" id="champPhoto" accept="image/*" class="form-control">
                        <div class="form-text">Taille maximale : 5 Mo.</div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const champ = id => document.getElementById(id);

    // ------------------------------------------------------------------
    // Sélection multiple : la barre d'actions apparaît dès qu'au moins une classe est cochée
    // ------------------------------------------------------------------
    const cases = Array.from(document.querySelectorAll('.case-classe'));
    const toutes = champ('toutSelectionner');
    const barre = champ('barreActions');
    const compteur = champ('nbSelection');
    const action = champ('actionChoisie');
    const formateur = champ('formateurCible');
    const formulaire = champ('formActions');
    const modalSuppressionGroupee = champ('modalSuppressionGroupee');
    let confirmerSuppressionGroupee = false;

    function majSelection() {
        const nb = cases.filter(c => c.checked).length;
        compteur.textContent = nb;
        barre.classList.toggle('d-none', nb === 0);
        toutes.checked = nb > 0 && nb === cases.length;
        toutes.indeterminate = nb > 0 && nb < cases.length;
    }

    cases.forEach(c => c.addEventListener('change', majSelection));

    toutes.addEventListener('change', function () {
        cases.forEach(c => c.checked = toutes.checked);
        majSelection();
    });

    champ('annulerSelection').addEventListener('click', function () {
        cases.forEach(c => c.checked = false);
        majSelection();
    });

    // Le menu du formateur n'apparaît que pour « Affecter un formateur »
    action.addEventListener('change', function () {
        formateur.classList.toggle('d-none', action.value !== 'affecter_formateur');
    });

    formulaire.addEventListener('submit', function (e) {
        const nb = cases.filter(c => c.checked).length;

        if (action.value === 'supprimer') {
            if (!confirmerSuppressionGroupee) {
                e.preventDefault();
                champ('nbSuppressionGroupee').textContent = nb;
                bootstrap.Modal.getOrCreateInstance(modalSuppressionGroupee).show();
            } else {
                confirmerSuppressionGroupee = false;
            }
        }

        if (action.value === 'fermer') {
            // La fenêtre de confirmation générique soumet le formulaire après accord
            formulaire.dataset.confirmation = 'Fermer ' + nb + ' classe(s) ? Les taux de présence seront figés. Action définitive.';
            formulaire.dataset.confirmationBouton = 'Fermer les classes';
        } else {
            delete formulaire.dataset.confirmation;
        }
    });

    champ('confirmerSuppressionGroupee').addEventListener('click', function () {
        confirmerSuppressionGroupee = true;
        formulaire.requestSubmit();
    });

    majSelection();

    // ------------------------------------------------------------------
    // Confirmation par fenêtre : tout formulaire portant data-confirmation (message)
    // et data-confirmation-bouton (texte du bouton) demande d'abord l'accord.
    // ------------------------------------------------------------------
    const modalConfirmation = champ('modalConfirmation');
    let formulaireEnAttente = null;

    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (!form.dataset.confirmation || form === formulaireEnAttente) return;
        if (e.defaultPrevented) return;

        e.preventDefault();
        formulaireEnAttente = form;
        champ('confirmationMessage').textContent = form.dataset.confirmation;
        champ('confirmationValider').textContent = form.dataset.confirmationBouton || 'Confirmer';

        // Une seule fenêtre à la fois : on ferme la fiche « Voir » si elle est ouverte
        const fiche = bootstrap.Modal.getInstance(champ('modalDetail'));
        if (fiche) {
            champ('modalDetail').addEventListener('hidden.bs.modal', function () {
                bootstrap.Modal.getOrCreateInstance(modalConfirmation).show();
            }, { once: true });
            fiche.hide();
        } else {
            bootstrap.Modal.getOrCreateInstance(modalConfirmation).show();
        }
    });

    champ('confirmationValider').addEventListener('click', function () {
        if (formulaireEnAttente) {
            // submit() ne redéclenche pas l'événement « submit » : pas de nouvelle demande
            formulaireEnAttente.submit();
        }
    });

    modalConfirmation.addEventListener('hidden.bs.modal', function () {
        formulaireEnAttente = null;
    });

    // ------------------------------------------------------------------
    // Fenêtre « Créer / Modifier »
    // ------------------------------------------------------------------
    const urlAjout = @json(route('admin.cours.classes.store', '__COURS__'));
    const lieuParDefaut = @json(\App\Models\Classe::LIEU_PAR_DEFAUT);
    const modeAvecErreur = @json($modeAvecErreur);
    const modalClasse = champ('modalClasse');
    const formClasse = champ('formClasse');

    // Adapte la fenêtre au mode : "ajout" (POST, cours à choisir) ou "modif" (PUT, cours fixe)
    function preparer(mode) {
        const modif = mode === 'modif';
        champ('classeTitre').textContent = modif ? 'Modifier une classe' : 'Créer une classe';
        champ('champMode').value = mode;
        champ('champMethode').disabled = !modif;
        champ('blocCours').classList.toggle('d-none', modif);
        champ('champCours').required = !modif;
    }

    // En création, l'adresse d'envoi dépend du cours choisi
    function actionAjout() {
        const cours = champ('champCours').value;
        champ('champAction').value = cours ? urlAjout.replace('__COURS__', cours) : '#';
        formClasse.action = champ('champAction').value;
    }

    champ('champCours').addEventListener('change', actionAjout);

    modalClasse.addEventListener('show.bs.modal', function (e) {
        const bouton = e.relatedTarget;
        if (!bouton) return;

        const d = bouton.dataset;
        preparer(d.mode);

        if (d.mode === 'modif') {
            champ('champAction').value = d.action;
            formClasse.action = d.action;
            champ('champNom').value = d.nom;
            champ('champCapacite').value = d.capacite;
            champ('champDebut').value = d.debut;
            champ('champFin').value = d.fin;
            champ('champFormateur').value = d.formateur || '';
            champ('champLieu').value = d.lieu;
            champ('champLien').value = d.lien || '';
        } else {
            champ('champCours').value = '';
            champ('champNom').value = '';
            champ('champCapacite').value = 10;
            champ('champDebut').value = '';
            champ('champFin').value = '';
            champ('champFormateur').value = '';
            champ('champLieu').value = lieuParDefaut;
            champ('champLien').value = '';
            actionAjout();
        }
        champ('champPhoto').value = '';
    });

    // Validation refusée : on rouvre la fenêtre avec ce que l'utilisateur avait saisi (déjà dans les champs)
    if (modeAvecErreur) {
        preparer(modeAvecErreur);
        formClasse.action = champ('champAction').value || '#';
        bootstrap.Modal.getOrCreateInstance(modalClasse).show();
    }

    // Fenêtre « Voir » : copie le bloc caché de la classe
    champ('modalDetail').addEventListener('show.bs.modal', function (e) {
        const bouton = e.relatedTarget;
        champ('detailTitre').textContent = bouton.dataset.titre;
        champ('detailCorps').innerHTML = champ(bouton.dataset.detail).innerHTML;
    });

    // Fenêtre de suppression : nom, adresse d'envoi et avertissement s'il y a des inscrits
    champ('modalSuppression').addEventListener('show.bs.modal', function (e) {
        const d = e.relatedTarget.dataset;
        const inscrits = parseInt(d.inscrits, 10);
        const payes = parseInt(d.payes, 10);
        const alerte = champ('suppressionAlerte');

        champ('suppressionNom').textContent = d.nom;
        champ('formSuppression').action = d.action;

        if (inscrits > 0) {
            alerte.textContent = inscrits + ' inscription(s) seront supprimées avec elle'
                + (payes > 0 ? ', dont ' + payes + ' déjà payée(s) (le paiement sera supprimé aussi).' : '.');
        }
        alerte.classList.toggle('d-none', inscrits === 0);
    });
});
</script>
@endsection