@extends('layouts.master')

@section('title', 'Gestion des sessions')

@section('content')

@component('components.breadcrumb')
    @slot('li_1')
        <a href="{{ route('dashboard') }}">Accueil</a>
    @endslot
    @slot('title')
        Sessions
    @endslot
@endcomponent

@php
    // Retour d'une validation qui a échoué : "_mode" (ajout ou modification) dit quelle fenêtre rouvrir
    // (erreur de validation OU message d'erreur du serveur, tant que la saisie a été conservée)
    $modeAvecErreur = ($errors->any() || session('error')) ? old('_mode') : null;
    $erreurClasse = in_array($modeAvecErreur, ['classe-ajout', 'classe-modif'], true);
    $libellesEtat = ['a_venir' => 'À venir', 'en_cours' => 'En cours', 'terminee' => 'Terminée'];

    // Session à déplier d'office : après un ajout (?ouvrir=ID) ou quand la fenêtre d'une de ses classes se rouvre sur une erreur
    $sessionOuverte = (int) (request('ouvrir') ?: ($erreurClasse ? old('_session') : 0));
@endphp

<div class="container-fluid pb-4">

    {{-- Statistiques --}}
    <div class="row g-2 mb-3">
        @foreach ([
            ['ri-calendar-schedule-line', 'info', $stats['sessions'], 'Sessions'],
            ['ri-building-line', 'success', $stats['entreprises'], 'Entreprises avec une session'],
            ['ri-graduation-cap-line', 'primary', $stats['classes'], 'Classes de session'],
        ] as [$icone, $teinte, $valeur, $libelle])
            <div class="col-sm-6 col-xl-4">
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

    @php
        $libellesEtats = ['en_cours' => 'En cours', 'a_venir' => 'À venir', 'terminee' => 'Terminées'];

        // URL de la liste sans certains paramètres (sert au "×" de chaque étiquette)
        $sans = fn (array $cles) => route('admin.sessions.index', request()->except(array_merge($cles, ['page'])));

        // Étiquettes des filtres actifs : [texte, paramètres à retirer, icône]
        $etiquettes = [];
        if ($etats) {
            $etiquettes[] = [implode(' / ', array_map(fn ($e) => $libellesEtats[$e], $etats)), ['etat'], 'ri-flag-line'];
        }
        if (request()->filled('date_debut')) {
            $etiquettes[] = [
                'Début le ' . \Illuminate\Support\Carbon::parse(request('date_debut'))->format('d/m/Y'),
                ['date_debut'],
                'ri-calendar-event-line',
            ];
        }
    @endphp

    {{-- ===== Barre de suppression groupée : cachée tant qu'aucune session n'est cochée.
         Les cases du tableau y sont rattachées par form="formGroupe". ===== --}}
    <form id="formGroupe" method="POST" action="{{ route('admin.sessions.destroy-multiple') }}">
        @csrf
        <div id="barreActions" class="card border shadow-sm mb-3 d-none">
            <div class="card-body d-flex flex-wrap align-items-center gap-2">
                <strong><span id="nbSelection">0</span> session(s) sélectionnée(s)</strong>
                <button type="button" class="btn btn-danger" id="supprimerSelection">
                    <i class="ri-delete-bin-line me-1"></i>Supprimer
                </button>
                <button type="button" id="annulerSelection" class="btn btn-outline-secondary">Désélectionner</button>
            </div>
        </div>
    </form>

    {{-- ===== Ligne d'outils harmonisée avec l'index des cours ===== --}}
    <div class="d-flex flex-wrap align-items-start justify-content-between gap-2 mb-3">
        <button type="button" class="btn btn-primary fw-semibold"
                data-bs-toggle="modal" data-bs-target="#modalSession" data-mode="ajout">
            <i class="ri-add-line me-1"></i>Créer une session
        </button>

        <form method="GET" action="{{ route('admin.sessions.index') }}" class="col-12 col-md-8 col-lg-6 col-xl-5 ms-md-auto">
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
                           placeholder="Rechercher une session ou une entreprise…" aria-label="Rechercher une session ou une entreprise">
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
                                    <i class="ri-equalizer-line text-primary me-2"></i>Filtres des sessions
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
                                            <h6 class="fw-semibold mb-3"><i class="ri-flag-line text-primary me-1"></i>État</h6>
                                            @foreach ($libellesEtats as $valeur => $libelle)
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" name="etat[]" value="{{ $valeur }}"
                                                           id="etat-{{ $valeur }}" @checked(in_array($valeur, $etats, true))>
                                                    <label class="form-check-label" for="etat-{{ $valeur }}">{{ $libelle }}</label>
                                                </div>
                                            @endforeach
                                            <p class="text-muted fs-13 mb-0 mt-3">Plusieurs états cochés : les sessions de l'un ou de l'autre s'affichent.</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="card h-100 mb-0">
                                        <div class="card-body">
                                            <h6 class="fw-semibold mb-3"><i class="ri-calendar-event-line text-primary me-1"></i>Date de début</h6>
                                            <label for="filtre-date-debut" class="form-label small mb-1">Date de début de la session</label>
                                            <input type="date" name="date_debut" id="filtre-date-debut"
                                                   class="form-control form-control-sm" value="{{ request('date_debut') }}">
                                            <p class="text-muted fs-13 mb-0 mt-3">La date correspond au début de la première classe de la session.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer justify-content-between">
                            <a href="{{ route('admin.sessions.index') }}" class="btn btn-outline-secondary">
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

    <div class="card border shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0 table-liste">
                <thead>
                    <tr class="table-light">
                        <th class="col-coche"><input type="checkbox" id="toutSelectionner" class="form-check-input" aria-label="Tout sélectionner"></th>
                        <th>Session</th>
                        <th>Entreprise</th>
                        <th class="col-periode">Dates</th>
                        <th class="col-nombre">Classes</th>
                        <th class="col-badge">État</th>
                        <th class="text-end col-actions-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sessions as $session)
                        <tr>
                            <td>
                                <input type="checkbox" name="ids[]" value="{{ $session->id }}" form="formGroupe"
                                       class="form-check-input case-session" aria-label="Sélectionner {{ $session->nom }}">
                            </td>
                            <td title="{{ $session->nom }}">
                                <span class="fw-medium">{{ $session->nom }}</span>
                            </td>
                            <td class="text-muted" title="{{ $session->entreprise->raison_sociale }}">{{ $session->entreprise->raison_sociale }}</td>
                            <td>
                                @if ($session->date_debut)
                                    {{ $session->date_debut->format('d/m/Y') }} → {{ $session->date_fin->format('d/m/Y') }}
                                @else
                                    <span class="text-muted">Aucune classe</span>
                                @endif
                            </td>
                            <td><span class="badge bg-primary-subtle text-primary">{{ $session->classes_count }}</span></td>
                            <td><span class="badge bg-{{ $session->etat_couleur }}-subtle text-{{ $session->etat_couleur }}">{{ $session->etat_libelle }}</span></td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    <button type="button" class="btn btn-sm btn-soft-success" title="Voir les classes de la session"
                                            data-bs-toggle="modal" data-bs-target="#modalClassesSession"
                                            data-session="{{ $session->id }}" data-titre="{{ $session->nom }}">
                                        <i class="ri-folder-open-line"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-soft-secondary" title="Modifier la session"
                                            data-bs-toggle="modal" data-bs-target="#modalSession"
                                            data-mode="modif"
                                            data-action="{{ route('admin.sessions.update', $session) }}"
                                            data-nom="{{ $session->nom }}"
                                            data-entreprise="{{ $session->entreprise_id }}"
                                            data-description="{{ $session->description }}">
                                        <i class="ri-edit-line"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-soft-danger" title="Supprimer la session"
                                            data-bs-toggle="modal" data-bs-target="#modalSuppression"
                                            data-action="{{ route('admin.sessions.destroy', $session) }}"
                                            data-nom="{{ $session->nom }}"
                                            data-classes="{{ (int) $session->classes_count }}">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="ri-calendar-schedule-line display-5 d-block mb-2"></i>
                                Aucune session ne correspond à votre recherche.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($sessions->total() > 0)
            <div class="card-footer bg-transparent d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <small class="text-muted">
                    Affichage de {{ $sessions->firstItem() }} à {{ $sessions->lastItem() }} sur {{ $sessions->total() }} résultat(s)
                </small>
                {{ $sessions->links() }}
            </div>
        @endif
    </div>
