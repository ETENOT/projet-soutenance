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

        {{-- =========================================================
             RÉCAPITULATIF
             ========================================================= --}}
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    Récapitulatif de votre inscription
                </h5>
            </div>

            <div class="card-body">

                <p class="mb-1">
                    <span class="text-muted">Cours :</span>

                    <strong>
                        {{ $classe->cours->titre }}
                    </strong>
                </p>

                <p class="mb-1">
                    <span class="text-muted">Classe :</span>

                    {{ $classe->nom }}
                </p>

                <p class="mb-3">
                    <span class="text-muted">Dates :</span>

                    du {{ $classe->date_debut->format('d/m/Y') }}

                    au {{ $classe->date_fin->format('d/m/Y') }}
                </p>

                <h3 class="text-primary mb-0">
                    {{ number_format($montant, 0, ',', ' ') }} fcfa
                </h3>

            </div>
        </div>


        {{-- =========================================================
             CHOIX DU MODE DE PAIEMENT
             ========================================================= --}}
        <div class="card">

            <div class="card-header">
                <h5 class="card-title mb-0">
                    Choisissez votre mode de paiement
                </h5>
            </div>

            <div class="card-body">

                <div class="row g-3">


                    {{-- =================================================
                         PAIEMENT EN ESPÈCES
                         ================================================= --}}
                    <div class="col-md-6">

                        <div class="border rounded p-4 h-100">

                            <div class="mb-3">
                                <i class="ri-money-dollar-circle-line text-success"
                                   style="font-size: 2rem;"></i>
                            </div>

                            <h5 class="mb-2">
                                Paiement en espèces
                            </h5>

                            <p class="text-muted mb-4">
                                Vous pouvez régler votre inscription
                                directement auprès de l'administration.
                            </p>

                            <div class="alert alert-warning mb-3">

                                <small>
                                    Après votre choix, votre inscription
                                    sera enregistrée et restera en attente
                                    de confirmation du paiement par
                                    l'administration.
                                </small>

                            </div>

                            <form method="POST"
                                  action="{{ route('paiements.especes', $classe) }}">

                                @csrf

                                <button type="submit"
                                        class="btn btn-success w-100">

                                    Continuer avec le paiement en espèces

                                </button>

                            </form>

                        </div>

                    </div>


                    {{-- =================================================
                         PAIEMENT SINGPAY
                         ================================================= --}}
                    <div class="col-md-6">

                        <div class="border rounded p-4 h-100">

                            <div class="mb-3">
                                <i class="ri-smartphone-line text-primary"
                                   style="font-size: 2rem;"></i>
                            </div>

                            <h5 class="mb-2">
                                Paiement en ligne
                            </h5>

                            <p class="text-muted mb-4">
                                Payez en ligne de manière sécurisée avec
                                SingPay et choisissez votre opérateur
                                Mobile Money.
                            </p>

                            <form method="POST"
                                  action="{{ route('paiements.payer', $classe) }}"
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
            </div>
        </div>
    </div>
</div>



{{-- ================================================================
     PROTECTION CONTRE LES DOUBLES CLICS SINGPAY
     ================================================================ --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {

        var formulaire = document.getElementById('form-singpay');
        var bouton = document.getElementById('btn-singpay');

        if (!formulaire || !bouton) {
            return;
        }

        formulaire.addEventListener('submit', function () {

            bouton.disabled = true;

            bouton.textContent = 'Redirection vers SingPay...';

        });

    });
</script>

@endsection
