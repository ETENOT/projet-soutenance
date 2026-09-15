@extends('layouts.master')

@section('title')
    Gestion des utilisateurs
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Administration
        @endslot

        @slot('title')
            Gestion des utilisateurs
        @endslot
    @endcomponent

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0">Liste des utilisateurs</h4>

            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary-subtle text-primary">
                    {{ $utilisateurs->total() }} utilisateur(s)
                </span>
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
                    <i data-feather="plus" class="me-1"></i>Ajouter
                </a>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Date de création</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($utilisateurs as $utilisateur)
                            <tr>
                                <td>
                                    <span class="fw-medium">
                                        {{ $utilisateur->name }}
                                    </span>
                                </td>

                                <td>{{ $utilisateur->email }}</td>

                                <td>
                                    @if ($utilisateur->role)
                                        <span class="badge bg-primary-subtle text-primary">
                                            {{ ucfirst($utilisateur->role->nom) }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary">
                                            Aucun rôle
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    {{ $utilisateur->created_at?->format('d/m/Y') }}
                                </td>

                                <td class="text-end">
                                    <a href="{{ route('admin.users.edit', $utilisateur) }}" class="btn btn-sm btn-soft-primary">
                                        Modifier
                                    </a>

                                    @if ($utilisateur->role?->nom !== 'admin')
                                        <form
                                            action="{{ route('admin.users.destroy', $utilisateur) }}"
                                            method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Supprimer cet utilisateur définitivement ?');"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-soft-danger"
                                            >
                                                Supprimer
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted fs-13">
                                            Compte protégé
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    Aucun utilisateur trouvé.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $utilisateurs->links() }}
            </div>
        </div>
    </div>
@endsection