@extends('layouts.master')

@section('title', 'Attestations')

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

    {{-- ===== En-tête ===== --}}
    <p class="text-muted mb-4">
        Toutes les attestations délivrées depuis la mise en service de la plateforme, les plus récentes en premier.
    </p>

    {{-- ===== Statistique ===== --}}
    <div class="row g-2 mb-3">
        <div class="col-sm-6 col-xl-3">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center gap-2 py-2 px-3">
                    <span class="avatar-sm flex-shrink-0">
                        <span class="avatar-title rounded-3 bg-success-subtle text-success fs-4"><i class="ri-award-line"></i></span>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-0">{{ $totalAttestations }}</h5>
                        <p class="text-muted mb-0 fs-12">Attestations délivrées</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Recherche ===== --}}
    <div class="card border shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.attestations.index') }}" class="row g-2 align-items-center">
                <div class="col-lg-6">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent"><i class="ri-search-line text-primary"></i></span>
                        <input type="text" name="q" value="{{ request('q') }}" class="form-control"
                               placeholder="Rechercher (stagiaire, cours ou numéro)..." aria-label="Rechercher une attestation">
                    </div>
                </div>
                <div class="col-lg-6 d-flex gap-2 justify-content-lg-end">
                    <button type="submit" class="btn btn-primary"><i class="ri-filter-3-line me-1"></i> Rechercher</button>
                    <a href="{{ route('admin.attestations.index') }}" class="btn btn-outline-secondary">
                        <i class="ri-refresh-line me-1"></i> Réinitialiser
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- ===== Tableau ===== --}}
    <div class="card border shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle table-nowrap mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Délivrée le</th>
                        <th>N°</th>
                        <th>Stagiaire</th>
                        <th>Cours</th>
                        <th>Classe</th>
                        <th>Présence</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($attestations as $attestation)
                        <tr>
                            {{-- Date et heure de délivrance (= création de l'attestation) --}}
                            <td>{{ $attestation->created_at->format('d/m/Y H:i') }}</td>
                            <td class="text-muted">{{ $attestation->numero }}</td>
                            {{-- Nom figé sur le document (celui qui est imprimé sur le PDF) --}}
                            <td class="fw-medium">{{ $attestation->beneficiaire }}</td>
                            <td>{{ $attestation->intitule }}</td>
                            <td>
                                @if ($attestation->classe)
                                    {{ $attestation->classe->cours?->titre }} — {{ $attestation->classe->nom }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ rtrim(rtrim(number_format($attestation->taux_presence, 2, ',', ''), '0'), ',') }} %</td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    {{-- Voir dans un nouvel onglet --}}
                                    <a href="{{ route('attestations.voir', $attestation) }}" target="_blank" rel="noopener"
                                       class="btn btn-sm btn-soft-primary" title="Voir">
                                        <i class="ri-eye-line"></i>
                                    </a>
                                    {{-- Télécharger le PDF --}}
                                    <a href="{{ route('attestations.telecharger', $attestation) }}"
                                       class="btn btn-sm btn-soft-success" title="Télécharger">
                                        <i class="ri-download-2-line"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Aucune attestation trouvée.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination (15 par page) --}}
        @if ($attestations->hasPages())
            <div class="card-footer">{{ $attestations->links() }}</div>
        @endif
    </div>
</div>

@endsection