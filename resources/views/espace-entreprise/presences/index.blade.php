@extends('layouts.master')

@section('title', 'Présences')

@section('content')

@component('components.breadcrumb')
    @slot('li_1')
        <a href="{{ route('dashboard') }}">Accueil</a>
    @endslot
    @slot('title')
        Présences
    @endslot
@endcomponent

<div class="container-fluid pb-4">

    <p class="text-muted mb-3">
        Feuilles de présence des membres que <strong>{{ $entreprise->raison_sociale }}</strong> a inscrits.
        Une feuille devient consultable et téléchargeable en PDF dès que la classe est <strong>fermée</strong>.
    </p>

    @php
        // Les deux onglets, décrits une seule fois puis affichés en boucle
        $onglets = [
            ['id' => 'pr-fermees', 'titre' => 'Disponibles', 'icone' => 'ri-file-list-3-line', 'items' => $fermees, 'vide' => 'Aucune classe fermée pour le moment.'],
            ['id' => 'pr-attente', 'titre' => 'En attente de fermeture', 'icone' => 'ri-time-line', 'items' => $enAttente, 'vide' => 'Aucune classe en attente.'],
        ];
    @endphp

    {{-- Boutons des onglets (Bootstrap « nav-pills ») avec le nombre de classes --}}
    <div class="card border shadow-sm mb-3">
        <div class="card-body p-2">
            <ul class="nav nav-pills flex-nowrap overflow-auto gap-1" role="tablist">
                @foreach ($onglets as $o)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link text-nowrap {{ $loop->first ? 'active' : '' }}" type="button"
                                data-bs-toggle="tab" data-bs-target="#{{ $o['id'] }}" role="tab">
                            <i class="{{ $o['icone'] }} me-1"></i> {{ $o['titre'] }}
                            <span class="badge bg-danger ms-1">{{ $o['items']->count() }}</span>
                        </button>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    {{-- Contenu des onglets : une carte par classe --}}
    <div class="tab-content">
        @foreach ($onglets as $o)
            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="{{ $o['id'] }}" role="tabpanel">
                @forelse ($o['items'] as $classe)
                    <div class="card border shadow-sm mb-3">
                        <div class="card-body p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                            <div>
                                <h5 class="fw-bold mb-1">{{ $classe->cours->titre }}</h5>
                                <div class="text-muted small">
                                    <i class="ri-graduation-cap-line text-danger me-1"></i>Classe : {{ $classe->nom }}
                                    @if ($classe->session) · session {{ $classe->session->nom }} @endif
                                </div>
                                <div class="text-muted small">
                                    <i class="ri-calendar-event-line text-danger me-1"></i>
                                    Du {{ $classe->date_debut->format('d/m/Y') }} au {{ $classe->date_fin->format('d/m/Y') }}
                                    @if ($classe->formateur) · <i class="ri-user-star-line text-danger mx-1"></i>{{ $classe->formateur->name }} @endif
                                </div>
                                {{-- Nombre de membres inscrits PAR l'entreprise (withCount du contrôleur) --}}
                                <span class="badge bg-success-subtle text-success mt-1">
                                    <i class="ri-team-line me-1"></i>{{ $classe->inscrits_entreprise_count }} membre(s) inscrit(s)
                                </span>
                            </div>

                            <div class="d-flex flex-wrap gap-2">
                                @if ($classe->estFermee())
                                    <a href="{{ route('entreprise.presences.show', $classe) }}" class="btn btn-primary btn-sm">
                                        <i class="ri-eye-line me-1"></i> Voir les présences
                                    </a>
                                    <a href="{{ route('entreprise.presences.pdf', $classe) }}" class="btn btn-outline-danger btn-sm">
                                        <i class="ri-file-pdf-2-line me-1"></i> Télécharger le PDF
                                    </a>
                                @else
                                    {{-- La classe n'est pas fermée : rien n'est encore figé --}}
                                    <span class="badge bg-warning-subtle text-warning px-3 py-2">
                                        <i class="ri-lock-line me-1"></i> Disponible après la fermeture de la classe
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="card border shadow-sm"><div class="card-body text-center text-muted py-5">
                        <i class="{{ $o['icone'] }} display-5 d-block mb-2"></i>{{ $o['vide'] }}
                    </div></div>
                @endforelse
            </div>
        @endforeach
    </div>
</div>

@endsection