</div>

@foreach ($sessions as $session)
@foreach ($session->classes as $classe)
    <template id="membres-classe-{{ $classe->id }}">
        @forelse ($classe->inscriptions as $inscription)
            <div class="d-flex justify-content-between align-items-start gap-3 {{ $loop->last ? '' : 'border-bottom pb-2 mb-2' }}">
                <div class="min-w-0">
                    <div class="fw-medium">{{ $inscription->user?->name ?? 'Compte supprimé' }}</div>
                    <div class="text-muted small">{{ $inscription->user?->email ?? '—' }}</div>
                </div>
                <div class="text-end flex-shrink-0">
                    {{-- Payé / Paiement partiel (+ reste à payer) / En attente --}}
                    @include('paiements._statut-badge', ['inscription' => $inscription])
                    <div class="text-muted small mt-1">Inscrit le {{ $inscription->created_at?->format('d/m/Y') ?? '—' }}</div>
                </div>
            </div>
        @empty
            <p class="text-muted mb-0">Aucun membre n'est inscrit dans cette classe.</p>
        @endforelse
    </template>
@endforeach
@endforeach

{{-- ===== Informations de la classe et membres inscrits ===== --}}
<div class="modal fade" id="modalInfoClasse" tabindex="-1" aria-labelledby="modalInfoClasseTitre" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title fw-semibold" id="modalInfoClasseTitre"></h5>
                    <p class="text-muted small mb-0" id="modalInfoClasseCours"></p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <div class="text-muted small">Période</div>
                        <div><span id="modalInfoClasseDebut"></span> → <span id="modalInfoClasseFin"></span></div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small">Formateur</div>
                        <div id="modalInfoClasseFormateur"></div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small">Lieu</div>
                        <div id="modalInfoClasseLieu"></div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small">Inscrits / capacité</div>
                        <div id="modalInfoClasseCapacite"></div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small">État</div>
                        <span id="modalInfoClasseEtat" class="badge"></span>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2 mb-3">
                    <h6 class="fw-semibold mb-0">Membres inscrits</h6>
                    <span class="badge bg-primary-subtle text-primary" id="modalInfoClasseNombre"></span>
                </div>
                <div id="modalInfoClasseMembres" class="d-flex flex-column gap-2"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

