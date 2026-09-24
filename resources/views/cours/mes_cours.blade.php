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
            @if($inscription->paiement)
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

                            {{-- Statut de paiement : "payée" = l'inscription possède un Paiement
                                (même règle que le tableau de bord). --}}
                            @if($inscription->paiement)
                                <p class="mb-3">
                                    <!-- <span class="badge bg-success-subtle text-success">Payée</span> -->
                                    <small class="text-muted d-block mt-1">
                                        {{ $inscription->paiement->libelle_mode }}
                                        @if($inscription->paiement->reference)
                                            · {{ $inscription->paiement->reference }}
                                        @endif
                                    </small>
                                </p>
                                @else
                                <!-- <p class="mb-3">
                                    <span class="badge bg-warning-subtle text-warning">En attente de paiement</span>
                                </p> -->
                            @endif

                            <div class="d-flex gap-2">
                                <a href="{{ route('cours.show', $inscription->classe->cours) }}"
                                class="btn btn-primary btn-sm">
                                    Voir le cours
                                </a>

                                <!-- @unless($inscription->paiement)
                                    <a href="{{ route('inscriptions.paiement.create', $inscription) }}"
                                    class="btn btn-success btn-sm">
                                        Payer en ligne
                                    </a>
                                @endunless -->
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @empty
            <div class="col-12">
                <p class="text-muted text-center py-4">
                    Vous n’êtes inscrit à aucun cours pour le moment.
                </p>
            </div>
        @endforelse
    </div>
@endsection