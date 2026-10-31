@extends('layouts.master')

@section('title', 'Gestion des formateurs')

@section('content')

{{-- ===== Fil d'Ariane ===== --}}
@component('components.breadcrumb')
    @slot('li_1')
        <a href="{{ route('dashboard') }}">Accueil</a>
    @endslot
    @slot('title')
        Formateurs
    @endslot
@endcomponent

@php
    // Retour d'une validation qui a échoué : "_mode" (ajout ou modification) dit quelle fenêtre rouvrir
    $modeAvecErreur = $errors->any() ? old('_mode') : null;

    $libellesSituation = ['classes_en_cours' => 'Avec des classes en cours', 'sans_classe' => 'Sans classe affectée'];
@endphp

<div class="container-fluid pb-4">

    {{-- ===== Statistiques ===== --}}
    <div class="row g-2 mb-3">
        <div class="col-sm-6 col-xl-3">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center gap-2 py-2 px-3">
                    <span class="avatar-sm flex-shrink-0">
                        <span class="avatar-title rounded-3 bg-primary-subtle text-primary fs-4"><i class="ri-user-star-line"></i></span>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-0">{{ $totalFormateurs }}</h5>
                        <p class="text-muted mb-0 fs-12">Formateurs</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center gap-2 py-2 px-3">
                    <span class="avatar-sm flex-shrink-0">
                        <span class="avatar-title rounded-3 bg-warning-subtle text-warning fs-4"><i class="ri-group-line"></i></span>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-0">{{ $classesSansFormateur }}</h5>
                        <p class="text-muted mb-0 fs-12">Classes ouvertes sans formateur</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Étiquettes des filtres actifs ===== --}}
    @php
        // URL de la liste sans certains paramètres (sert au "×" de chaque étiquette)
        $sans = fn (array $cles) => route('admin.formateurs.index', request()->except(array_merge($cles, ['page'])));

        // Étiquettes des filtres actifs : [texte, paramètres à retirer, icône]
        $etiquettes = [];
        if (isset($libellesSituation[request('situation')])) {
            $etiquettes[] = [$libellesSituation[request('situation')], ['situation'], 'ri-filter-3-line'];
        }
    @endphp

    {{-- ===== Barre de suppression groupée : cachée tant qu'aucun formateur n'est coché.
         Les cases du tableau y sont rattachées par form="formGroupe". ===== --}}
    <form id="formGroupe" method="POST" action="{{ route('admin.formateurs.destroy-multiple') }}">
        @csrf
        <div id="barreActions" class="card border shadow-sm mb-3 d-none">
            <div class="card-body d-flex flex-wrap align-items-center gap-2">
                <strong><span id="nbSelection">0</span> formateur(s) sélectionné(s)</strong>
                <button type="button" class="btn btn-danger" id="supprimerSelection">
                    <i class="ri-delete-bin-line me-1"></i>Supprimer
                </button>
                <button type="button" id="annulerSelection" class="btn btn-outline-secondary">Désélectionner</button>
            </div>
        </div>
    </form>

    {{-- ===== Même ligne : bouton de création à gauche, recherche et filtres à droite ===== --}}
    <div class="row g-2 justify-content-between align-items-start mb-3">
        <div class="col-auto">
            <button type="button" class="btn btn-primary fw-semibold"
                    data-bs-toggle="modal" data-bs-target="#modalFormateur" data-mode="ajout">
                <i class="ri-add-line me-1"></i>Créer un formateur
            </button>
        </div>

        <div class="col-12 col-lg-8 col-xl-6">
            <form method="GET" action="{{ route('admin.formateurs.index') }}">
                <div class="d-flex gap-2 align-items-start">
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
                               placeholder="Rechercher un formateur (nom ou e-mail)…" aria-label="Rechercher un formateur">
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
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title fw-semibold" id="titreFiltres">
                                    <i class="ri-equalizer-line text-primary me-2"></i>Filtres des formateurs
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                            </div>

                            <div class="modal-body">
                                <h6 class="fw-semibold mb-3"><i class="ri-filter-3-line text-primary me-1"></i>Situation</h6>

                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="situation" value="" id="situation-toutes"
                                           @checked(! isset($libellesSituation[request('situation')]))>
                                    <label class="form-check-label" for="situation-toutes">Tous les formateurs</label>
                                </div>
                                @foreach ($libellesSituation as $valeur => $libelle)
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="situation" value="{{ $valeur }}"
                                               id="situation-{{ $valeur }}" @checked(request('situation') === $valeur)>
                                        <label class="form-check-label" for="situation-{{ $valeur }}">{{ $libelle }}</label>
                                    </div>
                                @endforeach
                            </div>

                            <div class="modal-footer justify-content-between">
                                <a href="{{ route('admin.formateurs.index') }}" class="btn btn-outline-secondary">
                                    <i class="ri-refresh-line me-1"></i>Réinitialiser
                                </a>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ri-check-line me-1"></i>Appliquer
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <button type="submit" class="visually-hidden" tabindex="-1" aria-hidden="true">Rechercher</button>
            </form>
        </div>
    </div>

    {{-- ===== Tableau des formateurs : le dernier créé en premier ===== --}}
    <div class="card border shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0">
                <thead>
                    <tr class="table-light">
                        <th>
                            <input type="checkbox" id="toutSelectionner" class="form-check-input" aria-label="Tout sélectionner">
                        </th>
                        <th>Nom</th>
                        <th>E-mail</th>
                        <th>Classes</th>
                        <th>En cours</th>
                        <th>Note moyenne</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($formateurs as $formateur)
                        {{-- Statistiques d'avis de ce formateur (calculées en une requête dans le contrôleur) --}}
                        @php $stats = $statsAvis->get($formateur->id); @endphp
                        <tr>
                            <td>
                                <input type="checkbox" name="ids[]" value="{{ $formateur->id }}" form="formGroupe"
                                       class="form-check-input case-formateur" aria-label="Sélectionner {{ $formateur->name }}">
                            </td>
                            <td class="fw-semibold cellule-tronquee" title="{{ $formateur->name }}">
                                <a href="{{ route('admin.formateurs.index', $formateur) }}" class="text-reset">{{ $formateur->name }}</a>
                            </td>
                            <td class="text-muted cellule-tronquee-lg" title="{{ $formateur->email }}">{{ $formateur->email }}</td>
                            <td>{{ $formateur->classes_animes_count }}</td>
                            <td>
                                @if ($formateur->classes_en_cours_count > 0)
                                    <span class="badge bg-success-subtle text-success">{{ $formateur->classes_en_cours_count }}</span>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>
                            <td class="text-nowrap">
                                @if ($stats)
                                    @include('cours.partials.etoiles', ['note' => $stats->moyenne, 'taille' => 'fs-6'])
                                    <span class="ms-1 text-muted small">{{ number_format($stats->moyenne, 1, ',', '') }} ({{ $stats->nb }} avis)</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end text-nowrap">
                                <div class="d-flex justify-content-end gap-1">
                                    {{-- Voir : classes et avis (modèle caché #detail-ID copié dans la fenêtre) --}}
                                    <button type="button" class="btn btn-sm btn-soft-primary" title="Voir les classes et les avis"
                                            data-bs-toggle="modal" data-bs-target="#modalDetail"
                                            data-detail="detail-{{ $formateur->id }}"
                                            data-titre="{{ $formateur->name }}">
                                        <i class="ri-eye-line"></i>
                                    </button>

                                    {{-- Modifier : les valeurs actuelles voyagent dans les data-* du bouton --}}
                                    <button type="button" class="btn btn-sm btn-soft-secondary" title="Modifier le formateur"
                                            data-bs-toggle="modal" data-bs-target="#modalFormateur"
                                            data-mode="modif"
                                            data-action="{{ route('admin.formateurs.update', $formateur) }}"
                                            data-nom="{{ $formateur->name }}"
                                            data-email="{{ $formateur->email }}">
                                        <i class="ri-edit-line"></i>
                                    </button>

                                    {{-- Supprimer : la fenêtre confirme, ou explique pourquoi c'est impossible (classes non fermées) --}}
                                    <button type="button" class="btn btn-sm btn-soft-danger" title="Supprimer le formateur"
                                            data-bs-toggle="modal" data-bs-target="#modalSuppression"
                                            data-action="{{ route('admin.formateurs.destroy', $formateur) }}"
                                            data-nom="{{ $formateur->name }}"
                                            data-classes-ouvertes="{{ (int) $formateur->classes_ouvertes_count }}">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="ri-user-star-line display-5 d-block mb-2"></i>
                                Aucun formateur ne correspond à votre recherche.
                                @if ($etiquettes || request()->filled('q'))
                                    <a href="{{ route('admin.formateurs.index') }}" class="d-block mt-2">Réinitialiser les filtres</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($formateurs->total() > 0)
            <div class="card-footer bg-transparent d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <small class="text-muted">
                    Affichage de {{ $formateurs->firstItem() }} à {{ $formateurs->lastItem() }}
                    sur {{ $formateurs->total() }} résultat(s)
                </small>
                {{ $formateurs->links() }}
            </div>
        @endif
    </div>

    {{-- Contenus des fenêtres « Voir » : un modèle <template> par formateur.
         Un <template> n'est pas dans la page : les identifiants des onglets ne sont jamais en double. --}}
    @foreach ($formateurs as $formateur)
        @php
            $classes = $classesParFormateur->get($formateur->id, collect());
            $avis = $avisParFormateur->get($formateur->id, collect());
            $moyennes = \App\Models\AvisFormateur::moyennes($avis);
            // Classes qu'on peut lui affecter : toutes celles ouvertes, sauf celles qu'il anime déjà
            $affectables = $classesAffectables->filter(fn ($c) => $c->formateur_id !== $formateur->id);
        @endphp
        <template id="detail-{{ $formateur->id }}">
            <p class="mb-3">
                <i class="ri-mail-line me-1"></i>{{ $formateur->email }}
                @if ($formateur->email_verified_at)
                    <span class="badge bg-success-subtle text-success ms-2">E-mail vérifié</span>
                @else
                    <span class="badge bg-warning-subtle text-warning ms-2">E-mail non vérifié</span>
                @endif
            </p>

            <ul class="nav nav-tabs mb-3" role="tablist">
                <li class="nav-item" role="presentation">
                    <button type="button" class="nav-link active" data-bs-toggle="tab"
                            data-bs-target="#onglet-classes-{{ $formateur->id }}" role="tab">
                        Classes <span class="badge bg-primary-subtle text-primary ms-1">{{ $classes->count() }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button type="button" class="nav-link" data-bs-toggle="tab"
                            data-bs-target="#onglet-avis-{{ $formateur->id }}" role="tab">
                        Avis <span class="badge bg-primary-subtle text-primary ms-1">{{ $avis->count() }}</span>
                    </button>
                </li>
            </ul>

            <div class="tab-content">
                {{-- ===== Classes affectées + affectation ===== --}}
                <div class="tab-pane fade show active" id="onglet-classes-{{ $formateur->id }}" role="tabpanel">
                    <form action="{{ route('admin.formateurs.classes.store', $formateur) }}" method="POST" class="d-flex flex-wrap gap-2 mb-3">
                        @csrf
                        <select name="classe_id" class="form-select w-auto flex-grow-1" required aria-label="Classe à affecter">
                            <option value="">— Affecter une classe —</option>
                            @foreach ($affectables as $uneClasse)
                                <option value="{{ $uneClasse->id }}">
                                    {{ $uneClasse->cours->titre }} · {{ $uneClasse->nom }}
                                    ({{ $uneClasse->date_debut->format('d/m/Y') }} → {{ $uneClasse->date_fin->format('d/m/Y') }})
                                    @if ($uneClasse->formateur) — actuellement : {{ $uneClasse->formateur->name }} @endif
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-primary" @disabled($affectables->isEmpty())>
                            <i class="ri-links-line me-1"></i>Affecter
                        </button>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Classe</th>
                                    <th>Cours</th>
                                    <th>Dates</th>
                                    <th>État</th>
                                    <th>Inscrits payés</th>
                                    <th class="text-end">Retirer</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($classes as $uneClasse)
                                    <tr>
                                        <td class="fw-medium">{{ $uneClasse->nom }}</td>
                                        <td>{{ $uneClasse->cours->titre }}</td>
                                        <td class="text-nowrap">{{ $uneClasse->date_debut->format('d/m/Y') }} → {{ $uneClasse->date_fin->format('d/m/Y') }}</td>
                                        <td><span class="badge bg-{{ $uneClasse->etat_couleur }}-subtle text-{{ $uneClasse->etat_couleur }}">{{ $uneClasse->etat_libelle }}</span></td>
                                        <td>{{ $uneClasse->inscrits_payes_count }} / {{ $uneClasse->capacite_max }}</td>
                                        <td class="text-end">
                                            @if ($uneClasse->estFermee())
                                                <span class="btn btn-sm btn-soft-secondary disabled" title="Classe fermée : affectation figée">
                                                    <i class="ri-lock-line"></i>
                                                </span>
                                            @else
                                                {{-- Retirer le formateur de cette classe (la classe reste, sans formateur) --}}
                                                <form action="{{ route('admin.formateurs.classes.destroy', [$formateur, $uneClasse]) }}" method="POST"
                                                      class="d-inline"
                                                      data-confirmation="Retirer {{ $formateur->name }} de la classe « {{ $uneClasse->nom }} » ? La classe restera sans formateur."
                                                      data-confirmation-bouton="Retirer">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-soft-danger" title="Retirer le formateur de cette classe">
                                                        <i class="ri-link-unlink-m"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-4">Aucune classe affectée pour le moment.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- ===== Avis anonymes des stagiaires (ni nom, ni e-mail : user_id n'est même pas chargé) ===== --}}
                <div class="tab-pane fade" id="onglet-avis-{{ $formateur->id }}" role="tabpanel">
                    <p class="text-muted small mb-3"><i class="ri-eye-off-line me-1"></i>Avis anonymes : les stagiaires ne sont jamais identifiés.</p>

                    @if ($avis->isEmpty())
                        <p class="text-center text-muted py-4 mb-0">Aucun avis pour le moment. Les stagiaires peuvent en donner un à la fin de la formation.</p>
                    @else
                        <div class="row g-4">
                            <div class="col-lg-4">
                                <div class="text-center mb-3">
                                    <div class="display-6 fw-bold">
                                        {{ number_format($moyennes['globale'], 1, ',', '') }}<small class="fs-5 fw-normal text-muted"> / 5</small>
                                    </div>
                                    @include('cours.partials.etoiles', ['note' => $moyennes['globale'], 'taille' => 'fs-4'])
                                    <p class="text-muted mt-1 mb-0">{{ $avis->count() }} avis</p>
                                </div>
                                @include('evaluations.partials.lignes-notes', [
                                    'criteres' => \App\Models\AvisFormateur::CRITERES,
                                    'source' => $moyennes,
                                ])
                            </div>

                            <div class="col-lg-8">
                                {{-- Les 20 avis les plus récents (la moyenne ci-contre porte sur tous) --}}
                                @foreach ($avis->take(20) as $unAvis)
                                    <div class="border rounded-3 p-3 mb-3">
                                        <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                                            <div class="text-muted small">{{ $unAvis->classe->cours->titre }} · {{ $unAvis->classe->nom }}</div>
                                            <small class="text-muted text-nowrap">{{ $unAvis->created_at->format('d/m/Y') }}</small>
                                        </div>

                                        @include('evaluations.partials.lignes-notes', [
                                            'criteres' => \App\Models\AvisFormateur::CRITERES,
                                            'source' => $unAvis,
                                        ])

                                        @if ($unAvis->commentaire)
                                            {{-- e() échappe le HTML, puis nl2br conserve les retours à la ligne --}}
                                            <p class="text-muted mt-2 mb-0">{!! nl2br(e($unAvis->commentaire)) !!}</p>
                                        @endif
                                    </div>
                                @endforeach
                                @if ($avis->count() > 20)
                                    <p class="text-muted small mb-0">20 avis affichés sur {{ $avis->count() }}.</p>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </template>
    @endforeach
</div>

{{-- ===== Fenêtre « Voir » ===== --}}
<div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailTitre"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body" id="detailCorps"></div>
        </div>
    </div>
</div>

{{-- ===== Fenêtre de confirmation générique (retrait d'une classe) : remplace le confirm() du navigateur ===== --}}
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

{{-- ===== Fenêtre de confirmation de suppression ===== --}}
<div class="modal fade" id="modalSuppression" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="formSuppression" method="POST" action="#">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title">Supprimer le formateur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    {{-- Aucune classe ouverte : confirmation simple --}}
                    <div id="suppressionConfirmation">
                        <p class="mb-2">Voulez-vous vraiment supprimer <strong id="suppressionNom"></strong> ?</p>
                        <p class="text-muted mb-0">Ses classes fermées seront conservées, sans formateur. Cette action est définitive.</p>
                    </div>

                    {{-- Des classes non fermées lui sont affectées : suppression impossible --}}
                    <div id="suppressionBloquee" class="d-none">
                        <p class="mb-2"><strong id="suppressionNomBloquee"></strong> ne peut pas être supprimé : <span id="suppressionNombre"></span> classe(s) non fermée(s) lui sont encore affectées.</p>
                        <p class="text-muted mb-0">Ouvrez sa fiche (œil), onglet « Classes », et retirez-le de ces classes ou réaffectez-les, puis revenez le supprimer.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fermer</button>
                    <button type="submit" class="btn btn-danger" id="suppressionValider">Supprimer</button>
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
                <h5 class="modal-title">Supprimer les formateurs sélectionnés</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Voulez-vous vraiment supprimer <strong id="nbSuppressionGroupee">0</strong> formateur(s) ?</p>
                <p class="text-muted mb-2">Un formateur qui a encore des classes non fermées est ignoré : retirez-le d'abord de ces classes.</p>
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
<div class="modal fade" id="modalFormateur" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formFormateur" method="POST" action="{{ route('admin.formateurs.store') }}" novalidate>
                @csrf
                {{-- Champs techniques : _method = PUT seulement en modification ; _mode et _action
                     permettent de rouvrir la bonne fenêtre si la validation échoue --}}
                <input type="hidden" name="_method" value="PUT" id="champMethode" disabled>
                <input type="hidden" name="_mode" id="champMode" value="{{ old('_mode') }}">
                <input type="hidden" name="_action" id="champAction" value="{{ old('_action') }}">

                <div class="modal-header">
                    <h5 class="modal-title" id="formateurTitre">Créer un formateur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>

                <div class="modal-body">
                    @if ($modeAvecErreur)
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $erreur)
                                    <li>{{ $erreur }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label for="champNom" class="form-label">Nom complet</label>
                        <input type="text" name="name" id="champNom" class="form-control" value="{{ old('name') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="champEmail" class="form-label">Adresse e-mail</label>
                        <input type="email" name="email" id="champEmail" class="form-control" value="{{ old('email') }}" required>
                        <div class="form-text" id="noteEmail">L'adresse est considérée comme vérifiée : pas de code à saisir.</div>
                    </div>

                    {{-- Création : pas de saisie, le compte reçoit le mot de passe par défaut (à changer à la 1re connexion) --}}
                    <div class="alert alert-info py-2 small" id="noteMotDePasseDefaut">
                        Mot de passe temporaire : <strong>{{ \App\Models\User::MOT_DE_PASSE_PAR_DEFAUT }}</strong>.
                        L'utilisateur devra le changer à sa première connexion.
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
<script src="{{ URL::asset('build/js/pages/password-addon.init.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const urlAjout = @json(route('admin.formateurs.store'));
    const modeAvecErreur = @json($modeAvecErreur);

    const modalFormateur = document.getElementById('modalFormateur');
    const form = document.getElementById('formFormateur');
    const champ = id => document.getElementById(id);

    // Sélection multiple : la barre de suppression apparaît dès qu'au moins un formateur est coché
    const cases = Array.from(document.querySelectorAll('.case-formateur'));
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

    // Suppression groupée : fenêtre de confirmation, puis envoi du formulaire
    champ('supprimerSelection').addEventListener('click', function () {
        champ('nbSuppressionGroupee').textContent = cases.filter(c => c.checked).length;
        bootstrap.Modal.getOrCreateInstance(champ('modalSuppressionGroupee')).show();
    });
    champ('confirmerSuppressionGroupee').addEventListener('click', function () {
        champ('formGroupe').submit();
    });

    majSelection();

    // Adapte la fenêtre au mode : "ajout" (POST) ou "modif" (PUT, mot de passe facultatif)
    function preparer(mode) {
        const modif = mode === 'modif';
        champ('formateurTitre').textContent = modif ? 'Modifier le formateur' : 'Créer un formateur';
        champ('champMode').value = mode;
        champ('champMethode').disabled = !modif;
        champ('noteMotDePasse').classList.toggle('d-none', !modif);
        champ('noteEmail').classList.toggle('d-none', modif);
    }

    // Ouverture par un bouton : on remplit le formulaire (sauf retour d'erreur, géré plus bas)
    modalFormateur.addEventListener('show.bs.modal', function (e) {
        const bouton = e.relatedTarget;
        if (!bouton) return;

        const d = bouton.dataset;
        preparer(d.mode);
        champ('champMotDePasse').value = '';
        champ('champConfirmation').value = '';

        if (d.mode === 'modif') {
            champ('champAction').value = d.action;
            form.action = d.action;
            champ('champNom').value = d.nom;
            champ('champEmail').value = d.email;
        } else {
            champ('champAction').value = urlAjout;
            form.action = urlAjout;
            champ('champNom').value = '';
            champ('champEmail').value = '';
        }
    });

    // Validation refusée : on rouvre la fenêtre avec ce qui avait été saisi (déjà dans les champs)
    if (modeAvecErreur) {
        preparer(modeAvecErreur);
        form.action = champ('champAction').value || urlAjout;
        bootstrap.Modal.getOrCreateInstance(modalFormateur).show();
    }

    // Fenêtre de suppression : confirme, ou explique pourquoi c'est impossible
    champ('modalSuppression').addEventListener('show.bs.modal', function (e) {
        const d = e.relatedTarget.dataset;
        const nombre = parseInt(d.classesOuvertes, 10);
        const bloquee = nombre > 0;

        champ('suppressionNom').textContent = d.nom;
        champ('suppressionNomBloquee').textContent = d.nom;
        champ('suppressionNombre').textContent = nombre;
        champ('formSuppression').action = d.action;

        champ('suppressionConfirmation').classList.toggle('d-none', bloquee);
        champ('suppressionBloquee').classList.toggle('d-none', !bloquee);
        champ('suppressionValider').classList.toggle('d-none', bloquee);
    });

    // Fenêtre « Voir » : copie le modèle <template> du formateur
    champ('modalDetail').addEventListener('show.bs.modal', function (e) {
        const bouton = e.relatedTarget;
        champ('detailTitre').textContent = bouton.dataset.titre;
        champ('detailCorps').replaceChildren(champ(bouton.dataset.detail).content.cloneNode(true));
    });

    // Confirmation par fenêtre : tout formulaire portant data-confirmation (message) et
    // data-confirmation-bouton (texte du bouton) demande d'abord l'accord de l'administrateur.
    const modalConfirmation = champ('modalConfirmation');
    let formulaireEnAttente = null;

    document.addEventListener('submit', function (e) {
        const f = e.target;
        if (!f.dataset.confirmation || e.defaultPrevented) return;

        e.preventDefault();
        formulaireEnAttente = f;
        champ('confirmationMessage').textContent = f.dataset.confirmation;
        champ('confirmationValider').textContent = f.dataset.confirmationBouton || 'Confirmer';

        // Une seule fenêtre à la fois : on ferme la fiche « Voir » avant d'ouvrir la confirmation
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
        // submit() ne redéclenche pas l'événement « submit » : pas de nouvelle demande
        if (formulaireEnAttente) formulaireEnAttente.submit();
    });
});
</script>
@endsection