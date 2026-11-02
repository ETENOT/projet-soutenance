@extends('layouts.master')

@section('title', $classe->cours->titre . ' — ' . $classe->nom)

@section('content')

@component('components.breadcrumb')
    @slot('li_1')
        <a href="{{ route('entreprise.sessions.show', $session) }}">{{ $session->nom }}</a>
    @endslot
    @slot('title')
        {{ $classe->nom }}
    @endslot
@endcomponent

<div class="container-fluid pb-4">

    <div class="card border shadow-sm mb-4">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h4 class="fw-bold mb-1">{{ $classe->cours->titre }}</h4>
                <div class="text-muted small"><i class="ri-graduation-cap-line text-danger me-1"></i>Classe : {{ $classe->nom }}</div>
                <div class="text-muted small">
                    <i class="ri-calendar-event-line text-danger me-1"></i>
                    Du {{ $classe->date_debut->format('d/m/Y') }} au {{ $classe->date_fin->format('d/m/Y') }}
                </div>
                <div class="text-muted small">
                    <i class="ri-map-pin-line text-danger me-1"></i>{{ $classe->lieu }}
                    @if ($classe->formateur) · <i class="ri-user-star-line text-danger mx-1"></i>{{ $classe->formateur->name }} @endif
                </div>
            </div>
            <span class="badge bg-{{ $classe->etat_couleur }}-subtle text-{{ $classe->etat_couleur }} px-3 py-2 fs-12">
                {{ $classe->etat_libelle }}
            </span>
        </div>
    </div>

    {{-- Membres inscrits PAR l'entreprise (pas ceux qui se sont inscrits eux-mêmes) --}}
    <div class="card border shadow-sm">
        <div class="card-header"><h5 class="card-title mb-0">Membres inscrits par {{ $entreprise->raison_sociale }} ({{ $inscriptions->count() }})</h5></div>
        <div class="table-responsive">
            <table class="table table-hover align-middle table-nowrap mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nom</th>
                        <th>E-mail</th>
                        <th>Inscrit le</th>
                        <th>Présence / attestation</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($inscriptions as $inscription)
                        <tr>
                            <td class="fw-medium">
                                {{ $inscription->user->name }}
                                {{-- Le particulier a pu quitter l'entreprise depuis son inscription --}}
                                @if ((int) $inscription->user->particulier?->entreprise_id !== (int) $entreprise->id)
                                    <span class="badge bg-secondary-subtle text-secondary ms-1">N'est plus membre</span>
                                @endif
                            </td>
                            {{-- E-mail du membre + lien vers l'évolution de ses quiz dans cette classe --}}
                            <td>
                                {{ $inscription->user->email }}
                                <a href="{{ route('entreprise.quiz.show', $inscription) }}" class="ms-2 small" title="Évolution des quiz de ce membre">
                                    <i class="ri-line-chart-line"></i> Quiz
                                </a>
                            </td>
                            <td>{{ \Illuminate\Support\Carbon::parse($inscription->date_inscription)->format('d/m/Y') }}</td>
                            <td>
                                {{-- Taux et attestation figés à la fermeture de la classe --}}
                                @if ($classe->estFermee())
                                    {{ rtrim(rtrim(number_format($inscription->taux_presence ?? 0, 2, ',', ''), '0'), ',') }} % de présence
                                    @if ($inscription->attestation_eligible)
                                        <span class="badge bg-success-subtle text-success ms-1">Attestation délivrée</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning ms-1">Attestation non délivrée</span>
                                    @endif
                                @else
                                    <span class="text-muted">Après clôture de la classe</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-5">
                            <i class="ri-team-line display-5 d-block mb-2"></i>
                            Aucun membre de votre entreprise n'est encore inscrit à cette classe.
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection