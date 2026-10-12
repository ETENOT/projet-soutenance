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

        <form method="GET" action="{{ route('cours.catalogue') }}" class="col-md-8 col-lg-5 px-0">
            <div class="input-group input-group-sm bg-white rounded-3 shadow-sm p-1">
                <span class="input-group-text bg-transparent border-0 text-primary">
                    <i class="ri-search-line"></i>
                </span>
                <input type="search" name="search" value="{{ $search }}"
                       class="form-control border-0 shadow-none"
                       placeholder="Rechercher une formation..."
                       aria-label="Rechercher une formation">
                <button type="submit" class="btn btn-danger px-3 fw-semibold rounded-2">Rechercher</button>
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
            <div class="col-xl-4 col-md-6">
                @include('cours.partials.carte', [
                    'unCours' => $unCours,
                    'index'   => $cours->firstItem() + $loop->index - 1,
                ])
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