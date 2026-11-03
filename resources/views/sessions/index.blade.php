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
                        <th class="text-end col-actions-4">Actions</th>
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
                                    <button type="button" class="btn btn-sm btn-soft-primary" title="Voir le détail"
                                            data-bs-toggle="modal" data-bs-target="#modalVoir"
                                            data-detail="detail-{{ $session->id }}" data-titre="{{ $session->nom }}">
                                        <i class="ri-eye-line"></i>
                                    </button>
                                    <a href="{{ route('admin.sessions.show', $session) }}" class="btn btn-sm btn-soft-success" title="Ouvrir la session (classes et inscrits)">
                                        <i class="ri-folder-open-line"></i>
                                    </a>
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

{{-- Modèles cachés du détail : un par session, clonés dans la fenêtre « Voir » --}}
@foreach ($sessions as $session)
    <template id="detail-{{ $session->id }}">
        <dl class="row mb-3">
            <dt class="col-sm-4 text-muted fw-normal">Entreprise</dt>
            <dd class="col-sm-8">{{ $session->entreprise->raison_sociale }}</dd>
            <dt class="col-sm-4 text-muted fw-normal">Période</dt>
            <dd class="col-sm-8">
                @if ($session->date_debut)
                    {{ $session->date_debut->format('d/m/Y') }} → {{ $session->date_fin->format('d/m/Y') }}
                @else
                    Aucune classe
                @endif
            </dd>
            <dt class="col-sm-4 text-muted fw-normal">État</dt>
            <dd class="col-sm-8"><span class="badge bg-{{ $session->etat_couleur }}-subtle text-{{ $session->etat_couleur }}">{{ $session->etat_libelle }}</span></dd>
            @if ($session->description)
                <dt class="col-sm-4 text-muted fw-normal">Description</dt>
                <dd class="col-sm-8">{{ $session->description }}</dd>
            @endif
        </dl>

        <h6 class="fw-semibold">Classes ({{ $session->classes_count }})</h6>
        @forelse ($session->classes as $classe)
            <div class="d-flex justify-content-between align-items-center gap-2 border-bottom py-2">
                <div class="overflow-hidden">
                    <div class="fw-medium text-truncate" title="{{ $classe->nom }}">{{ $classe->nom }}</div>
                    <div class="text-muted fs-12 text-truncate" title="{{ $classe->cours?->titre }}">{{ $classe->cours?->titre ?? 'Cours supprimé' }}</div>
                </div>
                <span class="badge bg-primary-subtle text-primary flex-shrink-0">{{ $classe->inscriptions_count }} / {{ $classe->capacite_max }}</span>
            </div>
        @empty
            <p class="text-muted mb-0">Aucune classe pour le moment.</p>
        @endforelse
    </template>
@endforeach

{{-- ===== Fenêtre « Voir » ===== --}}
<div class="modal fade" id="modalVoir" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-truncate" id="voirTitre">Session</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body" id="voirContenu"></div>
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
    if (modeAvecErreur) {
        preparer(modeAvecErreur);
        formSession.action = champ('champAction').value || urlAjout;
        bootstrap.Modal.getOrCreateInstance(modalSession).show();
    }

    // Fenêtre « Voir » : on clone le modèle caché de la session
    champ('modalVoir').addEventListener('show.bs.modal', function (e) {
        const d = e.relatedTarget.dataset;
        champ('voirTitre').textContent = d.titre;
        champ('voirTitre').title = d.titre;
        const contenu = champ('voirContenu');
        contenu.replaceChildren(document.getElementById(d.detail).content.cloneNode(true));
    });

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
});
</script>
@endsection