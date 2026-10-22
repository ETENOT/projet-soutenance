@extends('layouts.master')

@section('title', 'Avis sur moi')

@section('content')

@component('components.breadcrumb')
    @slot('li_1')
        <a href="{{ route('dashboard') }}">Espace formateur</a>
    @endslot
    @slot('title')
        Avis sur moi
    @endslot
@endcomponent

<div class="container-fluid pb-4">

    <form method="GET" action="{{ route('avis_formateurs.formateur.index') }}" class="mb-3">
        <div class="input-group">
            <label class="input-group-text" for="filtre-classe-avis">
                <i class="ri-filter-3-line me-1"></i>Classe
            </label>
            <select id="filtre-classe-avis" name="classe" class="form-select">
                <option value="">Tous mes avis ({{ $nombreAvisTotal }})</option>
                @foreach ($mesClasses as $classe)
                    <option value="{{ $classe->id }}" @selected($classeFiltre?->id === $classe->id)>
                        {{ $classe->cours->titre }} — {{ $classe->nom }} ({{ $classe->avis_formateurs_count }} avis)
                    </option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary">
                <i class="ri-search-line me-1"></i>Filtrer
            </button>
            @if ($classeFiltre)
                <a href="{{ route('avis_formateurs.formateur.index') }}" class="btn btn-light">Effacer</a>
            @endif
        </div>
    </form>

    <div class="row g-4">

        {{-- ===== Note moyenne + détail par critère ===== --}}
        <div class="col-lg-4">
            <div class="card border shadow-sm mb-0">
                <div class="card-body p-4">
                    <div class="text-center">
                        <div class="display-5 fw-bold">
                            {{ $moyennes ? number_format($moyennes['globale'], 1, ',', '') : '—' }}<small class="fs-5 fw-normal text-muted"> / 5</small>
                        </div>

                        @include('cours.partials.etoiles', ['note' => $moyennes['globale'] ?? 0, 'taille' => 'fs-4'])

                        <p class="text-muted mt-1 mb-3">
                            {{ $avis->count() }} {{ $avis->count() === 1 ? 'avis affiché' : 'avis affichés' }}
                        </p>
                    </div>

                    @if ($moyennes)
                        @include('evaluations.partials.lignes-notes', [
                            'criteres' => \App\Models\AvisFormateur::CRITERES,
                            'source' => $moyennes,
                        ])
                    @endif
                </div>
            </div>
        </div>

        {{-- ===== Liste des avis (sans le nom des stagiaires) ===== --}}
        <div class="col-lg-8">
            <div class="card border shadow-sm mb-0">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">
                        <i class="ri-chat-quote-line text-danger me-2"></i>Ce que disent les stagiaires
                    </h5>

                    @forelse ($avis as $unAvis)
                        <div class="border rounded-3 p-3 mb-3">
                            <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                                <div class="text-muted small">
                                    {{ $unAvis->classe->cours->titre }} · {{ $unAvis->classe->nom }}
                                </div>
                                <small class="text-muted text-nowrap">{{ $unAvis->created_at->format('d/m/Y') }}</small>
                            </div>

                            @include('evaluations.partials.lignes-notes', [
                                'criteres' => \App\Models\AvisFormateur::CRITERES,
                                'source' => $unAvis,
                            ])

                            @if ($unAvis->commentaire)
                                <p class="text-muted mt-2 mb-0">{!! nl2br(e($unAvis->commentaire)) !!}</p>
                            @endif
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">
                            <i class="ri-star-line fs-1 d-block mb-2"></i>
                            Aucun avis pour le moment. Les stagiaires peuvent le donner à la fin de la formation.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@endsection