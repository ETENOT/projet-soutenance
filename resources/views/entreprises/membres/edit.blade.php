@extends('layouts.master')

@section('title', 'Modifier un membre')

@section('content')

@component('components.breadcrumb')
    @slot('li_1')
        <a href="{{ route('admin.entreprises.show', $entreprise) }}">{{ $entreprise->raison_sociale }}</a>
    @endslot
    @slot('title')
        Modifier {{ $membre->name }}
    @endslot
@endcomponent

<form action="{{ route('admin.entreprises.membres.update', [$entreprise, $membre]) }}" method="POST" novalidate>
    @csrf
    @method('PUT')

    <div class="row justify-content-center">
        <div class="col-xl-9">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informations du membre</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Nom complet <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $membre->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label">Adresse e-mail <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $membre->email) }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label for="telephone" class="form-label">Téléphone <span class="text-danger">*</span></label>
                            <input type="tel" id="telephone" data-champ-telephone="telephone" placeholder="77123456"
                                   class="form-control @error('telephone') is-invalid @enderror"
                                   value="{{ old('telephone', $membre->particulier->telephone) }}" required>
                            <div class="form-text">Format international, avec l'indicatif du pays.</div>
                            @error('telephone')<div class="text-danger fs-13 mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label for="date_de_naissance" class="form-label">Date de naissance <span class="text-muted">(facultative)</span></label>
                            <input type="date" name="date_de_naissance" id="date_de_naissance"
                                   class="form-control @error('date_de_naissance') is-invalid @enderror"
                                   value="{{ old('date_de_naissance', $membre->particulier->date_de_naissance) }}">
                            @error('date_de_naissance')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mb-4">
                <a href="{{ route('admin.entreprises.show', $entreprise) }}" class="btn btn-light">Annuler</a>
                <button type="submit" class="btn btn-primary">
                    <i class="ri-save-line me-1"></i> Enregistrer
                </button>
            </div>
        </div>
    </div>
</form>

@endsection

@section('css')
    @include('partials.css-telephone')
@endsection

@section('script')
    @include('partials.script-telephone')
@endsection