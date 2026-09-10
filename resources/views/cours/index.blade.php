@extends('layouts.master')

@section('title')
    Gestion des cours
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Administration
        @endslot
        @slot('title')
            Gestion des cours
        @endslot
    @endcomponent

    {{-- Message de confirmation après création/modification/suppression --}}
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0">Liste des cours</h4>
            <a href="{{ route('admin.cours.create') }}" class="btn btn-primary">
                <i data-feather="plus" class="me-1"></i>Créer un cours
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-nowrap align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Titre</th>
                            <th>Prix particulier</th>
                            <th>Prix entreprise</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cours as $unCours)
                            <tr>
                                <td>{{ $unCours->titre }}</td>
                                <td>{{ number_format($unCours->prix_particulier, 2) }} €</td>
                                <td>{{ number_format($unCours->prix_entreprise, 2) }} €</td>
                                <td class="text-end">
                                    <a href="{{ route('admin.cours.edit', $unCours) }}" class="btn btn-sm btn-soft-primary">
                                        Modifier
                                    </a>
                                    <form action="{{ route('admin.cours.destroy', $unCours) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Supprimer ce cours définitivement ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-soft-danger">Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">Aucun cours créé pour l'instant.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
