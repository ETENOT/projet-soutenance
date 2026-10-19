@extends('layouts.master')

@section('title', 'Gestion des cours')

@section('content')

{{-- ===== Fil d'Ariane ===== --}}
@component('components.breadcrumb')
    @slot('li_1')
        <a href="{{ route('dashboard') }}">Accueil</a>
    @endslot
    @slot('title')
        Formations
    @endslot
@endcomponent

<div class="container-fluid pb-4">

    {{-- ===== En-tête ===== --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <p class="text-muted mb-0 col-lg-8 px-0">
            Gérez vos formations, vos classes et vos contenus pédagogiques.
        </p>

        <a href="{{ route('admin.cours.create') }}" class="btn btn-primary fw-semibold">
            <i class="ri-add-line me-1"></i> Nouveau cours
        </a>
    </div>

    {{-- ===== Statistiques (cartes compactes : avatar-sm, chiffre en h5, libellé en fs-12) ===== --}}
    <div class="row g-2 mb-3">

        <div class="col-md-4">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center gap-2 py-2 px-3">
                    <span class="avatar-sm flex-shrink-0">
                        <span class="avatar-title rounded-3 bg-primary-subtle text-primary fs-4">
                            <i class="ri-book-open-line"></i>
                        </span>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-0">{{ $nombreCours }}</h5>
                        <p class="text-muted mb-0 fs-12">Cours</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center gap-2 py-2 px-3">
                    <span class="avatar-sm flex-shrink-0">
                        <span class="avatar-title rounded-3 bg-danger-subtle text-danger fs-4">
                            <i class="ri-list-check-2"></i>
                        </span>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-0">{{ $nombreChapitres }}</h5>
                        <p class="text-muted mb-0 fs-12">Chapitres</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center gap-2 py-2 px-3">
                    <span class="avatar-sm flex-shrink-0">
                        <span class="avatar-title rounded-3 bg-success-subtle text-success fs-4">
                            <i class="ri-group-line"></i>
                        </span>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-0">{{ $nombreClasses }}</h5>
                        <p class="text-muted mb-0 fs-12">Classes</p>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ===== Erreurs de filtre (ex. maximum inférieur au minimum) ===== --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $erreur)
                    <li>{{ $erreur }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ===== Recherche façon Odoo (étiquettes + menu Filtres / Regrouper par) ===== --}}
    @php
        $libellesGroupe = ['categorie' => 'Catégorie', 'disponibilite' => 'Disponibilité'];
        $libellesDispo = [
            'a_venir' => 'Classes à venir',
            'sans_a_venir' => 'Aucune classe à venir',
            'aucune' => 'Aucune classe',
        ];

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
            $etiquettes[] = ['Catégorie : ' . request('categorie'), ['categorie'], 'ri-price-tag-3-line'];
        }
        if (request()->filled('classes_min') || request()->filled('classes_max')) {
            $etiquettes[] = ['Classes : ' . (request('classes_min') ?: '0') . ' à ' . (request('classes_max') ?: '∞'), ['classes_min', 'classes_max'], 'ri-group-line'];
        }
        if (request()->filled('prix_min') || request()->filled('prix_max')) {
            $etiquettes[] = ['Prix : ' . number_format((int) request('prix_min', 0), 0, ',', ' ') . ' à ' . (request()->filled('prix_max') ? number_format((int) request('prix_max'), 0, ',', ' ') : '∞') . ' FCFA', ['prix_min', 'prix_max'], 'ri-money-dollar-circle-line'];
        }
        if ($groupe) {
            $etiquettes[] = ['Regroupé par ' . mb_strtolower($libellesGroupe[$groupe]), ['groupe'], 'ri-stack-line'];
        }

        // Cases du menu "Filtres" (un bloc = une section séparée par un trait)
        $filtres = [
            [
                ['nom' => 'avec_classes_a_venir', 'valeur' => '1', 'libelle' => 'Avec classes à venir', 'coche' => request()->boolean('avec_classes_a_venir')],
                ['nom' => 'sans_classe', 'valeur' => '1', 'libelle' => 'Sans classe', 'coche' => request()->boolean('sans_classe')],
            ],
            [
                ['nom' => 'sans_chapitre', 'valeur' => '1', 'libelle' => 'Sans chapitre', 'coche' => request()->boolean('sans_chapitre')],
                ['nom' => 'sans_quiz', 'valeur' => '1', 'libelle' => 'Sans quiz', 'coche' => request()->boolean('sans_quiz')],
            ],
        ];
    @endphp

    @include('partials.barre-recherche', [
        'action' => route('admin.cours.index'),
        'placeholder' => 'Rechercher une formation (titre, catégorie, description)…',
        'etiquettes' => $etiquettes,
        'filtres' => $filtres,
        'groupes' => $libellesGroupe,
        'groupe' => $groupe,
        'vueCriteres' => 'cours.partials.criteres-recherche',
    ])

    {{-- ===== Barre de suppression multiple =====
         Cachée tant qu'aucun cours n'est coché. Les cases du tableau sont rattachées à CE formulaire
         par leur attribut form="formSuppression" (on ne peut pas imbriquer ce formulaire autour du tableau,
         qui contient déjà les formulaires de suppression de chaque ligne). --}}
    <form id="formSuppression" method="POST" action="{{ route('admin.cours.destroy-multiple') }}">
        @csrf
        <div id="barreSelection" class="card border shadow-sm mb-3 d-none">
            <div class="card-body d-flex flex-wrap align-items-center gap-2">
                <strong><span id="nbSelection">0</span> cours sélectionné(s)</strong>

                <button type="submit" class="btn btn-danger">
                    <i class="ri-delete-bin-line me-1"></i> Supprimer la sélection
                </button>
                <button type="button" id="annulerSelection" class="btn btn-outline-secondary">Désélectionner</button>
            </div>
        </div>
    </form>

    {{-- ===== Tableau des cours ===== --}}
    <div class="card border shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle table-nowrap mb-0">
                <thead class="table-light">
                    <tr>
                        {{-- Coche / décoche tous les cours de la page --}}
                        <th style="width: 40px;">
                            <input type="checkbox" id="toutSelectionner" class="form-check-input" aria-label="Tout sélectionner">
                        </th>
                        <th>Formation</th>
                        <th>Catégorie</th>
                        <th class="text-center">Chapitres</th>
                        <th class="text-center">Classes</th>
                        <th>Prix particulier</th>
                        <th>Prix entreprise</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @php
                        // dernier groupe affiché : sert à n'écrire l'en-tête qu'au changement de groupe
                        $groupePrecedent = null;
                    @endphp
                    @forelse($cours as $unCours)
                        {{-- En-tête de groupe (Regrouper par) --}}
                        @if ($groupe)
                            @php
                                $cle = $cleGroupe($unCours);
                                $libelleGroupe = $groupe === 'categorie'
                                    ? ($unCours->categorie ?: 'Sans catégorie')
                                    : $libellesDispo[$cle];
                            @endphp
                            @if ($cle !== $groupePrecedent)
                                @php
                                    $groupePrecedent = $cle;
                                @endphp
                                <tr class="table-active">
                                    <td colspan="8" class="fw-semibold">
                                        <i class="ri-stack-line text-primary me-1"></i>{{ $libelleGroupe }}
                                        <span class="badge bg-primary-subtle text-primary ms-1">{{ $comptesGroupes[$cle] ?? 0 }}</span>
                                    </td>
                                </tr>
                            @endif
                        @endif

                        <tr>
                            {{-- Case de sélection : rattachée au formulaire de suppression multiple.
                                 data-classes = nombre de classes du cours (sert à avertir avant la suppression). --}}
                            <td>
                                <input type="checkbox" name="ids[]" value="{{ $unCours->id }}" form="formSuppression"
                                       data-classes="{{ $unCours->classes_count }}"
                                       class="form-check-input case-cours" aria-label="Sélectionner {{ $unCours->titre }}">
                            </td>

                            <td style="min-width: 280px;">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title rounded-3 bg-primary-subtle text-primary fs-4">
                                            <i class="ri-book-2-line"></i>
                                        </span>
                                    </span>
                                    <div style="white-space: normal;">
                                        <div class="fw-semibold">{{ $unCours->titre }}</div>
                                        @if($unCours->description)
                                            <small class="text-muted">
                                                {{ \Illuminate\Support\Str::limit($unCours->description, 90) }}
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td>
                                @if($unCours->categorie)
                                    <span class="badge bg-primary-subtle text-primary">{{ $unCours->categorie }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            <td class="text-center">
                                <i class="ri-list-check-2 me-1 text-danger"></i>{{ $unCours->chapitres_count }}
                            </td>

                            <td class="text-center">
                                <i class="ri-group-line me-1 text-danger"></i>{{ $unCours->classes_count }}
                            </td>

                            <td class="fw-semibold text-danger">
                                {{ number_format($unCours->prix_particulier, 0, ',', ' ') }} FCFA
                            </td>

                            <td class="fw-semibold">
                                {{ number_format($unCours->prix_entreprise, 0, ',', ' ') }} FCFA
                            </td>

                            {{-- Actions : voir (œil conservé), modifier, supprimer.
                                 Mêmes boutons que la gestion des utilisateurs et des classes. --}}
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('admin.cours.show', $unCours) }}"
                                       class="btn btn-sm btn-soft-primary" title="Voir le cours">
                                        <i class="ri-eye-line"></i>
                                    </a>

                                    <a href="{{ route('admin.cours.edit', $unCours) }}"
                                       class="btn btn-sm btn-soft-secondary" title="Modifier le cours">
                                        <i class="ri-edit-line"></i>
                                    </a>

                                    <form action="{{ route('admin.cours.destroy', $unCours) }}" method="POST"
                                          onsubmit="return confirm('Voulez-vous vraiment supprimer ce cours ?');">
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
                            <td colspan="8" class="text-center text-muted py-5">
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

        @if($cours->total() > 0)
            <div class="card-footer bg-transparent d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <small class="text-muted">
                    Affichage de {{ $cours->firstItem() }} à {{ $cours->lastItem() }}
                    sur {{ $cours->total() }} résultat(s)
                </small>
                {{ $cours->links() }}
            </div>
        @endif
    </div>

</div>

<script>
    // Sélection multiple : affiche la barre de suppression dès qu'au moins un cours est coché.
    document.addEventListener('DOMContentLoaded', function () {
        const cases = Array.from(document.querySelectorAll('.case-cours'));
        const toutes = document.getElementById('toutSelectionner');
        const barre = document.getElementById('barreSelection');
        const compteur = document.getElementById('nbSelection');
        const formulaire = document.getElementById('formSuppression');

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

        // Confirmation avant la suppression, avec le nombre de classes qui disparaîtront avec les cours
        formulaire.addEventListener('submit', function (e) {
            const cochees = cases.filter(c => c.checked);
            const nbClasses = cochees.reduce((total, c) => total + parseInt(c.dataset.classes || '0', 10), 0);
            const avertissement = nbClasses > 0
                ? '\n\nCela supprimera aussi ' + nbClasses + ' classe(s) liée(s) à ces cours.'
                : '';

            if (!confirm('Supprimer définitivement ' + cochees.length + ' cours ?' + avertissement)) {
                e.preventDefault();
            }
        });

        majSelection();
    });
</script>

@endsection