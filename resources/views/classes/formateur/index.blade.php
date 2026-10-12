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

    <div class="card">
        <div class="card-body">
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
                                <td class="fw-medium">{{ $classe->nom }}</td>
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
                                <td>{{ $classe->lieu }}</td>
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
                                    Aucune classe ne vous est affectée pour l'instant.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection