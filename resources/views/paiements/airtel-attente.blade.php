@extends('layouts.master')

@section('title')
    Paiement Airtel Money
@endsection

{{--
    Page d'attente après la demande de paiement Airtel Money.
    Le particulier valide le paiement sur son téléphone (code PIN) ; pendant ce temps,
    la page demande le statut au serveur toutes les 5 secondes (route paiements.airtel.statut),
    qui lui-même interroge Airtel. Variable : $transaction (TransactionAirtel).
--}}

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Cours
        @endslot
        @slot('title')
            Paiement Airtel Money
        @endslot
    @endcomponent

    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body p-4 text-center">

                    {{-- En attente de la validation sur le téléphone --}}
                    <div id="etat-attente">
                        <div class="spinner-border text-danger mb-3" role="status" style="width: 3rem; height: 3rem;">
                            <span class="visually-hidden">Chargement…</span>
                        </div>
                        <h4 class="mb-2">Validez le paiement sur votre téléphone</h4>
                        <p class="text-muted mb-3">
                            Une demande de paiement de
                            <strong>{{ number_format($transaction->montant, 0, ',', ' ') }} fcfa</strong>
                            {{-- msisdn = 8 chiffres (74123456) -> affiché au format local 074 12 34 56 --}}
                            a été envoyée au <strong>{{ preg_replace('/^(\d{2})(\d{2})(\d{2})(\d{2})$/', '0$1 $2 $3 $4', $transaction->msisdn) }}</strong>.<br>
                            Saisissez votre code PIN Airtel Money pour confirmer.
                        </p>
                        <p class="text-muted fs-13 mb-0">
                            Formation : {{ $transaction->inscription->classe->cours->titre }}
                            ({{ $transaction->inscription->classe->nom }})
                        </p>

                        {{-- Affiché si la confirmation tarde (téléphone éteint, demande ignorée…) --}}
                        <div id="message-delai" class="alert alert-warning mt-4 mb-0 d-none text-start">
                            <i class="ri-time-line me-1"></i>
                            Pas encore de confirmation. Si vous n'avez rien reçu, vérifiez que votre téléphone est
                            allumé, puis réessayez depuis
                            <a href="{{ route('paiements.statut_paiement') }}">Statut de mes paiements</a>.
                            Si vous avez déjà validé, patientez : le paiement sera pris en compte dès qu'Airtel le confirme.
                        </div>
                    </div>

                    {{-- Paiement refusé ou annulé --}}
                    <div id="etat-echec" class="d-none">
                        <i class="ri-close-circle-line text-danger" style="font-size: 3.5rem;"></i>
                        <h4 class="mb-2">Paiement non effectué</h4>
                        <p class="text-muted" id="message-echec"></p>
                        <a href="{{ route('paiements.statut_paiement') }}" class="btn btn-primary">
                            Statut de mes paiements
                        </a>
                    </div>

                    {{-- Paiement confirmé : redirection automatique --}}
                    <div id="etat-reussi" class="d-none">
                        <i class="ri-checkbox-circle-line text-success" style="font-size: 3.5rem;"></i>
                        <h4 class="mb-0">Paiement confirmé, redirection…</h4>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const urlStatut = @json(route('paiements.airtel.statut', $transaction));
            const INTERVALLE = 5000;          // une vérification toutes les 5 s
            const DELAI_MESSAGE = 120000;     // message d'aide après 2 min sans réponse
            const DUREE_MAX = 600000;         // on arrête de vérifier après 10 min
            const debut = Date.now();
            let minuteur = null;

            function afficher(id) {
                ['etat-attente', 'etat-echec', 'etat-reussi'].forEach(function (bloc) {
                    document.getElementById(bloc).classList.toggle('d-none', bloc !== id);
                });
            }

            async function verifier() {
                try {
                    const reponse = await fetch(urlStatut, { headers: { 'Accept': 'application/json' } });
                    if (!reponse.ok) throw new Error('HTTP ' + reponse.status);
                    const donnees = await reponse.json();

                    if (donnees.statut === 'reussie') {
                        afficher('etat-reussi');
                        window.location.href = donnees.redirection;
                        return;
                    }

                    if (donnees.statut === 'echouee') {
                        document.getElementById('message-echec').textContent = donnees.message;
                        afficher('etat-echec');
                        return;
                    }
                } catch (e) {
                    // Coupure réseau passagère : on réessaie simplement au prochain tour
                }

                const ecoule = Date.now() - debut;
                if (ecoule > DELAI_MESSAGE) {
                    document.getElementById('message-delai').classList.remove('d-none');
                }
                if (ecoule < DUREE_MAX) {
                    minuteur = setTimeout(verifier, INTERVALLE);
                }
            }

            minuteur = setTimeout(verifier, INTERVALLE);
        });
    </script>
@endsection
