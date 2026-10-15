@extends('layouts.master')

@section('title', 'Fiche d\'évaluation')

@section('content')

@component('components.breadcrumb')
    @slot('li_1')
        <a href="{{ route('admin.evaluations.index', ['onglet' => 'formations']) }}">Évaluations</a>
    @endslot
    @slot('title')
        Fiche de {{ $evaluation->user?->name ?? 'Utilisateur supprimé' }}
    @endslot
@endcomponent

<div class="container-fluid pb-4">
    <div class="row">

        {{-- ===== En-tête ===== --}}
        <div class="col-lg-4">
            <div class="card border shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">En-tête de la fiche</h5>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>Intitulé :</strong> {{ $evaluation->classe->cours->titre }}</p>
                    <p class="mb-1"><strong>Classe :</strong> {{ $evaluation->classe->nom }}</p>
                    <p class="mb-1"><strong>Formateur :</strong> {{ $evaluation->classe->formateur?->name ?? '—' }}</p>
                    <p class="mb-1">
                        <strong>Dates :</strong>
                        du {{ $evaluation->classe->date_debut->format('d/m/Y') }} au {{ $evaluation->classe->date_fin->format('d/m/Y') }}
                    </p>
                    <hr>
                    <p class="mb-1"><strong>Stagiaire :</strong> {{ $evaluation->user?->name ?? 'Utilisateur supprimé' }}</p>
                    <p class="mb-1"><strong>Mail :</strong> {{ $evaluation->user?->email ?? '—' }}</p>
                    <p class="mb-0"><strong>Reçue le :</strong> {{ $evaluation->created_at->format('d/m/Y à H:i') }}</p>
                </div>
            </div>
            <a href="{{ route('admin.evaluations.index', ['classe' => $evaluation->classe_id]) }}" class="btn btn-light w-100 mb-3">
                <i class="ri-arrow-left-line me-1"></i>Retour aux évaluations
            </a>
        </div>

        <div class="col-lg-8">

            {{-- ===== Avis sur le formateur (même stagiaire, même classe) ===== --}}
            @if ($avisFormateur)
                <div class="card border shadow-sm mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Le formateur</h5>
                    </div>
                    <div class="card-body">
                        @include('evaluations.partials.lignes-notes', [
                            'criteres' => \App\Models\AvisFormateur::CRITERES,
                            'source' => $avisFormateur,
                        ])
                        @if ($avisFormateur->commentaire)
                            <p class="text-muted mt-2 mb-0">{!! nl2br(e($avisFormateur->commentaire)) !!}</p>
                        @endif
                    </div>
                </div>
            @endif

            {{-- ===== Environnement + La formation ===== --}}
            @foreach (\App\Models\EvaluationFormation::CRITERES as $section => $criteres)
                <div class="card border shadow-sm mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ $section }}</h5>
                    </div>
                    <div class="card-body">
                        @include('evaluations.partials.lignes-notes', ['criteres' => $criteres, 'source' => $evaluation])
                    </div>
                </div>
            @endforeach

            {{-- ===== Verso : remarques libres ===== --}}
            <div class="card border shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">Remarques</h5>
                </div>
                <div class="card-body">
                    @foreach (\App\Models\EvaluationFormation::TEXTES as $champ => $titre)
                        <div class="mb-2">
                            <div class="fw-medium">{{ $titre }}</div>
                            @if ($evaluation->{$champ})
                                <p class="text-muted mb-0 text-break">{!! nl2br(e($evaluation->{$champ})) !!}</p>
                            @else
                                <p class="text-muted fst-italic mb-0">Non renseigné</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

@endsection