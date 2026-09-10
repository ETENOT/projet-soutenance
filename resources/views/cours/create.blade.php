@extends('layouts.master')

@section('title')
    Créer un cours
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('admin.cours.index') }}">Gestion des cours</a>
        @endslot
        @slot('title')
            Créer un cours
        @endslot
    @endcomponent

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.cours.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="titre" class="form-label">Titre du cours</label>
                    <input type="text" name="titre" id="titre" class="form-control @error('titre') is-invalid @enderror"
                           value="{{ old('titre') }}">
                    @error('titre')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="prix_particulier" class="form-label">Prix particulier (€)</label>
                        <input type="number" step="0.01" name="prix_particulier" id="prix_particulier"
                               class="form-control @error('prix_particulier') is-invalid @enderror"
                               value="{{ old('prix_particulier') }}">
                        @error('prix_particulier')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="prix_entreprise" class="form-label">Prix entreprise (€)</label>
                        <input type="number" step="0.01" name="prix_entreprise" id="prix_entreprise"
                               class="form-control @error('prix_entreprise') is-invalid @enderror"
                               value="{{ old('prix_entreprise') }}">
                        @error('prix_entreprise')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Créer le cours</button>
                <a href="{{ route('admin.cours.index') }}" class="btn btn-light">Annuler</a>
            </form>
        </div>
    </div>
@endsection
