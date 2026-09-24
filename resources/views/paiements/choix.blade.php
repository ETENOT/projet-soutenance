@extends('layouts.master')

@section('title')
    Choix du paiement
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Formation
        @endslot
        @slot('title')
            Choix du mode de paiement
        @endslot
    @endcomponent

    <div class="row justify-content-center">
        <div class="col-lg-8">

            {{-- Récapitulatif de l'inscription --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Récapitulatif de votre inscription</h5>
                </div>

                <div class="card-body">
                    <p class="mb-1">
                        <span class="text-muted">Cours :</span>
                        <strong>{{ $inscription->classe->cours->titre }}</strong>
                    </p>

                    <p class="mb-1">
                        <span class="text-muted">Classe :</span>
                        {{ $inscription->classe->nom }}
                    </p>

                    <p class="mb-3">
                        <span class="text-muted">Dates :</span>
                        du {{ $inscription->classe->date_debut->format('d/m/Y') }}
                        au {{ $inscription->classe->date_fin->format('d/m/Y') }}
                    </p>

                    <h3 class="text-primary mb-0">
                        {{ number_format($montant, 0, ',', ' ') }} fcfa
                    </h3>
                </div>
            </div>

            {{-- Choix du mode de paiement --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Choisissez votre mode de paiement</h5>
                </div>

                <div class="card-body">

                    <div class="row g-3">

                        {{-- Paiement en espèces --}}
                        <div class="col-md-6">
                            <div class="border rounded p-4 h-100">
                                <div class="mb-3">
                                    <i class="ri-money-dollar-circle-line text-success"
                                       style="font-size: 2rem;"></i>
                                </div>

                                <h5 class="mb-2">Paiement en espèces</h5>

                                <p class="text-muted mb-4">
                                    Vous pouvez régler votre inscription directement
                                    auprès de l'administration.
                                </p>

                                <div class="alert alert-warning mb-3">
                                    <small>
                                        Votre inscription est enregistrée mais reste
                                        en attente de paiement. Elle sera validée
                                        lorsque l'administration aura reçu et
                                        confirmé votre paiement.
                                    </small>
                                </div>

                            <a href="{{ route('paiements.statut_paiement') }}"
                            class="btn btn-success w-100">
                                Continuer avec le paiement en espèces
                            </a>

                            </div>
                        </div>

                        {{-- Paiement SingPay --}}
                        <div class="col-md-6">
                            <div class="border rounded p-4 h-100">
                                <div class="mb-3">
                                    <i class="ri-smartphone-line text-primary"
                                       style="font-size: 2rem;"></i>
                                </div>

                                <h5 class="mb-2">Paiement en ligne</h5>

                                <p class="text-muted mb-4">
                                    Payez en ligne de manière sécurisée avec SingPay
                                    et choisissez votre opérateur Mobile Money.
                                </p>

                                <form method="POST"
                                      action="{{ route('paiements.payer', $inscription) }}"
                                      id="form-singpay">
                                    @csrf

                                    <button type="submit"
                                            class="btn btn-primary w-100"
                                            id="btn-singpay">
                                        Continuer avec SingPay
                                    </button>
                                </form>
                            </div>
                        </div>

                    </div>

                    <div class="mt-4">
                        <a href="{{ route('cours.mes') }}"
                           class="btn btn-soft-secondary">
                            Annuler
                        </a>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <script>
        // Bloque le bouton après le premier clic pour éviter
        // plusieurs demandes de paiement SingPay.
        document.addEventListener('DOMContentLoaded', function () {
            var formulaire = document.getElementById('form-singpay');

            formulaire.addEventListener('submit', function () {
                var bouton = document.getElementById('btn-singpay');

                bouton.disabled = true;
                bouton.textContent = 'Redirection vers SingPay...';
            });
        });
    </script>
@endsection