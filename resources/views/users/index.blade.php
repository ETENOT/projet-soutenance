@extends('layouts.master')

@section('title', 'Gestion des utilisateurs')

@section('content')

{{-- ===== Fil d'Ariane ===== --}}
@component('components.breadcrumb')
    @slot('li_1')
        <a href="{{ route('dashboard') }}">Accueil</a>
    @endslot
    @slot('title')
        Utilisateurs
    @endslot
@endcomponent

@php
    // Retour d'une validation qui a échoué : "_mode" (ajout ou modification) dit quelle fenêtre rouvrir
    $modeAvecErreur = $errors->any() ? old('_mode') : null;

    $libellesRole = ['particulier' => 'Particulier', 'entreprise' => 'Entreprise', 'formateur' => 'Formateur', 'admin' => 'Administrateur'];
@endphp

<div class="container-fluid pb-4">

    {{-- ===== Statistiques (totaux sur toute la base) ===== --}}
    <div class="row g-2 mb-3">
        <div class="col-sm-6 col-xl-3">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center gap-2 py-2 px-3">
                    <span class="avatar-sm flex-shrink-0">
                        <span class="avatar-title rounded-3 bg-success-subtle text-success fs-4"><i class="ri-user-3-line"></i></span>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-0">{{ $stats['particuliers'] }}</h5>
                        <p class="text-muted mb-0 fs-12">Particuliers</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center gap-2 py-2 px-3">
                    <span class="avatar-sm flex-shrink-0">
                        <span class="avatar-title rounded-3 bg-info-subtle text-info fs-4"><i class="ri-building-line"></i></span>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-0">{{ $stats['entreprises'] }}</h5>
                        <p class="text-muted mb-0 fs-12">Entreprises</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center gap-2 py-2 px-3">
                    <span class="avatar-sm flex-shrink-0">
                        <span class="avatar-title rounded-3 bg-danger-subtle text-danger fs-4"><i class="ri-shield-user-line"></i></span>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-0">{{ $stats['admins'] }}</h5>
                        <p class="text-muted mb-0 fs-12">Administrateurs</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center gap-2 py-2 px-3">
                    <span class="avatar-sm flex-shrink-0">
                        <span class="avatar-title rounded-3 bg-primary-subtle text-primary fs-4"><i class="ri-group-line"></i></span>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-0">{{ $stats['total'] }}</h5>
                        <p class="text-muted mb-0 fs-12">Total utilisateurs</p>
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
        $sans = fn (array $cles) => route('admin.users.index', request()->except(array_merge($cles, ['page'])));

        // Étiquettes des filtres actifs : [texte, paramètres à retirer, icône]
        $etiquettes = [];
        if ($rolesChoisis) {
            $etiquettes[] = ['Rôle : ' . collect($rolesChoisis)->map(fn ($r) => $libellesRole[$r])->implode(' ou '), ['role'], 'ri-user-settings-line'];
        }
        if (request()->boolean('avec_paiement')) {
            $etiquettes[] = ['Avec paiement', ['avec_paiement'], 'ri-bank-card-line'];
        }
        if (request()->filled('date_du') || request()->filled('date_au')) {
            $du = request()->filled('date_du') ? \Carbon\Carbon::parse(request('date_du'))->format('d/m/Y') : '…';
            $au = request()->filled('date_au') ? \Carbon\Carbon::parse(request('date_au'))->format('d/m/Y') : '…';
            $etiquettes[] = ['Inscrit : ' . $du . ' → ' . $au, ['date_du', 'date_au'], 'ri-calendar-line'];
        }
        if (request()->filled('inscriptions_min') || request()->filled('inscriptions_max')) {
            $etiquettes[] = ['Inscriptions : ' . (request('inscriptions_min') ?: '0') . ' à ' . (request('inscriptions_max') ?: '∞'), ['inscriptions_min', 'inscriptions_max'], 'ri-group-line'];
        }
    @endphp

    {{-- ===== Barre de suppression groupée : cachée tant qu'aucun utilisateur n'est coché.
         Les cases du tableau y sont rattachées par form="formGroupe". ===== --}}
    <form id="formGroupe" method="POST" action="{{ route('admin.users.destroy-multiple') }}">
        @csrf
        <div id="barreActions" class="card border shadow-sm mb-3 d-none">
            <div class="card-body d-flex flex-wrap align-items-center gap-2">
                <strong><span id="nbSelection">0</span> utilisateur(s) sélectionné(s)</strong>
                <button type="button" class="btn btn-danger" id="supprimerSelection">
                    <i class="ri-delete-bin-line me-1"></i>Supprimer
                </button>
                <button type="button" id="annulerSelection" class="btn btn-outline-secondary">Désélectionner</button>
            </div>
        </div>
    </form>

    {{-- ===== Ligne d'outils : bouton de création à gauche, recherche + filtres à droite ===== --}}
    <div class="d-flex flex-wrap align-items-start justify-content-between gap-2 mb-3">

        <button type="button" class="btn btn-primary fw-semibold"
                data-bs-toggle="modal" data-bs-target="#modalUtilisateur" data-mode="ajout">
            <i class="ri-add-line me-1"></i>Créer un utilisateur
        </button>

        <form method="GET" action="{{ route('admin.users.index') }}" class="col-12 col-md-8 col-lg-6 col-xl-5 ms-md-auto">
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
                           placeholder="Rechercher un utilisateur (nom ou e-mail)…" aria-label="Rechercher un utilisateur">
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
                                    <i class="ri-equalizer-line text-primary me-2"></i>Filtres des utilisateurs
                                </h5>
                                <p class="text-muted small mb-0">Choisissez vos critères, puis appliquez-les à la liste.</p>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                        </div>

                        <div class="modal-body">
                            <div class="row g-4">
                                <div class="col-md-5">
                                    <div class="card h-100 mb-0">
                                        <div class="card-body">
                                            <h6 class="fw-semibold mb-3"><i class="ri-user-settings-line text-primary me-1"></i>Rôle</h6>

                                            @foreach ($libellesRole as $valeur => $libelle)
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" name="role[]" value="{{ $valeur }}"
                                                           id="filtre-role-{{ $valeur }}" @checked(in_array($valeur, $rolesChoisis, true))>
                                                    <label class="form-check-label" for="filtre-role-{{ $valeur }}">{{ $libelle }}</label>
                                                </div>
                                            @endforeach

                                            <hr>

                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="avec_paiement" value="1"
                                                       id="filtre-avec-paiement" @checked(request()->boolean('avec_paiement'))>
                                                <label class="form-check-label" for="filtre-avec-paiement">Avec paiement</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-7">
                                    <div class="card mb-0">
                                        <div class="card-body">
                                            <h6 class="fw-semibold mb-3"><i class="ri-list-settings-line text-primary me-1"></i>Autres critères</h6>

                                            <label class="form-label small mb-1">Date d'inscription sur la plateforme</label>
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

                                            <label class="form-label small mb-1">Nombre d'inscriptions aux classes</label>
                                            <div class="row g-2">
                                                <div class="col-6">
                                                    <input type="number" min="0" name="inscriptions_min" value="{{ request('inscriptions_min') }}"
                                                           class="form-control form-control-sm" placeholder="Minimum" aria-label="Nombre minimum d'inscriptions">
                                                </div>
                                                <div class="col-6">
                                                    <input type="number" min="0" name="inscriptions_max" value="{{ request('inscriptions_max') }}"
                                                           class="form-control form-control-sm" placeholder="Maximum" aria-label="Nombre maximum d'inscriptions">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer justify-content-between">
                            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
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

    {{-- ===== Tableau des utilisateurs : le dernier créé en premier (tri fait par le contrôleur) ===== --}}
    <div class="card border shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle table-nowrap mb-0">
                <thead>
                    <tr class="table-light">
                        <th>
                            <input type="checkbox" id="toutSelectionner" class="form-check-input" aria-label="Tout sélectionner">
                        </th>
                        <th>Nom</th>
                        <th>E-mail</th>
                        <th>Rôle</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($utilisateurs as $utilisateur)
                        @php
                            $nomRole = $utilisateur->role?->nom;
                            $badgeRole = match ($nomRole) {
                                'admin'       => ['bg-danger-subtle text-danger', 'Administrateur'],
                                'entreprise'  => ['bg-info-subtle text-info', 'Entreprise'],
                                'particulier' => ['bg-primary-subtle text-primary', 'Particulier'],
                                'formateur'   => ['bg-secondary-subtle text-secondary', 'Formateur'],
                                default       => ['bg-secondary-subtle text-secondary', '—'],
                            };
                            // Comptes protégés : administrateurs et vous-même (ni sélection, ni suppression)
                            $protege = $nomRole === 'admin' || $utilisateur->id === auth()->id();

                            // Profil propre au rôle (peut être vide)
                            $particulier = $nomRole === 'particulier' ? $utilisateur->particulier : null;
                            $entreprise = $nomRole === 'entreprise' ? $utilisateur->entreprise : null;
                            $naissance = $particulier?->date_de_naissance
                                ? \Carbon\Carbon::parse($particulier->date_de_naissance)->format('Y-m-d')
                                : '';
                        @endphp
                        <tr>
                            <td>
                                <input type="checkbox" name="ids[]" value="{{ $utilisateur->id }}" form="formGroupe"
                                       class="form-check-input case-utilisateur" aria-label="Sélectionner {{ $utilisateur->name }}"
                                       @disabled($protege)>
                            </td>
                            <td class="fw-semibold cellule-tronquee" title="{{ $utilisateur->name }}">{{ $utilisateur->name }}</td>
                            <td class="text-muted cellule-tronquee-lg" title="{{ $utilisateur->email }}">{{ $utilisateur->email }}</td>
                            <td><span class="badge {{ $badgeRole[0] }}">{{ $badgeRole[1] }}</span></td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    {{-- Voir : le contenu du bloc caché #detail-ID est copié dans la fenêtre --}}
                                    <button type="button" class="btn btn-sm btn-soft-primary" title="Voir"
                                            data-bs-toggle="modal" data-bs-target="#modalDetail"
                                            data-detail="detail-{{ $utilisateur->id }}"
                                            data-titre="{{ $utilisateur->name }}">
                                        <i class="ri-eye-line"></i>
                                    </button>

                                    {{-- Modifier : compte + profil du rôle --}}
                                    <button type="button" class="btn btn-sm btn-soft-secondary" title="Modifier"
                                            data-bs-toggle="modal" data-bs-target="#modalUtilisateur"
                                            data-mode="modif"
                                            data-action="{{ route('admin.users.update', $utilisateur) }}"
                                            data-nom="{{ $utilisateur->name }}"
                                            data-email="{{ $utilisateur->email }}"
                                            data-role="{{ $utilisateur->role_id }}"
                                            data-admin="{{ $nomRole === 'admin' ? '1' : '' }}"
                                            data-telephone="{{ $particulier?->telephone }}"
                                            data-naissance="{{ $naissance }}"
                                            data-raison="{{ $entreprise?->raison_sociale }}"
                                            data-adresse="{{ $entreprise?->adresse }}"
                                            data-contact="{{ $entreprise?->contact_principal }}"
                                            data-secteur="{{ $entreprise?->secteur_activite }}">
                                        <i class="ri-edit-line"></i>
                                    </button>

                                    {{-- Supprimer : demande confirmation dans une fenêtre --}}
                                    @if ($protege)
                                        <button type="button" class="btn btn-sm btn-soft-danger" title="Compte protégé : suppression impossible" disabled>
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-sm btn-soft-danger" title="Supprimer"
                                                data-bs-toggle="modal" data-bs-target="#modalSuppression"
                                                data-action="{{ route('admin.users.destroy', $utilisateur) }}"
                                                data-nom="{{ $utilisateur->name }}"
                                                data-inscriptions="{{ $utilisateur->inscriptions_count }}">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">
                                <i class="ri-user-search-line display-5 d-block mb-2"></i>
                                Aucun utilisateur ne correspond à votre recherche.
                                @if ($etiquettes || request()->filled('q'))
                                    <a href="{{ route('admin.users.index') }}" class="d-block mt-2">Réinitialiser les filtres</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($utilisateurs->total() > 0)
            <div class="card-footer bg-transparent d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <small class="text-muted">
                    Affichage de {{ $utilisateurs->firstItem() }} à {{ $utilisateurs->lastItem() }}
                    sur {{ $utilisateurs->total() }} résultat(s)
                </small>
                {{ $utilisateurs->links() }}
            </div>
        @endif
    </div>

    {{-- Contenus cachés des fenêtres « Voir » (un par utilisateur, copiés par le script) --}}
    @foreach ($utilisateurs as $utilisateur)
        @php
            $nomRole = $utilisateur->role?->nom;
            $particulier = $nomRole === 'particulier' ? $utilisateur->particulier : null;
            $entreprise = $nomRole === 'entreprise' ? $utilisateur->entreprise : null;
        @endphp
        <div id="detail-{{ $utilisateur->id }}" class="d-none">
            <dl class="row mb-0">
                <dt class="col-sm-5">Type de compte</dt>
                <dd class="col-sm-7">{{ $libellesRole[$nomRole] ?? '—' }}</dd>

                <dt class="col-sm-5">Statut du compte</dt>
                <dd class="col-sm-7">
                    @if ($utilisateur->email_verified_at)
                        <span class="badge bg-success-subtle text-success">E-mail vérifié</span>
                    @else
                        <span class="badge bg-warning-subtle text-warning">E-mail non vérifié</span>
                    @endif
                </dd>

                <dt class="col-sm-5">Inscrit sur la plateforme le</dt>
                <dd class="col-sm-7">{{ $utilisateur->created_at?->format('d/m/Y') ?? '—' }}</dd>

                <dt class="col-sm-5">Inscriptions aux classes</dt>
                <dd class="col-sm-7">{{ $utilisateur->inscriptions_count }}</dd>

                @if ($nomRole === 'particulier')
                    <dt class="col-sm-5">Téléphone</dt>
                    <dd class="col-sm-7">{{ $particulier?->telephone ?: '—' }}</dd>

                    <dt class="col-sm-5">Date de naissance</dt>
                    <dd class="col-sm-7">
                        {{ $particulier?->date_de_naissance ? \Carbon\Carbon::parse($particulier->date_de_naissance)->format('d/m/Y') : '—' }}
                    </dd>
                @elseif ($nomRole === 'entreprise')
                    <dt class="col-sm-5">Raison sociale</dt>
                    <dd class="col-sm-7">{{ $entreprise?->raison_sociale ?: '—' }}</dd>

                    <dt class="col-sm-5">Siège social</dt>
                    <dd class="col-sm-7">{{ $entreprise?->adresse ?: '—' }}</dd>

                    <dt class="col-sm-5">Contact principal</dt>
                    <dd class="col-sm-7">{{ $entreprise?->contact_principal ?: '—' }}</dd>

                    <dt class="col-sm-5">Secteur d'activité</dt>
                    <dd class="col-sm-7">{{ $entreprise?->secteur_activite ?: '—' }}</dd>
                @endif
            </dl>
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
                    <h5 class="modal-title">Supprimer l'utilisateur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Voulez-vous vraiment supprimer <strong id="suppressionNom"></strong> ?</p>
                    {{-- Affiché seulement si l'utilisateur a des inscriptions : elles disparaissent avec le compte --}}
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
                <h5 class="modal-title">Supprimer les utilisateurs sélectionnés</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Voulez-vous vraiment supprimer <strong id="nbSuppressionGroupee">0</strong> utilisateur(s) ?</p>
                <p class="text-danger mb-2">Un utilisateur ayant des inscriptions payées est ignoré : supprimez-le depuis sa ligne.</p>
                <p class="text-muted mb-0">Leurs inscriptions seront supprimées avec eux. Cette action est définitive.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-danger" id="confirmerSuppressionGroupee">Supprimer</button>
            </div>
        </div>
    </div>
</div>

{{-- ===== Fenêtre « Créer / Modifier » (un seul formulaire pour les deux) ===== --}}
<div class="modal fade" id="modalUtilisateur" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formUtilisateur" method="POST" action="{{ route('admin.users.store') }}">
                @csrf
                {{-- Champs techniques : _method = PUT seulement en modification ; _mode et _action
                     permettent de rouvrir la bonne fenêtre si la validation échoue --}}
                <input type="hidden" name="_method" value="PUT" id="champMethode" disabled>
                <input type="hidden" name="_mode" id="champMode" value="{{ old('_mode') }}">
                <input type="hidden" name="_action" id="champAction" value="{{ old('_action') }}">

                <div class="modal-header">
                    <h5 class="modal-title" id="utilisateurTitre">Créer un utilisateur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="champNom" class="form-label">Nom</label>
                        <input type="text" name="name" id="champNom" class="form-control" maxlength="255"
                               value="{{ old('name') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="champEmail" class="form-label">E-mail</label>
                        <input type="email" name="email" id="champEmail" class="form-control" maxlength="255"
                               value="{{ old('email') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="champRole" class="form-label">Type de compte</label>
                        <select name="role_id" id="champRole" class="form-select" required>
                            <option value="">— Choisir —</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}" data-nom="{{ $role->nom }}"
                                        @selected((string) old('role_id') === (string) $role->id)>
                                    {{ $libellesRole[$role->nom] ?? ucfirst($role->nom) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Mot de passe : bouton œil géré par password-addon.init.js (comme à l'inscription) --}}
                    <div class="mb-3">
                        <label for="champMotDePasse" class="form-label">Mot de passe</label>
                        <div class="position-relative auth-pass-inputgroup">
                            <input type="password" name="password" id="champMotDePasse"
                                   class="form-control password-input pe-5" minlength="8" autocomplete="new-password">
                            <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon"
                                    type="button" tabindex="-1" aria-label="Afficher ou masquer le mot de passe">
                                <i class="ri-eye-fill align-middle"></i>
                            </button>
                        </div>
                        <div class="form-text d-none" id="noteMotDePasse">Laissez vide pour conserver le mot de passe actuel.</div>
                    </div>

                    <div class="mb-3">
                        <label for="champConfirmation" class="form-label">Confirmation du mot de passe</label>
                        <div class="position-relative auth-pass-inputgroup">
                            <input type="password" name="password_confirmation" id="champConfirmation"
                                   class="form-control password-input pe-5" minlength="8" autocomplete="new-password">
                            <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon"
                                    type="button" tabindex="-1" aria-label="Afficher ou masquer la confirmation">
                                <i class="ri-eye-fill align-middle"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Particulier : affiché seulement si le type de compte est "particulier".
                         Le téléphone n'a pas de "name" : intl-tel-input crée à l'envoi un champ caché
                         "telephone" contenant le numéro international complet (ex. +24177123456). --}}
                    <div class="d-none" id="blocParticulier">
                        <div class="mb-3">
                            <label for="champTelephone" class="form-label">Téléphone</label>
                            <input type="tel" id="champTelephone" class="form-control">
                        </div>
                        <div class="mb-0">
                            <label for="champNaissance" class="form-label">Date de naissance <span class="text-muted">(facultatif)</span></label>
                            <input type="date" name="date_de_naissance" id="champNaissance" class="form-control"
                                   max="{{ today()->toDateString() }}" value="{{ old('date_de_naissance') }}">
                        </div>
                    </div>

                    {{-- Entreprise : affiché seulement si le type de compte est "entreprise" --}}
                    <div class="d-none" id="blocEntreprise">
                        <div class="mb-3">
                            <label for="champRaisonSociale" class="form-label">Raison sociale</label>
                            <input type="text" name="raison_sociale" id="champRaisonSociale" class="form-control"
                                   maxlength="255" value="{{ old('raison_sociale') }}">
                        </div>
                        <div class="mb-3">
                            <label for="champAdresse" class="form-label">Siège social</label>
                            <input type="text" name="adresse" id="champAdresse" class="form-control"
                                   maxlength="255" value="{{ old('adresse') }}">
                        </div>
                        <div class="mb-3">
                            <label for="champContact" class="form-label">Contact principal</label>
                            <input type="tel" id="champContact" class="form-control">
                        </div>
                        <div class="mb-0">
                            <label for="champSecteur" class="form-label">Secteur d'activité <span class="text-muted">(facultatif)</span></label>
                            <input type="text" name="secteur_activite" id="champSecteur" class="form-control"
                                   maxlength="255" value="{{ old('secteur_activite') }}">
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

@section('css')
    {{-- Styles du sélecteur de pays (drapeaux, liste déroulante) d'intl-tel-input --}}
    <link rel="stylesheet" href="{{ URL::asset('build/libs/intl-tel-input/css/intlTelInput.min.css') }}">
@endsection

@section('script')
{{-- Variante "WithUtils" : validation et placeholder par pays inclus --}}
<script src="{{ URL::asset('build/libs/intl-tel-input/js/intlTelInputWithUtils.min.js') }}"></script>
{{-- Bouton œil des mots de passe --}}
<script src="{{ URL::asset('build/js/pages/password-addon.init.js') }}"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const urlAjout = @json(route('admin.users.store'));
    const modeAvecErreur = @json($modeAvecErreur);

    const modalUtilisateur = document.getElementById('modalUtilisateur');
    const form = document.getElementById('formUtilisateur');
    const champ = id => document.getElementById(id);

    // ------------------------------------------------------------------
    // Téléphones : sélecteur de pays (intl-tel-input), comme à l'inscription
    // ------------------------------------------------------------------

    // Réorganise chaque ligne de la liste des pays en "Nom  drapeau  +indicatif".
    // Idempotente : les lignes déjà traitées sont ignorées.
    function formaterListePays() {
        document.querySelectorAll('.iti__country-list li.iti__country:not([data-format-ok])').forEach(li => {
            const flag = li.querySelector(':scope > .iti__flag');
            const nameEl = li.querySelector(':scope > .iti__country-name');
            const dialEl = nameEl ? nameEl.querySelector('.iti__dial-code') : null;

            if (!flag || !dialEl) return;

            dialEl.textContent = '+' + li.dataset.dialCode;
            nameEl.after(flag);
            flag.after(dialEl);
            li.dataset.formatOk = '1';
        });
    }

    // inputId : champ visible (sans "name") ; nomEnvoye : nom du champ caché envoyé au serveur
    function preparerTelephone(inputId, nomEnvoye, ancienneValeur) {
        const inputEl = champ(inputId);

        const iti = window.intlTelInput(inputEl, {
            initialCountry: 'ga',
            countryOrder: ['ga', 'fr'],
            countryNameLocale: 'fr',
            separateDialCode: true,
            strictMode: true,
            // Champ caché créé à l'envoi : numéro complet au format international (+24177123456)
            hiddenInputs: () => ({ phone: nomEnvoye }),
        });

        // Retour d'une validation refusée : on remet le numéro saisi
        if (ancienneValeur) {
            iti.setNumber(ancienneValeur);
        }

        // Marque le champ invalide si le numéro ne correspond pas au pays choisi
        function majValidite() {
            if (inputEl.disabled || inputEl.value.trim() === '') {
                inputEl.setCustomValidity('');
                return;
            }
            inputEl.setCustomValidity(iti.isValidNumber() === false ? 'Numéro invalide pour le pays sélectionné.' : '');
        }

        inputEl.addEventListener('input', majValidite);
        inputEl.addEventListener('countrychange', majValidite);
        inputEl.addEventListener('open:countrydropdown', formaterListePays);

        // Remplit le champ avec un numéro international enregistré (ou le vide).
        // Protégé : une erreur de la bibliothèque ne doit pas bloquer le reste de la fenêtre.
        function remplir(valeur) {
            try {
                if (valeur) {
                    iti.setNumber(valeur);
                } else {
                    iti.setNumber('');
                    iti.setCountry('ga');
                }
                majValidite();
            } catch (erreur) {
                console.error('Téléphone :', erreur);
            }
        }

        return { inputEl, iti, majValidite, remplir };
    }

    const telephone = preparerTelephone('champTelephone', 'telephone', @json(old('telephone')));
    const contact = preparerTelephone('champContact', 'contact_principal', @json(old('contact_principal')));

    // ------------------------------------------------------------------
    // Sélection multiple : la barre de suppression apparaît dès qu'au moins un utilisateur est coché
    // (les comptes protégés ont une case désactivée : on ne les sélectionne jamais)
    // ------------------------------------------------------------------
    const cases = Array.from(document.querySelectorAll('.case-utilisateur:not(:disabled)'));
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

    // ------------------------------------------------------------------
    // Fenêtre « Créer / Modifier »
    // ------------------------------------------------------------------

    // Affiche ou cache un bloc de champs ; les champs cachés sont désactivés (donc pas envoyés).
    // Les champs téléphone (et la recherche de pays) sont pilotés à part par intl-tel-input.
    function basculerBloc(id, actif) {
        const bloc = champ(id);
        bloc.classList.toggle('d-none', !actif);
        bloc.querySelectorAll('input:not([type=hidden]):not(.iti__search-input):not(#champTelephone):not(#champContact)')
            .forEach(i => i.disabled = !actif);
    }

    // Champs propres au type de compte choisi (création ET modification)
    function majChampsRole() {
        const option = champ('champRole').selectedOptions[0];
        const nomRole = option ? option.dataset.nom : '';
        const particulier = nomRole === 'particulier';
        const entreprise = nomRole === 'entreprise';

        basculerBloc('blocParticulier', particulier);
        basculerBloc('blocEntreprise', entreprise);

        try {
            telephone.iti.setDisabled(!particulier);
            contact.iti.setDisabled(!entreprise);
            telephone.inputEl.required = particulier;
            contact.inputEl.required = entreprise;
            telephone.majValidite();
            contact.majValidite();
        } catch (erreur) {
            console.error('Téléphone :', erreur);
        }

        champ('champRaisonSociale').required = entreprise;
        champ('champAdresse').required = entreprise;
    }

    // Adapte la fenêtre au mode : "ajout" (POST, mot de passe obligatoire) ou "modif" (PUT, mot de passe facultatif)
    function preparer(mode) {
        const modif = mode === 'modif';
        champ('utilisateurTitre').textContent = modif ? 'Modifier un utilisateur' : 'Créer un utilisateur';
        champ('champMode').value = mode;
        champ('champMethode').disabled = !modif;
        champ('champMotDePasse').required = !modif;
        champ('champConfirmation').required = !modif;
        champ('noteMotDePasse').classList.toggle('d-none', !modif);
        majChampsRole();
    }

    champ('champRole').addEventListener('change', majChampsRole);

    // Ouverture par un bouton : on remplit le formulaire (sauf retour d'erreur, géré plus bas)
    modalUtilisateur.addEventListener('show.bs.modal', function (e) {
        const bouton = e.relatedTarget;
        if (!bouton) return;

        const d = bouton.dataset;
        const modif = d.mode === 'modif';

        // Titre et mode d'abord : ils ne dépendent pas des champs téléphone
        preparer(d.mode);

        // Un compte administrateur ne peut pas changer de rôle : les autres choix sont désactivés
        Array.from(champ('champRole').options).forEach(function (o) {
            o.disabled = modif && d.admin === '1' && o.value !== d.role;
        });

        // Mots de passe : jamais pré-remplis, et masqués par défaut
        ['champMotDePasse', 'champConfirmation'].forEach(function (id) {
            champ(id).value = '';
            champ(id).type = 'password';
        });

        if (modif) {
            champ('champAction').value = d.action;
            form.action = d.action;
            champ('champNom').value = d.nom;
            champ('champEmail').value = d.email;
            champ('champRole').value = d.role;
            champ('champNaissance').value = d.naissance || '';
            champ('champRaisonSociale').value = d.raison || '';
            champ('champAdresse').value = d.adresse || '';
            champ('champSecteur').value = d.secteur || '';

            // Le rôle est rempli : on affiche le bon bloc, puis on remplit les téléphones
            majChampsRole();
            telephone.remplir(d.telephone || '');
            contact.remplir(d.contact || '');
        } else {
            champ('champAction').value = urlAjout;
            form.action = urlAjout;
            ['champNom', 'champEmail', 'champNaissance', 'champRaisonSociale',
             'champAdresse', 'champSecteur'].forEach(id => champ(id).value = '');
            champ('champRole').value = '';

            majChampsRole();
            telephone.remplir('');
            contact.remplir('');
        }
    });

    // Validation refusée : on rouvre la fenêtre avec ce que l'utilisateur avait saisi (déjà dans les champs)
    if (modeAvecErreur) {
        preparer(modeAvecErreur);
        form.action = champ('champAction').value || urlAjout;
        bootstrap.Modal.getOrCreateInstance(modalUtilisateur).show();
    }

    // Fenêtre de suppression : nom, adresse d'envoi et avertissement si l'utilisateur a des inscriptions
    champ('modalSuppression').addEventListener('show.bs.modal', function (e) {
        const d = e.relatedTarget.dataset;
        const alerte = champ('suppressionAlerte');
        const nb = parseInt(d.inscriptions, 10) || 0;

        champ('suppressionNom').textContent = d.nom;
        champ('formSuppression').action = d.action;

        if (nb > 0) {
            alerte.textContent = 'Ce compte a ' + nb + ' inscription(s) : elles seront supprimées avec lui.';
        }
        alerte.classList.toggle('d-none', nb === 0);
    });

    // Fenêtre « Voir » : copie le bloc caché de l'utilisateur
    champ('modalDetail').addEventListener('show.bs.modal', function (e) {
        const bouton = e.relatedTarget;
        champ('detailTitre').textContent = bouton.dataset.titre;
        champ('detailCorps').innerHTML = champ(bouton.dataset.detail).innerHTML;
    });
});
</script>
@endsection