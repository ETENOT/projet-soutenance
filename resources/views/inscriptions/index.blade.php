@extends('layouts.master')

@section('title', 'Gestion des inscriptions')

@section('content')

{{-- ===== Fil d'Ariane ===== --}}
@component('components.breadcrumb')
    @slot('li_1')
        <a href="{{ route('dashboard') }}">Accueil</a>
    @endslot
    @slot('title')
        Inscriptions
    @endslot
@endcomponent

@php
    // Retour d'une validation qui a échoué : "_mode" (ajout ou modification) dit quelle fenêtre rouvrir
    $modeAvecErreur = $errors->any() ? old('_mode') : null;

    $fcfa = fn ($montant) => number_format($montant, 0, ',', ' ') . ' FCFA';
@endphp

<div class="container-fluid pb-4">

    {{-- ===== Statistiques (totaux sur toute la base) ===== --}}
    <div class="row g-2 mb-3">
        <div class="col-sm-6 col-xl-4">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center gap-2 py-2 px-3">
                    <span class="avatar-sm flex-shrink-0">
                        <span class="avatar-title rounded-3 bg-primary-subtle text-primary fs-4"><i class="ri-user-add-line"></i></span>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-0">{{ $stats['total'] }}</h5>
                        <p class="text-muted mb-0 fs-12">Inscriptions</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-4">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center gap-2 py-2 px-3">
                    <span class="avatar-sm flex-shrink-0">
                        <span class="avatar-title rounded-3 bg-success-subtle text-success fs-4"><i class="ri-checkbox-circle-line"></i></span>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-0">{{ $stats['payees'] }}</h5>
                        <p class="text-muted mb-0 fs-12">Payées</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-4">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center gap-2 py-2 px-3">
                    <span class="avatar-sm flex-shrink-0">
                        <span class="avatar-title rounded-3 bg-warning-subtle text-warning fs-4"><i class="ri-time-line"></i></span>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-0">{{ $stats['en_attente'] }}</h5>
                        <p class="text-muted mb-0 fs-12">En attente de paiement</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Erreurs de filtre ===== --}}
    @if ($errors->any() && ! $modeAvecErreur)
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $erreur)
                    <li>{{ $erreur }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ===== Recherche : étiquettes des filtres actifs + fenêtre Filtres ===== --}}
    @php
        $sans = fn (array $cles) => route('admin.inscriptions.index', request()->except(array_merge($cles, ['page'])));

        $libellesPaiement = ['paye' => 'Payé', 'en_attente' => 'En attente'];

        $etiquettes = [];
        if ($statutsChoisis) {
            $etiquettes[] = ['Paiement : ' . collect($statutsChoisis)->map(fn ($s) => $libellesPaiement[$s])->implode(' ou '), ['statut_paiement'], 'ri-bank-card-line'];
        }
        if (request()->filled('cours')) {
            $etiquettes[] = ['Cours : ' . ($coursListe->firstWhere('id', (int) request('cours'))?->titre ?? '?'), ['cours'], 'ri-book-open-line'];
        }
        if (request()->filled('classe')) {
            $etiquettes[] = ['Classe : ' . ($classesListe->firstWhere('id', (int) request('classe'))?->nom ?? '?'), ['classe'], 'ri-group-line'];
        }
        if (request()->filled('date_du') || request()->filled('date_au')) {
            $du = request()->filled('date_du') ? \Carbon\Carbon::parse(request('date_du'))->format('d/m/Y') : '…';
            $au = request()->filled('date_au') ? \Carbon\Carbon::parse(request('date_au'))->format('d/m/Y') : '…';
            $etiquettes[] = ['Inscrit : ' . $du . ' → ' . $au, ['date_du', 'date_au'], 'ri-calendar-line'];
        }
    @endphp

    {{-- ===== Barre de suppression groupée : au-dessus de la recherche ===== --}}
    <form id="formGroupe" method="POST" action="{{ route('admin.inscriptions.destroy-multiple') }}">
        @csrf
        <div id="barreActions" class="card border shadow-sm mb-3 d-none">
            <div class="card-body d-flex flex-wrap align-items-center gap-2">
                <strong>
                    <span id="nbSelection">0</span> inscription(s) sélectionnée(s)
                </strong>

                <button type="button" class="btn btn-danger" id="supprimerSelection">
                    <i class="ri-delete-bin-line me-1"></i>Supprimer
                </button>

                <button type="button" id="annulerSelection" class="btn btn-outline-secondary">
                    Désélectionner
                </button>
            </div>
        </div>
    </form>

    {{-- ===== Ligne principale : création à gauche, recherche à droite ===== --}}
    <div class="row g-2 align-items-center mb-3">

        <div class="col-12 col-lg-auto">
            <button type="button" class="btn btn-primary fw-semibold"
                    data-bs-toggle="modal" data-bs-target="#modalInscription" data-mode="ajout">
                <i class="ri-add-line me-1"></i>Créer une inscription
            </button>
        </div>

        <div class="col-12 col-lg">
            <form method="GET" action="{{ route('admin.inscriptions.index') }}">
                <div class="d-flex flex-column flex-md-row justify-content-lg-end align-items-stretch align-items-md-center gap-2">

                    <div class="form-control h-auto d-flex flex-wrap align-items-center gap-1 py-1 shadow-sm"
                         style="min-width: 0; max-width: 650px;">

                        <i class="ri-search-line text-primary me-1"></i>

                        @foreach ($etiquettes as [$texte, $retirer, $icone])
                            <span class="badge bg-primary-subtle text-primary d-inline-flex align-items-center gap-1 fs-12 py-2">
                                <i class="{{ $icone }}"></i>{{ $texte }}
                                <a href="{{ $sans($retirer) }}" class="text-primary ms-1"
                                   title="Retirer ce filtre"
                                   aria-label="Retirer le filtre {{ $texte }}">
                                    <i class="ri-close-line"></i>
                                </a>
                            </span>
                        @endforeach

                        <input type="text" name="q" value="{{ request('q') }}"
                               class="form-control border-0 shadow-none flex-grow-1 w-auto p-1"
                               placeholder="Rechercher un utilisateur (nom ou e-mail)…"
                               aria-label="Rechercher un utilisateur inscrit">
                    </div>

                    <button type="button" class="btn btn-light border shadow-sm flex-shrink-0"
                            data-bs-toggle="modal" data-bs-target="#modalFiltres"
                            aria-label="Ouvrir les filtres">
                        <i class="ri-equalizer-line text-primary me-1"></i>
                        Filtres
                        @if (count($etiquettes))
                            <span class="badge bg-primary ms-1">{{ count($etiquettes) }}</span>
                        @endif
                    </button>

                </div>

                <button type="submit" class="visually-hidden" tabindex="-1" aria-hidden="true">
                    Rechercher
                </button>
            </form>
        </div>
    </div>

    {{-- ===== Fenêtre des filtres ===== --}}
    <form method="GET" action="{{ route('admin.inscriptions.index') }}" id="formFiltres">
        <div class="modal fade" id="modalFiltres" tabindex="-1" aria-labelledby="titreFiltres" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title fw-semibold" id="titreFiltres">
                                <i class="ri-equalizer-line text-primary me-2"></i>Filtres des inscriptions
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
                                        <h6 class="fw-semibold mb-3"><i class="ri-bank-card-line text-primary me-1"></i>Paiement</h6>

                                        @foreach ($libellesPaiement as $valeur => $libelle)
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" name="statut_paiement[]" value="{{ $valeur }}"
                                                       id="filtre-paiement-{{ $valeur }}" @checked(in_array($valeur, $statutsChoisis, true))>
                                                <label class="form-check-label" for="filtre-paiement-{{ $valeur }}">{{ $libelle }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-7">
                                <div class="card mb-0">
                                    <div class="card-body">
                                        <h6 class="fw-semibold mb-3"><i class="ri-list-settings-line text-primary me-1"></i>Autres critères</h6>

                                        <label for="filtre-cours" class="form-label small mb-1">Cours</label>
                                        <select name="cours" id="filtre-cours" class="form-select form-select-sm mb-3" aria-label="Filtrer par cours">
                                            <option value="">Tous les cours</option>
                                            @foreach ($coursListe as $cours)
                                                <option value="{{ $cours->id }}" @selected((string) request('cours') === (string) $cours->id)>{{ $cours->titre }}</option>
                                            @endforeach
                                        </select>

                                        <label for="filtre-classe" class="form-label small mb-1">Classe</label>
                                        <select name="classe" id="filtre-classe" class="form-select form-select-sm mb-3" aria-label="Filtrer par classe">
                                            <option value="">Toutes les classes</option>
                                            @foreach ($classesListe->groupBy(fn ($c) => $c->cours?->titre ?? 'Sans cours') as $titreCours => $classesDuCours)
                                                <optgroup label="{{ $titreCours }}">
                                                    @foreach ($classesDuCours as $uneClasse)
                                                        <option value="{{ $uneClasse->id }}" @selected((string) request('classe') === (string) $uneClasse->id)>{{ $uneClasse->nom }}</option>
                                                    @endforeach
                                                </optgroup>
                                            @endforeach
                                        </select>

                                        <label class="form-label small mb-1">Date d'inscription</label>
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <input type="date" name="date_du" value="{{ request('date_du') }}"
                                                       class="form-control form-control-sm" aria-label="Début de la période">
                                            </div>
                                            <div class="col-6">
                                                <input type="date" name="date_au" value="{{ request('date_au') }}"
                                                       class="form-control form-control-sm" aria-label="Fin de la période">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer justify-content-between">
                        <a href="{{ route('admin.inscriptions.index') }}" class="btn btn-outline-secondary">
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
    </form>

    {{-- ===== Tableau des inscriptions ===== --}}
    <div class="card border shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle table-nowrap mb-0">
                <thead>
                    <tr class="table-light">
                        <th><input type="checkbox" id="toutSelectionner" class="form-check-input" aria-label="Tout sélectionner"></th>
                        <th>Utilisateur</th>
                        <th>E-mail</th>
                        <th>Cours</th>
                        <th>Classe</th>
                        <th>Montant versé</th>
                        <th>Reste</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($inscriptions as $inscription)
                        @php
                            $payee = $inscription->estPayee();
                            $montant = $payee ? $inscription->paiement->montant : $inscription->montant_du;
                            $verse = $payee ? $inscription->paiement->montant : 0;
                            $reste = $montant - $verse;
                        @endphp
                        <tr>
                            <td>
                                <input type="checkbox" name="ids[]" value="{{ $inscription->id }}" form="formGroupe"
                                       class="form-check-input case-inscription"
                                       aria-label="Sélectionner l'inscription de {{ $inscription->user->name }}">
                            </td>
                            <td class="fw-semibold cellule-tronquee" title="{{ $inscription->user->name }}">{{ $inscription->user->name }}</td>
                            <td class="text-muted cellule-tronquee-lg" title="{{ $inscription->user->email }}">{{ $inscription->user->email }}</td>
                            <td class="cellule-tronquee" title="{{ $inscription->classe->cours->titre }}">{{ $inscription->classe->cours->titre }}</td>
                            <td class="cellule-tronquee-sm" title="{{ $inscription->classe->nom }}">{{ $inscription->classe->nom }}</td>
                            <td>{{ $fcfa($verse) }}</td>
                            <td>{{ $fcfa($reste) }}</td>
                            <td>
                                @if ($payee)
                                    <span class="badge bg-success-subtle text-success">Payé</span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning">En attente</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    @unless ($payee)
                                        <button type="button" class="btn btn-sm btn-soft-success" title="Encaisser le paiement"
                                                data-bs-toggle="modal" data-bs-target="#modalEncaissement"
                                                data-action="{{ route('paiements.store', $inscription) }}"
                                                data-nom="{{ $inscription->user->name }}"
                                                data-montant="{{ $fcfa($montant) }}">
                                            <i class="ri-money-dollar-circle-line"></i>
                                        </button>
                                    @endunless

                                    <button type="button" class="btn btn-sm btn-soft-primary" title="Voir"
                                            data-bs-toggle="modal" data-bs-target="#modalDetail"
                                            data-detail="detail-{{ $inscription->id }}"
                                            data-titre="{{ $inscription->user->name }}">
                                        <i class="ri-eye-line"></i>
                                    </button>

                                    <button type="button" class="btn btn-sm btn-soft-secondary" title="Modifier"
                                            data-bs-toggle="modal" data-bs-target="#modalInscription"
                                            data-mode="modif"
                                            data-action="{{ route('admin.inscriptions.update', $inscription) }}"
                                            data-nom="{{ $inscription->user->name }}"
                                            data-classe="{{ $inscription->classe_id }}"
                                            data-cours="{{ $inscription->classe->cours_id }}"
                                            data-date="{{ \Carbon\Carbon::parse($inscription->date_inscription)->format('Y-m-d') }}"
                                            data-payee="{{ $payee ? '1' : '' }}"
                                            data-bloque="{{ $inscription->classe->etat !== 'a_venir' ? '1' : '' }}">
                                        <i class="ri-edit-line"></i>
                                    </button>

                                    <button type="button" class="btn btn-sm btn-soft-danger" title="Supprimer"
                                            data-bs-toggle="modal" data-bs-target="#modalSuppression"
                                            data-action="{{ route('admin.inscriptions.destroy', $inscription) }}"
                                            data-nom="{{ $inscription->user->name }}"
                                            data-paye="{{ $payee ? $fcfa($verse) : '' }}">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">
                                <i class="ri-user-add-line display-5 d-block mb-2"></i>
                                Aucune inscription ne correspond à votre recherche.
                                @if ($etiquettes || request()->filled('q'))
                                    <a href="{{ route('admin.inscriptions.index') }}" class="d-block mt-2">Réinitialiser les filtres</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($inscriptions->total() > 0)
            <div class="card-footer bg-transparent d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <small class="text-muted">
                    Affichage de {{ $inscriptions->firstItem() }} à {{ $inscriptions->lastItem() }}
                    sur {{ $inscriptions->total() }} résultat(s)
                </small>
                {{ $inscriptions->links() }}
            </div>
        @endif
    </div>

    {{-- Contenus cachés des fenêtres « Voir » --}}
    @foreach ($inscriptions as $inscription)
        @php
            $payee = $inscription->estPayee();
            $classe = $inscription->classe;
        @endphp
        <div id="detail-{{ $inscription->id }}" class="d-none">
            <dl class="row mb-3">
                <dt class="col-sm-4">E-mail</dt>
                <dd class="col-sm-8">{{ $inscription->user->email }}</dd>

                <dt class="col-sm-4">Type de compte</dt>
                <dd class="col-sm-8">{{ ucfirst($inscription->user->role?->nom ?? '—') }}</dd>

                <dt class="col-sm-4">Cours</dt>
                <dd class="col-sm-8">{{ $classe->cours->titre }}</dd>

                <dt class="col-sm-4">Classe</dt>
                <dd class="col-sm-8">{{ $classe->nom }} ({{ $classe->etat_libelle }})</dd>

                <dt class="col-sm-4">Période</dt>
                <dd class="col-sm-8">{{ $classe->date_debut->format('d/m/Y') }} → {{ $classe->date_fin->format('d/m/Y') }}</dd>

                <dt class="col-sm-4">Inscrit le</dt>
                <dd class="col-sm-8">{{ \Carbon\Carbon::parse($inscription->date_inscription)->format('d/m/Y') }}</dd>

                <dt class="col-sm-4">Paiement</dt>
                <dd class="col-sm-8">
                    @if ($payee)
                        {{ $fcfa($inscription->paiement->montant) }} · {{ $inscription->paiement->libelle_mode }}
                        le {{ $inscription->paiement->created_at->format('d/m/Y H:i') }}
                        @if ($inscription->paiement->reference)
                            <div class="text-muted">Référence : {{ $inscription->paiement->reference }}</div>
                        @endif
                    @else
                        Aucun paiement enregistré.
                    @endif
                </dd>

                <dt class="col-sm-4">Présence</dt>
                <dd class="col-sm-8">
                    @if ($inscription->taux_presence !== null)
                        {{ rtrim(rtrim(number_format($inscription->taux_presence, 2, ',', ''), '0'), ',') }} %
                        · attestation {{ $inscription->attestation_eligible ? 'éligible' : 'non éligible' }}
                    @else
                        Calculée à la fermeture de la classe.
                    @endif
                </dd>
            </dl>

            @if ($payee)
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('paiements.recu', $inscription->paiement) }}" target="_blank" class="btn btn-sm btn-light">Voir le reçu</a>
                    <a href="{{ route('paiements.recu.telecharger', $inscription->paiement) }}" class="btn btn-sm btn-light">Télécharger le reçu</a>
                </div>
            @endif
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

