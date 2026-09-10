@extends('layouts.master')

@section('title')
    Catalogue des cours
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Cours
        @endslot
        @slot('title')
            Catalogue des cours
        @endslot
    @endcomponent

    <div class="row">
        @forelse($cours as $unCours)
            <div class="col-xxl-3 col-md-6">
                <div class="card card-animate h-100">
                    <div class="card-body d-flex flex-column">
                        {{-- Icône générique : pas de colonne image dans le schéma actuel --}}
                        <div class="avatar-sm mb-3">
                            <span class="avatar-title bg-primary-subtle rounded-circle fs-2">
                                <i data-feather="book-open" class="text-primary"></i>
                            </span>
                        </div>

                        <h5 class="mb-3">{{ $unCours->titre }}</h5>

                        <div class="mt-auto">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Particulier</span>
                                <span class="fw-semibold">{{ number_format($unCours->prix_particulier, 2) }} €</span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span class="text-muted">Entreprise</span>
                                <span class="fw-semibold">{{ number_format($unCours->prix_entreprise, 2) }} €</span>
                            </div>

                            <a href="{{ route('cours.show', $unCours) }}" class="btn btn-primary w-100">
                                Voir le détail
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            {{-- Cas où la table cours est vide (ton catalogue à récupérer plus tard) --}}
            <div class="col-12">
                <div class="text-center py-5">
                    <i data-feather="inbox" style="width: 48px; height: 48px;" class="text-muted mb-3"></i>
                    <p class="text-muted">Aucun cours disponible pour le moment.</p>
                </div>
            </div>
        @endforelse
    </div>
@endsection