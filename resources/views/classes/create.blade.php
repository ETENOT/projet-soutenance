@extends('layouts.master')

@section('title')
    Créer une classe — {{ $cours->titre }}
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('admin.cours.classes.index', $cours) }}">Classes de "{{ $cours->titre }}"</a>
        @endslot
        @slot('title')
            Créer une classe
        @endslot
    @endcomponent

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.cours.classes.store', $cours) }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label for="nom" class="form-label">Nom de la classe</label>
                    <input type="text" name="nom" id="nom" class="form-control @error('nom') is-invalid @enderror"
                           value="{{ old('nom') }}">
                    @error('nom')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="capacite_max" class="form-label">Capacité maximale</label>
                    <input type="number" name="capacite_max" id="capacite_max" min="1" max="255"
                           class="form-control @error('capacite_max') is-invalid @enderror"
                           value="{{ old('capacite_max', 10) }}">
                    @error('capacite_max')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="lieu" class="form-label">Lieu</label>
                    <input type="text" name="lieu" id="lieu"
                           class="form-control @error('lieu') is-invalid @enderror"
                           value="{{ old('lieu', \App\Models\Classe::LIEU_PAR_DEFAUT) }}">
                    @error('lieu')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="lieu_lien" class="form-label">Lien Google Maps du lieu <span class="text-muted">(facultatif)</span></label>
                    <input type="url" name="lieu_lien" id="lieu_lien" placeholder="https://maps.app.goo.gl/..."
                           class="form-control @error('lieu_lien') is-invalid @enderror"
                           value="{{ old('lieu_lien') }}">
                    <div class="form-text">Sans lien, une recherche Google Maps sur le nom du lieu est utilisée.</div>
                    @error('lieu_lien')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="lieu_image" class="form-label">Photo du lieu <span class="text-muted">(facultative)</span></label>
                    <div class="mb-2">
                        <img src="{{ asset(\App\Models\Classe::IMAGE_LIEU_PAR_DEFAUT) }}"
                             alt="Bâtiment du lieu par défaut" class="rounded object-fit-cover" width="120" height="80">
                        <small class="text-muted ms-2">Photo du bâtiment par défaut, remplaçable par une autre image.</small>
                    </div>
                    <input type="file" name="lieu_image" id="lieu_image" accept="image/*"
                           class="form-control @error('lieu_image') is-invalid @enderror">
                    <div class="form-text">Taille maximale : 5 Mo.</div>
                    @error('lieu_image')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="formateur_id" class="form-label">Formateur</label>
                    <select name="formateur_id" id="formateur_id"
                            class="form-select @error('formateur_id') is-invalid @enderror">
                        <option value="">— Aucun formateur —</option>
                        @foreach ($formateurs as $formateur)
                            <option value="{{ $formateur->id }}"
                                @selected((string) old('formateur_id', '') === (string) $formateur->id)>
                                {{ $formateur->name }} ({{ $formateur->email }})
                            </option>
                        @endforeach
                    </select>
                    @error('formateur_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="date_debut" class="form-label">Date de début</label>
                        <input type="date" name="date_debut" id="date_debut"
                               class="form-control @error('date_debut') is-invalid @enderror"
                               value="{{ old('date_debut') }}">
                        @error('date_debut')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="date_fin" class="form-label">Date de fin</label>
                        <input type="date" name="date_fin" id="date_fin"
                               class="form-control @error('date_fin') is-invalid @enderror"
                               value="{{ old('date_fin') }}">
                        @error('date_fin')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Jours à ne pas compter dans le taux de présence (modifiables plus tard) --}}
                <div class="mb-3">
                    @include('classes.jours_exclus', ['joursExclus' => [], 'debutId' => 'date_debut', 'finId' => 'date_fin'])
                </div>

                <button type="submit" class="btn btn-primary">Créer la classe</button>
                <a href="{{ route('admin.cours.classes.index', $cours) }}" class="btn btn-light">Annuler</a>
            </form>
        </div>
    </div>
@endsection