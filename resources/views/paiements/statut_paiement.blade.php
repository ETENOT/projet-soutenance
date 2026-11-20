@extends('layouts.master')

@section('title')
    Mes cours
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('cours.mes') }}">Cours</a>
        @endslot
        @slot('title')
            Statut de mes paiements
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

                        {{-- Statut de paiement : Payé (totalité) / Payé partiellement (+ reste à payer) / En attente --}}
                        @if($inscription->paiement)
                            <p class="mb-3">
                                @if($inscription->estPartielle())
                                    <span class="badge bg-info-subtle text-info">Payé partiellement</span>
                                    <small class="d-block mt-1">
                                        Versé : {{ number_format($inscription->paiement->montant, 0, ',', ' ') }} fcfa
                                        sur {{ number_format($inscription->paiement->montant_total, 0, ',', ' ') }} fcfa
                                    </small>
                                    <strong class="d-block text-danger">
                                        Reste à payer : {{ number_format($inscription->paiement->reste_a_payer, 0, ',', ' ') }} fcfa
                                    </strong>
                                    <small class="text-muted d-block">
                                        Réglez le reste auprès de l'administration pour recevoir votre attestation.
                                    </small>
                                @else
                                    <span class="badge bg-success-subtle text-success">Payé</span>
                                @endif
                                <small class="text-muted d-block mt-1">
                                    {{ $inscription->paiement->libelle_mode }}
                                    @if($inscription->paiement->reference)
                                        · {{ $inscription->paiement->reference }}
                                    @endif
                                </small>
                            </p>
                        @else
                            <p class="mb-3">
                                <span class="badge bg-warning-subtle text-warning">En attente de paiement</span>
                            </p>
                        @endif

                        <div class="d-flex gap-2">
                            @if($inscription->paiement)
                                <a href="{{ route('paiements.recu', $inscription->paiement) }}" target="_blank" class="btn btn-outline-primary btn-sm">Reçu</a>
                            @endif

                            <a href="{{ route('cours.show', $inscription->classe->cours) }}"
                               class="btn btn-primary btn-sm">
                                Voir le cours
                            </a>

                            @unless($inscription->paiement)
                                <a href="{{ route('paiements.create', $inscription) }}"
                                   class="btn btn-success btn-sm">
                                    Payer en ligne
                                </a>
                            @endunless
                        </div>
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