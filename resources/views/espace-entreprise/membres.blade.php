@extends('layouts.master')

@section('title', 'Membres entreprises')

@section('content')

@component('components.breadcrumb')
    @slot('li_1')
        <a href="{{ route('dashboard') }}">Accueil</a>
    @endslot
    @slot('title')
        Membres entreprises
    @endslot
@endcomponent

<div class="container-fluid pb-4">

    <p class="text-muted mb-3">
        Particuliers membres de <strong>{{ $entreprise->raison_sociale }}</strong>.
        Les membres sont ajoutés et gérés par l'administrateur de la plateforme.
    </p>

    {{-- Recherche --}}
    <form method="GET" action="{{ route('entreprise.membres.index') }}" class="mb-3">
        <div class="input-group">
            <span class="input-group-text bg-transparent"><i class="ri-search-line text-primary"></i></span>
            <input type="text" name="q" value="{{ request('q') }}" class="form-control"
                   placeholder="Rechercher un membre (nom ou e-mail)…" aria-label="Rechercher un membre">
            <button type="submit" class="btn btn-primary">Rechercher</button>
            @if (request()->filled('q'))
                <a href="{{ route('entreprise.membres.index') }}" class="btn btn-outline-secondary">Effacer</a>
            @endif
        </div>
    </form>

    <div class="card border shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle table-nowrap mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nom</th>
                        <th>E-mail</th>
                        <th>Téléphone</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($membres as $membre)
                        <tr>
                            <td class="fw-medium">{{ $membre->name }}</td>
                            <td>{{ $membre->email }}</td>
                            <td>{{ $membre->particulier?->telephone }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-5">
                                <i class="ri-team-line display-5 d-block mb-2"></i>
                                @if (request()->filled('q'))
                                    Aucun membre ne correspond à votre recherche.
                                @else
                                    Votre entreprise n'a pas encore de membre.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($membres->total() > 0)
            <div class="card-footer bg-transparent d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <small class="text-muted">
                    Affichage de {{ $membres->firstItem() }} à {{ $membres->lastItem() }}
                    sur {{ $membres->total() }} résultat(s)
                </small>
                {{ $membres->links() }}
            </div>
        @endif
    </div>
</div>

@endsection