@extends('layouts.master')

@section('title')
    Classes — {{ $cours->titre }}
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('admin.cours.index') }}">Gestion des cours</a>
        @endslot
        @slot('title')
            Classes de "{{ $cours->titre }}"
        @endslot
    @endcomponent

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0">Liste des classes</h4>
            <a href="{{ route('admin.cours.classes.create', $cours) }}" class="btn btn-primary">
                <i data-feather="plus" class="me-1"></i>Créer une classe
            </a>
        </div>
        <div class="card-body">
            @forelse($classes as $classe)
                <div class="card border mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="mb-1">{{ $classe->nom }}</h5>
                                <p class="text-muted mb-0">
                                    {{ $classe->date_debut->format('d/m/Y') }} → {{ $classe->date_fin->format('d/m/Y') }}
                                    · Capacité : {{ $classe->inscriptions->count() }}/{{ $classe->capacite_max }}
                                </p>
                            </div>
                            <div>
                                <a href="{{ route('admin.cours.classes.edit', [$cours, $classe]) }}" class="btn btn-sm btn-soft-primary">
                                    Modifier
                                </a>
                                <form action="{{ route('admin.cours.classes.destroy', [$cours, $classe]) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Supprimer cette classe ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-soft-danger">Supprimer</button>
                                </form>
                            </div>
                        </div>

                        {{-- Reporter les sessions --}}
                        <form action="{{ route('admin.cours.classes.reporter', [$cours, $classe]) }}" method="POST" class="row g-2 align-items-end mb-3">
                            @csrf
                            @method('PUT')
                            <div class="col-auto">
                                <label class="form-label mb-0 fs-13">Nouvelle date début</label>
                                <input type="date" name="date_debut" class="form-control form-control-sm" value="{{ $classe->date_debut->format('Y-m-d') }}">
                            </div>
                            <div class="col-auto">
                                <label class="form-label mb-0 fs-13">Nouvelle date fin</label>
                                <input type="date" name="date_fin" class="form-control form-control-sm" value="{{ $classe->date_fin->format('Y-m-d') }}">
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-sm btn-outline-warning">Reporter</button>
                            </div>
                        </form>

                        {{-- Utilisateurs inscrits --}}
                        <div class="border-top pt-3">
                            <p class="fw-medium mb-2">Utilisateurs inscrits</p>
                            @forelse($classe->inscriptions as $inscription)
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span>{{ $inscription->user->name }} ({{ $inscription->user->email }})</span>
                                    <form action="{{ route('admin.cours.classes.inscrits.destroy', [$cours, $classe, $inscription->user]) }}" method="POST"
                                          onsubmit="return confirm('Retirer cet utilisateur de la classe ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-link text-danger p-0">Retirer</button>
                                    </form>
                                </div>
                            @empty
                                <p class="text-muted fs-13 mb-2">Aucun utilisateur inscrit pour l'instant.</p>
                            @endforelse

                            {{-- Ajout manuel d'un utilisateur --}}
                            <form action="{{ route('admin.cours.classes.inscrits.store', [$cours, $classe]) }}" method="POST" class="d-flex gap-2 mt-2">
                                @csrf
                                <select name="user_id" class="form-select form-select-sm" required>
                                    <option value="">Choisir un utilisateur...</option>
                                    @foreach(\App\Models\User::orderBy('name')->get() as $utilisateur)
                                        <option value="{{ $utilisateur->id }}">{{ $utilisateur->name }} ({{ $utilisateur->email }})</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-sm btn-outline-primary">Ajouter</button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-muted text-center py-4">Aucune classe créée pour ce cours pour l'instant.</p>
            @endforelse
        </div>
    </div>
@endsection