{{-- ===== Ajouter / modifier une classe (un seul formulaire pour les deux) ===== --}}
<div class="modal fade" id="modalClasse" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formClasse" method="POST" action="#" novalidate>
                @csrf
                <input type="hidden" name="_method" value="PUT" id="classeMethode" disabled>
                <input type="hidden" name="_mode" id="classeMode" value="{{ old('_mode') }}">
                <input type="hidden" name="_action" id="classeAction" value="{{ old('_action') }}">
                <input type="hidden" name="_session" id="classeSession" value="{{ old('_session') }}">

                <div class="modal-header">
                    <h5 class="modal-title" id="classeTitre">Ajouter une classe</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">

                    <div class="row g-3">
                        <div class="col-md-6" id="blocCours">
                            <label for="classeCours" class="form-label">Cours <span class="text-danger">*</span></label>
                            <select name="cours_id" id="classeCours" class="form-select">
                                <option value="">— Choisir —</option>
                                @foreach ($tousLesCours as $c)
                                    <option value="{{ $c->id }}" @selected((string) old('cours_id') === (string) $c->id)>{{ $c->titre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="classeNom" class="form-label">Nom de la classe <span class="text-danger">*</span></label>
                            <input type="text" name="nom" id="classeNom" class="form-control" value="{{ old('nom') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label for="classeDebut" class="form-label">Début <span class="text-danger">*</span></label>
                            <input type="date" name="date_debut" id="classeDebut" class="form-control" value="{{ old('date_debut') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label for="classeFin" class="form-label">Fin <span class="text-danger">*</span></label>
                            <input type="date" name="date_fin" id="classeFin" class="form-control" value="{{ old('date_fin') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label for="classeCapacite" class="form-label">Capacité <span class="text-danger">*</span></label>
                            <input type="number" name="capacite_max" id="classeCapacite" class="form-control" min="1" max="255" value="{{ old('capacite_max', 10) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="classeFormateur" class="form-label">Formateur <span class="text-muted">(facultatif)</span></label>
                            <select name="formateur_id" id="classeFormateur" class="form-select">
                                <option value="">— Aucun —</option>
                                @foreach ($tousLesFormateurs as $f)
                                    <option value="{{ $f->id }}" @selected((string) old('formateur_id') === (string) $f->id)>{{ $f->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="classeLieu" class="form-label">Lieu <span class="text-danger">*</span></label>
                            <input type="text" name="lieu" id="classeLieu" class="form-control" value="{{ old('lieu', \App\Models\Classe::LIEU_PAR_DEFAUT) }}" required>
                        </div>
                        <div class="col-12">
                            <label for="classeLien" class="form-label">Lien Google Maps <span class="text-muted">(facultatif)</span></label>
                            <input type="url" name="lieu_lien" id="classeLien" class="form-control" value="{{ old('lieu_lien', \App\Models\Classe::LIEU_LIEN_PAR_DEFAUT) }}">
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

{{-- ===== Suppression d'une classe : confirme, ou explique pourquoi c'est impossible ===== --}}
<div class="modal fade" id="modalSuppressionClasse" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="formSuppressionClasse" method="POST" action="#">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title">Supprimer la classe</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <div id="suppClasseConfirmation">
                        <p class="mb-2">Voulez-vous vraiment supprimer la classe <strong id="suppClasseNom"></strong> ?</p>
                        <p class="text-muted mb-0">Cette action est définitive.</p>
                    </div>
                    <div id="suppClasseBloquee" class="d-none">
                        <p class="mb-2"><strong id="suppClasseNomBloquee"></strong> ne peut pas être supprimée : <span id="suppClasseNombre"></span> membre(s) y sont inscrits.</p>
                        <p class="text-muted mb-0">Désinscrivez-les d'abord (bouton « Gérer »), puis revenez la supprimer.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fermer</button>
                    <button type="submit" class="btn btn-danger" id="suppClasseValider">Supprimer</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Contenu de la fenêtre « Classes de la session » : un modèle caché par session --}}
@foreach ($sessions as $session)
    <template id="classes-{{ $session->id }}">
        @if ($session->description)
            <p class="text-muted small mb-3">{{ $session->description }}</p>
        @endif
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
            <h6 class="fw-semibold mb-0">Classes de la session ({{ $session->classes->count() }})</h6>
            <button type="button" class="btn btn-sm btn-primary"
                    data-bs-toggle="modal" data-bs-target="#modalClasse"
                    data-mode="classe-ajout" data-session="{{ $session->id }}"
                    data-action="{{ route('admin.sessions.classes.store', $session) }}">
                <i class="ri-add-line me-1"></i>Ajouter une classe
            </button>
        </div>
        <div class="table-responsive bg-white border rounded">
            <table class="table table-sm align-middle mb-0 table-liste">
                <thead>
                    <tr class="table-light">
                        <th>Cours</th>
                        <th>Classe</th>
                        <th class="col-periode">Période</th>
                        <th>Formateur</th>
                        <th class="col-nombre">Inscrits</th>
                        <th class="col-badge">État</th>
                        <th class="text-end col-actions-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($session->classes as $classe)
                        @php $nbInscrits = $classe->inscriptions->count(); @endphp
                        <tr>
                            <td class="fw-semibold" title="{{ $classe->cours?->titre }}">{{ $classe->cours?->titre ?? 'Cours supprimé' }}</td>
                            <td class="fw-medium" title="{{ $classe->nom }}">{{ $classe->nom }}</td>
                            <td>{{ $classe->date_debut->format('d/m/Y') }} → {{ $classe->date_fin->format('d/m/Y') }}</td>
                            <td class="text-muted" title="{{ $classe->formateur?->name }}">{{ $classe->formateur?->name ?? 'Sans formateur' }}</td>
                            <td><span class="badge bg-primary-subtle text-primary">{{ $nbInscrits }} / {{ $classe->capacite_max }}</span></td>
                            <td><span class="badge bg-{{ $classe->etat_couleur }}-subtle text-{{ $classe->etat_couleur }}">{{ $libellesEtat[$classe->etat] ?? $classe->etat }}</span></td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    @if ($classe->cours)
                                        <button type="button" class="btn btn-sm btn-soft-primary" title="Voir les informations de la classe"
                                                aria-label="Voir les informations de la classe {{ $classe->nom }}"
                                                data-bs-toggle="modal" data-bs-target="#modalInfoClasse"
                                                data-membres="membres-classe-{{ $classe->id }}"
                                                data-nom="{{ $classe->nom }}"
                                                data-cours="{{ $classe->cours->titre }}"
                                                data-debut="{{ $classe->date_debut->format('d/m/Y') }}"
                                                data-fin="{{ $classe->date_fin->format('d/m/Y') }}"
                                                data-formateur="{{ $classe->formateur?->name ?? 'Sans formateur' }}"
                                                data-lieu="{{ $classe->lieu }}"
                                                data-capacite="{{ $nbInscrits }} / {{ $classe->capacite_max }}"
                                                data-etat="{{ $libellesEtat[$classe->etat] ?? $classe->etat }}"
                                                data-etat-couleur="{{ $classe->etat_couleur }}">
                                            <i class="ri-eye-line"></i>
                                        </button>
                                        <a href="{{ route('admin.cours.classes.gerer', [$classe->cours, $classe]) }}" class="btn btn-sm btn-soft-primary" title="Gérer les inscrits et les jours non comptabilisés">
                                            <i class="ri-team-line"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-soft-secondary" title="Modifier la classe"
                                                data-bs-toggle="modal" data-bs-target="#modalClasse"
                                                data-mode="classe-modif" data-session="{{ $session->id }}"
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
                                    @endif
                                    <button type="button" class="btn btn-sm btn-soft-danger" title="Supprimer la classe"
                                            data-bs-toggle="modal" data-bs-target="#modalSuppressionClasse"
                                            data-action="{{ route('admin.sessions.classes.destroy', [$session, $classe]) }}"
                                            data-nom="{{ $classe->nom }}"
                                            data-inscrits="{{ $nbInscrits }}">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Cette session ne contient encore aucune classe.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </template>
@endforeach

{{-- ===== Classes d'une session ===== --}}
<div class="modal fade" id="modalClassesSession" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-truncate" id="classesSessionTitre">Session</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body" id="classesSessionContenu"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

{{-- ===== Fenêtre « Créer / Modifier » (un seul formulaire pour les deux) ===== --}}
<div class="modal fade" id="modalSession" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formSession" method="POST" action="{{ route('admin.sessions.store') }}" novalidate>
                @csrf
                {{-- _method = PUT seulement en modification ; _mode et _action permettent de rouvrir la bonne fenêtre si la validation échoue --}}
                <input type="hidden" name="_method" value="PUT" id="champMethode" disabled>
                <input type="hidden" name="_mode" id="champMode" value="{{ old('_mode') }}">
                <input type="hidden" name="_action" id="champAction" value="{{ old('_action') }}">

                <div class="modal-header">
                    <h5 class="modal-title" id="sessionTitre">Créer une session</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>

                <div class="modal-body">

                    <div class="mb-3">
                        <label for="champNom" class="form-label">Nom de la session <span class="text-danger">*</span></label>
                        <input type="text" name="nom" id="champNom" class="form-control" value="{{ old('nom') }}"
                               placeholder="Ex. Word niveau 1 — Janvier 2027" required>
                    </div>

                    <div class="mb-3">
                        <label for="champEntreprise" class="form-label">Entreprise <span class="text-danger">*</span></label>
                        <select name="entreprise_id" id="champEntreprise" class="form-select" required>
                            <option value="">— Choisir —</option>
                            @foreach ($entreprises as $entreprise)
                                <option value="{{ $entreprise->id }}" @selected((string) old('entreprise_id') === (string) $entreprise->id)>
                                    {{ $entreprise->raison_sociale }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-0">
                        <label for="champDescription" class="form-label">Description <span class="text-muted">(facultative)</span></label>
                        <textarea name="description" id="champDescription" rows="3" maxlength="2000" class="form-control">{{ old('description') }}</textarea>
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

{{-- ===== Fenêtre de suppression : confirme, ou explique pourquoi c'est impossible ===== --}}
<div class="modal fade" id="modalSuppression" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="formSuppression" method="POST" action="#">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title">Supprimer la session</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <div id="suppressionConfirmation">
                        <p class="mb-2">Voulez-vous vraiment supprimer la session <strong id="suppressionNom"></strong> ?</p>
                        <p class="text-muted mb-0">Cette action est définitive.</p>
                    </div>

                    <div id="suppressionBloquee" class="d-none">
                        <p class="mb-2"><strong id="suppressionNomBloquee"></strong> ne peut pas être supprimée : elle contient encore <span id="suppressionNombre"></span> classe(s).</p>
                        <p class="text-muted mb-0">Ouvrez la session (icône dossier), supprimez ses classes, puis revenez la supprimer.</p>
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
                <h5 class="modal-title">Supprimer les sessions sélectionnées</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Voulez-vous vraiment supprimer <strong id="nbSuppressionGroupee">0</strong> session(s) ?</p>
                <p class="text-muted mb-2">Une session qui contient encore des classes est ignorée : supprimez d'abord ses classes.</p>
                <p class="text-muted mb-0">Cette action est définitive.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-danger" id="confirmerSuppressionGroupee">Supprimer</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const champ = id => document.getElementById(id);

    // Sélection multiple : la barre de suppression apparaît dès qu'au moins une session est cochée
    const cases = Array.from(document.querySelectorAll('.case-session'));
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
    const urlAjout = @json(route('admin.sessions.store'));
    const modeAvecErreur = @json($modeAvecErreur);
    const modalSession = champ('modalSession');
    const formSession = champ('formSession');

    // Adapte la fenêtre au mode : "ajout" (POST) ou "modif" (PUT)
    function preparer(mode) {
        const modif = mode === 'modif';
        champ('sessionTitre').textContent = modif ? 'Modifier la session' : 'Créer une session';
        champ('champMode').value = mode;
        champ('champMethode').disabled = !modif;
    }

    modalSession.addEventListener('show.bs.modal', function (e) {
        const bouton = e.relatedTarget;
        if (!bouton) return;

        const d = bouton.dataset;
        preparer(d.mode);

        if (d.mode === 'modif') {
            champ('champAction').value = d.action;
            formSession.action = d.action;
            champ('champNom').value = d.nom;
            champ('champEntreprise').value = d.entreprise;
            champ('champDescription').value = d.description || '';
        } else {
            champ('champAction').value = urlAjout;
            formSession.action = urlAjout;
            champ('champNom').value = '';
            champ('champEntreprise').value = '';
            champ('champDescription').value = '';
        }
    });

    // Validation refusée : on rouvre la fenêtre avec ce qui avait été saisi (déjà dans les champs)
    if (modeAvecErreur === 'ajout' || modeAvecErreur === 'modif') {
        preparer(modeAvecErreur);
        formSession.action = champ('champAction').value || urlAjout;
        bootstrap.Modal.getOrCreateInstance(modalSession).show();
    }

    champ('modalSuppression').addEventListener('show.bs.modal', function (e) {
        const d = e.relatedTarget.dataset;
        const nombre = parseInt(d.classes, 10);
        const bloquee = nombre > 0;

        champ('suppressionNom').textContent = d.nom;
        champ('suppressionNomBloquee').textContent = d.nom;
        champ('suppressionNombre').textContent = nombre;
        champ('formSuppression').action = d.action;

        champ('suppressionConfirmation').classList.toggle('d-none', bloquee);
        champ('suppressionBloquee').classList.toggle('d-none', !bloquee);
        champ('suppressionValider').classList.toggle('d-none', bloquee);
    });
    // ------------------------------------------------------------------
    // Fenêtre « Classes de la session » : on clone le modèle caché de la session.
    // Les fenêtres ouvertes depuis celle-ci (infos, modifier, supprimer) la remplacent,
    // puis on y revient en les fermant. L'id est gardé dans l'onglet pour survivre à un rechargement.
    // ------------------------------------------------------------------
    const CLE = 'session-ouverte';
    const modalClasses = champ('modalClassesSession');
    const secondaires = ['modalInfoClasse', 'modalClasse', 'modalSuppressionClasse'].map(champ);
    let sessionCourante = null;
    let retour = false;

    function memoriser(id) {
        try { id ? sessionStorage.setItem(CLE, id) : sessionStorage.removeItem(CLE); } catch (e) {}
    }

    function remplir(id, titre) {
        sessionCourante = id;
        champ('classesSessionTitre').textContent = titre;
        champ('classesSessionContenu').replaceChildren(document.getElementById('classes-' + id).content.cloneNode(true));
    }

    modalClasses.addEventListener('show.bs.modal', function (e) {
        if (e.relatedTarget) {
            remplir(e.relatedTarget.dataset.session, e.relatedTarget.dataset.titre);
        }
        memoriser(sessionCourante);
    });

    modalClasses.addEventListener('hidden.bs.modal', function () {
        if (!secondaires.some(m => m.classList.contains('show')) && !retour) {
            memoriser(null);
        }
    });

    secondaires.forEach(function (m) {
        m.addEventListener('show.bs.modal', function () { retour = true; });
        m.addEventListener('hidden.bs.modal', function () {
            if (retour && sessionCourante) {
                retour = false;
                bootstrap.Modal.getOrCreateInstance(modalClasses).show();
            }
        });
    });

    // Au chargement : après un ajout (?ouvrir=ID), une erreur sur une classe, ou une modification, on rouvre la session
    let aOuvrir = @json($sessionOuverte);
    if (!aOuvrir) { try { aOuvrir = sessionStorage.getItem(CLE); } catch (e) {} }
    const modeleOuvrir = aOuvrir ? document.getElementById('classes-' + aOuvrir) : null;
    const boutonSession = modeleOuvrir ? document.querySelector('[data-bs-target="#modalClassesSession"][data-session="' + aOuvrir + '"]') : null;
    if (modeleOuvrir && !@json($erreurClasse)) {
        remplir(aOuvrir, boutonSession ? boutonSession.dataset.titre : 'Session');
        bootstrap.Modal.getOrCreateInstance(modalClasses).show();
    }

    // ------------------------------------------------------------------
    // Informations d'une classe et de ses membres
    // ------------------------------------------------------------------
    champ('modalInfoClasse').addEventListener('show.bs.modal', function (e) {
        const bouton = e.relatedTarget;
        if (!bouton) return;
        const d = bouton.dataset;
        champ('modalInfoClasseTitre').textContent = d.nom;
        champ('modalInfoClasseCours').textContent = d.cours;
        champ('modalInfoClasseDebut').textContent = d.debut;
        champ('modalInfoClasseFin').textContent = d.fin;
        champ('modalInfoClasseFormateur').textContent = d.formateur;
        champ('modalInfoClasseLieu').textContent = d.lieu || '—';
        champ('modalInfoClasseCapacite').textContent = d.capacite;
        const etat = champ('modalInfoClasseEtat');
        etat.textContent = d.etat;
        etat.className = 'badge bg-' + d.etatCouleur + '-subtle text-' + d.etatCouleur;

        const modeleMembres = document.getElementById(d.membres);
        champ('modalInfoClasseMembres').replaceChildren(modeleMembres.content.cloneNode(true));
        champ('modalInfoClasseNombre').textContent = modeleMembres.content.querySelectorAll('.fw-medium').length;
    });

    // ------------------------------------------------------------------
    // Fenêtre « Ajouter / modifier une classe »
    // ------------------------------------------------------------------
    const modalClasse = champ('modalClasse');
    const formClasse = champ('formClasse');

    function preparerClasse(mode) {
        const modif = mode === 'classe-modif';
        champ('classeTitre').textContent = modif ? 'Modifier la classe' : 'Ajouter une classe';
        champ('classeMode').value = mode;
        champ('classeMethode').disabled = !modif;
        // le cours ne se change pas après création
        champ('blocCours').classList.toggle('d-none', modif);
        champ('classeCours').disabled = modif;
    }

    modalClasse.addEventListener('show.bs.modal', function (e) {
        const bouton = e.relatedTarget;
        if (!bouton) return;
        const d = bouton.dataset;
        preparerClasse(d.mode);
        champ('classeSession').value = d.session;
        champ('classeAction').value = d.action;
        formClasse.action = d.action;

        if (d.mode === 'classe-modif') {
            champ('classeNom').value = d.nom;
            champ('classeDebut').value = d.debut;
            champ('classeFin').value = d.fin;
            champ('classeCapacite').value = d.capacite;
            champ('classeFormateur').value = d.formateur || '';
            champ('classeLieu').value = d.lieu;
            champ('classeLien').value = d.lien || '';
        } else {
            champ('classeCours').value = '';
            champ('classeNom').value = '';
            champ('classeDebut').value = '';
            champ('classeFin').value = '';
            champ('classeCapacite').value = 10;
            champ('classeFormateur').value = '';
            champ('classeLieu').value = @json(\App\Models\Classe::LIEU_PAR_DEFAUT);
            champ('classeLien').value = @json(\App\Models\Classe::LIEU_LIEN_PAR_DEFAUT);
        }
    });

    // Suppression d'une classe
    champ('modalSuppressionClasse').addEventListener('show.bs.modal', function (e) {
        const d = e.relatedTarget.dataset;
        const nombre = parseInt(d.inscrits, 10);
        const bloquee = nombre > 0;
        champ('suppClasseNom').textContent = d.nom;
        champ('suppClasseNomBloquee').textContent = d.nom;
        champ('suppClasseNombre').textContent = nombre;
        champ('formSuppressionClasse').action = d.action;
        champ('suppClasseConfirmation').classList.toggle('d-none', bloquee);
        champ('suppClasseBloquee').classList.toggle('d-none', !bloquee);
        champ('suppClasseValider').classList.toggle('d-none', bloquee);
    });

    // Validation refusée sur une classe : on rouvre sa fenêtre (la session est dépliée plus haut)
    if (@json($erreurClasse)) {
        if (modeleOuvrir) {
            remplir(aOuvrir, boutonSession ? boutonSession.dataset.titre : 'Session');
        }
        preparerClasse(modeAvecErreur);
        formClasse.action = champ('classeAction').value;
        retour = true;
        bootstrap.Modal.getOrCreateInstance(modalClasse).show();
    }
});
</script>
@endsection