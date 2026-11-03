@extends('layouts.master')

@section('title', 'Session ' . $session->nom)

@section('content')

@component('components.breadcrumb')
    @slot('li_1')
        <a href="{{ route('admin.sessions.index') }}">Sessions</a>
    @endslot
    @slot('title')
        {{ $session->nom }}
    @endslot
@endcomponent

@php
    // Retour d'une validation qui a échoué : "_mode" dit quelle fenêtre rouvrir
    // (erreur de validation OU message d'erreur du serveur, tant que la saisie a été conservée)
    $modeAvecErreur = ($errors->any() || session('error')) ? old('_mode') : null;
    $libellesEtat = ['a_venir' => 'À venir', 'en_cours' => 'En cours', 'terminee' => 'Terminée'];

    // URL de la page sans certains paramètres (sert au "×" de chaque étiquette)
    $sans = fn (array $cles) => route('admin.sessions.show', array_merge(['sessionFormation' => $session->id], request()->except(array_merge($cles, ['page']))));

    // Étiquettes des filtres actifs : [texte, paramètres à retirer, icône]
    $etiquettes = [];
    if ($etats) {
        $etiquettes[] = [implode(' / ', array_map(fn ($e) => $libellesEtat[$e], $etats)), ['etat'], 'ri-flag-line'];
    }
    if (request()->filled('cours_id')) {
        $etiquettes[] = [$coursDeLaSession->firstWhere('id', (int) request('cours_id'))?->titre ?? 'Cours', ['cours_id'], 'ri-book-open-line'];
    }
    if (request()->filled('formateur_id')) {
        $etiquettes[] = [$formateursDeLaSession->firstWhere('id', (int) request('formateur_id'))?->name ?? 'Formateur', ['formateur_id'], 'ri-user-star-line'];
    }
    if (request()->filled('date_debut')) {
        $etiquettes[] = ['Début le ' . \Illuminate\Support\Carbon::parse(request('date_debut'))->format('d/m/Y'), ['date_debut'], 'ri-calendar-event-line'];
    }
    $rechercheActive = request()->filled('q') || count($etiquettes) > 0;
@endphp

