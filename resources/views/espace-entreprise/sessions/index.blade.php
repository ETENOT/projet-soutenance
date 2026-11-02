@extends('layouts.master')

@section('title', 'Mes sessions')

@section('content')

@component('components.breadcrumb')
    @slot('li_1')
        <a href="{{ route('dashboard') }}">Accueil</a>
    @endslot
    @slot('title')
        Mes sessions
    @endslot
@endcomponent

<div class="container-fluid pb-4">

    {{-- Bandeau d'introduction --}}
    <div class="bg-primary bg-gradient text-white rounded-4 p-4 p-md-5 mb-4">
        <span class="badge rounded-pill bg-white bg-opacity-25 text-white px-3 py-2 mb-3">
            <i class="ri-calendar-schedule-line me-1"></i> Formations sur mesure
        </span>
        <h3 class="fw-bold text-white mb-2">Mes sessions</h3>
        <p class="text-white opacity-75 mb-0 col-lg-8 px-0">
            Les sessions préparées par l'administrateur pour {{ $entreprise->raison_sociale }} : chaque session regroupe
            les classes suivies par vos membres.
        </p>
    </div>

    {{-- Les trois groupes, définis une seule fois pour les onglets et leur contenu --}}
    {{-- $enCours, $aVenir, $terminees : envoyés par EntrepriseSessionController::index() --}}
    @php
        $groupes = [
            ['id' => 'sess-encours', 'titre' => 'En cours', 'icone' => 'ri-book-open-line', 'teinte' => 'primary', 'items' => $enCours, 'vide' => 'Aucune session en cours.'],
            ['id' => 'sess-avenir', 'titre' => 'À venir', 'icone' => 'ri-time-line', 'teinte' => 'warning', 'items' => $aVenir, 'vide' => 'Aucune session à venir.'],
            ['id' => 'sess-terminees', 'titre' => 'Terminées', 'icone' => 'ri-archive-line', 'teinte' => 'secondary', 'items' => $terminees, 'vide' => 'Aucune session terminée.'],
        ];
    @endphp

    {{-- Boutons des onglets, avec le nombre de sessions de chaque groupe --}}
    <div class="card border shadow-sm mb-3">
        <div class="card-body p-2">
            <ul class="nav nav-pills flex-nowrap overflow-auto gap-1" role="tablist">
                @foreach ($groupes as $g)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link text-nowrap {{ $loop->first ? 'active' : '' }}" type="button"
                                data-bs-toggle="tab" data-bs-target="#{{ $g['id'] }}" role="tab">
                            <i class="{{ $g['icone'] }} me-1"></i> {{ $g['titre'] }}
                            <span class="badge bg-danger ms-1">{{ $g['items']->count() }}</span>
                        </button>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    {{-- Contenu des onglets : une carte par session --}}
    <div class="tab-content">
        @foreach ($groupes as $g)
            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="{{ $g['id'] }}" role="tabpanel">
                @forelse ($g['items'] as $session)
                    <div class="card border shadow-sm mb-3">
                        <div class="card-body p-4">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="avatar-md flex-shrink-0">
                                        <span class="avatar-title rounded-3 bg-{{ $g['teinte'] }}-subtle text-{{ $g['teinte'] }} fs-2">
                                            <i class="{{ $g['icone'] }}"></i>
                                        </span>
                                    </span>
                                    <div>
                                        <h5 class="fw-bold mb-1">{{ $session->nom }}</h5>
                                        <div class="text-muted small">
                                            {{-- Dates calculées automatiquement : début de la 1re classe → fin de la dernière --}}
                                            <i class="ri-calendar-event-line text-danger me-1"></i>
                                            @if ($session->date_debut)
                                                Du {{ $session->date_debut->format('d/m/Y') }} au {{ $session->date_fin->format('d/m/Y') }}
                                            @else
                                                Dates à définir (aucune classe pour l'instant)
                                            @endif
                                        </div>
                                        <div class="text-muted small">
                                            {{-- $inscrits[id] = nombre de membres inscrits VIA l'entreprise dans cette session --}}
                                            <i class="ri-graduation-cap-line text-danger me-1"></i>
                                            {{ $session->classes_count }} classe(s) ·
                                            <i class="ri-team-line text-danger mx-1"></i>
                                            {{ $inscrits[$session->id] ?? 0 }} membre(s) inscrit(s)
                                        </div>
                                    </div>
                                </div>

                                <a href="{{ route('entreprise.sessions.show', $session) }}" class="btn btn-primary btn-sm text-nowrap">
                                    Voir la session <i class="ri-arrow-right-line ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="card border shadow-sm"><div class="card-body text-center text-muted py-5">
                        <i class="{{ $g['icone'] }} display-5 d-block mb-2"></i>{{ $g['vide'] }}
                    </div></div>
                @endforelse
            </div>
        @endforeach
    </div>
</div>

@endsection