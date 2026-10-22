@extends('layouts.master')

@section('title', 'Ajouter une inscription')

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('admin.inscriptions.index') }}">Gestion des inscriptions</a>
        @endslot
        @slot('title')
            Ajouter une inscription
        @endslot
    @endcomponent

    <div class="row justify-content-center">
        <div class="col-xl-7">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Inscrire un utilisateur à une classe</h5>
                </div>

                <div class="card-body">
                    <form action="{{ route('admin.inscriptions.store') }}" method="POST" novalidate>
                        @csrf

                        {{-- Utilisateur : particuliers et entreprises uniquement --}}
                        <div class="mb-3">
                            <label for="user_id" class="form-label">Utilisateur</label>
                            <select name="user_id" id="user_id" class="form-select @error('user_id') is-invalid @enderror">
                                <option value="">— Choisir un utilisateur —</option>
                                @foreach ($utilisateurs as $utilisateur)
                                    <option value="{{ $utilisateur->id }}" @selected((int) old('user_id') === $utilisateur->id)>
                                        {{ $utilisateur->name }} ({{ $utilisateur->email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Classe : seulement celles encore ouvertes aux inscriptions ; une classe pleine est grisée --}}
                        <div class="mb-3">
                            <label for="classe_id" class="form-label">Classe</label>
                            <select name="classe_id" id="classe_id" class="form-select @error('classe_id') is-invalid @enderror">
                                <option value="">— Choisir une classe —</option>
                                @foreach ($classes as $classe)
                                    @php($pleine = $classe->inscriptions_count >= $classe->capacite_max)
                                    <option value="{{ $classe->id }}" @selected((int) old('classe_id') === $classe->id) @disabled($pleine)>
                                        {{ $classe->cours->titre }} — {{ $classe->nom }}
                                        (début {{ $classe->date_debut->format('d/m/Y') }},
                                        {{ $classe->inscriptions_count }}/{{ $classe->capacite_max }} inscrits{{ $pleine ? ', complète' : '' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('classe_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            @if ($classes->isEmpty())
                                <div class="form-text text-warning">Aucune classe n'est ouverte aux inscriptions pour le moment.</div>
                            @endif
                        </div>

                        <div class="mb-4">
                            <label for="date_inscription" class="form-label">Date d'inscription</label>
                            <input type="date" name="date_inscription" id="date_inscription"
                                   value="{{ old('date_inscription', today()->toDateString()) }}" max="{{ today()->toDateString() }}"
                                   class="form-control @error('date_inscription') is-invalid @enderror">
                            @error('date_inscription')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <p class="text-muted fs-13">
                            L'inscription est créée « en attente de paiement » ; l'utilisateur est prévenu (cloche + e-mail).
                            Le paiement se valide ensuite depuis la liste.
                        </p>

                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                        <a href="{{ route('admin.inscriptions.index') }}" class="btn btn-light">Annuler</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection