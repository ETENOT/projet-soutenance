@extends(Auth::check() ? 'layouts.master' : 'layouts.master-without-nav')

@section('title')
Catalogue des cours
@endsection

{{-- Le layout visiteur attend la balise <body> depuis la vue --}}
@guest
    @section('body')
    <body>
    @endsection
@endguest

@section('content')

@php
    $role = Auth::user()?->role?->nom;

    // Icônes de secours : utilisées en rotation quand le cours n'a pas d'image
    $icones = [
        'ri-computer-line',
        'ri-code-s-slash-line',
        'ri-briefcase-4-line',
        'ri-calculator-line',
        'ri-book-open-line',
    ];
@endphp

{{-- ===== Fil d'Ariane (connectés) ===== --}}
@auth
    @component('components.breadcrumb')
        @slot('li_1') Cours @endslot
        @slot('title') Catalogue des cours @endslot
    @endcomponent
@endauth

<div class="{{ Auth::check() ? 'container-fluid' : 'container pb-5' }}">

    {{-- ===== Barre visiteur ===== --}}
    @guest
        <div class="d-flex justify-content-between align-items-center py-3 mb-3">
            <a href="{{ url('/') }}">
                <img src="{{ asset('images/logo_neovision.png') }}" alt="NEO VISION" height="40">
            </a>
            <a href="{{ route('login') }}" class="btn btn-primary">Se connecter</a>
        </div>
    @endguest

    {{-- ===== Hero + recherche ===== --}}
    <div class="bg-primary bg-gradient text-white rounded-4 p-4 p-md-5 mb-4">
        <span class="badge rounded-pill bg-white bg-opacity-25 text-white px-3 py-2 mb-3">
            <i class="ri-graduation-cap-line me-1"></i> Formations professionnelles
        </span>

        <h1 class="fw-bold text-white mb-2">Découvrez nos formations</h1>
        <p class="text-white opacity-75 col-lg-7 px-0 mb-4">
            Développez vos compétences grâce à nos formations conçues pour vous accompagner dans votre progression.
        </p>

        <form method="GET" action="{{ route('cours.catalogue') }}" class="col-lg-8 px-0">
            <div class="input-group bg-white rounded-3 shadow p-1">
                <span class="input-group-text bg-transparent border-0 text-primary">
                    <i class="ri-search-line"></i>
                </span>
                <input type="search" name="search" value="{{ $search }}"
                       class="form-control border-0 shadow-none"
                       placeholder="Rechercher une formation..."
                       aria-label="Rechercher une formation">
                <button type="submit" class="btn btn-danger px-4 fw-semibold rounded-2">Rechercher</button>
            </div>
        </form>
    </div>

    {{-- ===== En-tête des résultats ===== --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-1">Nos formations</h4>
            <p class="text-muted small mb-0">
                @if($search !== '')
                    {{ $cours->total() }} résultat(s) pour « {{ $search }} »
                @else
                    {{ $cours->total() }} formation(s) disponible(s)
                @endif
            </p>
        </div>

        @if($search !== '')
            <a href="{{ route('cours.catalogue') }}" class="btn btn-outline-secondary btn-sm">
                <i class="ri-close-line me-1"></i> Réinitialiser
            </a>
        @endif
    </div>

    {{-- ===== Liste des cours ===== --}}
    <div class="row g-4">
        @forelse($cours as $unCours)
            @php
                $icon = $icones[($cours->firstItem() + $loop->index - 1) % count($icones)];
                $categorie = strtolower((string) $unCours->categorie);

                // Icône selon la catégorie
                if (str_contains($categorie, 'excel')) {
                    $icon = 'ri-file-excel-2-line';
                } elseif (str_contains($categorie, 'word')) {
                    $icon = 'ri-file-word-2-line';
                } elseif (str_contains($categorie, 'powerpoint')) {
                    $icon = 'ri-file-ppt-2-line';
                } elseif (str_contains($categorie, 'web') || str_contains($categorie, 'développement') || str_contains($categorie, 'informatique')) {
                    $icon = 'ri-code-s-slash-line';
                } elseif (str_contains($categorie, 'gestion')) {
                    $icon = 'ri-briefcase-4-line';
                }
            @endphp

            <div class="col-xl-4 col-md-6">
                <div class="card card-animate h-100 overflow-hidden border shadow-sm mb-0">

                    {{-- Image du cours ou image de secours --}}
                    @if(!empty($unCours->image))
                        <div class="ratio ratio-16x9">
                            <img src="{{ asset('storage/' . $unCours->image) }}"
                                 alt="{{ $unCours->titre }}" class="object-fit-cover">
                        </div>
                    @else
                        <div class="ratio ratio-16x9 bg-primary-subtle">
                            <div class="d-flex align-items-center justify-content-center">
                                <span class="avatar-lg">
                                    <span class="avatar-title rounded-3 bg-white text-primary fs-1 shadow-sm">
                                        <i class="{{ $icon }}"></i>
                                    </span>
                                </span>
                            </div>
                        </div>
                    @endif

                    <div class="card-body d-flex flex-column">
                        @if(!empty($unCours->categorie))
                            <span class="badge bg-primary-subtle text-primary text-uppercase align-self-start mb-2">
                                {{ $unCours->categorie }}
                            </span>
                        @endif

                        <h5 class="fw-bold mb-2">{{ $unCours->titre }}</h5>

                        <p class="text-muted small mb-0">
                            {{ \Illuminate\Support\Str::limit($unCours->description ?: 'Description à venir pour cette formation.', 100) }}
                        </p>

                        {{-- Informations --}}
                        <div class="d-flex gap-4 text-muted small border-top border-bottom py-2 my-3">
                            <span><i class="ri-book-open-line me-1"></i>{{ $unCours->chapitres_count }} chapitre(s)</span>
                            <span><i class="ri-calendar-2-line me-1"></i>{{ $unCours->classes_count }} classe(s)</span>
                        </div>

                        {{-- Prix + bouton --}}
                        <div class="d-flex justify-content-between align-items-center gap-2 mt-auto">
                            <div>
                                <small class="text-muted d-block">À partir de</small>
                                <span class="fs-5 fw-bold text-danger">
                                    {{ number_format($role === 'entreprise' ? $unCours->prix_entreprise : $unCours->prix_particulier, 0, ',', ' ') }} FCFA
                                </span>
                            </div>
                            <a href="{{ route('cours.show', $unCours) }}" class="btn btn-primary fw-semibold text-nowrap">
                                Découvrir <i class="ri-arrow-right-line ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            {{-- Aucun cours --}}
            <div class="col-12 text-center py-5">
                <i class="ri-inbox-line text-muted display-4"></i>
                <h5 class="mt-3">Aucune formation trouvée</h5>
                <p class="text-muted">Essayez avec un autre terme de recherche.</p>

                @if($search !== '')
                    <a href="{{ route('cours.catalogue') }}" class="btn btn-primary">Voir toutes les formations</a>
                @endif
            </div>
        @endforelse
    </div>

    {{-- ===== Pagination ===== --}}
    @if($cours->hasPages())
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-4 gap-3">
            <small class="text-muted">
                Affichage de {{ $cours->firstItem() }} à {{ $cours->lastItem() }}
                sur {{ $cours->total() }} formation(s)
            </small>
            {{ $cours->links() }}
        </div>
    @endif

</div>
@endsection