{{-- ===== Fenêtre de confirmation d'encaissement ===== --}}
<div class="modal fade" id="modalEncaissement" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="formEncaissement" method="POST" action="#">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Encaisser le paiement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Enregistrer le paiement au comptoir de <strong id="encaissementNom"></strong>
                        (<span id="encaissementMontant"></span>) ?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">Encaisser</button>
                </div>
            </form>
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
                    <h5 class="modal-title">Supprimer l'inscription</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Voulez-vous vraiment supprimer l'inscription de <strong id="suppressionNom"></strong> ?</p>
                    <p class="text-danger mb-2 d-none" id="suppressionAlerte"></p>
                    <p class="text-muted mb-0">L'utilisateur sera prévenu. Cette action est définitive.</p>
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
                <h5 class="modal-title">Supprimer les inscriptions sélectionnées</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Voulez-vous vraiment supprimer <strong id="nbSuppressionGroupee">0</strong> inscription(s) ?</p>
                <p class="text-danger mb-2">Une inscription déjà payée est ignorée : supprimez-la depuis sa ligne.</p>
                <p class="text-muted mb-0">Les utilisateurs seront prévenus. Cette action est définitive.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-danger" id="confirmerSuppressionGroupee">Supprimer</button>
            </div>
        </div>
    </div>
</div>

{{-- ===== Fenêtre « Créer / Modifier » ===== --}}
<div class="modal fade" id="modalInscription" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formInscription" method="POST" action="{{ route('admin.inscriptions.store') }}">
                @csrf
                <input type="hidden" name="_method" value="PUT" id="champMethode" disabled>
                <input type="hidden" name="_mode" id="champMode" value="{{ old('_mode') }}">
                <input type="hidden" name="_action" id="champAction" value="{{ old('_action') }}">
                <input type="hidden" name="_nom" id="champNom" value="{{ old('_nom') }}">

                <div class="modal-header">
                    <h5 class="modal-title" id="inscriptionTitre">Créer une inscription</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3" id="blocUtilisateur">
                        <label for="champUtilisateur" class="form-label">Utilisateur</label>
                        <input type="search" id="filtreUtilisateur" class="form-control form-control-sm mb-2"
                               placeholder="Filtrer par nom ou e-mail…" autocomplete="off">
                        <select name="user_id" id="champUtilisateur" class="form-select">
                            <option value="">— Choisir —</option>
                            @foreach ($utilisateurs as $utilisateur)
                                <option value="{{ $utilisateur->id }}" @selected((string) old('user_id') === (string) $utilisateur->id)>
                                    {{ $utilisateur->name }} ({{ $utilisateur->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3 d-none" id="blocUtilisateurFixe">
                        <label class="form-label">Utilisateur</label>
                        <div class="form-control-plaintext fw-medium py-0" id="utilisateurFixe"></div>
                    </div>

                    <div class="mb-3">
                        <label for="champClasse" class="form-label">Classe</label>
                        <select name="classe_id" id="champClasse" class="form-select" required>
                            <option value="">— Choisir —</option>
                            @foreach ($classesForm as $uneClasse)
                                <option value="{{ $uneClasse->id }}"
                                        data-cours="{{ $uneClasse->cours_id }}"
                                        data-pleine="{{ $uneClasse->inscriptions_count >= $uneClasse->capacite_max ? '1' : '' }}"
                                        @selected((string) old('classe_id') === (string) $uneClasse->id)>
                                    {{ $uneClasse->cours->titre }} — {{ $uneClasse->nom }}
                                    (début {{ $uneClasse->date_debut->format('d/m/Y') }},
                                    {{ $uneClasse->inscriptions_count }}/{{ $uneClasse->capacite_max }})
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text d-none" id="noteClasse"></div>
                    </div>

                    <div class="mb-0">
                        <label for="champDate" class="form-label">Date d'inscription</label>
                        <input type="date" name="date_inscription" id="champDate" class="form-control"
                               max="{{ today()->toDateString() }}"
                               value="{{ old('date_inscription', today()->toDateString()) }}" required>
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
    const urlAjout = @json(route('admin.inscriptions.store'));
    const aujourdhui = @json(today()->toDateString());
    const modeAvecErreur = @json($modeAvecErreur);

    const modalInscription = document.getElementById('modalInscription');
    const form = document.getElementById('formInscription');
    const champ = id => document.getElementById(id);
    const optionsClasse = Array.from(champ('champClasse').options).filter(o => o.value);

    // Sélection multiple
    const cases = Array.from(document.querySelectorAll('.case-inscription'));
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

    // Suppression groupée
    champ('supprimerSelection').addEventListener('click', function () {
        champ('nbSuppressionGroupee').textContent = cases.filter(c => c.checked).length;
        bootstrap.Modal.getOrCreateInstance(champ('modalSuppressionGroupee')).show();
    });
    champ('confirmerSuppressionGroupee').addEventListener('click', function () {
        champ('formGroupe').submit();
    });

    majSelection();

    // Préparation du formulaire d'ajout ou de modification
    function preparer(mode) {
        const modif = mode === 'modif';
        champ('inscriptionTitre').textContent = modif ? "Modifier l'inscription" : 'Créer une inscription';
        champ('champMode').value = mode;
        champ('champMethode').disabled = !modif;
        champ('blocUtilisateur').classList.toggle('d-none', modif);
        champ('blocUtilisateurFixe').classList.toggle('d-none', !modif);
        champ('champUtilisateur').disabled = modif;
        champ('champUtilisateur').required = !modif;
        champ('utilisateurFixe').textContent = champ('champNom').value;
    }

    // Filtrage des classes disponibles
    function filtrerClasses(classeActuelle, coursImpose) {
        optionsClasse.forEach(function (o) {
            const pleine = o.dataset.pleine === '1' && o.value !== classeActuelle;
            const autreCours = coursImpose && o.dataset.cours !== coursImpose;
            o.disabled = pleine || autreCours;
            o.hidden = autreCours;
        });
    }

    // Ouverture de la fenêtre de création ou de modification
    modalInscription.addEventListener('show.bs.modal', function (e) {
        const bouton = e.relatedTarget;
        if (!bouton) return;

        const d = bouton.dataset;
        const note = champ('noteClasse');
        champ('champNom').value = d.nom || '';
        preparer(d.mode);
        note.classList.add('d-none');

        if (d.mode === 'modif') {
            champ('champAction').value = d.action;
            form.action = d.action;
            filtrerClasses(d.classe, d.payee ? d.cours : null);
            champ('champClasse').value = d.classe;
            champ('champDate').value = d.date;

            if (d.bloque) {
                note.textContent = "La classe a déjà commencé : l'inscription ne peut plus changer de classe (seule la date est modifiable).";
                note.classList.remove('d-none');
            } else if (d.payee) {
                note.textContent = 'Inscription payée : seules les classes du même cours sont proposées.';
                note.classList.remove('d-none');
            }
        } else {
            champ('champAction').value = urlAjout;
            form.action = urlAjout;
            filtrerClasses(null, null);
            champ('champUtilisateur').value = '';
            champ('filtreUtilisateur').value = '';
            Array.from(champ('champUtilisateur').options).forEach(o => o.hidden = false);
            champ('champClasse').value = '';
            champ('champDate').value = aujourdhui;
        }
    });

    // Réouverture de la fenêtre après une erreur de validation
    if (modeAvecErreur) {
        preparer(modeAvecErreur);
        filtrerClasses(null, null);
        form.action = champ('champAction').value || urlAjout;
        bootstrap.Modal.getOrCreateInstance(modalInscription).show();
    }

    // Filtre de la liste d'utilisateurs dans la fenêtre d'inscription
    champ('filtreUtilisateur').addEventListener('input', function () {
        const terme = this.value.trim().toLocaleLowerCase();
        Array.from(champ('champUtilisateur').options).forEach(function (o) {
            o.hidden = o.value !== '' && !o.textContent.toLocaleLowerCase().includes(terme);
        });
    });

    // Fenêtre d'encaissement
    document.getElementById('modalEncaissement').addEventListener('show.bs.modal', function (e) {
        const d = e.relatedTarget.dataset;
        champ('encaissementNom').textContent = d.nom;
        champ('encaissementMontant').textContent = d.montant;
        champ('formEncaissement').action = d.action;
    });

    // Fenêtre de suppression
    document.getElementById('modalSuppression').addEventListener('show.bs.modal', function (e) {
        const d = e.relatedTarget.dataset;
        const alerte = champ('suppressionAlerte');

        champ('suppressionNom').textContent = d.nom;
        champ('formSuppression').action = d.action;

        if (d.paye) {
            alerte.textContent = 'Cette inscription est payée (' + d.paye + ') : le paiement sera supprimé avec elle.';
        }
        alerte.classList.toggle('d-none', !d.paye);
    });

    // Fenêtre « Voir »
    document.getElementById('modalDetail').addEventListener('show.bs.modal', function (e) {
        const bouton = e.relatedTarget;
        champ('detailTitre').textContent = bouton.dataset.titre;
        champ('detailCorps').innerHTML = champ(bouton.dataset.detail).innerHTML;
    });
});
</script>
@endsection