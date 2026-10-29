@extends('layouts.master')

@section('title')
    Suivi des quiz
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('dashboard') }}">Administration</a>
        @endslot
        @slot('title')
            Suivi des quiz
        @endslot
    @endcomponent

@php
    $libellesStatut = ['termine' => 'Terminés', 'en_cours' => 'En cours'];
    $coursFiltre = request()->filled('cours_id') ? $cours->firstWhere('id', (int) request('cours_id')) : null;
@endphp

<div class="container-fluid pb-4">

    {{-- ===== Statistiques ===== --}}
    <div class="row g-2 mb-3">
        <div class="col-sm-6 col-xl-3">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center gap-2 py-2 px-3">
                    <span class="avatar-sm flex-shrink-0">
                        <span class="avatar-title rounded-3 bg-primary-subtle text-primary fs-4"><i class="ri-questionnaire-line"></i></span>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-0">{{ $stats['total'] }}</h5>
                        <p class="text-muted mb-0 fs-12">Tentatives</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center gap-2 py-2 px-3">
                    <span class="avatar-sm flex-shrink-0">
                        <span class="avatar-title rounded-3 bg-success-subtle text-success fs-4"><i class="ri-checkbox-circle-line"></i></span>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-0">{{ $stats['termines'] }}</h5>
                        <p class="text-muted mb-0 fs-12">Terminées</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center gap-2 py-2 px-3">
                    <span class="avatar-sm flex-shrink-0">
                        <span class="avatar-title rounded-3 bg-info-subtle text-info fs-4"><i class="ri-percent-line"></i></span>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-0">{{ $stats['moyenne'] !== null ? number_format($stats['moyenne'], 1, ',', ' ') . ' %' : '—' }}</h5>
                        <p class="text-muted mb-0 fs-12">Moyenne générale</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Erreurs de validation (ex. aucune tentative sélectionnée) ===== --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $erreur)
                    <li>{{ $erreur }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ===== Barre de suppression groupée : cachée tant qu'aucune tentative n'est cochée.
         Les cases du tableau y sont rattachées par form="formGroupe". ===== --}}
    <form id="formGroupe" method="POST" action="{{ route('admin.quiz.destroy-multiple') }}">
        @csrf
        <div id="barreActions" class="card border shadow-sm mb-3 d-none">
            <div class="card-body d-flex flex-wrap align-items-center gap-2">
                <strong><span id="nbSelection">0</span> tentative(s) sélectionnée(s)</strong>
                <button type="button" class="btn btn-danger" id="supprimerSelection">
                    <i class="ri-delete-bin-line me-1"></i>Supprimer
                </button>
                <button type="button" id="annulerSelection" class="btn btn-outline-secondary">Désélectionner</button>
            </div>
        </div>
    </form>

    {{-- ===== Étiquettes des filtres actifs ===== --}}
    @php
        $sans = fn (array $cles) => route('admin.quiz.index', request()->except(array_merge($cles, ['page'])));

        $etiquettes = [];
        if ($coursFiltre) {
            $etiquettes[] = ['Cours : ' . $coursFiltre->titre, ['cours_id'], 'ri-book-open-line'];
        }
        if (isset($libellesStatut[request('statut')])) {
            $etiquettes[] = [$libellesStatut[request('statut')], ['statut'], 'ri-filter-3-line'];
        }
    @endphp

    {{-- Pas de bouton « Créer » : les tentatives sont passées par les particuliers et les visiteurs.
         Recherche et filtres à droite. --}}
    <div class="row g-2 justify-content-end align-items-start mb-3">
        <div class="col-12 col-lg-8 col-xl-6">
            <form method="GET" action="{{ route('admin.quiz.index') }}">
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
                               placeholder="Rechercher un participant (nom ou e-mail)…" aria-label="Rechercher un participant">
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
                                    <i class="ri-equalizer-line text-primary me-2"></i>Filtres des tentatives
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                            </div>

                            <div class="modal-body">
                                <div class="mb-4">
                                    <label for="filtreCours" class="form-label fw-semibold">
                                        <i class="ri-book-open-line text-primary me-1"></i>Cours
                                    </label>
                                    <select name="cours_id" id="filtreCours" class="form-select">
                                        <option value="">Tous les cours</option>
                                        @foreach ($cours as $c)
                                            <option value="{{ $c->id }}" @selected((string) request('cours_id') === (string) $c->id)>{{ $c->titre }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <h6 class="fw-semibold mb-3"><i class="ri-filter-3-line text-primary me-1"></i>Statut</h6>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="statut" value="" id="statut-tous"
                                           @checked(! isset($libellesStatut[request('statut')]))>
                                    <label class="form-check-label" for="statut-tous">Tous les statuts</label>
                                </div>
                                @foreach ($libellesStatut as $valeur => $libelle)
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="statut" value="{{ $valeur }}"
                                               id="statut-{{ $valeur }}" @checked(request('statut') === $valeur)>
                                        <label class="form-check-label" for="statut-{{ $valeur }}">{{ $libelle }}</label>
                                    </div>
                                @endforeach
                            </div>

                            <div class="modal-footer justify-content-between">
                                <a href="{{ route('admin.quiz.index') }}" class="btn btn-outline-secondary">
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

    {{-- ===== Tableau des tentatives : la plus récente en premier ===== --}}
    <div class="card border shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0">
                <thead>
                    <tr class="table-light">
                        <th>
                            <input type="checkbox" id="toutSelectionner" class="form-check-input" aria-label="Tout sélectionner">
                        </th>
                        <th>Date</th>
                        <th>Participant</th>
                        <th>E-mail</th>
                        <th>Cours</th>
                        <th>Statut</th>
                        <th>Note</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($quizzes as $quiz)
                        @php
                            $resultat = $quiz->resultats->first();
                            $reussi = $resultat && $quiz->bareme > 0 && ($resultat->score / $quiz->bareme) >= 0.5;
                            // user_id null = visiteur non inscrit : on affiche l'e-mail saisi avant le quiz
                            $nomParticipant = $quiz->user?->name ?? 'Visiteur';
                            $emailParticipant = $quiz->user?->email ?? $quiz->email_visiteur;
                        @endphp
                        <tr>
                            <td>
                                {{-- data-encours = 1 : tentative non terminée (sert à avertir avant la suppression) --}}
                                <input type="checkbox" name="ids[]" value="{{ $quiz->id }}" form="formGroupe"
                                       data-encours="{{ $resultat ? 0 : 1 }}"
                                       class="form-check-input case-quiz" aria-label="Sélectionner la tentative {{ $quiz->id }}">
                            </td>
                            <td class="text-nowrap">
                                {{ \Carbon\Carbon::parse($quiz->date)->format('d/m/Y') }}
                                <small class="text-muted">{{ substr($quiz->heure_debut, 0, 5) }}</small>
                            </td>
                            <td class="fw-semibold">
                                {{ $nomParticipant }}
                                @unless ($quiz->user)
                                    <span class="badge bg-secondary-subtle text-secondary ms-1">Visiteur</span>
                                @endunless
                            </td>
                            <td>{{ $emailParticipant }}</td>
                            <td>{{ $quiz->cours->titre }}</td>
                            <td>
                                @if ($resultat)
                                    <span class="badge bg-success-subtle text-success">Terminé</span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning">En cours</span>
                                @endif
                            </td>
                            <td class="text-nowrap">
                                @if ($resultat)
                                    {{-- Vert à partir de 50 %, rouge en dessous --}}
                                    <span class="fw-semibold {{ $reussi ? 'text-success' : 'text-danger' }}">
                                        {{ (float) $resultat->score }} / {{ $quiz->bareme }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end text-nowrap">
                                <div class="d-flex justify-content-end gap-1">
                                    {{-- Voir : correction question par question (modèle caché #detail-ID copié dans la fenêtre) --}}
                                    <button type="button" class="btn btn-sm btn-soft-primary" title="Voir la correction"
                                            data-bs-toggle="modal" data-bs-target="#modalDetail"
                                            data-detail="detail-{{ $quiz->id }}"
                                            data-titre="{{ $nomParticipant }} — {{ $quiz->cours->titre }}">
                                        <i class="ri-eye-line"></i>
                                    </button>

                                    <button type="button" class="btn btn-sm btn-soft-danger" title="Supprimer la tentative"
                                            data-bs-toggle="modal" data-bs-target="#modalSuppression"
                                            data-action="{{ route('admin.quiz.destroy', $quiz) }}"
                                            data-nom="{{ $nomParticipant }}"
                                            data-cours="{{ $quiz->cours->titre }}"
                                            data-encours="{{ $resultat ? 0 : 1 }}">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="ri-questionnaire-line display-5 d-block mb-2"></i>
                                Aucune tentative trouvée.
                                @if ($etiquettes || request()->filled('q'))
                                    <a href="{{ route('admin.quiz.index') }}" class="d-block mt-2">Réinitialiser les filtres</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($quizzes->total() > 0)
            <div class="card-footer bg-transparent d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <small class="text-muted">
                    Affichage de {{ $quizzes->firstItem() }} à {{ $quizzes->lastItem() }}
                    sur {{ $quizzes->total() }} résultat(s)
                </small>
                {{ $quizzes->links() }}
            </div>
        @endif
    </div>

    {{-- Contenus des fenêtres « Voir » : un modèle <template> par tentative (rien n'est affiché tant qu'on n'ouvre pas la fenêtre) --}}
    @foreach ($quizzes as $quiz)
        @php $resultat = $quiz->resultats->first(); @endphp
        <template id="detail-{{ $quiz->id }}">
            <p class="text-muted mb-3">
                {{ \Carbon\Carbon::parse($quiz->date)->format('d/m/Y') }} à {{ substr($quiz->heure_debut, 0, 5) }}
                —
                @if ($quiz->user)
                    {{ $quiz->user->name }} ({{ $quiz->user->email }})
                @else
                    Visiteur non inscrit ({{ $quiz->email_visiteur }})
                @endif
                —
                @if ($resultat)
                    @php $reussi = $quiz->bareme > 0 && ($resultat->score / $quiz->bareme) >= 0.5; @endphp
                    <strong class="{{ $reussi ? 'text-success' : 'text-danger' }}">{{ (float) $resultat->score }} / {{ $quiz->bareme }}</strong>
                @else
                    <span class="badge bg-warning-subtle text-warning">En cours</span>
                @endif
            </p>

            {{-- Correction : bonne réponse en vert, réponse fausse choisie en rouge --}}
            @forelse ($quiz->reponses as $i => $reponse)
                <div class="border rounded-3 p-3 mb-3">
                    <h6 class="mb-3">{{ $i + 1 }}. {{ $reponse->question->enonce }}</h6>

                    <ul class="list-unstyled mb-0">
                        @foreach ($reponse->question->options as $option)
                            @php $choisie = $reponse->option_id === $option->id; @endphp
                            <li class="mb-1 {{ $option->est_correct ? 'text-success fw-semibold' : ($choisie ? 'text-danger' : '') }}">
                                @if ($option->est_correct)
                                    <i class="ri-check-line"></i>
                                @elseif ($choisie)
                                    <i class="ri-close-line"></i>
                                @else
                                    <i class="ri-checkbox-blank-circle-line text-muted"></i>
                                @endif
                                {{ $option->libelle }}
                                @if ($choisie)
                                    <small class="text-muted">(réponse donnée)</small>
                                @endif
                            </li>
                        @endforeach
                    </ul>

                    @if ($reponse->option_id === null)
                        <span class="badge bg-secondary-subtle text-secondary mt-2">Sans réponse</span>
                    @endif
                </div>
            @empty
                <p class="text-center text-muted py-4 mb-0">Aucune question enregistrée pour cette tentative.</p>
            @endforelse
        </template>
    @endforeach
</div>

{{-- ===== Fenêtre « Voir » ===== --}}
<div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailTitre"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body" id="detailCorps"></div>
        </div>
    </div>
</div>

{{-- ===== Confirmation de suppression (une tentative) ===== --}}
<div class="modal fade" id="modalSuppression" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="formSuppression" method="POST" action="#">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title">Supprimer la tentative</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Supprimer la tentative de <strong id="suppressionNom"></strong> au quiz « <span id="suppressionCours"></span> » ?</p>
                    <p class="text-muted mb-2">Son résultat et sa correction disparaissent définitivement.</p>

                    {{-- Visible seulement si la tentative n'est pas terminée --}}
                    <div class="alert alert-warning mb-0 d-none" id="suppressionAvertissement">
                        Cette tentative est <strong>en cours</strong> : si le participant est en train de répondre, il perdra son quiz.
                    </div>
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
                <h5 class="modal-title">Supprimer les tentatives sélectionnées</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Voulez-vous vraiment supprimer <strong id="nbSuppressionGroupee">0</strong> tentative(s) ?</p>
                <div class="alert alert-warning d-none" id="avertissementGroupe">
                    <strong id="nbEnCoursGroupe"></strong> de ces tentatives sont <strong>en cours</strong> :
                    les participants qui répondent en ce moment perdront leur quiz.
                </div>
                <p class="text-muted mb-0">Les résultats et les corrections disparaissent définitivement.</p>
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

    // Sélection multiple : la barre de suppression apparaît dès qu'au moins une tentative est cochée
    const cases = Array.from(document.querySelectorAll('.case-quiz'));
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

    // Suppression groupée : fenêtre de confirmation (avec alerte si des tentatives sont en cours), puis envoi
    champ('supprimerSelection').addEventListener('click', function () {
        const cochees = cases.filter(c => c.checked);
        const nbEnCours = cochees.filter(c => c.dataset.encours === '1').length;
        champ('nbSuppressionGroupee').textContent = cochees.length;
        champ('nbEnCoursGroupe').textContent = nbEnCours;
        champ('avertissementGroupe').classList.toggle('d-none', nbEnCours === 0);
        bootstrap.Modal.getOrCreateInstance(champ('modalSuppressionGroupee')).show();
    });
    champ('confirmerSuppressionGroupee').addEventListener('click', function () {
        champ('formGroupe').submit();
    });

    majSelection();

    // Fenêtre « Voir » : copie le modèle <template> de la tentative
    champ('modalDetail').addEventListener('show.bs.modal', function (e) {
        const bouton = e.relatedTarget;
        champ('detailTitre').textContent = bouton.dataset.titre;
        champ('detailCorps').replaceChildren(champ(bouton.dataset.detail).content.cloneNode(true));
    });

    // Fenêtre de suppression d'une tentative
    champ('modalSuppression').addEventListener('show.bs.modal', function (e) {
        const d = e.relatedTarget.dataset;
        champ('formSuppression').action = d.action;
        champ('suppressionNom').textContent = d.nom;
        champ('suppressionCours').textContent = d.cours;
        champ('suppressionAvertissement').classList.toggle('d-none', d.encours !== '1');
    });
});
</script>
@endsection