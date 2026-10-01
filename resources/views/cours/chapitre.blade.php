@extends(Auth::check() ? 'layouts.master' : 'layouts.master-without-nav')

@section('title', $chapitre->titre)

@section('content')

<style>

    .list-group-item.active {
        background-color: #eef6ff;
        border-color: #d7e8ff;
        color: #0f47ad;
        font-weight: 600;
    }

</style>

@auth

@component('components.breadcrumb')

    @slot('li_1')
        {{ $cours->titre }}
    @endslot

    @slot('title')
        {{ $chapitre->titre }}
    @endslot

@endcomponent

@endauth

<div class="container-fluid">

    <div class="row g-4">


        {{-- =================================================
             COLONNE GAUCHE — panneau unique : chapitres / ressources / quiz
        ================================================== --}}
        <div class="col-lg-3">

            <div class="card shadow-sm">

                {{-- Contenu du chapitre (liste des chapitres du cours) --}}
                <div class="card-header bg-white">
                    <strong>Contenu du chapitre</strong>
                </div>

                <div class="list-group list-group-flush">

                    @foreach($cours->chapitres as $item)

                        <a href="{{ route('cours.chapitre', [$cours, $item]) }}"
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center
                           {{ $item->id == $chapitre->id ? 'active' : '' }}">

                            <span>
                                <small>{{ $item->ordre }}.</small>
                                {{ $item->titre }}
                            </span>

                            <i class="bi bi-chevron-right small"></i>

                        </a>

                    @endforeach

                </div>


                {{-- Ressources --}}
                <div class="card-header bg-white border-top">
                    <strong>
                        <i class="bi bi-folder me-1"></i>
                        Ressources
                    </strong>
                </div>

                <div class="list-group list-group-flush">

                    @forelse($chapitre->resources->where('type', '!=', 'video') as $resource)

                        <a href="{{ route('cours.resources.voir', [$cours, $resource]) }}"
                           class="list-group-item list-group-item-action">

                            <i class="bi bi-file-earmark-pdf text-danger me-2"></i>
                            {{ $resource->titre }}

                        </a>

                    @empty

                        <div class="list-group-item text-muted small">
                            Aucune ressource
                        </div>

                    @endforelse

                    @foreach($chapitre->resources->where('type', 'video') as $resource)

                        <a href="{{ route('cours.resources.voir', [$cours, $resource]) }}"
                           class="list-group-item list-group-item-action">

                            <i class="bi bi-play-circle text-primary me-2"></i>
                            {{ $resource->titre }}

                        </a>

                    @endforeach

                </div>


                {{-- Quiz : décoratif pour l'instant, pas de quiz par chapitre
                     (le quiz du cours reste unique et non obligatoire) --}}
                <div class="card-header bg-white border-top">
                    <strong>
                        <i class="bi bi-question-circle me-1"></i>
                        Quiz
                    </strong>
                </div>

                <div class="list-group-item d-flex justify-content-between align-items-center text-muted">
                    Quiz du chapitre
                    <i class="bi bi-lock small"></i>
                </div>

            </div>

        </div>


        {{-- =================================================
             COLONNE DROITE — vidéo + à retenir
        ================================================== --}}
        <div class="col-lg-9">

            <div class="card shadow-sm mb-4">

                <div class="card-body p-4">

                    <h5 class="fw-bold mb-3">
                        {{ $chapitre->titre }}
                    </h5>


                    @php
                        $video = $chapitre->resources->firstWhere('type', 'video');
                    @endphp

                    @if($video)

                        <a href="{{ route('cours.resources.voir', [$cours, $video]) }}"
                           target="_blank"
                           class="d-flex align-items-center justify-content-center rounded mb-4 text-decoration-none"
                           style="background:#0b1437; aspect-ratio:16/9;">

                            <i class="bi bi-play-circle-fill text-white" style="font-size:3.5rem;"></i>

                        </a>

                    @endif


                    {{-- À retenir : chaque ligne du contenu du chapitre devient une puce --}}
                    <div class="p-3 rounded" style="background:#eef6ff;">

                        <div class="fw-bold text-primary mb-2">
                            <i class="bi bi-lightbulb me-1"></i>
                            À retenir
                        </div>

                        <ul class="mb-0">

                            @foreach(explode("\n", trim($chapitre->contenu ?? '')) as $ligne)

                                @continue(trim($ligne) === '')

                                <li>{{ trim($ligne) }}</li>

                            @endforeach

                        </ul>

                    </div>

                </div>

            </div>


            {{-- Navigation --}}
            <div class="d-flex justify-content-between">

                @php

                $chapitrePrecedent = $cours->chapitres
                    ->where('ordre', '<', $chapitre->ordre)
                    ->sortByDesc('ordre')
                    ->first();


                $chapitreSuivant = $cours->chapitres
                    ->where('ordre', '>', $chapitre->ordre)
                    ->sortBy('ordre')
                    ->first();

                @endphp


                @if($chapitrePrecedent)

                    <a href="{{ route('cours.chapitre', [$cours, $chapitrePrecedent]) }}"
                       class="btn btn-outline-secondary">

                        <i class="bi bi-arrow-left"></i>
                        Chapitre précédent

                    </a>

                @else

                    <span></span>

                @endif


                @if($chapitreSuivant)

                    <a href="{{ route('cours.chapitre', [$cours, $chapitreSuivant]) }}"
                       class="btn btn-primary">

                        Chapitre suivant
                        <i class="bi bi-arrow-right"></i>

                    </a>

                @endif

            </div>

        </div>

    </div>

</div>

@endsection