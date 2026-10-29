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

@php
    // Retour d'une validation qui a échoué : "_mode" (ajout ou modification) dit quelle fenêtre rouvrir
    $modeAvecErreur = $errors->any() ? old('_mode') : null;

    $coursFiltre = request()->filled('cours_id') ? $cours->firstWhere('id', (int) request('cours_id')) : null;
@endphp

<div class="container-fluid pb-4">

    {{-- ===== Statistiques ===== --}}
    <div class="row g-2 mb-3">
        <div class="col-sm-6 col-xl-3">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center gap-2 py-2 px-3">
                    <span class="avatar-sm flex-shrink-0">
                        <span class="avatar-title rounded-3 bg-primary-subtle text-primary fs-4"><i class="ri-question-answer-line"></i></span>
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
                        <span class="avatar-title rounded-3 bg-warning-subtle text-warning fs-4"><i class="ri-error-warning-line"></i></span>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-0">{{ $stats['cours_vides'] }}</h5>
                        {{-- Au survol : les titres des cours concernés (sans question, pas de quiz possible) --}}
                        <p class="text-muted mb-0 fs-12" title="{{ $cours->where('questions_count', 0)->pluck('titre')->join(', ') }}">Cours sans question</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center gap-2 py-2 px-3">
                    <span class="avatar-sm flex-shrink-0">
                        <span class="avatar-title rounded-3 bg-info-subtle text-info fs-4"><i class="ri-checkbox-circle-line"></i></span>
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

    {{-- ===== Erreurs de validation de l'action groupée (la fenêtre Créer/Modifier affiche les siennes) ===== --}}
    @if ($errors->any() && ! $modeAvecErreur)
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $erreur)
                    <li>{{ $erreur }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ===== Barre d'actions groupées : cachée tant qu'aucune question n'est cochée.
         Les cases du tableau y sont rattachées par form="formGroupe". ===== --}}
    <form id="formGroupe" method="POST" action="{{ route('admin.questions.actions') }}">
        @csrf
        <div id="barreActions" class="card border shadow-sm mb-3 d-none">
            <div class="card-body d-flex flex-wrap align-items-center gap-2">
                <strong><span id="nbSelection">0</span> question(s) sélectionnée(s)</strong>

                <select name="action" id="actionChoisie" class="form-select w-auto" aria-label="Action à appliquer">
                    <option value="">Choisir une action…</option>
                    <option value="changer_cours">Changer de cours</option>
                    <option value="supprimer">Supprimer les questions</option>
                </select>

                {{-- Visible seulement pour l'action « Changer de cours » --}}
                <select name="cours_cible" id="coursCible" class="form-select w-auto d-none" aria-label="Cours de destination">
                    <option value="">Choisir le cours…</option>
                    @foreach ($cours as $c)
                        <option value="{{ $c->id }}">{{ $c->titre }}</option>
                    @endforeach
                </select>

                <button type="submit" class="btn btn-primary">
                    <i class="ri-check-line me-1"></i>Appliquer
                </button>
                <button type="button" id="annulerSelection" class="btn btn-outline-secondary">Désélectionner</button>
            </div>
        </div>
    </form>

    {{-- ===== Étiquettes des filtres actifs ===== --}}
    @php
        $sans = fn (array $cles) => route('admin.questions.index', request()->except(array_merge($cles, ['page'])));

        $etiquettes = [];
        if ($coursFiltre) {
            $etiquettes[] = ['Cours : ' . $coursFiltre->titre, ['cours_id'], 'ri-book-open-line'];
        }
    @endphp

    {{-- ===== Même ligne : boutons de création à gauche, recherche et filtres à droite ===== --}}
    <div class="row g-2 justify-content-between align-items-start mb-3">
        <div class="col-auto d-flex flex-wrap gap-2">
            {{-- On garde le cours filtré : la nouvelle question y est pré-associée --}}
            <button type="button" class="btn btn-primary fw-semibold"
                    data-bs-toggle="modal" data-bs-target="#modalQuestion" data-mode="ajout"
                    data-cours="{{ $coursFiltre?->id }}">
                <i class="ri-add-line me-1"></i>Créer une question
            </button>

            {{-- Import en masse : page dédiée (choix du fichier, modèle à télécharger, liste des erreurs) --}}
            <a href="{{ route('admin.questions.import') }}" class="btn btn-soft-success">
                <i class="ri-file-upload-line me-1"></i>Importer .csv
            </a>
        </div>

        <div class="col-12 col-lg-8 col-xl-6">
            <form method="GET" action="{{ route('admin.questions.index') }}">
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
                               placeholder="Rechercher dans les énoncés…" aria-label="Rechercher une question">
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
                                    <i class="ri-equalizer-line text-primary me-2"></i>Filtres des questions
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                            </div>

                            <div class="modal-body">
                                <label for="filtreCours" class="form-label fw-semibold">
                                    <i class="ri-book-open-line text-primary me-1"></i>Cours
                                </label>
                                <select name="cours_id" id="filtreCours" class="form-select">
                                    <option value="">Tous les cours</option>
                                    @foreach ($cours as $c)
                                        <option value="{{ $c->id }}" @selected((string) request('cours_id') === (string) $c->id)>
                                            {{ $c->titre }} ({{ $c->questions_count }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="modal-footer justify-content-between">
                                <a href="{{ route('admin.questions.index') }}" class="btn btn-outline-secondary">
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

    {{-- ===== Tableau des questions : la dernière créée en premier ===== --}}
    <div class="card border shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0">
                <thead>
                    <tr class="table-light">
                        <th>
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
                        <tr>
                            {{-- data-posee : nombre de tentatives où la question a déjà été posée (sert aux avertissements) --}}
                            <td>
                                <input type="checkbox" name="ids[]" value="{{ $question->id }}" form="formGroupe"
                                       data-posee="{{ $question->reponses_count }}"
                                       class="form-check-input case-question" aria-label="Sélectionner la question {{ $question->id }}">
                            </td>

                            <td class="enonce-question" title="{{ $question->enonce }}">
                                {{ \Illuminate\Support\Str::limit($question->enonce, 90) }}
                            </td>

                            <td><span class="badge bg-primary-subtle text-primary">{{ $question->cours->titre }}</span></td>

                            <td class="text-success">{{ \Illuminate\Support\Str::limit($question->options->firstWhere('est_correct', true)?->libelle ?? '—', 40) }}</td>

                            <td class="text-center">{{ $question->options->count() }}</td>

                            <td class="text-end text-nowrap">
                                <div class="d-flex justify-content-end gap-1">
                                    {{-- Modifier : énoncé, cours et options voyagent dans les data-* du bouton --}}
                                    <button type="button" class="btn btn-sm btn-soft-secondary" title="Modifier la question"
                                            data-bs-toggle="modal" data-bs-target="#modalQuestion"
                                            data-mode="modif"
                                            data-action="{{ route('admin.questions.update', $question) }}"
                                            data-cours="{{ $question->cours_id }}"
                                            data-enonce="{{ $question->enonce }}"
                                            data-posee="{{ $question->reponses_count }}"
                                            data-options="{{ $question->options->map(fn ($o) => ['id' => $o->id, 'libelle' => $o->libelle, 'correcte' => (bool) $o->est_correct])->values()->toJson() }}">
                                        <i class="ri-edit-line"></i>
                                    </button>

                                    <button type="button" class="btn btn-sm btn-soft-danger" title="Supprimer la question"
                                            data-bs-toggle="modal" data-bs-target="#modalSuppression"
                                            data-action="{{ route('admin.questions.destroy', $question) }}"
                                            data-posee="{{ $question->reponses_count }}"
                                            data-enonce="{{ \Illuminate\Support\Str::limit($question->enonce, 120) }}">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="ri-question-answer-line display-5 d-block mb-2"></i>
                                Aucune question trouvée.
                                @if ($etiquettes || request()->filled('q'))
                                    <a href="{{ route('admin.questions.index') }}" class="d-block mt-2">Réinitialiser les filtres</a>
                                @endif
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
                {{ $questions->links() }}
            </div>
        @endif
    </div>
</div>

{{-- ===== Confirmation de suppression (une question) ===== --}}
<div class="modal fade" id="modalSuppression" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="formSuppression" method="POST" action="#">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title">Supprimer la question</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Voulez-vous vraiment supprimer cette question ?</p>
                    <p class="fst-italic mb-2" id="suppressionEnonce"></p>

                    {{-- Visible seulement si la question a déjà servi dans des tentatives --}}
                    <div class="alert alert-warning mb-0 d-none" id="suppressionAvertissement">
                        Cette question a déjà été posée dans <strong id="suppressionNb"></strong> tentative(s).
                        La supprimer l'enlève aussi de leur correction, et une tentative encore en cours perdrait cette question.
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
                <h5 class="modal-title">Supprimer les questions sélectionnées</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Voulez-vous vraiment supprimer <strong id="nbSuppressionGroupee">0</strong> question(s) ?</p>
                <div class="alert alert-warning d-none" id="avertissementGroupe">
                    <strong id="nbPoseesGroupe"></strong> de ces questions ont déjà été posées dans des tentatives :
                    elles disparaîtront de leur correction, et une tentative encore en cours perdrait ces questions.
                </div>
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
<div class="modal fade" id="modalQuestion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formQuestion" method="POST" action="{{ route('admin.questions.store') }}" novalidate>
                @csrf
                {{-- Champs techniques : _method = PUT seulement en modification ; _mode et _action
                     permettent de rouvrir la bonne fenêtre si la validation échoue --}}
                <input type="hidden" name="_method" value="PUT" id="champMethode" disabled>
                <input type="hidden" name="_mode" id="champMode" value="{{ old('_mode') }}">
                <input type="hidden" name="_action" id="champAction" value="{{ old('_action') }}">

                <div class="modal-header">
                    <h5 class="modal-title" id="questionTitre">Créer une question</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>

                <div class="modal-body">
                    @if ($modeAvecErreur)
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach (collect($errors->all())->unique() as $erreur)
                                    <li>{{ $erreur }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Modification d'une question déjà posée : ce que ça change pour les tentatives passées --}}
                    <div class="alert alert-warning d-none" id="avertissementPosee">
                        Cette question a déjà été posée dans <strong id="nbPosee"></strong> tentative(s).
                        Changer la bonne réponse modifie la correction affichée (les notes déjà calculées ne bougent pas),
                        et retirer une option déjà choisie efface ce choix dans la correction.
                    </div>

                    <div class="mb-3">
                        <label for="champCours" class="form-label">Cours</label>
                        <select name="cours_id" id="champCours" class="form-select" required>
                            <option value="">— Choisir un cours —</option>
                            @foreach ($cours as $c)
                                <option value="{{ $c->id }}">{{ $c->titre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="champEnonce" class="form-label">Énoncé de la question</label>
                        <textarea name="enonce" id="champEnonce" rows="3" maxlength="2000" class="form-control" required></textarea>
                    </div>

                    <label class="form-label">Options de réponse <span class="text-muted">(cochez la bonne réponse)</span></label>
                    <div id="optionsConteneur"></div>

                    <button type="button" id="ajouterOption" class="btn btn-sm btn-soft-primary">
                        <i class="ri-add-line me-1"></i>Ajouter une option
                    </button>
                    <small class="text-muted ms-2">Entre 2 et 6 options, une seule bonne réponse.</small>
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
    const urlAjout = @json(route('admin.questions.store'));
    const modeAvecErreur = @json($modeAvecErreur);
    // Saisie conservée après une validation refusée : options (indexées par clé) et clé de la bonne réponse
    const anciennesOptions = @json(old('options'));
    const ancienneCorrecte = @json(old('correcte'));
    const ancienCours = @json(old('cours_id'));
    const ancienEnonce = @json(old('enonce'));

    const MIN = 2, MAX = 6;
    const modalQuestion = document.getElementById('modalQuestion');
    const form = document.getElementById('formQuestion');
    const champ = id => document.getElementById(id);
    const conteneur = champ('optionsConteneur');
    let prochaineCle = 0;

    // ---------- Sélection multiple ----------
    const cases = Array.from(document.querySelectorAll('.case-question'));
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

    // Le menu de cours n'apparaît que pour « Changer de cours »
    const action = champ('actionChoisie');
    action.addEventListener('change', function () {
        champ('coursCible').classList.toggle('d-none', action.value !== 'changer_cours');
    });

    // « Supprimer » demande d'abord confirmation dans une fenêtre, puis envoie le formulaire
    champ('formGroupe').addEventListener('submit', function (e) {
        if (action.value === 'supprimer' && !champ('formGroupe').dataset.confirme) {
            e.preventDefault();
            const cochees = cases.filter(c => c.checked);
            const nbPosees = cochees.filter(c => parseInt(c.dataset.posee, 10) > 0).length;
            champ('nbSuppressionGroupee').textContent = cochees.length;
            champ('nbPoseesGroupe').textContent = nbPosees;
            champ('avertissementGroupe').classList.toggle('d-none', nbPosees === 0);
            bootstrap.Modal.getOrCreateInstance(champ('modalSuppressionGroupee')).show();
        }
    });
    champ('confirmerSuppressionGroupee').addEventListener('click', function () {
        champ('formGroupe').dataset.confirme = '1';
        champ('formGroupe').requestSubmit();
    });

    majSelection();

    // ---------- Options de réponse (lignes construites en JavaScript) ----------
    // Active ou désactive les boutons selon le nombre d'options (2 à 6)
    function actualiser() {
        const lignes = conteneur.querySelectorAll('.option-row');
        champ('ajouterOption').disabled = lignes.length >= MAX;
        lignes.forEach(l => l.querySelector('.btn-supprimer-option').disabled = lignes.length <= MIN);
    }

    // Ajoute une ligne : cle = clé du formulaire (options[cle]), id = id de l'option existante (ou vide)
    function ajouterLigne(cle, id, libelle, correcte) {
        const ligne = document.createElement('div');
        ligne.className = 'input-group mb-2 option-row';

        const caseRadio = document.createElement('div');
        caseRadio.className = 'input-group-text';
        const radio = document.createElement('input');
        radio.type = 'radio';
        radio.name = 'correcte';
        radio.value = cle;
        radio.checked = !!correcte;
        radio.className = 'form-check-input mt-0';
        radio.title = 'Bonne réponse';
        radio.required = true;
        caseRadio.appendChild(radio);

        // id caché : permet au contrôleur de retrouver l'option existante (les anciennes réponses restent liées)
        const cache = document.createElement('input');
        cache.type = 'hidden';
        cache.name = 'options[' + cle + '][id]';
        cache.value = id || '';

        const texte = document.createElement('input');
        texte.type = 'text';
        texte.name = 'options[' + cle + '][libelle]';
        texte.className = 'form-control';
        texte.maxLength = 255;
        texte.placeholder = "Texte de l'option";
        texte.value = libelle || '';
        texte.required = true;

        const suppr = document.createElement('button');
        suppr.type = 'button';
        suppr.className = 'btn btn-soft-danger btn-supprimer-option';
        suppr.title = 'Retirer cette option';
        suppr.innerHTML = '<i class="ri-delete-bin-line"></i>';

        ligne.append(caseRadio, cache, texte, suppr);
        conteneur.appendChild(ligne);
        prochaineCle = Math.max(prochaineCle, parseInt(cle, 10) + 1);
    }

    champ('ajouterOption').addEventListener('click', function () {
        ajouterLigne(prochaineCle, '', '', false);
        actualiser();
    });

    conteneur.addEventListener('click', function (e) {
        const bouton = e.target.closest('.btn-supprimer-option');
        if (bouton && !bouton.disabled) {
            bouton.closest('.option-row').remove();
            actualiser();
        }
    });

    // Remplit la liste d'options : lignes = [{cle, id, libelle, correcte}]
    function remplirOptions(lignes) {
        conteneur.replaceChildren();
        prochaineCle = 0;
        lignes.forEach(l => ajouterLigne(l.cle, l.id, l.libelle, l.correcte));
        // Toujours au moins 2 lignes visibles
        while (conteneur.querySelectorAll('.option-row').length < MIN) ajouterLigne(prochaineCle, '', '', false);
        actualiser();
    }

    // ---------- Fenêtre Créer / Modifier ----------
    function preparer(mode, posee) {
        const modif = mode === 'modif';
        champ('questionTitre').textContent = modif ? 'Modifier la question' : 'Créer une question';
        champ('champMode').value = mode;
        champ('champMethode').disabled = !modif;
        champ('nbPosee').textContent = posee;
        champ('avertissementPosee').classList.toggle('d-none', !(modif && posee > 0));
    }

    modalQuestion.addEventListener('show.bs.modal', function (e) {
        const bouton = e.relatedTarget;
        if (!bouton) return;

        const d = bouton.dataset;
        const modif = d.mode === 'modif';
        preparer(d.mode, parseInt(d.posee || '0', 10));

        if (modif) {
            champ('champAction').value = d.action;
            form.action = d.action;
            champ('champCours').value = d.cours || '';
            champ('champEnonce').value = d.enonce;
            remplirOptions(JSON.parse(d.options).map((o, i) => ({ cle: i, id: o.id, libelle: o.libelle, correcte: o.correcte })));
        } else {
            champ('champAction').value = urlAjout;
            form.action = urlAjout;
            champ('champCours').value = d.cours || '';
            champ('champEnonce').value = '';
            remplirOptions([{ cle: 0 }, { cle: 1 }]);
        }
    });

    // Validation refusée : on rouvre la fenêtre avec ce qui avait été saisi
    if (modeAvecErreur) {
        preparer(modeAvecErreur, 0);
        form.action = champ('champAction').value || urlAjout;
        champ('champCours').value = ancienCours || '';
        champ('champEnonce').value = ancienEnonce || '';
        const lignes = Object.entries(anciennesOptions || {}).map(([cle, o]) => ({
            cle: cle, id: o.id, libelle: o.libelle, correcte: String(cle) === String(ancienneCorrecte)
        }));
        remplirOptions(lignes);
        bootstrap.Modal.getOrCreateInstance(modalQuestion).show();
    }

    // ---------- Fenêtre de suppression d'une question ----------
    champ('modalSuppression').addEventListener('show.bs.modal', function (e) {
        const d = e.relatedTarget.dataset;
        const posee = parseInt(d.posee, 10);
        champ('formSuppression').action = d.action;
        champ('suppressionEnonce').textContent = d.enonce;
        champ('suppressionNb').textContent = posee;
        champ('suppressionAvertissement').classList.toggle('d-none', posee === 0);
    });
});
</script>
@endsection