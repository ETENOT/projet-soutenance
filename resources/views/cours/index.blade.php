@extends('layouts.master')

@section('title', 'Gestion des cours')

@section('content')

<div class="container-fluid pb-4">

    {{-- ===== En-tête ===== --}}
    <div class="bg-primary bg-gradient text-white rounded-4 p-4 p-md-5 mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <span class="badge rounded-pill bg-white bg-opacity-25 text-white px-3 py-2 mb-3">
                    <i class="ri-settings-3-line me-1"></i> Administration
                </span>
                <h3 class="fw-bold text-white mb-2">Gestion des cours</h3>
                <p class="text-white opacity-75 mb-0">
                    Gérez vos formations, vos classes et vos contenus pédagogiques.
                </p>
            </div>

            <a href="{{ route('admin.cours.create') }}" class="btn btn-danger fw-semibold">
                <i class="ri-add-line me-1"></i> Nouveau cours
            </a>
        </div>
    </div>

    {{-- ===== Statistiques ===== --}}
    <div class="row g-3 mb-4">

        <div class="col-md-4">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="avatar-md flex-shrink-0">
                        <span class="avatar-title rounded-3 bg-primary-subtle text-primary fs-2">
                            <i class="ri-book-open-line"></i>
                        </span>
                    </span>
                    <div>
                        <p class="text-muted mb-1">Cours</p>
                        <h4 class="fw-bold mb-0">{{ $cours->total() }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="avatar-md flex-shrink-0">
                        <span class="avatar-title rounded-3 bg-danger-subtle text-danger fs-2">
                            <i class="ri-list-check-2"></i>
                        </span>
                    </span>
                    <div>
                        <p class="text-muted mb-1">Chapitres</p>
                        <h4 class="fw-bold mb-0">{{ $nombreChapitres }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="avatar-md flex-shrink-0">
                        <span class="avatar-title rounded-3 bg-success-subtle text-success fs-2">
                            <i class="ri-group-line"></i>
                        </span>
                    </span>
                    <div>
                        <p class="text-muted mb-1">Classes</p>
                        <h4 class="fw-bold mb-0">{{ $nombreClasses }}</h4>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ===== Recherche / filtre (sur la page affichée) ===== --}}
    <div class="card border shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-center">

                <div class="col-lg-8">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent">
                            <i class="ri-search-line text-primary"></i>
                        </span>
                        <input type="text" id="rechercheCours" class="form-control"
                               placeholder="Rechercher un cours..."
                               aria-label="Rechercher un cours">
                    </div>
                </div>

                <div class="col-lg-4">
                    <select id="filtreCategorie" class="form-select" aria-label="Filtrer par catégorie">
                        <option value="">Toutes les catégories</option>
                        @foreach($cours->pluck('categorie')->filter()->unique()->sort() as $categorie)
                            <option value="{{ strtolower($categorie) }}">{{ $categorie }}</option>
                        @endforeach
                    </select>
                </div>

            </div>
        </div>
    </div>

    {{-- ===== Liste des cours ===== --}}
    <div id="listeCours">

        @forelse($cours as $unCours)
            <div class="card cours-card card-animate border shadow-sm mb-3"
                 data-titre="{{ strtolower($unCours->titre) }}"
                 data-categorie="{{ strtolower($unCours->categorie) }}">
                <div class="card-body p-4">
                    <div class="row align-items-center g-3">

                        {{-- Informations principales --}}
                        <div class="col-lg-5">
                            <div class="d-flex align-items-start gap-3">
                                <span class="avatar-md flex-shrink-0">
                                    <span class="avatar-title rounded-3 bg-primary-subtle text-primary fs-2">
                                        <i class="ri-book-2-line"></i>
                                    </span>
                                </span>

                                <div>
                                    <h5 class="fw-bold mb-1">{{ $unCours->titre }}</h5>

                                    @if($unCours->categorie)
                                        <span class="badge bg-danger-subtle text-danger mb-2">
                                            {{ $unCours->categorie }}
                                        </span>
                                    @endif

                                    @if($unCours->description)
                                        <p class="text-muted mb-0 small">
                                            {{ \Illuminate\Support\Str::limit($unCours->description, 120) }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Statistiques du cours --}}
                        <div class="col-lg-2">
                            <div class="d-flex flex-wrap gap-4">
                                <div>
                                    <div class="small text-muted">Chapitres</div>
                                    <div class="fw-semibold">
                                        <i class="ri-list-check-2 me-1 text-danger"></i>{{ $unCours->chapitres_count }}
                                    </div>
                                </div>
                                <div>
                                    <div class="small text-muted">Classes</div>
                                    <div class="fw-semibold">
                                        <i class="ri-group-line me-1 text-danger"></i>{{ $unCours->classes_count }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Prix --}}
                        <div class="col-lg-2">
                            <div class="small text-muted">Particulier</div>
                            <div class="fw-bold text-danger">
                                {{ number_format($unCours->prix_particulier, 0, ',', ' ') }} FCFA
                            </div>
                            <div class="small text-muted mt-1">Entreprise</div>
                            <div class="fw-semibold">
                                {{ number_format($unCours->prix_entreprise, 0, ',', ' ') }} FCFA
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="col-lg-3">
                            <div class="d-flex justify-content-lg-end gap-2 flex-wrap">

                                <a href="{{ route('admin.cours.show', $unCours) }}"
                                   class="btn btn-primary btn-sm text-nowrap" title="Voir le cours">
                                    <i class="ri-eye-line me-1"></i> Voir
                                </a>

                                <a href="{{ route('admin.cours.edit', $unCours) }}"
                                   class="btn btn-outline-secondary btn-sm" title="Modifier le cours">
                                    <i class="ri-edit-line"></i>
                                </a>

                                <form action="{{ route('admin.cours.destroy', $unCours) }}" method="POST"
                                      onsubmit="return confirm('Voulez-vous vraiment supprimer ce cours ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm" title="Supprimer">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </form>

                            </div>
                        </div>

                    </div>
                </div>
            </div>
        @empty
            <div class="card border shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="ri-book-open-line display-5 text-muted d-block mb-3"></i>
                    <h5 class="fw-bold">Aucun cours</h5>
                    <p class="text-muted mb-3">Vous n'avez encore créé aucun cours.</p>
                    <a href="{{ route('admin.cours.create') }}" class="btn btn-danger">
                        <i class="ri-add-line me-1"></i> Créer le premier cours
                    </a>
                </div>
            </div>
        @endforelse

    </div>

    {{-- ===== Pagination ===== --}}
    @if($cours->hasPages())
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-4 gap-3">
            <small class="text-muted">
                Affichage de {{ $cours->firstItem() }} à {{ $cours->lastItem() }}
                sur {{ $cours->total() }} cours
            </small>

            {{ $cours->links() }}
        </div>
    @endif

</div>

@endsection

{{-- ===== Recherche et filtre sur les cartes affichées ===== --}}
@section('script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const recherche = document.getElementById('rechercheCours');
    const filtre = document.getElementById('filtreCategorie');
    const cartes = document.querySelectorAll('.cours-card');

    function filtrerCours() {
        const texte = recherche.value.toLowerCase().trim();
        const categorieChoisie = filtre.value.toLowerCase();

        cartes.forEach(function (carte) {
            const correspondRecherche =
                carte.dataset.titre.includes(texte) ||
                carte.dataset.categorie.includes(texte);

            const correspondCategorie =
                categorieChoisie === '' ||
                carte.dataset.categorie === categorieChoisie;

            carte.classList.toggle('d-none', !(correspondRecherche && correspondCategorie));
        });
    }

    recherche.addEventListener('input', filtrerCours);
    filtre.addEventListener('change', filtrerCours);
});
</script>
@endsection