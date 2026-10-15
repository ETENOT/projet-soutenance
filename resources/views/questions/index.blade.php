@extends('layouts.master')

@section('title')
    Banque de questions
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Administration
        @endslot
        @slot('title')
            Banque de questions
        @endslot
    @endcomponent

    <div class="container-fluid pb-4">

        {{-- ===== En-tête : description + boutons d'action ===== --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <p class="text-muted mb-0 col-lg-6 px-0">
                Gérez les questions (QCM) utilisées dans les quiz de chaque formation : ajoutez, importez,
                modifiez, déplacez d'un cours à l'autre ou supprimez-les.
            </p>

            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('admin.quiz.reglages') }}" class="btn btn-soft-secondary">
                    <i class="ri-settings-3-line me-1"></i>Noté sur…
                </a>

                {{-- Import en masse : page dédiée (choix du fichier, cours par défaut, modèle à télécharger) --}}
                <a href="{{ route('admin.questions.import') }}" class="btn btn-success">
                    <i class="ri-file-upload-line me-1"></i>Importer .csv
                </a>

                {{-- On garde le filtre de cours : la nouvelle question sera pré-associée à ce cours --}}
                <a href="{{ route('admin.questions.create', request()->only('cours_id')) }}" class="btn btn-primary fw-semibold">
                    <i class="ri-add-line me-1"></i>Ajouter une question
                </a>
            </div>
        </div>

        {{-- ===== Statistiques (cartes compactes : avatar-sm, chiffre en h5, libellé en fs-12) ===== --}}
        <div class="row g-2 mb-3">

            <div class="col-sm-6 col-xl-3">
                <div class="card border shadow-sm h-100 mb-0">
                    <div class="card-body d-flex align-items-center gap-2 py-2 px-3">
                        <span class="avatar-sm flex-shrink-0">
                            <span class="avatar-title rounded-3 bg-primary-subtle text-primary fs-4">
                                <i class="ri-question-answer-line"></i>
                            </span>
                        </span>
                        <div>
                            <h5 class="fw-bold mb-0">{{ $stats['total'] }}</h5>
                            <p class="text-muted mb-0 fs-12">Questions au total</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="card border shadow-sm h-100 mb-0">
                    <div class="card-body d-flex align-items-center gap-2 py-2 px-3">
                        <span class="avatar-sm flex-shrink-0">
                            <span class="avatar-title rounded-3 bg-success-subtle text-success fs-4">
                                <i class="ri-book-open-line"></i>
                            </span>
                        </span>
                        <div>
                            <h5 class="fw-bold mb-0">{{ $stats['cours_couverts'] }}</h5>
                            <p class="text-muted mb-0 fs-12">Cours avec questions</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="card border shadow-sm h-100 mb-0">
                    <div class="card-body d-flex align-items-center gap-2 py-2 px-3">
                        <span class="avatar-sm flex-shrink-0">
                            <span class="avatar-title rounded-3 bg-warning-subtle text-warning fs-4">
                                <i class="ri-error-warning-line"></i>
                            </span>
                        </span>
                        <div>
                            <h5 class="fw-bold mb-0">{{ $stats['cours_vides'] }}</h5>
                            <p class="text-muted mb-0 fs-12">Cours sans question</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="card border shadow-sm h-100 mb-0">
                    <div class="card-body d-flex align-items-center gap-2 py-2 px-3">
                        <span class="avatar-sm flex-shrink-0">
                            <span class="avatar-title rounded-3 bg-info-subtle text-info fs-4">
                                <i class="ri-checkbox-circle-line"></i>
                            </span>
                        </span>
                        <div>
                            <h5 class="fw-bold mb-0">{{ $stats['jamais_posees'] }}</h5>
                            <p class="text-muted mb-0 fs-12">Jamais posées</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Cours dont la banque est vide : un particulier ne pourrait pas passer de quiz --}}
        @php $coursSansQuestion = $cours->where('questions_count', 0); @endphp
        @if ($coursSansQuestion->isNotEmpty())
            <div class="alert alert-warning">
                <strong>Cours sans question :</strong>
                {{ $coursSansQuestion->pluck('titre')->join(', ') }}
            </div>
        @endif

        {{-- ===== Erreurs de validation (ex. aucune question sélectionnée) ===== --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $erreur)
                        <li>{{ $erreur }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ===== Filtres ===== --}}
        <div class="card border shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.questions.index') }}">
                    <div class="row g-3 align-items-center">

                        <div class="col-lg-4">
                            <select name="cours_id" class="form-select" aria-label="Filtrer par cours"
                                    onchange="this.form.submit()">
                                <option value="">Tous les cours</option>
                                @foreach ($cours as $c)
                                    <option value="{{ $c->id }}" @selected((string) request('cours_id') === (string) $c->id)>
                                        {{ $c->titre }} ({{ $c->questions_count }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-5">
                            <div class="input-group">
                                <span class="input-group-text bg-transparent">
                                    <i class="ri-search-line text-primary"></i>
                                </span>
                                <input type="text" name="q" value="{{ request('q') }}" class="form-control"
                                       placeholder="Rechercher dans les énoncés…" aria-label="Rechercher dans les énoncés">
                            </div>
                        </div>

                        <div class="col-lg-3 d-flex gap-2 justify-content-lg-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-filter-3-line me-1"></i> Filtrer
                            </button>
                            <a href="{{ route('admin.questions.index') }}" class="btn btn-outline-secondary">
                                <i class="ri-refresh-line me-1"></i> Réinitialiser
                            </a>
                        </div>

                    </div>
                </form>
            </div>
        </div>

        {{-- ===== Barre d'actions groupées =====
             Cachée tant qu'aucune question n'est cochée. Les cases du tableau sont rattachées à CE formulaire
             par leur attribut form="formActions" (on ne peut pas imbriquer ce formulaire autour du tableau,
             qui contient déjà les formulaires de suppression de chaque ligne). --}}
        <form id="formActions" method="POST" action="{{ route('admin.questions.actions') }}">
            @csrf
            <div id="barreActions" class="card border shadow-sm mb-3 d-none">
                <div class="card-body d-flex flex-wrap align-items-center gap-2">
                    <strong><span id="nbSelection">0</span> question(s) sélectionnée(s)</strong>

                    <select name="action" id="actionChoisie" class="form-select w-auto" aria-label="Action à appliquer">
                        <option value="">Choisir une action…</option>
                        <option value="changer_cours">Changer de cours</option>
                        <option value="supprimer">Supprimer les questions</option>
                    </select>

                    {{-- Visible seulement pour l'action "Changer de cours" --}}
                    <select name="cours_cible" id="coursCible" class="form-select w-auto d-none" aria-label="Cours de destination">
                        <option value="">Choisir le cours…</option>
                        @foreach ($cours as $c)
                            <option value="{{ $c->id }}">{{ $c->titre }}</option>
                        @endforeach
                    </select>

                    <button type="submit" class="btn btn-primary">
                        <i class="ri-check-line me-1"></i> Appliquer
                    </button>
                    <button type="button" id="annulerSelection" class="btn btn-outline-secondary">Désélectionner</button>
                </div>
            </div>
        </form>

        {{-- ===== Tableau des questions ===== --}}
        <div class="card border shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle table-nowrap mb-0">
                    <thead class="table-light">
                        <tr>
                            {{-- Coche / décoche toutes les questions de la page --}}
                            <th style="width: 40px;">
                                <input type="checkbox" id="toutSelectionner" class="form-check-input" aria-label="Tout sélectionner">
                            </th>
                            <th>Question</th>
                            <th>Cours</th>
                            <th>Bonne réponse</th>
                            <th class="text-center">Options</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($questions as $question)
                            @php
                                // Message de confirmation adapté : plus sévère si la question a déjà servi
                                $texteSuppression = $question->reponses_count > 0
                                    ? "Cette question a déjà été posée dans {$question->reponses_count} tentative(s). La supprimer modifiera leur correction. Continuer ?"
                                    : 'Supprimer cette question ?';
                            @endphp
                            <tr>
                                {{-- Case de sélection : rattachée au formulaire d'actions groupées.
                                     data-posee = 1 si la question a déjà servi dans une tentative (sert à avertir avant l'action). --}}
                                <td>
                                    <input type="checkbox" name="ids[]" value="{{ $question->id }}" form="formActions"
                                           data-posee="{{ $question->reponses_count > 0 ? 1 : 0 }}"
                                           class="form-check-input case-question" aria-label="Sélectionner la question {{ $question->id }}">
                                </td>

                                <td style="min-width: 280px; white-space: normal;">
                                    {{ \Illuminate\Support\Str::limit($question->enonce, 120) }}
                                </td>

                                <td><span class="badge bg-primary-subtle text-primary">{{ $question->cours->titre }}</span></td>

                                <td class="text-success">{{ $question->options->firstWhere('est_correct', true)?->libelle ?? '—' }}</td>

                                <td class="text-center">{{ $question->options->count() }}</td>

                                {{-- Actions : mêmes boutons que la gestion des utilisateurs et des classes --}}
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-1">
                                        <a href="{{ route('admin.questions.edit', $question) }}"
                                           class="btn btn-sm btn-soft-secondary" title="Modifier">
                                            <i class="ri-edit-line"></i>
                                        </a>

                                        <form action="{{ route('admin.questions.destroy', $question) }}" method="POST"
                                              onsubmit="return confirm(@js($texteSuppression));">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-soft-danger" title="Supprimer">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">
                                    <i class="ri-question-answer-line display-5 d-block mb-2"></i>
                                    Aucune question trouvée.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($questions->total() > 0)
                <div class="card-footer bg-transparent d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                    <small class="text-muted">
                        Affichage de {{ $questions->firstItem() }} à {{ $questions->lastItem() }}
                        sur {{ $questions->total() }} résultat(s)
                    </small>
                    {{ $questions->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>

    </div>

    <script>
        // Sélection multiple : affiche la barre d'actions dès qu'au moins une question est cochée.
        document.addEventListener('DOMContentLoaded', function () {
            const cases = Array.from(document.querySelectorAll('.case-question'));
            const toutes = document.getElementById('toutSelectionner');
            const barre = document.getElementById('barreActions');
            const compteur = document.getElementById('nbSelection');
            const action = document.getElementById('actionChoisie');
            const coursCible = document.getElementById('coursCible');
            const formulaire = document.getElementById('formActions');

            // Met à jour le compteur, la barre et l'état de la case "tout sélectionner"
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

            document.getElementById('annulerSelection').addEventListener('click', function () {
                cases.forEach(c => c.checked = false);
                majSelection();
            });

            // Le choix du cours n'apparaît que pour l'action "Changer de cours"
            action.addEventListener('change', function () {
                coursCible.classList.toggle('d-none', action.value !== 'changer_cours');
            });

            // Confirmation avant les actions qui modifient la banque, avec un avertissement
            // si certaines questions ont déjà été posées dans des tentatives.
            formulaire.addEventListener('submit', function (e) {
                const cochees = cases.filter(c => c.checked);
                const nbPosees = cochees.filter(c => c.dataset.posee === '1').length;
                const avertissement = nbPosees > 0
                    ? '\n\n' + nbPosees + ' de ces questions ont déjà été posées dans des tentatives : leur correction sera modifiée.'
                    : '';

                if (action.value === 'supprimer' &&
                    !confirm('Supprimer définitivement ' + cochees.length + ' question(s) ?' + avertissement)) {
                    e.preventDefault();
                }

                if (action.value === 'changer_cours' &&
                    !confirm('Déplacer ' + cochees.length + ' question(s) vers un autre cours ?' + avertissement)) {
                    e.preventDefault();
                }
            });

            majSelection();
        });
    </script>
@endsection