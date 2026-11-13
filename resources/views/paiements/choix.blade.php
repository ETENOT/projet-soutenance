@extends('layouts.master')

@section('title')
Choix du paiement
@endsection

@section('content')


@component('components.breadcrumb')
    @slot('li_1')
        Cours
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
                                <i class="ri-money-dollar-circle-line text-success fs-2"
                                  ></i>
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
                         PAIEMENT AIRTEL MONEY
                         ================================================= --}}
                    <div class="col-md-6">

                        <div class="border rounded p-4 h-100">

                            <div class="mb-3">
                                <i class="ri-smartphone-line text-primary fs-2"
                                  ></i>
                            </div>

                            <h5 class="mb-2">
                                Paiement Airtel Money
                            </h5>

                            <p class="text-muted mb-3">
                                Payez depuis votre compte Airtel Money : vous validez
                                le paiement sur votre téléphone avec votre code PIN.
                            </p>

                            <form method="POST"
                                  action="{{ route('paiements.payer.classe', $classe) }}"
                                  id="form-airtel">

                                @csrf

                                {{-- Numéro Airtel Money qui recevra la demande de paiement --}}
                                @include('paiements._numero-airtel')

                                {{-- Avant la passerelle : payer la totalité ou une partie (au moins la moitié) --}}
                                @include('paiements._choix-en-ligne', [
                                    'montant' => $montant,
                                    'minimumPartiel' => $minimumPartiel,
                                ])

                                <button type="submit"
                                        class="btn btn-danger w-100"
                                        id="btn-airtel">

                                    Payer avec Airtel Money

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
     PROTECTION CONTRE LES DOUBLES CLICS (une seule demande Airtel Money)
     ================================================================ --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {

        var formulaire = document.getElementById('form-airtel');
        var bouton = document.getElementById('btn-airtel');

        if (!formulaire || !bouton) {
            return;
        }

        formulaire.addEventListener('submit', function () {

            bouton.disabled = true;

            bouton.textContent = 'Envoi de la demande à Airtel Money...';

        });

    });
</script>

@endsection
