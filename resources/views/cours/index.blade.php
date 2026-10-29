@extends('layouts.master')

@section('title', 'Gestion des cours')

@section('content')

{{-- ===== Fil d'Ariane ===== --}}
@component('components.breadcrumb')
    @slot('li_1')
        <a href="{{ route('dashboard') }}">Accueil</a>
    @endslot
    @slot('title')
        Cours
    @endslot
@endcomponent

@php
    // Retour d'une validation qui a échoué : "_mode" (ajout ou modification) dit quelle fenêtre rouvrir
    $modeAvecErreur = $errors->any() ? old('_mode') : null;
@endphp

<div class="container-fluid pb-4">

    {{-- ===== Statistiques (totaux sur toute la base) ===== --}}
    <div class="row g-2 mb-3">
        <div class="col-sm-6 col-xl-4">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center gap-2 py-2 px-3">
                    <span class="avatar-sm flex-shrink-0">
                        <span class="avatar-title rounded-3 bg-primary-subtle text-primary fs-4"><i class="ri-book-open-line"></i></span>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-0">{{ $nombreCours }}</h5>
                        <p class="text-muted mb-0 fs-12">Cours</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-4">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center gap-2 py-2 px-3">
                    <span class="avatar-sm flex-shrink-0">
                        <span class="avatar-title rounded-3 bg-info-subtle text-info fs-4"><i class="ri-list-check-2"></i></span>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-0">{{ $nombreChapitres }}</h5>
                        <p class="text-muted mb-0 fs-12">Chapitres</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-4">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center gap-2 py-2 px-3">
                    <span class="avatar-sm flex-shrink-0">
                        <span class="avatar-title rounded-3 bg-success-subtle text-success fs-4"><i class="ri-group-line"></i></span>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-0">{{ $nombreClasses }}</h5>
                        <p class="text-muted mb-0 fs-12">Classes</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Erreurs de filtre (prix ou nombre négatif, maximum inférieur au minimum...) ===== --}}
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
        $sans = fn (array $cles) => route('admin.cours.index', request()->except(array_merge($cles, ['page'])));

        $libellesGroupe = ['categorie' => 'Catégorie'];

        // Étiquettes des filtres actifs : [texte, paramètres à retirer, icône]
        $etiquettes = [];
        if (request()->boolean('avec_classes_a_venir')) {
            $etiquettes[] = ['Avec classes à venir', ['avec_classes_a_venir'], 'ri-calendar-event-line'];
        }
        if (request()->boolean('sans_classe')) {
            $etiquettes[] = ['Sans classe', ['sans_classe'], 'ri-group-line'];
        }
        if (request()->boolean('sans_chapitre')) {
            $etiquettes[] = ['Sans chapitre', ['sans_chapitre'], 'ri-list-check-2'];
        }
        if (request()->boolean('sans_quiz')) {
            $etiquettes[] = ['Sans quiz', ['sans_quiz'], 'ri-questionnaire-line'];
        }
        if (request()->filled('categorie')) {
            $categorieChoisie = $toutesLesCategories->firstWhere('id', request('categorie'));
            $etiquettes[] = ['Catégorie : ' . ($categorieChoisie?->nom ?? request('categorie')), ['categorie'], 'ri-price-tag-3-line'];
        }
        if (request()->filled('classes_min') || request()->filled('classes_max')) {
            $etiquettes[] = ['Classes : ' . (request('classes_min') ?: '0') . ' à ' . (request('classes_max') ?: '∞'), ['classes_min', 'classes_max'], 'ri-group-line'];
        }
        if (request()->filled('prix_min') || request()->filled('prix_max')) {
            $etiquettes[] = ['Prix particulier : ' . (request('prix_min') ?: '0') . ' à ' . (request('prix_max') ?: '∞') . ' FCFA', ['prix_min', 'prix_max'], 'ri-money-dollar-circle-line'];
        }
        if ($groupe) {
            $etiquettes[] = ['Regroupé par ' . mb_strtolower($libellesGroupe[$groupe]), ['groupe'], 'ri-stack-line'];
        }
    @endphp

    {{-- ===== Barre d'actions groupées : cachée tant qu'aucun cours n'est coché.
         Les cases du tableau y sont rattachées par form="formGroupe". ===== --}}
    <form id="formGroupe" method="POST" action="{{ route('admin.cours.actions') }}">
        @csrf
        <div id="barreActions" class="card border shadow-sm mb-3 d-none">
            <div class="card-body d-flex flex-wrap align-items-center gap-2">
                <strong><span id="nbSelection">0</span> cours sélectionné(s)</strong>

                <select name="action" id="actionChoisie" class="form-select w-auto" aria-label="Action à appliquer">
                    <option value="">Choisir une action…</option>
                    <option value="changer_categorie">Changer de catégorie</option>
                    <option value="supprimer">Supprimer les cours</option>
                </select>

                {{-- Visible seulement pour l'action « Changer de catégorie » --}}
                <select name="categorie_id" id="categorieCible" class="form-select w-auto d-none" aria-label="Nouvelle catégorie">
                    <option value="">Choisir la catégorie…</option>
                    @foreach ($toutesLesCategories as $categorie)
                        <option value="{{ $categorie->id }}">{{ $categorie->nom }}</option>
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
                data-bs-toggle="modal" data-bs-target="#modalCours" data-mode="ajout">
            <i class="ri-add-line me-1"></i>Créer un cours
        </button>

        <form method="GET" action="{{ route('admin.cours.index') }}" class="col-12 col-md-8 col-lg-6 col-xl-5 ms-md-auto">
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
                           placeholder="Rechercher un cours (titre, catégorie, description)…" aria-label="Rechercher un cours">
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
                                    <i class="ri-equalizer-line text-primary me-2"></i>Filtres des cours
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
                                            <h6 class="fw-semibold mb-3"><i class="ri-filter-3-line text-primary me-1"></i>Contenu du cours</h6>

                                            @foreach ([
                                                'avec_classes_a_venir' => 'Avec classes à venir',
                                                'sans_classe' => 'Sans classe',
                                                'sans_chapitre' => 'Sans chapitre',
                                                'sans_quiz' => 'Sans quiz',
                                            ] as $nom => $libelle)
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" name="{{ $nom }}" value="1"
                                                           id="filtre-{{ $nom }}" @checked(request()->boolean($nom))>
                                                    <label class="form-check-label" for="filtre-{{ $nom }}">{{ $libelle }}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="card h-100 mb-0">
                                        <div class="card-body">
                                            <h6 class="fw-semibold mb-3"><i class="ri-stack-line text-primary me-1"></i>Regrouper par</h6>

                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="radio" name="groupe" value="" id="groupe-aucun" @checked(! $groupe)>
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

                                            <label for="filtre-categorie" class="form-label small mb-1">Catégorie</label>
                                            <select name="categorie" id="filtre-categorie" class="form-select form-select-sm mb-3" aria-label="Filtrer par catégorie">
                                                <option value="">Toutes les catégories</option>
                                                @foreach ($toutesLesCategories as $categorie)
                                                    <option value="{{ $categorie->id }}" @selected((string) request('categorie') === (string) $categorie->id)>{{ $categorie->nom }}</option>
                                                @endforeach
                                            </select>

                                            <label class="form-label small mb-1">Nombre de classes</label>
                                            <div class="row g-2 mb-3">
                                                <div class="col-6">
                                                    <input type="number" name="classes_min" min="0" value="{{ request('classes_min') }}"
                                                           class="form-control form-control-sm" placeholder="Minimum" aria-label="Classes minimum">
                                                </div>
                                                <div class="col-6">
                                                    <input type="number" name="classes_max" min="0" value="{{ request('classes_max') }}"
                                                           class="form-control form-control-sm" placeholder="Maximum" aria-label="Classes maximum">
                                                </div>
                                            </div>

                                            <label class="form-label small mb-1">Prix particulier (FCFA)</label>
                                            <div class="row g-2">
                                                <div class="col-6">
                                                    <input type="number" name="prix_min" min="0" value="{{ request('prix_min') }}"
                                                           class="form-control form-control-sm" placeholder="Minimum" aria-label="Prix minimum">
                                                </div>
                                                <div class="col-6">
                                                    <input type="number" name="prix_max" min="0" value="{{ request('prix_max') }}"
                                                           class="form-control form-control-sm" placeholder="Maximum" aria-label="Prix maximum">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer justify-content-between">
                            <a href="{{ route('admin.cours.index') }}" class="btn btn-outline-secondary">
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

    {{-- ===== Tableau des cours : le dernier créé en premier ===== --}}
    <div class="card border shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0 table-cours">
                <thead>
                    <tr class="table-light">
                        <th>
                            <input type="checkbox" id="toutSelectionner" class="form-check-input" aria-label="Tout sélectionner">
                        </th>
                        <th>Cours</th>
                        <th>Catégorie</th>
                        <th>Prix particulier</th>
                        <th>Prix entreprise</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- dernier groupe affiché : sert à n'écrire l'en-tête qu'au changement de groupe --}}
                    @php $groupePrecedent = null; @endphp
                    @forelse ($cours as $unCours)
                        @if ($groupe)
                            @php $cle = $cleGroupe($unCours); @endphp
                            @if ($cle !== $groupePrecedent)
                                @php $groupePrecedent = $cle; @endphp
                                <tr class="table-active">
                                    <td colspan="6" class="fw-semibold">
                                        <i class="ri-stack-line text-primary me-1"></i>{{ $cle !== '' ? $cle : 'Sans catégorie' }}
                                        <span class="badge bg-primary-subtle text-primary ms-1">{{ $comptesGroupes[$cle] ?? 0 }}</span>
                                    </td>
                                </tr>
                            @endif
                        @endif

                        <tr>
                            <td>
                                <input type="checkbox" name="ids[]" value="{{ $unCours->id }}" form="formGroupe"
                                       class="form-check-input case-cours" aria-label="Sélectionner {{ $unCours->titre }}">
                            </td>
                            <td>
                                <span class="fw-semibold">{{ $unCours->titre }}</span>
                            </td>
                            <td>{{ $unCours->categorie?->nom ?? '—' }}</td>
                            <td>{{ number_format($unCours->prix_particulier, 0, ',', ' ') }} FCFA</td>
                            <td>{{ number_format($unCours->prix_entreprise, 0, ',', ' ') }} FCFA</td>
                            <td class="text-end cours-actions">
                                <div class="d-flex justify-content-end gap-1">
                                    {{-- Voir : le contenu du bloc caché #detail-ID est copié dans la fenêtre --}}
                                    <button type="button" class="btn btn-sm btn-soft-primary" title="Voir les détails"
                                            data-bs-toggle="modal" data-bs-target="#modalDetail"
                                            data-detail="detail-{{ $unCours->id }}"
                                            data-titre="{{ $unCours->titre }}">
                                        <i class="ri-eye-line"></i>
                                    </button>

                                    {{-- Modifier : les valeurs actuelles voyagent dans les data-* du bouton --}}
                                    <button type="button" class="btn btn-sm btn-soft-secondary" title="Modifier le cours"
                                            data-bs-toggle="modal" data-bs-target="#modalCours"
                                            data-mode="modif"
                                            data-action="{{ route('admin.cours.update', $unCours) }}"
                                            data-titre="{{ $unCours->titre }}"
                                            data-categorie="{{ $unCours->categorie_id }}"
                                            data-prix-particulier="{{ (int) $unCours->prix_particulier }}"
                                            data-prix-entreprise="{{ (int) $unCours->prix_entreprise }}"
                                            data-description="{{ $unCours->description }}"
                                            data-image="{{ $unCours->image ? '1' : '' }}">
                                        <i class="ri-edit-line"></i>
                                    </button>

                                    {{-- Supprimer : demande confirmation dans une fenêtre --}}
                                    <button type="button" class="btn btn-sm btn-soft-danger" title="Supprimer le cours"
                                            data-bs-toggle="modal" data-bs-target="#modalSuppression"
                                            data-action="{{ route('admin.cours.destroy', $unCours) }}"
                                            data-titre="{{ $unCours->titre }}"
                                            data-classes="{{ $unCours->classes_count }}">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="ri-book-open-line display-5 d-block mb-2"></i>
                                Aucun cours ne correspond à votre recherche.
                                @if ($etiquettes || request()->filled('q'))
                                    <a href="{{ route('admin.cours.index') }}" class="d-block mt-2">Réinitialiser les filtres</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($cours->total() > 0)
            <div class="card-footer bg-transparent d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <small class="text-muted">
                    Affichage de {{ $cours->firstItem() }} à {{ $cours->lastItem() }}
                    sur {{ $cours->total() }} résultat(s)
                </small>
                {{ $cours->links() }}
            </div>
        @endif
    </div>

    {{-- Contenus cachés des fenêtres « Voir » (un par cours, copiés par le script) --}}
    @foreach ($cours as $unCours)
        <div id="detail-{{ $unCours->id }}" class="d-none">
            <dl class="row mb-3">
                <dt class="col-sm-4">Catégorie</dt>
                <dd class="col-sm-8">{{ $unCours->categorie?->nom ?? '—' }}</dd>

                <dt class="col-sm-4">Prix particulier</dt>
                <dd class="col-sm-8">{{ number_format($unCours->prix_particulier, 0, ',', ' ') }} FCFA</dd>

                <dt class="col-sm-4">Prix entreprise</dt>
                <dd class="col-sm-8">{{ number_format($unCours->prix_entreprise, 0, ',', ' ') }} FCFA</dd>

                <dt class="col-sm-4">Chapitres</dt>
                <dd class="col-sm-8">{{ $unCours->chapitres_count }}</dd>

                <dt class="col-sm-4">Classes</dt>
                <dd class="col-sm-8">{{ $unCours->classes_count }} (dont {{ $unCours->classes_a_venir_count }} à venir)</dd>

                <dt class="col-sm-4">Description</dt>
                <dd class="col-sm-8">{{ $unCours->description ?: '—' }}</dd>
            </dl>

            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('admin.cours.contenus', $unCours) }}" class="btn btn-sm btn-light">Chapitres et ressources</a>
                <a href="{{ route('admin.classes.index', ['cours_id' => $unCours->id]) }}" class="btn btn-sm btn-light">Classes de ce cours</a>
            </div>
        </div>
    @endforeach
</div>

{{-- ===== Fenêtre « Voir » ===== --}}
<div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailTitre"></h5>
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
                    <h5 class="modal-title">Supprimer le cours</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Voulez-vous vraiment supprimer le cours <strong id="suppressionNom"></strong> ?</p>
                    {{-- Affiché seulement si le cours a des classes : elles disparaissent avec lui --}}
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
<div class="modal fade" id="modalSuppressionGroupee" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Supprimer les cours sélectionnés</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Voulez-vous vraiment supprimer <strong id="nbSuppressionGroupee">0</strong> cours ?</p>
                <p class="text-danger mb-2">Leurs classes et les inscriptions de ces classes seront supprimées aussi. Un cours dont une inscription est déjà payée est ignoré : supprimez-le depuis sa ligne.</p>
                <p class="text-muted mb-0">Cette action est définitive.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-danger" id="confirmerSuppressionGroupee">Supprimer</button>
            </div>
        </div>
    </div>
</div>

{{-- ===== Fenêtre « Créer / Modifier » (un seul formulaire pour les deux) ===== --}}
<div class="modal fade" id="modalCours" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formCours" method="POST" action="{{ route('admin.cours.store') }}" enctype="multipart/form-data">
                @csrf
                {{-- Champs techniques : _method = PUT seulement en modification ; _mode et _action
                     permettent de rouvrir la bonne fenêtre si la validation échoue --}}
                <input type="hidden" name="_method" value="PUT" id="champMethode" disabled>
                <input type="hidden" name="_mode" id="champMode" value="{{ old('_mode') }}">
                <input type="hidden" name="_action" id="champAction" value="{{ old('_action') }}">

                <div class="modal-header">
                    <h5 class="modal-title" id="coursTitre">Créer un cours</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="champTitre" class="form-label">Titre</label>
                        <input type="text" name="titre" id="champTitre" class="form-control" value="{{ old('titre') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="champCategorie" class="form-label">Catégorie</label>
                        <select name="categorie_id" id="champCategorie" class="form-select" required>
                            <option value="">— Choisir —</option>
                            @foreach ($toutesLesCategories as $categorie)
                                <option value="{{ $categorie->id }}" @selected((string) old('categorie_id') === (string) $categorie->id)>
                                    {{ $categorie->nom }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">
                            Catégorie absente ? <a href="{{ route('admin.categories.index') }}">La créer</a>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <label for="champPrixParticulier" class="form-label">Prix particulier (FCFA)</label>
                            <input type="number" name="prix_particulier" id="champPrixParticulier" min="0" step="1"
                                   class="form-control" value="{{ old('prix_particulier') }}" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label for="champPrixEntreprise" class="form-label">Prix entreprise (FCFA)</label>
                            <input type="number" name="prix_entreprise" id="champPrixEntreprise" min="0" step="1"
                                   class="form-control" value="{{ old('prix_entreprise') }}" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="champDescription" class="form-label">Description (facultatif)</label>
                        <textarea name="description" id="champDescription" rows="3" class="form-control">{{ old('description') }}</textarea>
                    </div>

                    <div class="mb-0">
                        <label for="champImage" class="form-label">Image (facultatif)</label>
                        <input type="file" name="image" id="champImage" accept="image/png,image/jpeg,image/webp,image/gif"
                               class="form-control">
                        {{-- Seulement en modification, quand le cours a déjà une image --}}
                        <div class="form-check mt-2 d-none" id="blocSupprimerImage">
                            <input class="form-check-input" type="checkbox" name="supprimer_image" value="1" id="champSupprimerImage">
                            <label class="form-check-label" for="champSupprimerImage">Supprimer l'image actuelle</label>
                        </div>
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
    const urlAjout = @json(route('admin.cours.store'));
    const modeAvecErreur = @json($modeAvecErreur);

    const modalCours = document.getElementById('modalCours');
    const form = document.getElementById('formCours');
    const champ = id => document.getElementById(id);

    // Sélection multiple : la barre d'actions apparaît dès qu'au moins un cours est coché
    const cases = Array.from(document.querySelectorAll('.case-cours'));
    const toutes = champ('toutSelectionner');
    const barre = champ('barreActions');

    function majSelection() {
        const nb = cases.filter(c => c.checked).length;
        champ('nbSelection').textContent = nb;
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

    // Actions groupées : le menu de catégorie n'apparaît que pour « Changer de catégorie »
    const action = champ('actionChoisie');
    action.addEventListener('change', function () {
        champ('categorieCible').classList.toggle('d-none', action.value !== 'changer_categorie');
    });

    // « Supprimer » demande d'abord confirmation dans une fenêtre, puis envoie le formulaire
    champ('formGroupe').addEventListener('submit', function (e) {
        if (action.value === 'supprimer' && !champ('formGroupe').dataset.confirme) {
            e.preventDefault();
            champ('nbSuppressionGroupee').textContent = cases.filter(c => c.checked).length;
            bootstrap.Modal.getOrCreateInstance(champ('modalSuppressionGroupee')).show();
        }
    });
    champ('confirmerSuppressionGroupee').addEventListener('click', function () {
        champ('formGroupe').dataset.confirme = '1';
        champ('formGroupe').requestSubmit();
    });

    majSelection();

    // Adapte la fenêtre au mode : "ajout" (POST) ou "modif" (PUT)
    function preparer(mode) {
        const modif = mode === 'modif';
        champ('coursTitre').textContent = modif ? 'Modifier un cours' : 'Créer un cours';
        champ('champMode').value = mode;
        champ('champMethode').disabled = !modif;
        if (!modif) champ('blocSupprimerImage').classList.add('d-none');
    }

    // Ouverture par un bouton : on remplit le formulaire (sauf retour d'erreur, géré plus bas)
    modalCours.addEventListener('show.bs.modal', function (e) {
        const bouton = e.relatedTarget;
        if (!bouton) return;

        const d = bouton.dataset;
        preparer(d.mode);

        if (d.mode === 'modif') {
            champ('champAction').value = d.action;
            form.action = d.action;
            champ('champTitre').value = d.titre;
            champ('champCategorie').value = d.categorie || '';
            champ('champPrixParticulier').value = d.prixParticulier;
            champ('champPrixEntreprise').value = d.prixEntreprise;
            champ('champDescription').value = d.description || '';
            champ('blocSupprimerImage').classList.toggle('d-none', !d.image);
        } else {
            champ('champAction').value = urlAjout;
            form.action = urlAjout;
            champ('champTitre').value = '';
            champ('champCategorie').value = '';
            champ('champPrixParticulier').value = '';
            champ('champPrixEntreprise').value = '';
            champ('champDescription').value = '';
        }
        champ('champImage').value = '';
        champ('champSupprimerImage').checked = false;
    });

    // Validation refusée : on rouvre la fenêtre avec ce que l'utilisateur avait saisi (déjà dans les champs)
    if (modeAvecErreur) {
        preparer(modeAvecErreur);
        form.action = champ('champAction').value || urlAjout;
        bootstrap.Modal.getOrCreateInstance(modalCours).show();
    }

    // Fenêtre de suppression : titre du cours, adresse d'envoi et avertissement s'il a des classes
    document.getElementById('modalSuppression').addEventListener('show.bs.modal', function (e) {
        const d = e.relatedTarget.dataset;
        const classes = parseInt(d.classes, 10);
        const alerte = champ('suppressionAlerte');

        champ('suppressionNom').textContent = d.titre;
        champ('formSuppression').action = d.action;

        if (classes > 0) {
            alerte.textContent = classes + ' classe(s) seront supprimées avec lui, ainsi que leurs inscriptions et leurs paiements.';
        }
        alerte.classList.toggle('d-none', classes === 0);
    });

    // Fenêtre « Voir » : copie le bloc caché du cours
    document.getElementById('modalDetail').addEventListener('show.bs.modal', function (e) {
        const bouton = e.relatedTarget;
        champ('detailTitre').textContent = bouton.dataset.titre;
        champ('detailCorps').innerHTML = champ(bouton.dataset.detail).innerHTML;
    });
});
</script>
@endsection