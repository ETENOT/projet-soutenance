@extends('layouts.master')

@section('title', $session->nom)

@section('content')

@component('components.breadcrumb')
    @slot('li_1')
        <a href="{{ route('entreprise.sessions.index') }}">Mes sessions</a>
    @endslot
    @slot('title')
        {{ $session->nom }}
    @endslot
@endcomponent

<div class="container-fluid pb-4">

    <div class="card border shadow-sm mb-4">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h4 class="fw-bold mb-1">{{ $session->nom }}</h4>
                @if ($session->description)
                    <p class="text-muted mb-1">{{ $session->description }}</p>
                @endif
                <div class="text-muted small">
                    <i class="ri-calendar-event-line text-danger me-1"></i>
                    @if ($session->date_debut)
                        Du {{ $session->date_debut->format('d/m/Y') }} au {{ $session->date_fin->format('d/m/Y') }}
                    @else
                        Dates à définir
                    @endif
                </div>
            </div>
            <span class="badge bg-{{ $session->etat_couleur }}-subtle text-{{ $session->etat_couleur }} px-3 py-2 fs-12">
                {{ $session->etat_libelle }}
            </span>
        </div>
    </div>

    {{-- Même rangement que « Mes classes » côté particulier : en cours / à venir / terminées --}}
    @php
        $groupes = [
            ['id' => 'cl-encours', 'titre' => 'En cours', 'icone' => 'ri-book-open-line', 'teinte' => 'primary', 'items' => $enCours, 'vide' => 'Aucune classe en cours.'],
            ['id' => 'cl-avenir', 'titre' => 'À venir', 'icone' => 'ri-time-line', 'teinte' => 'warning', 'items' => $aVenir, 'vide' => 'Aucune classe à venir.'],
            ['id' => 'cl-terminees', 'titre' => 'Terminées', 'icone' => 'ri-archive-line', 'teinte' => 'secondary', 'items' => $terminees, 'vide' => 'Aucune classe terminée.'],
        ];
    @endphp

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

    <div class="tab-content">
        @foreach ($groupes as $g)
            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="{{ $g['id'] }}" role="tabpanel">
                @forelse ($g['items'] as $classe)
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
                                        <h5 class="fw-bold mb-1">{{ $classe->cours->titre }}</h5>
                                        <div class="text-muted small">
                                            <i class="ri-graduation-cap-line text-danger me-1"></i>Classe : {{ $classe->nom }}
                                        </div>
                                        <div class="text-muted small">
                                            <i class="ri-calendar-event-line text-danger me-1"></i>
                                            Du {{ $classe->date_debut->format('d/m/Y') }} au {{ $classe->date_fin->format('d/m/Y') }}
                                        </div>
                                        <div class="text-muted small">
                                            <i class="ri-map-pin-line text-danger me-1"></i>{{ $classe->lieu }}
                                            @if ($classe->formateur)
                                                · <i class="ri-user-star-line text-danger mx-1"></i>{{ $classe->formateur->name }}
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex flex-md-column align-items-md-end gap-2">
                                    {{-- Uniquement les inscriptions faites via l'entreprise --}}
                                    <span class="badge bg-success-subtle text-success px-3 py-2">
                                        <i class="ri-team-line me-1"></i>{{ $classe->inscrits_entreprise_count }} membre(s) inscrit(s)
                                    </span>
                                    <a href="{{ route('entreprise.sessions.classes.show', [$session, $classe]) }}"
                                       class="btn btn-primary btn-sm text-nowrap">
                                        Voir les inscrits <i class="ri-arrow-right-line ms-1"></i>
                                    </a>
                                </div>
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