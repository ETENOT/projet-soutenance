@extends('layouts.master')

@section('title', 'Fiche d\'inscription')

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('admin.inscriptions.index') }}">Gestion des inscriptions</a>
        @endslot
        @slot('title')
            Fiche d'inscription
        @endslot
    @endcomponent

    @php
        $payee = $inscription->estPayee();
        $classe = $inscription->classe;
    @endphp

    {{-- ===== Actions ===== --}}
    <div class="d-flex flex-wrap gap-2 mb-3">
        <a href="{{ route('admin.inscriptions.edit', $inscription) }}" class="btn btn-primary">
            <i class="ri-edit-line me-1"></i> Modifier
        </a>

        @unless ($payee)
            <form action="{{ route('paiements.store', $inscription) }}" method="POST"
                  data-confirmer="Enregistrer le paiement au comptoir de {{ $inscription->user->name }} ?">
                @csrf
                <button type="submit" class="btn btn-success">
                    <i class="ri-money-dollar-circle-line me-1"></i> Encaisser le paiement
                </button>
            </form>
        @endunless

        <form action="{{ route('admin.inscriptions.destroy', $inscription) }}" method="POST"
              data-confirmer="{{ $payee
                  ? 'Cette inscription est PAYÉE : son paiement sera supprimé définitivement avec elle. Continuer ?'
                  : 'Supprimer cette inscription définitivement ?' }}">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger">
                <i class="ri-delete-bin-line me-1"></i> Supprimer
            </button>
        </form>

        <a href="{{ route('admin.inscriptions.index') }}" class="btn btn-light ms-auto">Retour à la liste</a>
    </div>

    <div class="row g-3">

        {{-- ===== Utilisateur ===== --}}
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header"><h5 class="card-title mb-0">Utilisateur inscrit</h5></div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Nom</dt><dd class="col-sm-8">{{ $inscription->user->name }}</dd>
                        <dt class="col-sm-4">E-mail</dt><dd class="col-sm-8">{{ $inscription->user->email }}</dd>
                        <dt class="col-sm-4">Type de compte</dt><dd class="col-sm-8">{{ ucfirst($inscription->user->role?->nom ?? '—') }}</dd>
                        <dt class="col-sm-4">Inscrit le</dt>
                        <dd class="col-sm-8 mb-0">{{ \Carbon\Carbon::parse($inscription->date_inscription)->format('d/m/Y') }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        {{-- ===== Classe et cours ===== --}}
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header"><h5 class="card-title mb-0">Classe</h5></div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Cours</dt><dd class="col-sm-8">{{ $classe->cours->titre }}</dd>
                        <dt class="col-sm-4">Classe</dt>
                        <dd class="col-sm-8">
                            {{ $classe->nom }}
                            <span class="badge bg-{{ $classe->etat_couleur }}-subtle text-{{ $classe->etat_couleur }} ms-1">{{ $classe->etat_libelle }}</span>
                        </dd>
                        <dt class="col-sm-4">Période</dt>
                        <dd class="col-sm-8">du {{ $classe->date_debut->format('d/m/Y') }} au {{ $classe->date_fin->format('d/m/Y') }}</dd>
                        <dt class="col-sm-4">Lieu</dt><dd class="col-sm-8">{{ $classe->lieu }}</dd>
                        <dt class="col-sm-4">Formateur</dt><dd class="col-sm-8 mb-0">{{ $classe->formateur?->name ?? 'Non affecté' }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        {{-- ===== Paiement ===== --}}
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header"><h5 class="card-title mb-0">Paiement</h5></div>
                <div class="card-body">
                    @if ($payee)
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Statut</dt>
                            <dd class="col-sm-8"><span class="badge bg-success-subtle text-success">Payé</span></dd>
                            <dt class="col-sm-4">Montant</dt>
                            <dd class="col-sm-8">{{ number_format($inscription->paiement->montant, 0, ',', ' ') }} fcfa</dd>
                            <dt class="col-sm-4">Moyen</dt><dd class="col-sm-8">{{ $inscription->paiement->libelle_mode }}</dd>
                            <dt class="col-sm-4">Référence</dt><dd class="col-sm-8">{{ $inscription->paiement->reference ?? '—' }}</dd>
                            <dt class="col-sm-4">Payé le</dt>
                            <dd class="col-sm-8 mb-0">{{ $inscription->paiement->created_at->format('d/m/Y H:i') }}</dd>
                        </dl>
                    @else
                        <span class="badge bg-warning-subtle text-warning">En attente</span>
                        <p class="text-muted mb-0 mt-2">Aucun paiement enregistré pour cette inscription.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- ===== Présence (renseignée à la fermeture de la classe) ===== --}}
        <div class="col-lg-6">
            <div class="card h-100 mb-0">
                <div class="card-header"><h5 class="card-title mb-0">Présence</h5></div>
                <div class="card-body">
                    @if ($inscription->taux_presence !== null)
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Taux</dt><dd class="col-sm-8">{{ rtrim(rtrim(number_format($inscription->taux_presence, 2, ',', ''), '0'), ',') }} %</dd>
                            <dt class="col-sm-4">Attestation</dt>
                            <dd class="col-sm-8 mb-0">{{ $inscription->attestation_eligible ? 'Éligible' : 'Non éligible' }}</dd>
                        </dl>
                    @else
                        <p class="text-muted mb-0">Le taux de présence est calculé à la fermeture de la classe.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        // Confirmation avant encaissement / suppression
        document.querySelectorAll('form[data-confirmer]').forEach(function (formulaire) {
            formulaire.addEventListener('submit', function (e) {
                if (!confirm(formulaire.dataset.confirmer)) e.preventDefault();
            });
        });
    </script>
@endsection