@extends('layouts.master')

@section('title', 'Gestion des utilisateurs')

@section('content')

{{-- ===== Fil d'Ariane ===== --}}
@component('components.breadcrumb')
    @slot('li_1')
        <a href="{{ route('dashboard') }}">Accueil</a>
    @endslot
    @slot('title')
        Utilisateurs
    @endslot
@endcomponent

<div class="container-fluid pb-4">

    {{-- ===== En-tête ===== --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <p class="text-muted mb-0 col-lg-8 px-0">
            Consultez et gérez les comptes des utilisateurs de la plateforme (particuliers, entreprises et administrateurs).
        </p>

        <a href="{{ route('admin.users.create') }}" class="btn btn-primary fw-semibold">
            <i class="ri-add-line me-1"></i> Ajouter un utilisateur
        </a>
    </div>

    {{-- ===== Statistiques ===== --}}
    <div class="row g-3 mb-4">

        <div class="col-sm-6 col-xl-3">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="avatar-md flex-shrink-0">
                        <span class="avatar-title rounded-3 bg-success-subtle text-success fs-2">
                            <i class="ri-user-3-line"></i>
                        </span>
                    </span>
                    <div>
                        <h4 class="fw-bold mb-0">{{ $stats['particuliers'] }}</h4>
                        <p class="text-muted mb-0">Particuliers</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="avatar-md flex-shrink-0">
                        <span class="avatar-title rounded-3 bg-info-subtle text-info fs-2">
                            <i class="ri-building-line"></i>
                        </span>
                    </span>
                    <div>
                        <h4 class="fw-bold mb-0">{{ $stats['entreprises'] }}</h4>
                        <p class="text-muted mb-0">Entreprises</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="avatar-md flex-shrink-0">
                        <span class="avatar-title rounded-3 bg-danger-subtle text-danger fs-2">
                            <i class="ri-shield-user-line"></i>
                        </span>
                    </span>
                    <div>
                        <h4 class="fw-bold mb-0">{{ $stats['admins'] }}</h4>
                        <p class="text-muted mb-0">Administrateurs</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="avatar-md flex-shrink-0">
                        <span class="avatar-title rounded-3 bg-primary-subtle text-primary fs-2">
                            <i class="ri-group-line"></i>
                        </span>
                    </span>
                    <div>
                        <h4 class="fw-bold mb-0">{{ $stats['total'] }}</h4>
                        <p class="text-muted mb-0">Total utilisateurs</p>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ===== Filtres ===== --}}
    <div class="card border shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.users.index') }}">
                <div class="row g-3 align-items-center">

                    <div class="col-lg-4">
                        <div class="input-group">
                            <span class="input-group-text bg-transparent">
                                <i class="ri-search-line text-primary"></i>
                            </span>
                            <input type="text" name="q" value="{{ request('q') }}" class="form-control"
                                   placeholder="Rechercher un utilisateur..." aria-label="Rechercher un utilisateur">
                        </div>
                    </div>

                    <div class="col-sm-6 col-lg-2">
                        <select name="role" class="form-select" aria-label="Filtrer par rôle"
                                onchange="this.form.submit()">
                            <option value="">Tous les rôles</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->nom }}" @selected(request('role') === $role->nom)>
                                    {{ ucfirst($role->nom) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                        <select name="statut" class="form-select" aria-label="Filtrer par statut"
                                onchange="this.form.submit()">
                            <option value="">Tous les statuts</option>
                            <option value="verifie" @selected(request('statut') === 'verifie')>E-mail vérifié</option>
                            <option value="non_verifie" @selected(request('statut') === 'non_verifie')>E-mail non vérifié</option>
                        </select>
                    </div>

                    <div class="col-lg-3 d-flex gap-2 justify-content-lg-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-filter-3-line me-1"></i> Filtrer
                        </button>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                            <i class="ri-refresh-line me-1"></i> Réinitialiser
                        </a>
                    </div>

                </div>
            </form>
        </div>
    </div>

    {{-- ===== Tableau des utilisateurs ===== --}}
    <div class="card border shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle table-nowrap mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Nom complet</th>
                        <th>E-mail</th>
                        <th>Rôle</th>
                        <th>Statut</th>
                        <th>Formations</th>
                        <th>Inscrit le</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($utilisateurs as $utilisateur)
                        @php
                            $nomRole = $utilisateur->role?->nom;
                            $badgeRole = match ($nomRole) {
                                'admin'       => ['bg-danger-subtle text-danger', 'Administrateur'],
                                'entreprise'  => ['bg-info-subtle text-info', 'Entreprise'],
                                'particulier' => ['bg-primary-subtle text-primary', 'Particulier'],
                                default       => ['bg-secondary-subtle text-secondary', 'Aucun rôle'],
                            };
                            $protege = $nomRole === 'admin' || $utilisateur->id === auth()->id();
                        @endphp

                        <tr>
                            <td class="text-muted">USR-{{ str_pad($utilisateur->id, 3, '0', STR_PAD_LEFT) }}</td>

                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    @if($utilisateur->avatar)
                                        <img src="{{ asset('storage/' . $utilisateur->avatar) }}"
                                             alt="{{ $utilisateur->name }}"
                                             class="avatar-xs rounded-circle object-fit-cover flex-shrink-0">
                                    @else
                                        <span class="avatar-xs flex-shrink-0">
                                            <span class="avatar-title rounded-circle bg-primary-subtle text-primary fw-semibold">
                                                {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($utilisateur->name, 0, 1)) }}
                                            </span>
                                        </span>
                                    @endif
                                    <span class="fw-medium">{{ $utilisateur->name }}</span>
                                </div>
                            </td>

                            <td>{{ $utilisateur->email }}</td>

                            <td><span class="badge {{ $badgeRole[0] }}">{{ $badgeRole[1] }}</span></td>

                            <td>
                                @if($utilisateur->email_verified_at)
                                    <span class="badge bg-success-subtle text-success">
                                        <i class="ri-checkbox-circle-line me-1"></i>Vérifié
                                    </span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning">
                                        <i class="ri-time-line me-1"></i>Non vérifié
                                    </span>
                                @endif
                            </td>

                            <td>{{ $utilisateur->inscriptions_count }}</td>

                            <td>{{ $utilisateur->created_at?->format('d/m/Y') }}</td>

                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('admin.users.edit', $utilisateur) }}"
                                       class="btn btn-sm btn-soft-secondary" title="Modifier">
                                        <i class="ri-edit-line"></i>
                                    </a>

                                    @if($protege)
                                        <span class="btn btn-sm btn-soft-secondary disabled"
                                              title="Compte protégé : suppression impossible">
                                            <i class="ri-lock-line"></i>
                                        </span>
                                    @else
                                        <form action="{{ route('admin.users.destroy', $utilisateur) }}" method="POST"
                                              onsubmit="return confirm('Supprimer cet utilisateur définitivement ?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-soft-danger" title="Supprimer">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="ri-user-search-line display-5 d-block mb-2"></i>
                                Aucun utilisateur ne correspond à votre recherche.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($utilisateurs->total() > 0)
            <div class="card-footer bg-transparent d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <small class="text-muted">
                    Affichage de {{ $utilisateurs->firstItem() }} à {{ $utilisateurs->lastItem() }}
                    sur {{ $utilisateurs->total() }} résultat(s)
                </small>
                {{ $utilisateurs->links() }}
            </div>
        @endif
    </div>

</div>

@endsection