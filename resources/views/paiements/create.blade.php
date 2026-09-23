@extends('layouts.master')

@section('title')
    Paiement
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Formation
        @endslot
        @slot('title')
            Paiement en ligne
        @endslot
    @endcomponent

    <div class="row justify-content-center">
        <div class="col-lg-7">

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Récapitulatif</h5>
                </div>
                <div class="card-body">
                    <p class="mb-1"><span class="text-muted">Cours :</span> <strong>{{ $inscription->classe->cours->titre }}</strong></p>
                    <p class="mb-1"><span class="text-muted">Classe :</span> {{ $inscription->classe->nom }}</p>
                    <p class="mb-3">
                        <span class="text-muted">Dates :</span>
                        du {{ $inscription->classe->date_debut->format('d/m/Y') }}
                        au {{ $inscription->classe->date_fin->format('d/m/Y') }}
                    </p>
                    <h3 class="text-primary mb-0">{{ number_format($montant, 0, ',', ' ') }} fcfa</h3>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Paiement sécurisé</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('inscriptions.paiement.payer', $inscription) }}" id="form-paiement">
                        @csrf

                        <p class="text-muted mb-3">
                            Vous serez redirigé vers SingPay pour choisir votre opérateur Mobile Money et confirmer le paiement.
                        </p>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success" id="btn-payer">
                                Continuer vers SingPay
                            </button>
                            <a href="{{ route('cours.mes') }}" class="btn btn-soft-secondary">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Bloque le bouton dès l'envoi pour éviter les demandes de paiement en double.
        document.addEventListener('DOMContentLoaded', function () {
            var formulaire = document.getElementById('form-paiement');
            formulaire.addEventListener('submit', function () {
                var bouton = document.getElementById('btn-payer');
                bouton.disabled = true;
                bouton.textContent = 'Redirection vers SingPay...';
            });
        });
    </script>
@endsection