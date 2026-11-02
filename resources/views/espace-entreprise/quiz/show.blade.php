@extends('layouts.master')

@section('title', 'Quiz de ' . $membre->name)

@section('content')

@component('components.breadcrumb')
    @slot('li_1')
        <a href="{{ route('entreprise.quiz.index') }}">Suivi des quiz</a>
    @endslot
    @slot('title')
        {{ $membre->name }}
    @endslot
@endcomponent

@php
    // Formatage français d'un pourcentage : 12.5 -> « 12,5 % »
    $pct = fn ($v) => $v === null ? '—' : rtrim(rtrim(number_format($v, 1, ',', ''), '0'), ',') . ' %';
@endphp

<div class="container-fluid pb-4">

    {{-- En-tête : le membre et la classe suivie --}}
    <div class="card border shadow-sm mb-4">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h4 class="fw-bold mb-1">{{ $membre->name }}</h4>
                <div class="text-muted small"><i class="ri-mail-line text-danger me-1"></i>{{ $membre->email }}</div>
                <div class="text-muted small">
                    <i class="ri-book-open-line text-danger me-1"></i>{{ $classe->cours->titre }} — classe {{ $classe->nom }}
                    @if ($classe->session) · session {{ $classe->session->nom }} @endif
                </div>
                <div class="text-muted small">
                    {{-- Rappel de la règle : seuls les quiz passés pendant la classe sont comptés --}}
                    <i class="ri-calendar-event-line text-danger me-1"></i>
                    Quiz pris en compte du {{ $classe->date_debut->format('d/m/Y') }} au {{ $classe->date_fin->format('d/m/Y') }}
                </div>
            </div>
            <span class="badge bg-{{ $classe->etat_couleur }}-subtle text-{{ $classe->etat_couleur }} px-3 py-2 fs-12">
                {{ $classe->etat_libelle }}
            </span>
        </div>
    </div>

    {{-- Chiffres clés : 4 cartes construites à partir d'un tableau pour éviter de répéter le HTML --}}
    <div class="row g-3 mb-4">
        @foreach ([
            ['libelle' => 'Quiz passés', 'valeur' => $stats['nombre'], 'icone' => 'ri-questionnaire-line', 'teinte' => 'primary'],
            ['libelle' => 'Dernière note', 'valeur' => $stats['derniere_note'] ?? '—', 'icone' => 'ri-time-line', 'teinte' => 'info'],
            ['libelle' => 'Meilleur score', 'valeur' => $pct($stats['meilleure_pct']), 'icone' => 'ri-trophy-line', 'teinte' => 'success'],
            ['libelle' => 'Moyenne', 'valeur' => $pct($stats['moyenne_pct']), 'icone' => 'ri-bar-chart-line', 'teinte' => 'warning'],
        ] as $carte)
            <div class="col-sm-6 col-xl-3">
                <div class="card border shadow-sm h-100 mb-0">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="avatar-sm flex-shrink-0">
                            <span class="avatar-title rounded-3 bg-{{ $carte['teinte'] }}-subtle text-{{ $carte['teinte'] }} fs-3">
                                <i class="{{ $carte['icone'] }}"></i>
                            </span>
                        </span>
                        <div>
                            <div class="text-muted small">{{ $carte['libelle'] }}</div>
                            <div class="fs-5 fw-semibold">{{ $carte['valeur'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Courbe d'évolution : même graphique que celui du particulier, avec des textes adaptés à l'entreprise --}}
    <div class="card border shadow-sm mb-4">
        <div class="card-body">
            @include('quiz.partials.progression', [
                'progression' => $progression,
                'titreProgression' => 'Évolution des résultats',
                'messageUnPoint' => 'Un seul quiz passé pour l’instant : la courbe apparaîtra à partir de deux quiz.',
                'messageVide' => 'Ce membre n’a encore terminé aucun quiz de ce cours pendant la classe.',
            ])
        </div>
    </div>

    {{-- Détail des tentatives, de la plus récente à la plus ancienne --}}
    <div class="card border shadow-sm">
        <div class="card-header"><h5 class="card-title mb-0">Quiz passés ({{ $tentatives->count() }})</h5></div>
        <div class="table-responsive">
            <table class="table table-hover align-middle table-nowrap mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Heure</th>
                        <th>Note</th>
                        <th>Score</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tentatives as $tentative)
                        @php
                            // Une tentative terminée a un résultat (score) ; le barème est stocké sur le quiz
                            $score = (int) ($tentative->resultats->first()?->score ?? 0);
                            $bareme = (int) $tentative->bareme;
                            $pourcentage = $bareme > 0 ? round($score / $bareme * 100, 1) : 0;
                        @endphp
                        <tr>
                            <td>{{ \Illuminate\Support\Carbon::parse($tentative->date)->format('d/m/Y') }}</td>
                            {{-- heure_debut est « HH:MM:SS » : on garde « HH:MM » --}}
                            <td>{{ \Illuminate\Support\Str::substr($tentative->heure_debut, 0, 5) }}</td>
                            {{-- Rouge sous la moyenne, vert sinon --}}
                            <td class="fw-semibold {{ $pourcentage < 50 ? 'text-danger' : 'text-success' }}">{{ $score }} / {{ $bareme }}</td>
                            <td style="min-width: 160px;">
                                {{-- Petite barre de progression proportionnelle au score --}}
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height: 6px;">
                                        <div class="progress-bar {{ $pourcentage < 50 ? 'bg-danger' : 'bg-success' }}" style="width: {{ $pourcentage }}%"></div>
                                    </div>
                                    <span class="small">{{ $pct($pourcentage) }}</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">Aucun quiz terminé pendant la classe.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@section('script')
    {{-- Script ApexCharts qui dessine la courbe (partagé avec la page du particulier) --}}
    @include('quiz.partials.progression-script')
@endsection