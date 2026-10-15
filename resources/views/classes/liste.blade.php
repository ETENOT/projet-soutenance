@extends('layouts.master')

@section('title', 'Gestion des classes')

@section('content')

{{-- ===== Fil d'Ariane ===== --}}
@component('components.breadcrumb')
    @slot('li_1')
        <a href="{{ route('dashboard') }}">Accueil</a>
    @endslot
    @slot('title')
        Classes
    @endslot
@endcomponent

<div class="container-fluid pb-4">

    {{-- ===== En-tête ===== --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <p class="text-muted mb-0 col-lg-8 px-0">
            Gérez les classes (sessions) de vos formations : créez, modifiez, consultez les détails et suivez leur état.
        </p>

        {{-- Une classe appartient toujours à une formation : on choisit d'abord laquelle --}}
        <div class="dropdown">
            <button class="btn btn-primary fw-semibold dropdown-toggle" type="button"
                    data-bs-toggle="dropdown" aria-expanded="false">
                <i class="ri-add-line me-1"></i> Créer une classe
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><h6 class="dropdown-header">Pour quelle formation ?</h6></li>
                @forelse($tousLesCours as $unCours)
                    <li>
                        <a class="dropdown-item" href="{{ route('admin.cours.classes.create', $unCours) }}">
                            {{ $unCours->titre }}
                        </a>
                    </li>
                @empty
                    <li><span class="dropdown-item-text text-muted">Aucune formation créée.</span></li>
                @endforelse
            </ul>
        </div>
    </div>

    {{-- ===== Statistiques (cartes compactes : avatar-sm, chiffre en h5, libellé en fs-12) ===== --}}
    <div class="row g-2 mb-3">

        <div class="col-sm-6 col-xl-3">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center gap-2 py-2 px-3">
                    <span class="avatar-sm flex-shrink-0">
                        <span class="avatar-title rounded-3 bg-success-subtle text-success fs-4">
                            <i class="ri-play-circle-line"></i>
                        </span>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-0">{{ $stats['en_cours'] }}</h5>
                        <p class="text-muted mb-0 fs-12">Classes en cours</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center gap-2 py-2 px-3">
                    <span class="avatar-sm flex-shrink-0">
                        <span class="avatar-title rounded-3 bg-warning-subtle text-warning fs-4">
                            <i class="ri-calendar-event-line"></i>
                        </span>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-0">{{ $stats['a_venir'] }}</h5>
                        <p class="text-muted mb-0 fs-12">Classes à venir</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center gap-2 py-2 px-3">
                    <span class="avatar-sm flex-shrink-0">
                        <span class="avatar-title rounded-3 bg-info-subtle text-info fs-4">
                            <i class="ri-hourglass-line"></i>
                        </span>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-0">{{ $stats['terminees'] }}</h5>
                        <p class="text-muted mb-0 fs-12">Classes terminées</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center gap-2 py-2 px-3">
                    <span class="avatar-sm flex-shrink-0">
                        <span class="avatar-title rounded-3 bg-primary-subtle text-primary fs-4">
                            <i class="ri-book-open-line"></i>
                        </span>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-0">{{ $stats['formations'] }}</h5>
                        <p class="text-muted mb-0 fs-12">Formations concernées</p>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ===== Erreurs de filtre (période à l'envers, nombre négatif...) ===== --}}
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
            <form method="GET" action="{{ route('admin.classes.index') }}">
                <div class="row g-3 align-items-center">

                    <div class="col-lg-4">
                        <div class="input-group">
                            <span class="input-group-text bg-transparent">
                                <i class="ri-search-line text-primary"></i>
                            </span>
                            <input type="text" name="q" value="{{ request('q') }}" class="form-control"
                                   placeholder="Rechercher une classe..." aria-label="Rechercher une classe">
                        </div>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                        <select name="cours_id" class="form-select" aria-label="Filtrer par formation"
                                onchange="this.form.submit()">
                            <option value="">Toutes les formations</option>
                            @foreach($tousLesCours as $unCours)
                                <option value="{{ $unCours->id }}" @selected(request('cours_id') == $unCours->id)>
                                    {{ $unCours->titre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-sm-6 col-lg-2">
                        <select name="etat" class="form-select" aria-label="Filtrer par statut"
                                onchange="this.form.submit()">
                            <option value="">Tous les statuts</option>
                            <option value="en_cours" @selected(request('etat') === 'en_cours')>En cours</option>
                            <option value="a_venir" @selected(request('etat') === 'a_venir')>À venir</option>
                            <option value="terminee" @selected(request('etat') === 'terminee')>Terminée</option>
                        </select>
                    </div>

                    {{-- ----- Filtres supplémentaires ----- --}}

                    {{-- Catégorie du cours (Bureautique, Comptabilité...) --}}
                    <div class="col-sm-6 col-lg-3">
                        <select name="categorie" class="form-select" aria-label="Filtrer par catégorie"
                                onchange="this.form.submit()">
                            <option value="">Toutes les catégories</option>
                            @foreach($toutesLesCategories as $categorie)
                                <option value="{{ $categorie }}" @selected(request('categorie') === $categorie)>{{ $categorie }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Formateur de la classe (ou "Sans formateur") --}}
                    <div class="col-sm-6 col-lg-3">
                        <select name="formateur_id" class="form-select" aria-label="Filtrer par formateur"
                                onchange="this.form.submit()">
                            <option value="">Tous les formateurs</option>
                            <option value="aucun" @selected(request('formateur_id') === 'aucun')>— Sans formateur —</option>
                            @foreach($tousLesFormateurs as $formateur)
                                <option value="{{ $formateur->id }}" @selected((string) request('formateur_id') === (string) $formateur->id)>
                                    {{ $formateur->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Lieu --}}
                    <div class="col-sm-6 col-lg-3">
                        <select name="lieu" class="form-select" aria-label="Filtrer par lieu"
                                onchange="this.form.submit()">
                            <option value="">Tous les lieux</option>
                            @foreach($tousLesLieux as $lieu)
                                <option value="{{ $lieu }}" @selected(request('lieu') === $lieu)>{{ $lieu }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Nombre d'inscrits : de ... à ... (laisser vide = pas de limite) --}}
                    <div class="col-sm-6 col-lg-3">
                        <div class="input-group">
                            <span class="input-group-text">Inscrits</span>
                            <input type="number" name="inscrits_min" min="0" value="{{ request('inscrits_min') }}"
                                   class="form-control" placeholder="min" aria-label="Nombre d'inscrits minimum">
                            <input type="number" name="inscrits_max" min="0" value="{{ request('inscrits_max') }}"
                                   class="form-control" placeholder="max" aria-label="Nombre d'inscrits maximum">
                        </div>
                    </div>

                    {{-- Période : garde les classes qui se déroulent (au moins un jour) dans cet intervalle --}}
                    <div class="col-sm-6 col-lg-4">
                        <div class="input-group">
                            <span class="input-group-text">Du</span>
                            <input type="date" name="date_du" value="{{ request('date_du') }}"
                                   class="form-control" aria-label="Date de début de la période">
                            <span class="input-group-text">au</span>
                            <input type="date" name="date_au" value="{{ request('date_au') }}"
                                   class="form-control" aria-label="Date de fin de la période">
                        </div>
                    </div>

                    <div class="col-lg-8 d-flex gap-2 justify-content-lg-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-filter-3-line me-1"></i> Filtrer
                        </button>
                        <a href="{{ route('admin.classes.index') }}" class="btn btn-outline-secondary">
                            <i class="ri-refresh-line me-1"></i> Réinitialiser
                        </a>
                    </div>

                </div>
            </form>
        </div>
    </div>

    {{-- ===== Barre d'actions groupées =====
         Cachée tant qu'aucune classe n'est cochée. Les cases du tableau sont rattachées à CE formulaire
         par leur attribut form="formActions" (on ne peut pas imbriquer ce formulaire autour du tableau,
         qui contient déjà les formulaires de suppression de chaque ligne). --}}
    <form id="formActions" method="POST" action="{{ route('admin.classes.actions') }}">
        @csrf
        <div id="barreActions" class="card border shadow-sm mb-3 d-none">
            <div class="card-body d-flex flex-wrap align-items-center gap-2">
                <strong><span id="nbSelection">0</span> classe(s) sélectionnée(s)</strong>

                <select name="action" id="actionChoisie" class="form-select w-auto" aria-label="Action à appliquer">
                    <option value="">Choisir une action…</option>
                    <option value="affecter_formateur">Affecter un formateur</option>
                    <option value="retirer_formateur">Retirer le formateur</option>
                    <option value="fermer">Fermer les classes</option>
                    <option value="supprimer">Supprimer les classes</option>
                </select>

                {{-- Visible seulement pour l'action "Affecter un formateur" --}}
                <select name="formateur_id" id="formateurCible" class="form-select w-auto d-none" aria-label="Formateur à affecter">
                    <option value="">Choisir le formateur…</option>
                    @foreach($tousLesFormateurs as $formateur)
                        <option value="{{ $formateur->id }}">{{ $formateur->name }}</option>
                    @endforeach
                </select>

                <button type="submit" class="btn btn-primary">
                    <i class="ri-check-line me-1"></i> Appliquer
                </button>
                <button type="button" id="annulerSelection" class="btn btn-outline-secondary">Désélectionner</button>
            </div>
        </div>
    </form>

    {{-- ===== Tableau des classes ===== --}}
    <div class="card border shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle table-nowrap mb-0">
                <thead class="table-light">
                    <tr>
                        {{-- Coche / décoche toutes les classes de la page --}}
                        <th style="width: 40px;">
                            <input type="checkbox" id="toutSelectionner" class="form-check-input" aria-label="Tout sélectionner">
                        </th>
                        <th>Formation</th>
                        <th>Catégorie</th>
                        <th>Classe</th>
                        <th>Formateur</th>
                        <th>Dates</th>
                        <th>Lieu</th>
                        <th>Inscrits</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($classes as $classe)
                        @php
                            $etat = $classe->etat;
                            $badge = match ($etat) {
                                'en_cours' => ['bg-success-subtle text-success', 'En cours'],
                                'a_venir'  => ['bg-warning-subtle text-warning', 'À venir'],
                                default    => ['bg-info-subtle text-info', 'Terminée'],
                            };
                            $complete = $classe->inscriptions_count >= $classe->capacite_max;
                        @endphp

                        <tr>
                            {{-- Case de sélection : rattachée au formulaire d'actions groupées --}}
                            <td>
                                <input type="checkbox" name="ids[]" value="{{ $classe->id }}" form="formActions"
                                       class="form-check-input case-classe" aria-label="Sélectionner {{ $classe->nom }}">
                            </td>

                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    @if($classe->cours?->image)
                                        <img src="{{ asset('storage/' . $classe->cours->image) }}"
                                             alt="{{ $classe->cours->titre }}"
                                             class="avatar-sm rounded-3 object-fit-cover flex-shrink-0">
                                    @else
                                        <span class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title rounded-3 bg-primary-subtle text-primary fs-3">
                                                <i class="ri-book-open-line"></i>
                                            </span>
                                        </span>
                                    @endif

                                    <div>
                                        <div class="fw-semibold">{{ $classe->cours?->titre ?? 'Formation supprimée' }}</div>
                                    </div>
                                </div>
                            </td>

                            {{-- Catégorie du cours --}}
                            <td>
                                @if($classe->cours?->categorie)
                                    <span class="badge bg-primary-subtle text-primary">{{ $classe->cours->categorie }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            <td class="fw-medium">{{ $classe->nom }}</td>

                            {{-- Formateur affecté à cette classe (relation Classe::formateur) --}}
                            <td>
                                @if($classe->formateur)
                                    <i class="ri-user-star-line text-primary me-1"></i>{{ $classe->formateur->name }}
                                @else
                                    <span class="text-muted">Non affecté</span>
                                @endif
                            </td>

                            <td>
                                <div>
                                    {{ $classe->date_debut->format('d/m/Y') }}
                                    <i class="ri-arrow-right-line text-muted mx-1"></i>
                                    {{ $classe->date_fin->format('d/m/Y') }}
                                </div>
                                <small class="text-muted">
                                    {{ max(1, (int) ceil($classe->date_debut->diffInDays($classe->date_fin) / 7)) }} semaine(s)
                                </small>
                            </td>

                            <td>
                                <i class="ri-map-pin-line text-danger me-1"></i>{{ $classe->lieu }}
                            </td>

                            <td>
                                <span class="fw-semibold {{ $complete ? 'text-danger' : '' }}">
                                    {{ $classe->inscriptions_count }} / {{ $classe->capacite_max }}
                                </span>
                                @if($complete)
                                    <span class="badge bg-danger-subtle text-danger ms-1">Complète</span>
                                @endif
                            </td>

                            <td>
                                <span class="badge {{ $badge[0] }}">{{ $badge[1] }}</span>
                            </td>

                            {{-- Actions : modifier (gérer) et supprimer. L'icône "œil" a été retirée. --}}
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    @if($classe->cours)
                                        <a href="{{ route('admin.cours.classes.edit', [$classe->cours, $classe]) }}"
                                           class="btn btn-sm btn-soft-secondary" title="Gérer la classe et ses inscrits">
                                            <i class="ri-edit-line"></i>
                                        </a>

                                        <form action="{{ route('admin.cours.classes.destroy', [$classe->cours, $classe]) }}"
                                              method="POST"
                                              onsubmit="return confirm('Supprimer cette classe ?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-soft-danger" title="Supprimer">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-5">
                                <i class="ri-group-line display-5 d-block mb-2"></i>
                                Aucune classe ne correspond à votre recherche.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($classes->total() > 0)
            <div class="card-footer bg-transparent d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <small class="text-muted">
                    Affichage de {{ $classes->firstItem() }} à {{ $classes->lastItem() }}
                    sur {{ $classes->total() }} résultat(s)
                </small>
                {{ $classes->links() }}
            </div>
        @endif
    </div>

</div>

<script>
    // Sélection multiple : affiche la barre d'actions dès qu'au moins une classe est cochée.
    document.addEventListener('DOMContentLoaded', function () {
        const cases = Array.from(document.querySelectorAll('.case-classe'));
        const toutes = document.getElementById('toutSelectionner');
        const barre = document.getElementById('barreActions');
        const compteur = document.getElementById('nbSelection');
        const action = document.getElementById('actionChoisie');
        const formateur = document.getElementById('formateurCible');
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

        // Le choix du formateur n'apparaît que pour l'action "Affecter un formateur"
        action.addEventListener('change', function () {
            formateur.classList.toggle('d-none', action.value !== 'affecter_formateur');
        });

        // Confirmation pour les actions définitives
        formulaire.addEventListener('submit', function (e) {
            const nb = cases.filter(c => c.checked).length;
            if (action.value === 'supprimer' && !confirm('Supprimer définitivement ' + nb + ' classe(s) ?')) {
                e.preventDefault();
            }
            if (action.value === 'fermer' && !confirm('Fermer ' + nb + ' classe(s) ? Les taux de présence seront figés. Action définitive.')) {
                e.preventDefault();
            }
        });

        majSelection();
    });
</script>

@endsection