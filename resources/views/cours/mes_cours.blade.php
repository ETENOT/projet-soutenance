@extends('layouts.master')

@section('title')
    Mes cours
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Formation
        @endslot
        @slot('title')
            Mes cours
        @endslot
    @endcomponent

    <div class="row g-3">
        @forelse($inscriptions as $inscription)
            <div class="col-md-6 col-xl-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title mb-3">
                            {{ $inscription->classe->cours->titre }}
                        </h5>

                        <p class="text-muted mb-2">
                            Classe : {{ $inscription->classe->nom }}
                        </p>

                        <p class="text-muted mb-3">
                            Du {{ $inscription->classe->date_debut->format('d/m/Y') }}
                            au {{ $inscription->classe->date_fin->format('d/m/Y') }}
                        </p>

                        <a href="{{ route('cours.show', $inscription->classe->cours) }}"
                           class="btn btn-primary btn-sm">
                            Voir le cours
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <p class="text-muted text-center py-4">
                    Vous n’êtes inscrit à aucun cours pour le moment.
                </p>
            </div>
        @endforelse
    </div>
@endsection
