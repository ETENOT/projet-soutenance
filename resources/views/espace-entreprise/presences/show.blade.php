@extends('layouts.master')

@section('title', 'Présences — ' . $classe->cours->titre . ' — ' . $classe->nom)

@section('css')
<style>
    /* Grille des présences : la colonne « Membre » reste visible quand on fait défiler les jours */
    .nv-grille-presence th.nv-col-membre,
    .nv-grille-presence td.nv-col-membre { position: sticky; left: 0; z-index: 2; background: var(--vz-card-bg); min-width: 200px; }
    /* En-têtes de jours étroits (« lun. » / « 03/11 » sur deux lignes) */
    .nv-grille-presence th.nv-jour { min-width: 46px; font-size: 11px; line-height: 1.2; }
    .nv-grille-presence td.nv-case { width: 46px; padding: .35rem .25rem; }
</style>
@endsection

@section('content')

@component('components.breadcrumb')
    @slot('li_1')
        <a href="{{ route('entreprise.presences.index') }}">Présences</a>
    @endslot
    @slot('title')
        {{ $classe->nom }}
    @endslot
@endcomponent

<div class="container-fluid pb-4">

    {{-- En-tête de la classe + bouton PDF --}}
    <div class="card border shadow-sm mb-4">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h4 class="fw-bold mb-1">{{ $classe->cours->titre }}</h4>
                <div class="text-muted small"><i class="ri-graduation-cap-line text-danger me-1"></i>Classe : {{ $classe->nom }}
                    @if ($classe->session) · session {{ $classe->session->nom }} @endif
                </div>
                <div class="text-muted small">
                    {{-- $nbJoursComptes = jours de cours moins les jours non comptabilisés (dénominateur du taux) --}}
                    <i class="ri-calendar-event-line text-danger me-1"></i>
                    Du {{ $classe->date_debut->format('d/m/Y') }} au {{ $classe->date_fin->format('d/m/Y') }}
                    · {{ $nbJoursComptes }} jour(s) comptabilisé(s)
                </div>
                <div class="text-muted small">
                    <i class="ri-lock-line text-danger me-1"></i>Classe fermée le {{ $classe->fermee_le->format('d/m/Y') }}
                    @if ($classe->formateur) · <i class="ri-user-star-line text-danger mx-1"></i>{{ $classe->formateur->name }} @endif
                </div>
            </div>
            <a href="{{ route('entreprise.presences.pdf', $classe) }}" class="btn btn-danger">
                <i class="ri-file-pdf-2-line me-1"></i> Télécharger le PDF
            </a>
        </div>
    </div>

    {{-- Récapitulatif par membre --}}
    <div class="card border shadow-sm mb-4">
        <div class="card-header"><h5 class="card-title mb-0">Récapitulatif ({{ $lignes->count() }} membre(s))</h5></div>
        <div class="table-responsive">
            <table class="table table-hover align-middle table-nowrap mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Membre</th>
                        <th class="text-center">Jours présents</th>
                        <th class="text-center">Jours absents</th>
                        <th class="text-center">Taux de présence</th>
                        <th>Attestation (seuil {{ $seuil }} %)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($lignes as $ligne)
                        <tr>
                            <td>
                                <div class="fw-medium">{{ $ligne['membre']->name }}</div>
                                <div class="text-muted small">{{ $ligne['membre']->email }}</div>
                            </td>
                            <td class="text-center text-success fw-semibold">{{ $ligne['presents'] }}</td>
                            <td class="text-center text-danger fw-semibold">{{ $ligne['absents'] }}</td>
                            {{-- Taux figé à la fermeture (90.91 -> « 90,91 % », 50.00 -> « 50 % ») --}}
                            <td class="text-center">{{ rtrim(rtrim(number_format($ligne['taux'], 2, ',', ''), '0'), ',') }} %</td>
                            <td>
                                @if ($ligne['attestation'])
                                    <span class="badge bg-success-subtle text-success">Délivrée</span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning">Non délivrée</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">Aucun membre inscrit par votre entreprise.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Grille jour par jour --}}
    <div class="card border shadow-sm">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="card-title mb-0">Présences jour par jour</h5>
            {{-- Légende --}}
            <div class="d-flex flex-wrap gap-2 small">
                <span><span class="badge bg-success">P</span> Présent</span>
                <span><span class="badge bg-danger">A</span> Absent</span>
                <span><span class="badge bg-light text-muted border">—</span> Non comptabilisé</span>
            </div>
        </div>
        {{-- table-responsive : défilement horizontal quand la classe dure longtemps --}}
        <div class="table-responsive">
            <table class="table table-bordered align-middle text-center mb-0 nv-grille-presence">
                <thead class="table-light">
                    <tr>
                        <th class="text-start nv-col-membre">Membre</th>
                        @foreach ($jours as $jour)
                            {{-- ex. « lun. » puis « 03/11 » (translatedFormat suit la langue de l'application) --}}
                            <th class="nv-jour">{{ $jour->translatedFormat('D') }}<br>{{ $jour->format('d/m') }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($lignes as $ligne)
                        <tr>
                            <td class="text-start fw-medium nv-col-membre">{{ $ligne['membre']->name }}</td>
                            {{-- $ligne['statuts'] : ['2026-11-03' => 'present'|'absent'|'exclu', ...], dans l'ordre des jours --}}
                            @foreach ($ligne['statuts'] as $date => $statut)
                                <td class="nv-case" title="{{ \Illuminate\Support\Carbon::parse($date)->format('d/m/Y') }}">
                                    @if ($statut === 'present')
                                        <span class="badge bg-success">P</span>
                                    @elseif ($statut === 'absent')
                                        <span class="badge bg-danger">A</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection