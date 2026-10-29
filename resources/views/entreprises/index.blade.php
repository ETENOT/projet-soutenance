@extends('layouts.master')

@section('title', 'Gestion des entreprises')

@section('content')

{{-- ===== Fil d'Ariane ===== --}}
@component('components.breadcrumb')
    @slot('li_1')
        <a href="{{ route('dashboard') }}">Accueil</a>
    @endslot
    @slot('title')
        Entreprises
    @endslot
@endcomponent

<div class="container-fluid pb-4">

    {{-- ===== En-tête ===== --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <p class="text-muted mb-0 col-lg-8 px-0">
            Gérez les entreprises partenaires et leurs membres : ajoutez des particuliers déjà inscrits ou créez-les directement.
        </p>

        <a href="{{ route('admin.entreprises.create') }}" class="btn btn-primary fw-semibold">
            <i class="ri-add-line me-1"></i> Ajouter une entreprise
        </a>
    </div>

    {{-- ===== Statistiques ===== --}}
    <div class="row g-2 mb-3">
        <div class="col-sm-6 col-xl-4">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center gap-2 py-2 px-3">
                    <span class="avatar-sm flex-shrink-0">
                        <span class="avatar-title rounded-3 bg-info-subtle text-info fs-4"><i class="ri-building-line"></i></span>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-0">{{ $stats['entreprises'] }}</h5>
                        <p class="text-muted mb-0 fs-12">Entreprises</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-4">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center gap-2 py-2 px-3">
                    <span class="avatar-sm flex-shrink-0">
                        <span class="avatar-title rounded-3 bg-success-subtle text-success fs-4"><i class="ri-team-line"></i></span>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-0">{{ $stats['membres'] }}</h5>
                        <p class="text-muted mb-0 fs-12">Membres rattachés à une entreprise</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-4">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center gap-2 py-2 px-3">
                    <span class="avatar-sm flex-shrink-0">
                        <span class="avatar-title rounded-3 bg-primary-subtle text-primary fs-4"><i class="ri-user-3-line"></i></span>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-0">{{ $stats['particuliers_libres'] }}</h5>
                        <p class="text-muted mb-0 fs-12">Particuliers sans entreprise</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Recherche façon Odoo (étiquettes + menu Filtres) ===== --}}
    @php
        $etiquettes = [];
        if ($avecMembres) {
            $etiquettes[] = ['Avec membres', ['avec_membres'], 'ri-team-line'];
        }
        if ($sansMembre) {
            $etiquettes[] = ['Sans membre', ['sans_membre'], 'ri-user-unfollow-line'];
        }

        $filtres = [[
            ['nom' => 'avec_membres', 'valeur' => '1', 'libelle' => 'Avec membres', 'coche' => $avecMembres],
            ['nom' => 'sans_membre', 'valeur' => '1', 'libelle' => 'Sans membre', 'coche' => $sansMembre],
        ]];
    @endphp

    @include('partials.barre-recherche', [
        'action' => route('admin.entreprises.index'),
        'placeholder' => 'Rechercher une entreprise (raison sociale, secteur, responsable, e-mail)…',
        'etiquettes' => $etiquettes,
        'filtres' => $filtres,
    ])

    {{-- ===== Barre de suppression multiple (cachée tant que rien n'est coché) ===== --}}
    <form id="formSuppression" method="POST" action="{{ route('admin.entreprises.destroy-multiple') }}">
        @csrf
        <div id="barreSelection" class="card border shadow-sm mb-3 d-none">
            <div class="card-body d-flex flex-wrap align-items-center gap-2">
                <strong><span id="nbSelection">0</span> entreprise(s) sélectionnée(s)</strong>

                <button type="submit" class="btn btn-danger">
                    <i class="ri-delete-bin-line me-1"></i> Supprimer la sélection
                </button>
                <button type="button" id="annulerSelection" class="btn btn-outline-secondary">Désélectionner</button>
            </div>
        </div>
    </form>

    {{-- ===== Tableau des entreprises ===== --}}
    <div class="card border shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle table-nowrap mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="nv-selection-column">
                            <input type="checkbox" id="toutSelectionner" class="form-check-input" aria-label="Tout sélectionner">
                        </th>
                        <th>Entreprise</th>
                        <th>Responsable du compte</th>
                        <th>Contact principal</th>
                        <th>Membres</th>
                        <th>Créée le</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($entreprises as $entreprise)
                        @php $responsable = $entreprise->users->first(); @endphp
                        <tr>
                            <td>
                                <input type="checkbox" name="ids[]" value="{{ $entreprise->id }}" form="formSuppression"
                                       class="form-check-input case-entreprise" aria-label="Sélectionner {{ $entreprise->raison_sociale }}">
                            </td>

                            <td>
                                <a href="{{ route('admin.entreprises.show', $entreprise) }}" class="fw-medium">{{ $entreprise->raison_sociale }}</a>
                                @if ($entreprise->secteur_activite)
                                    <div class="text-muted fs-12">{{ $entreprise->secteur_activite }}</div>
                                @endif
                            </td>

                            <td>
                                @if ($responsable)
                                    {{ $responsable->name }}
                                    <div class="text-muted fs-12">{{ $responsable->email }}</div>
                                @else
                                    <span class="text-muted">Aucun compte</span>
                                @endif
                            </td>

                            <td>{{ $entreprise->contact_principal }}</td>

                            <td>
                                <span class="badge {{ $entreprise->membres_count ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                                    {{ $entreprise->membres_count }}
                                </span>
                            </td>

                            <td>{{ $entreprise->created_at?->format('d/m/Y') }}</td>

                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('admin.entreprises.show', $entreprise) }}"
                                       class="btn btn-sm btn-soft-primary" title="Voir l’entreprise et ses membres">
                                        <i class="ri-eye-line"></i>
                                    </a>

                                    <a href="{{ route('admin.entreprises.edit', $entreprise) }}"
                                       class="btn btn-sm btn-soft-secondary" title="Modifier l’entreprise">
                                        <i class="ri-edit-line"></i>
                                    </a>

                                    <form action="{{ route('admin.entreprises.destroy', $entreprise) }}" method="POST"
                                          onsubmit="return confirm('Supprimer cette entreprise, son compte et ses devis ? Ses membres seront conservés comme particuliers.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-soft-danger" title="Supprimer l’entreprise">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="ri-building-line display-5 d-block mb-2"></i>
                                Aucune entreprise ne correspond à votre recherche.
                                @if ($etiquettes || request()->filled('q'))
                                    <a href="{{ route('admin.entreprises.index') }}" class="d-block mt-2">Réinitialiser les filtres</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($entreprises->total() > 0)
            <div class="card-footer bg-transparent d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <small class="text-muted">
                    Affichage de {{ $entreprises->firstItem() }} à {{ $entreprises->lastItem() }}
                    sur {{ $entreprises->total() }} résultat(s)
                </small>
                {{ $entreprises->links() }}
            </div>
        @endif
    </div>
</div>

<script>
    // Sélection multiple : affiche la barre de suppression dès qu'au moins une entreprise est cochée.
    document.addEventListener('DOMContentLoaded', function () {
        const cases = Array.from(document.querySelectorAll('.case-entreprise'));
        const toutes = document.getElementById('toutSelectionner');
        const barre = document.getElementById('barreSelection');
        const compteur = document.getElementById('nbSelection');
        const formulaire = document.getElementById('formSuppression');

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

        // Confirmation avant la suppression : action définitive
        formulaire.addEventListener('submit', function (e) {
            const nb = cases.filter(c => c.checked).length;
            if (!confirm('Supprimer définitivement ' + nb + ' entreprise(s), leurs comptes et leurs devis ? Les membres seront conservés comme particuliers.')) {
                e.preventDefault();
            }
        });

        majSelection();
    });
</script>

@endsection