@extends('layouts.master')

@section('title', 'Ajouter une classe à la session')

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('admin.sessions.show', $session) }}">{{ $session->nom }}</a>
        @endslot
        @slot('title')
            Nouvelle classe
        @endslot
    @endcomponent

    <form action="{{ route('admin.sessions.classes.store', $session) }}" method="POST" novalidate>
        @csrf
        <div class="row justify-content-center">
            <div class="col-xl-9">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Classe pour {{ $session->entreprise->raison_sociale }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="cours_id" class="form-label">Cours <span class="text-danger">*</span></label>
                                <select name="cours_id" id="cours_id" class="form-select @error('cours_id') is-invalid @enderror" required>
                                    <option value="">— Choisir un cours —</option>
                                    @foreach ($cours as $c)
                                        <option value="{{ $c->id }}" @selected((int) old('cours_id') === $c->id)>{{ $c->titre }}</option>
                                    @endforeach
                                </select>
                                @error('cours_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <div class="form-text">Les classes d'une même session peuvent relever de cours différents.</div>
                            </div>

                            <div class="col-md-6">
                                <label for="nom" class="form-label">Nom de la classe <span class="text-danger">*</span></label>
                                <input type="text" name="nom" id="nom" class="form-control @error('nom') is-invalid @enderror"
                                       value="{{ old('nom') }}" required>
                                @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label for="date_debut" class="form-label">Date de début <span class="text-danger">*</span></label>
                                <input type="date" name="date_debut" id="date_debut" class="form-control @error('date_debut') is-invalid @enderror"
                                       value="{{ old('date_debut') }}" required>
                                @error('date_debut')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label for="date_fin" class="form-label">Date de fin <span class="text-danger">*</span></label>
                                <input type="date" name="date_fin" id="date_fin" class="form-control @error('date_fin') is-invalid @enderror"
                                       value="{{ old('date_fin') }}" required>
                                @error('date_fin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label for="capacite_max" class="form-label">Capacité maximale</label>
                                <input type="number" name="capacite_max" id="capacite_max" min="1" max="255"
                                       class="form-control @error('capacite_max') is-invalid @enderror" value="{{ old('capacite_max', 10) }}">
                                @error('capacite_max')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-8">
                                <label for="lieu" class="form-label">Lieu</label>
                                <input type="text" name="lieu" id="lieu" class="form-control @error('lieu') is-invalid @enderror"
                                       value="{{ old('lieu', \App\Models\Classe::LIEU_PAR_DEFAUT) }}">
                                @error('lieu')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label for="lieu_lien" class="form-label">Lien Google Maps <span class="text-muted">(facultatif)</span></label>
                                <input type="url" name="lieu_lien" id="lieu_lien" class="form-control @error('lieu_lien') is-invalid @enderror"
                                       value="{{ old('lieu_lien') }}">
                                @error('lieu_lien')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label for="formateur_id" class="form-label">Formateur <span class="text-muted">(facultatif)</span></label>
                                <select name="formateur_id" id="formateur_id" class="form-select @error('formateur_id') is-invalid @enderror">
                                    <option value="">— Aucun pour l'instant —</option>
                                    @foreach ($formateurs as $f)
                                        <option value="{{ $f->id }}" @selected((int) old('formateur_id') === $f->id)>{{ $f->name }}</option>
                                    @endforeach
                                </select>
                                @error('formateur_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <p class="text-muted small mt-3 mb-0">
                            <i class="ri-information-line me-1"></i>
                            La photo du lieu et les jours non comptabilisés se règlent ensuite depuis la modification de la classe.
                        </p>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mb-4">
                    <a href="{{ route('admin.sessions.show', $session) }}" class="btn btn-light">Annuler</a>
                    <button type="submit" class="btn btn-primary"><i class="ri-add-line me-1"></i> Ajouter la classe</button>
                </div>
            </div>
        </div>
    </form>
@endsection