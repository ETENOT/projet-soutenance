@extends('layouts.master')

@section('title', 'Détail de la classe')

@section('content')
@component('components.breadcrumb')
    @slot('li_1')
        <a href="{{ route('admin.classes.index') }}">Classes</a>
    @endslot
    @slot('title')
        {{ $classe->nom }}
    @endslot
@endcomponent

<div class="container-fluid pb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="mb-1">{{ $classe->nom }}</h4>
            <div class="text-muted">{{ $cours->titre }}</div>
        </div>
        <a href="{{ route('admin.cours.classes.edit', [$cours, $classe]) }}" class="btn btn-primary">
            <i class="ri-settings-3-line me-1"></i> Gérer
        </a>
    </div>

    <div class="row g-4">
        <div class="col-xl-5">
            <div class="card h-100">
                <div class="card-header"><h5 class="mb-0">Détails du cours</h5></div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Cours</dt>
                        <dd class="col-sm-7">{{ $cours->titre }}</dd>

                        <dt class="col-sm-5">Dates</dt>
                        <dd class="col-sm-7">
                            {{ $classe->date_debut->format('d/m/Y') }} – {{ $classe->date_fin->format('d/m/Y') }}
                        </dd>

                        <dt class="col-sm-5">Lieu</dt>
                        <dd class="col-sm-7">
                            @include('partials.lieu-classe', ['classe' => $classe, 'libelle' => false])
                        </dd>

                        <dt class="col-sm-5">Formateur</dt>
                        <dd class="col-sm-7">{{ $classe->formateur?->name ?? 'Non affecté' }}</dd>

                        <dt class="col-sm-5">Capacité maximale</dt>
                        <dd class="col-sm-7">{{ $classe->capacite_max }} participant(s)</dd>

                        <dt class="col-sm-5">Statut</dt>
                        <dd class="col-sm-7">
                            <span class="badge bg-{{ $classe->etat_couleur }}-subtle text-{{ $classe->etat_couleur }}">
                                {{ $classe->etat_libelle }}
                            </span>
                            @if ($classe->estFermee())
                                <span class="badge bg-dark-subtle text-dark ms-1">Fermée</span>
                            @endif
                        </dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-xl-7">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Inscrits</h5>
                    <span class="badge bg-primary-subtle text-primary">
                        {{ $classe->inscriptions_count }} / {{ $classe->capacite_max }}
                    </span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Courriel</th>
                                <th>Statut de paiement</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($classe->inscriptions as $inscription)
                                <tr>
                                    <td>{{ $inscription->user?->name ?? 'Compte supprimé' }}</td>
                                    <td>{{ $inscription->user?->email ?? '—' }}</td>
                                    <td>
                                        @if ($inscription->estPayee())
                                            <span class="badge bg-success-subtle text-success">Payé</span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning">En attente</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">Aucun inscrit dans cette classe.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
