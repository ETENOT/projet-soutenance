@extends('layouts.master')

@section('title')
    Modifier la classe — {{ $cours->titre }}
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('admin.cours.classes.index', $cours) }}">Classes de "{{ $cours->titre }}"</a>
        @endslot
        @slot('title')
            Modifier "{{ $classe->nom }}"
        @endslot
    @endcomponent

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.cours.classes.update', [$cours, $classe]) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="nom" class="form-label">Nom de la classe</label>
                    <input type="text" name="nom" id="nom" class="form-control @error('nom') is-invalid @enderror"
                           value="{{ old('nom', $classe->nom) }}">
                    @error('nom')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="capacite_max" class="form-label">Capacité maximale</label>
                    <input type="number" name="capacite_max" id="capacite_max" min="1" max="255"
                           class="form-control @error('capacite_max') is-invalid @enderror"
                           value="{{ old('capacite_max', $classe->capacite_max) }}">
                    @error('capacite_max')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="date_debut" class="form-label">Date de début</label>
                        <input type="date" name="date_debut" id="date_debut"
                               class="form-control @error('date_debut') is-invalid @enderror"
                               value="{{ old('date_debut', $classe->date_debut->format('Y-m-d')) }}">
                        @error('date_debut')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="date_fin" class="form-label">Date de fin</label>
                        <input type="date" name="date_fin" id="date_fin"
                               class="form-control @error('date_fin') is-invalid @enderror"
                               value="{{ old('date_fin', $classe->date_fin->format('Y-m-d')) }}">
                        @error('date_fin')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                <a href="{{ route('admin.cours.classes.index', $cours) }}" class="btn btn-light">Annuler</a>
            </form>
        </div>
    </div>
@endsection