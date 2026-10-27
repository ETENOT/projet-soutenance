@extends('layouts.master')

@section('title')
    Mes classes
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Espace formateur
        @endslot
        @slot('title')
            Mes classes
        @endslot
    @endcomponent

    <div class="container-fluid pb-4">
        <form method="GET" action="{{ route('classes.formateur.index') }}" class="mb-3">
            <div class="input-group">
                <input type="search" name="q" value="{{ $recherche }}"
                       class="form-control" placeholder="Rechercher une classe, un cours ou un lieu…"
                       aria-label="Rechercher dans mes classes">
                <select id="filtre-etat" name="etat" class="form-select" aria-label="Filtrer par statut"
                        style="max-width: 170px">
                    <option value="">Tous les statuts</option>
                    <option value="a_venir" @selected($etatFiltre === 'a_venir')>À venir</option>
                    <option value="en_cours" @selected($etatFiltre === 'en_cours')>En cours</option>
                    <option value="terminee" @selected($etatFiltre === 'terminee')>Terminées</option>
                </select>
                <button type="submit" class="btn btn-primary" aria-label="Rechercher">
                    <i class="ri-search-line"></i>
                </button>
                @if ($etatFiltre || $recherche !== '')
                    <a href="{{ route('classes.formateur.index') }}" class="btn btn-light">Effacer</a>
                @endif
            </div>
        </form>

        <div class="card border-0 shadow-sm">
            <div class="card-body px-3 px-lg-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Classe</th>
                                <th>Cours</th>
                                <th>État</th>
                                <th>Dates</th>
                                <th>Lieu</th>
                                <th class="text-center">Inscrits</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($classes as $classe)
                                <tr>
                                    <td class="fw-medium">{{ $classe->cours?->titre }} — {{ $classe->nom }}</td>
                                    <td>{{ $classe->cours->titre ?? '—' }}</td>
                                    <td>
                                        {{-- $classe->etat_libelle / etat_couleur : voir App\Models\Classe --}}
                                        <span class="badge bg-{{ $classe->etat_couleur }}-subtle text-{{ $classe->etat_couleur }}">
                                            {{ $classe->etat_libelle }}
                                        </span>
                                        @if ($classe->estFermee())
                                            <span class="badge bg-dark-subtle text-dark">Fermée</span>
                                        @endif
                                    </td>
                                    <td>{{ $classe->date_debut->format('d/m/Y') }} → {{ $classe->date_fin->format('d/m/Y') }}</td>
                                    <td>@include('partials.lieu-classe', ['classe' => $classe])</td>
                                    {{-- payes_count vient du withCount() du contrôleur ; capacité = capacite_max --}}
                                    <td class="text-center">{{ $classe->payes_count }} / {{ $classe->capacite_max }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('classes.formateur.show', $classe) }}" class="btn btn-sm btn-soft-primary">
                                            Ouvrir
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        @if ($etatFiltre || $recherche !== '')
                                            Aucune classe ne correspond à votre recherche.
                                            <a href="{{ route('classes.formateur.index') }}" class="d-block mt-2">Effacer les filtres</a>
                                        @else
                                            Aucune classe ne vous est affectée pour l'instant.
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection