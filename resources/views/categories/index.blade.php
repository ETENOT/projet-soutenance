@extends('layouts.master')

@section('title', 'Catégories')

@section('content')
@component('components.breadcrumb')
    @slot('li_1')
        <a href="{{ route('dashboard') }}">Accueil</a>
    @endslot
    @slot('title')
        Catégories
    @endslot
@endcomponent

<div class="container-fluid pb-4">
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $erreur)
                    <li>{{ $erreur }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Ajouter une catégorie</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.categories.store') }}" class="row g-2 align-items-end">
                @csrf
                <div class="col-md-8">
                    <label for="nom" class="form-label">Nom</label>
                    <input id="nom" name="nom" value="{{ old('nom') }}"
                           class="form-control @error('nom') is-invalid @enderror" required maxlength="255">
                    @error('nom')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-add-line me-1"></i> Ajouter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Catégories existantes</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Cours rattachés</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $categorie)
                        <tr>
                            <td>
                                <form id="modifier-categorie-{{ $categorie->id }}" method="POST"
                                      action="{{ route('admin.categories.update', $categorie) }}"
                                      class="d-flex gap-2">
                                    @csrf
                                    @method('PUT')
                                    <input name="nom" value="{{ $categorie->nom }}" required maxlength="255"
                                           aria-label="Nom de la catégorie {{ $categorie->nom }}" class="form-control">
                                </form>
                            </td>
                            <td>{{ $categorie->cours_count }}</td>
                            <td class="text-end text-nowrap">
                                <div class="d-flex justify-content-end gap-1">
                                    <button type="submit" form="modifier-categorie-{{ $categorie->id }}"
                                            class="btn btn-sm btn-soft-secondary" title="Modifier la catégorie {{ $categorie->nom }}">
                                        <i class="ri-edit-line"></i>
                                    </button>
                                    @if ($categorie->cours_count === 0)
                                        <form method="POST" action="{{ route('admin.categories.destroy', $categorie) }}"
                                              onsubmit="return confirm('Supprimer cette catégorie ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-soft-danger"
                                                    title="Supprimer la catégorie {{ $categorie->nom }}">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </form>
                                    @else
                                        <button type="button" class="btn btn-sm btn-soft-danger disabled" disabled
                                                title="Des cours sont rattachés à cette catégorie">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">Aucune catégorie enregistrée.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