<div class="container-fluid pb-4">

    {{-- ===== Barre de suppression groupée : cachée tant qu'aucune classe n'est cochée.
         Les cases du tableau y sont rattachées par form="formGroupe". ===== --}}
    <form id="formGroupe" method="POST" action="{{ route('admin.sessions.classes.destroy-multiple', $session) }}">
        @csrf
        <div id="barreActions" class="card border shadow-sm mb-3 d-none">
            <div class="card-body d-flex flex-wrap align-items-center gap-2">
                <strong><span id="nbSelection">0</span> classe(s) sélectionnée(s)</strong>
                <button type="button" class="btn btn-danger" id="supprimerSelection">
                    <i class="ri-delete-bin-line me-1"></i>Supprimer
                </button>
                <button type="button" id="annulerSelection" class="btn btn-outline-secondary">Désélectionner</button>
            </div>
        </div>
    </form>

    {{-- Ajouter une classe à gauche, recherche à droite --}}
    <div class="d-flex flex-wrap align-items-start justify-content-between gap-2 mb-3">
        <button type="button" class="btn btn-primary fw-semibold" data-bs-toggle="modal" data-bs-target="#modalClasse" data-mode="classe-ajout">
            <i class="ri-add-line me-1"></i>Ajouter une classe
        </button>

        <form method="GET" action="{{ route('admin.sessions.show', $session) }}" class="col-12 col-md-8 col-lg-6 col-xl-5 ms-md-auto">
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
                           placeholder="Rechercher une classe, un cours ou un formateur…" aria-label="Rechercher une classe, un cours ou un formateur">
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
                                    <i class="ri-equalizer-line text-primary me-2"></i>Filtres des classes
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
                                            @foreach ($libellesEtat as $valeur => $libelle)
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" name="etat[]" value="{{ $valeur }}"
                                                           id="etat-{{ $valeur }}" @checked(in_array($valeur, $etats, true))>
                                                    <label class="form-check-label" for="etat-{{ $valeur }}">{{ $libelle }}</label>
                                                </div>
                                            @endforeach
                                            <p class="text-muted fs-13 mb-0 mt-3">Plusieurs états cochés : les classes de l'un ou de l'autre s'affichent.</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="card h-100 mb-0">
                                        <div class="card-body">
                                            <h6 class="fw-semibold mb-3"><i class="ri-book-open-line text-primary me-1"></i>Cours et formateur</h6>
                                            <label for="filtre-cours" class="form-label small mb-1">Cours</label>
                                            <select name="cours_id" id="filtre-cours" class="form-select form-select-sm mb-3">
                                                <option value="">Tous les cours</option>
                                                @foreach ($coursDeLaSession as $c)
                                                    <option value="{{ $c->id }}" @selected((string) request('cours_id') === (string) $c->id)>{{ $c->titre }}</option>
                                                @endforeach
                                            </select>
                                            <label for="filtre-formateur" class="form-label small mb-1">Formateur</label>
                                            <select name="formateur_id" id="filtre-formateur" class="form-select form-select-sm">
                                                <option value="">Tous les formateurs</option>
                                                @foreach ($formateursDeLaSession as $f)
                                                    <option value="{{ $f->id }}" @selected((string) request('formateur_id') === (string) $f->id)>{{ $f->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="card mb-0">
                                        <div class="card-body">
                                            <h6 class="fw-semibold mb-3"><i class="ri-calendar-event-line text-primary me-1"></i>Date de début</h6>
                                            <label for="filtre-date-debut" class="form-label small mb-1">Classes qui commencent le</label>
                                            <input type="date" name="date_debut" id="filtre-date-debut"
                                                   class="form-control form-control-sm" value="{{ request('date_debut') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer justify-content-between">
                            <a href="{{ route('admin.sessions.show', $session) }}" class="btn btn-outline-secondary">
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
                        <th>Cours</th>
                        <th>Classe</th>
                        <th class="col-periode">Période</th>
                        <th>Formateur</th>
                        <th class="col-nombre">Inscrits</th>
                        <th class="col-badge">État</th>
                        <th class="text-end col-actions-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($classes as $classe)
                        @php $nbInscrits = $classe->inscriptions->count(); @endphp
                        <tr>
                            <td>
                                <input type="checkbox" name="ids[]" value="{{ $classe->id }}" form="formGroupe"
                                       class="form-check-input case-classe" aria-label="Sélectionner {{ $classe->nom }}">
                            </td>
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
                                                data-mode="classe-modif"
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
                                            data-bs-toggle="modal" data-bs-target="#modalSuppression"
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
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="ri-graduation-cap-line display-5 d-block mb-2"></i>
                                {{ $rechercheActive ? 'Aucune classe ne correspond à votre recherche.' : 'Cette session ne contient encore aucune classe.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($classes->count() > 0)
            <div class="card-footer bg-transparent">
                <small class="text-muted">{{ $classes->count() }} classe(s)</small>
            </div>
        @endif
    </div>
</div>

@foreach ($classes as $classe)
    <template id="membres-classe-{{ $classe->id }}">
        @forelse ($classe->inscriptions as $inscription)
            <div class="d-flex justify-content-between align-items-start gap-3 {{ $loop->last ? '' : 'border-bottom pb-2 mb-2' }}">
                <div class="min-w-0">
                    <div class="fw-medium">{{ $inscription->user?->name ?? 'Compte supprimé' }}</div>
                    <div class="text-muted small">{{ $inscription->user?->email ?? '—' }}</div>
                </div>
                <div class="text-end flex-shrink-0">
                    <span class="badge bg-{{ $inscription->paiement ? 'success' : 'warning' }}-subtle text-{{ $inscription->paiement ? 'success' : 'warning' }}">
                        {{ $inscription->paiement ? 'Payé' : 'Impayé' }}
                    </span>
                    <div class="text-muted small mt-1">Inscrit le {{ $inscription->created_at?->format('d/m/Y') ?? '—' }}</div>
                </div>
            </div>
        @empty
            <p class="text-muted mb-0">Aucun membre n'est inscrit dans cette classe.</p>
        @endforelse
    </template>
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
            <form id="formClasse" method="POST" action="{{ route('admin.sessions.classes.store', $session) }}" novalidate>
                @csrf
                <input type="hidden" name="_method" value="PUT" id="classeMethode" disabled>
                <input type="hidden" name="_mode" id="classeMode" value="{{ old('_mode') }}">
                <input type="hidden" name="_action" id="classeAction" value="{{ old('_action') }}">

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
                    <div id="suppressionConfirmation">
                        <p class="mb-2">Voulez-vous vraiment supprimer la classe <strong id="suppressionNom"></strong> ?</p>
                        <p class="text-muted mb-0">Cette action est définitive.</p>
                    </div>
                    <div id="suppressionBloquee" class="d-none">
                        <p class="mb-2"><strong id="suppressionNomBloquee"></strong> ne peut pas être supprimée : <span id="suppressionNombre"></span> membre(s) y sont inscrits.</p>
                        <p class="text-muted mb-0">Désinscrivez-les d'abord (bouton « Gérer »), puis revenez la supprimer.</p>
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
                <h5 class="modal-title">Supprimer les classes sélectionnées</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Voulez-vous vraiment supprimer <strong id="nbSuppressionGroupee">0</strong> classe(s) ?</p>
                <p class="text-muted mb-2">Une classe qui a encore des inscrits est ignorée : désinscrivez d'abord ses membres.</p>
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
    const urlAjout = @json(route('admin.sessions.classes.store', $session));
    const modeAvecErreur = @json($modeAvecErreur);

    // ----- Fenêtre d'informations d'une classe et de ses membres inscrits -----
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
        const membres = champ('modalInfoClasseMembres');
        membres.replaceChildren(modeleMembres.content.cloneNode(true));
        champ('modalInfoClasseNombre').textContent = modeleMembres.content.querySelectorAll('.fw-medium').length;
    });

    // ----- Sélection multiple : la barre de suppression apparaît dès qu'au moins une classe est cochée -----
    const cases = Array.from(document.querySelectorAll('.case-classe'));
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

    // ----- Fenêtre « Ajouter / modifier une classe » -----
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

        if (d.mode === 'classe-modif') {
            champ('classeAction').value = d.action;
            formClasse.action = d.action;
            champ('classeNom').value = d.nom;
            champ('classeDebut').value = d.debut;
            champ('classeFin').value = d.fin;
            champ('classeCapacite').value = d.capacite;
            champ('classeFormateur').value = d.formateur || '';
            champ('classeLieu').value = d.lieu;
            champ('classeLien').value = d.lien || '';
        } else {
            champ('classeAction').value = urlAjout;
            formClasse.action = urlAjout;
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

    // ----- Suppression d'une classe -----
    champ('modalSuppression').addEventListener('show.bs.modal', function (e) {
        const d = e.relatedTarget.dataset;
        const nombre = parseInt(d.inscrits, 10);
        const bloquee = nombre > 0;
        champ('suppressionNom').textContent = d.nom;
        champ('suppressionNomBloquee').textContent = d.nom;
        champ('suppressionNombre').textContent = nombre;
        champ('formSuppression').action = d.action;
        champ('suppressionConfirmation').classList.toggle('d-none', bloquee);
        champ('suppressionBloquee').classList.toggle('d-none', !bloquee);
        champ('suppressionValider').classList.toggle('d-none', bloquee);
    });

    // ----- Validation refusée : on rouvre la bonne fenêtre -----
    if (modeAvecErreur === 'classe-ajout' || modeAvecErreur === 'classe-modif') {
        preparerClasse(modeAvecErreur);
        formClasse.action = champ('classeAction').value || urlAjout;
        bootstrap.Modal.getOrCreateInstance(modalClasse).show();
    }
});
</script>
@endsection