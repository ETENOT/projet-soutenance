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
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Liste des classes</h4>
        <a href="{{ route('admin.cours.classes.create', $cours) }}" class="btn btn-primary">
            <i data-feather="plus" class="me-1"></i>Créer une classe
        </a>
    </div>

    <div class="row g-3">
        @forelse($classes as $classe)
            <div class="col-md-6 col-xl-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="mb-2">{{ $classe->nom }}</h5>
                        <p class="text-muted mb-2">
                            {{ $classe->date_debut->format('d/m/Y') }} → {{ $classe->date_fin->format('d/m/Y') }}
                        </p>
                        <span class="badge bg-primary-subtle text-primary mb-3">
                            {{ $classe->inscriptions->count() }}/{{ $classe->capacite_max }} inscrits
                        </span>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.cours.classes.edit', [$cours, $classe]) }}" class="btn btn-sm btn-soft-primary flex-grow-1">
                                Gérer
                            </a>
                            <form action="{{ route('admin.cours.classes.destroy', [$cours, $classe]) }}" method="POST"
                                  onsubmit="return confirm('Supprimer cette classe ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-soft-danger">Supprimer</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <p class="text-muted text-center py-4">Aucune classe créée pour ce cours pour l'instant.</p>
            </div>
        @endforelse
    </div>
@endsection