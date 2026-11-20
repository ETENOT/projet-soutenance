@extends('layouts.master')

@section('title', 'Attestations')

{{--
    Attestations reçues par les membres de l'entreprise, uniquement pour les classes où l'entreprise
    les a inscrits (voir EntrepriseAttestationController). De la plus récente à la plus ancienne.
    Variables : $entreprise, $attestations (paginées), $totalAttestations
--}}

@section('content')

@component('components.breadcrumb')
    @slot('li_1')
        <a href="{{ route('dashboard') }}">Accueil</a>
    @endslot
    @slot('title')
        Attestations
    @endslot
@endcomponent

<div class="container-fluid pb-4">

    <p class="text-muted mb-3">
        Attestations de formation reçues par les membres que <strong>{{ $entreprise->raison_sociale }}</strong> a inscrits.
        Une attestation est délivrée à la fermeture de la classe, aux membres ayant atteint le taux de présence requis.
    </p>

    {{-- ===== Statistique + recherche ===== --}}
    <div class="row g-2 align-items-start mb-3">
        <div class="col-sm-6 col-xl-3">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center gap-2 py-2 px-3">
                    <span class="avatar-sm flex-shrink-0">
                        <span class="avatar-title rounded-3 bg-success-subtle text-success fs-4"><i class="ri-award-line"></i></span>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-0">{{ $totalAttestations }}</h5>
                        <p class="text-muted mb-0 fs-12">Attestations reçues</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6 col-xl-5 ms-auto">
            <form method="GET" action="{{ route('entreprise.attestations.index') }}">
                <div class="d-flex gap-2">
                    <div class="form-control d-flex align-items-center gap-1 py-1 shadow-sm flex-grow-1">
                        <i class="ri-search-line text-primary me-1"></i>
                        <input type="text" name="q" value="{{ request('q') }}"
                               class="form-control border-0 shadow-none p-1"
                               placeholder="Rechercher (membre, cours ou numéro)…" aria-label="Rechercher une attestation">
                    </div>
                    @if (request()->filled('q'))
                        <a href="{{ route('entreprise.attestations.index') }}" class="btn btn-light border shadow-sm" title="Effacer la recherche">
                            <i class="ri-close-line"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- ===== Tableau : les plus récentes en premier ===== --}}
    <div class="card border shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0 table-liste">
                <thead>
                    <tr class="table-light">
                        <th style="width: 10rem;">Délivrée le</th>
                        <th style="width: 10rem;">N°</th>
                        <th>Membre</th>
                        <th>Cours</th>
                        <th>Classe</th>
                        <th>Session</th>
                        <th class="col-nombre">Présence</th>
                        <th class="text-end" style="width: 6.5rem;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($attestations as $attestation)
                        <tr>
                            <td>{{ $attestation->created_at->format('d/m/Y H:i') }}</td>
                            <td class="text-muted" title="{{ $attestation->numero }}">{{ $attestation->numero }}</td>
                            {{-- Nom figé sur le document (celui imprimé sur le PDF) --}}
                            <td class="fw-medium" title="{{ $attestation->beneficiaire }}">{{ $attestation->beneficiaire }}</td>
                            <td title="{{ $attestation->intitule }}">{{ $attestation->intitule }}</td>
                            <td class="text-muted">
                                {{ $attestation->classe?->nom ?? '—' }}
                                <small class="d-block">
                                    Du {{ $attestation->date_debut?->format('d/m/Y') }} au {{ $attestation->date_fin?->format('d/m/Y') }}
                                </small>
                            </td>
                            <td class="text-muted">{{ $attestation->classe?->session?->nom ?? '—' }}</td>
                            <td>{{ rtrim(rtrim(number_format($attestation->taux_presence, 2, ',', ''), '0'), ',') }} %</td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    {{-- Voir dans un nouvel onglet --}}
                                    <a href="{{ route('entreprise.attestations.voir', $attestation) }}" target="_blank" rel="noopener"
                                       class="btn btn-sm btn-soft-primary" title="Voir l’attestation">
                                        <i class="ri-eye-line"></i>
                                    </a>
                                    {{-- Télécharger le PDF --}}
                                    <a href="{{ route('entreprise.attestations.telecharger', $attestation) }}"
                                       class="btn btn-sm btn-soft-success" title="Télécharger l’attestation">
                                        <i class="ri-download-2-line"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="ri-award-line display-5 d-block mb-2"></i>
                                @if (request()->filled('q'))
                                    Aucune attestation ne correspond à votre recherche.
                                    <a href="{{ route('entreprise.attestations.index') }}" class="d-block mt-2">Effacer la recherche</a>
                                @else
                                    Aucune attestation pour le moment : elles apparaîtront ici à la fermeture des classes.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($attestations->total() > 0)
            <div class="card-footer bg-transparent d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <small class="text-muted">
                    Affichage de {{ $attestations->firstItem() }} à {{ $attestations->lastItem() }}
                    sur {{ $attestations->total() }} attestation(s)
                </small>
                {{ $attestations->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
