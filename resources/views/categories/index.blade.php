@extends('layouts.master')

@section('title', 'Gestion des catégories')

@section('content')

{{-- ===== Fil d'Ariane ===== --}}
@component('components.breadcrumb')
    @slot('li_1')
        <a href="{{ route('dashboard') }}">Accueil</a>
    @endslot
    @slot('title')
        Catégories
    @endslot
@endcomponent

@php
    // Retour d'une validation qui a échoué : "_mode" (ajout ou modification) dit quelle fenêtre rouvrir
    $modeAvecErreur = $errors->any() ? old('_mode') : null;
@endphp

<div class="container-fluid pb-4">

    {{-- ===== Statistique ===== --}}
    <div class="row g-2 mb-3">
        <div class="col-sm-6 col-xl-3">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center gap-2 py-2 px-3">
                    <span class="avatar-sm flex-shrink-0">
                        <span class="avatar-title rounded-3 bg-primary-subtle text-primary fs-4"><i class="ri-price-tag-3-line"></i></span>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-0">{{ $nombreCategories }}</h5>
                        <p class="text-muted mb-0 fs-12">Catégories</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Barre de suppression groupée : cachée tant qu'aucune catégorie n'est cochée.
         Les cases du tableau y sont rattachées par form="formGroupe". ===== --}}
    <form id="formGroupe" method="POST" action="{{ route('admin.categories.destroy-multiple') }}">
        @csrf
        <div id="barreActions" class="card border shadow-sm mb-3 d-none">
            <div class="card-body d-flex flex-wrap align-items-center gap-2">
                <strong><span id="nbSelection">0</span> catégorie(s) sélectionnée(s)</strong>
                <button type="button" class="btn btn-danger" id="supprimerSelection">
                    <i class="ri-delete-bin-line me-1"></i>Supprimer
                </button>
                <button type="button" id="annulerSelection" class="btn btn-outline-secondary">Désélectionner</button>
            </div>
        </div>
    </form>

    {{-- ===== Même ligne : bouton de création à gauche, recherche par nom à droite ===== --}}
    <div class="row g-2 justify-content-between align-items-start mb-3">
        <div class="col-auto">
            <button type="button" class="btn btn-primary fw-semibold"
                    data-bs-toggle="modal" data-bs-target="#modalCategorie" data-mode="ajout">
                <i class="ri-add-line me-1"></i>Créer une catégorie
            </button>
        </div>

        <div class="col-12 col-md-6 col-lg-4">
            <form method="GET" action="{{ route('admin.categories.index') }}">
                <div class="input-group shadow-sm">
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control"
                           placeholder="Rechercher une catégorie…" aria-label="Rechercher une catégorie">
                    @if (request()->filled('q'))
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-light border" title="Effacer la recherche">
                            <i class="ri-close-line"></i>
                        </a>
                    @endif
                    <button type="submit" class="btn btn-light border" title="Rechercher" aria-label="Rechercher">
                        <i class="ri-search-line text-primary"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===== Tableau des catégories : la dernière créée en premier ===== --}}
    <div class="card border shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0">
                <thead>
                    <tr class="table-light">
                        <th>
                            <input type="checkbox" id="toutSelectionner" class="form-check-input" aria-label="Tout sélectionner">
                        </th>
                        <th>Nom</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $categorie)
                        <tr>
                            <td>
                                <input type="checkbox" name="ids[]" value="{{ $categorie->id }}" form="formGroupe"
                                       class="form-check-input case-categorie" aria-label="Sélectionner {{ $categorie->nom }}">
                            </td>
                            <td class="fw-semibold">{{ $categorie->nom }}</td>
                            <td class="text-end text-nowrap">
                                <div class="d-flex justify-content-end gap-1">
                                    {{-- Modifier : le nom actuel voyage dans les data-* du bouton --}}
                                    <button type="button" class="btn btn-sm btn-soft-secondary" title="Modifier la catégorie"
                                            data-bs-toggle="modal" data-bs-target="#modalCategorie"
                                            data-mode="modif"
                                            data-action="{{ route('admin.categories.update', $categorie) }}"
                                            data-nom="{{ $categorie->nom }}">
                                        <i class="ri-edit-line"></i>
                                    </button>

                                    {{-- Supprimer : la fenêtre confirme, ou explique pourquoi c'est impossible (cours rattachés) --}}
                                    <button type="button" class="btn btn-sm btn-soft-danger" title="Supprimer la catégorie"
                                            data-bs-toggle="modal" data-bs-target="#modalSuppression"
                                            data-action="{{ route('admin.categories.destroy', $categorie) }}"
                                            data-nom="{{ $categorie->nom }}"
                                            data-cours="{{ (int) $categorie->cours_count }}"
                                            data-url-cours="{{ route('admin.cours.index', ['categorie' => $categorie->id]) }}">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-5">
                                <i class="ri-price-tag-3-line display-5 d-block mb-2"></i>
                                Aucune catégorie.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($categories->total() > 0)
            <div class="card-footer bg-transparent d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <small class="text-muted">
                    Affichage de {{ $categories->firstItem() }} à {{ $categories->lastItem() }}
                    sur {{ $categories->total() }} résultat(s)
                </small>
                {{ $categories->links() }}
            </div>
        @endif
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
                    <h5 class="modal-title">Supprimer la catégorie</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    {{-- Catégorie vide : confirmation simple --}}
                    <div id="suppressionConfirmation">
                        <p class="mb-2">Voulez-vous vraiment supprimer la catégorie <strong id="suppressionNom"></strong> ?</p>
                        <p class="text-muted mb-0">Cette action est définitive.</p>
                    </div>

                    {{-- Catégorie qui contient des cours : suppression impossible (elle laisserait des cours sans catégorie) --}}
                    <div id="suppressionBloquee" class="d-none">
                        <p class="mb-2">La catégorie <strong id="suppressionNomBloquee"></strong> ne peut pas être supprimée : <span id="suppressionNombre"></span> cours y sont rattachés.</p>
                        <p class="text-muted mb-2">Déplacez d'abord ces cours dans une autre catégorie (action « Changer de catégorie » de la liste des cours), puis revenez la supprimer.</p>
                        <a href="#" id="suppressionLienCours" class="btn btn-sm btn-light">Voir ces cours</a>
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
                <h5 class="modal-title">Supprimer les catégories sélectionnées</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Voulez-vous vraiment supprimer <strong id="nbSuppressionGroupee">0</strong> catégorie(s) ?</p>
                <p class="text-muted mb-2">Une catégorie qui contient des cours est ignorée : déplacez d'abord ses cours.</p>
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
<div class="modal fade" id="modalCategorie" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formCategorie" method="POST" action="{{ route('admin.categories.store') }}">
                @csrf
                {{-- Champs techniques : _method = PUT seulement en modification ; _mode et _action
                     permettent de rouvrir la bonne fenêtre si la validation échoue --}}
                <input type="hidden" name="_method" value="PUT" id="champMethode" disabled>
                <input type="hidden" name="_mode" id="champMode" value="{{ old('_mode') }}">
                <input type="hidden" name="_action" id="champAction" value="{{ old('_action') }}">

                <div class="modal-header">
                    <h5 class="modal-title" id="categorieTitre">Créer une catégorie</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>

                <div class="modal-body">
                    @if ($modeAvecErreur)
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $erreur)
                                    <li>{{ $erreur }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mb-0">
                        <label for="champNom" class="form-label">Nom de la catégorie</label>
                        <input type="text" name="nom" id="champNom" class="form-control" maxlength="255"
                               value="{{ old('nom') }}" required>
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
    const urlAjout = @json(route('admin.categories.store'));
    const modeAvecErreur = @json($modeAvecErreur);

    const modalCategorie = document.getElementById('modalCategorie');
    const form = document.getElementById('formCategorie');
    const champ = id => document.getElementById(id);

    // Sélection multiple : la barre de suppression apparaît dès qu'au moins une catégorie est cochée
    const cases = Array.from(document.querySelectorAll('.case-categorie'));
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

    // Adapte la fenêtre au mode : "ajout" (POST) ou "modif" (PUT)
    function preparer(mode) {
        const modif = mode === 'modif';
        champ('categorieTitre').textContent = modif ? 'Modifier la catégorie' : 'Créer une catégorie';
        champ('champMode').value = mode;
        champ('champMethode').disabled = !modif;
    }

    // Ouverture par un bouton : on remplit le formulaire (sauf retour d'erreur, géré plus bas)
    modalCategorie.addEventListener('show.bs.modal', function (e) {
        const bouton = e.relatedTarget;
        if (!bouton) return;

        const d = bouton.dataset;
        preparer(d.mode);

        if (d.mode === 'modif') {
            champ('champAction').value = d.action;
            form.action = d.action;
            champ('champNom').value = d.nom;
        } else {
            champ('champAction').value = urlAjout;
            form.action = urlAjout;
            champ('champNom').value = '';
        }
    });

    // Validation refusée : on rouvre la fenêtre avec ce qui avait été saisi (déjà dans le champ)
    if (modeAvecErreur) {
        preparer(modeAvecErreur);
        form.action = champ('champAction').value || urlAjout;
        bootstrap.Modal.getOrCreateInstance(modalCategorie).show();
    }

    // Fenêtre de suppression : nom de la catégorie et adresse d'envoi
    champ('modalSuppression').addEventListener('show.bs.modal', function (e) {
        const d = e.relatedTarget.dataset;
        const nombre = parseInt(d.cours, 10);
        const bloquee = nombre > 0;

        champ('suppressionNom').textContent = d.nom;
        champ('suppressionNomBloquee').textContent = d.nom;
        champ('suppressionNombre').textContent = nombre;
        champ('suppressionLienCours').href = d.urlCours;
        champ('formSuppression').action = d.action;

        // Des cours rattachés : on explique au lieu de proposer la suppression
        champ('suppressionConfirmation').classList.toggle('d-none', bloquee);
        champ('suppressionBloquee').classList.toggle('d-none', !bloquee);
        champ('suppressionValider').classList.toggle('d-none', bloquee);
    });
});
</script>
@endsection