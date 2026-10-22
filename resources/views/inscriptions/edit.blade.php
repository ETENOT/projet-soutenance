@extends('layouts.master')

@section('title', 'Modifier une inscription')

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('admin.inscriptions.index') }}">Gestion des inscriptions</a>
        @endslot
        @slot('title')
            Modifier l'inscription
        @endslot
    @endcomponent

    <div class="row justify-content-center">
        <div class="col-xl-7">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Inscription de {{ $inscription->user->name }}</h5>
                </div>

                <div class="card-body">
                    <form action="{{ route('admin.inscriptions.update', $inscription) }}" method="POST" novalidate>
                        @csrf
                        @method('PUT')

                        {{-- L'utilisateur ne change pas : pour une autre personne, on crée une nouvelle inscription --}}
                        <div class="mb-3">
                            <label class="form-label">Utilisateur</label>
                            <input type="text" class="form-control" disabled
                                   value="{{ $inscription->user->name }} ({{ $inscription->user->email }})">
                        </div>

                        <div class="mb-3">
                            <label for="classe_id" class="form-label">Classe</label>

                            @if ($transfertBloque)
                                {{-- Un champ désactivé n'est pas envoyé : on renvoie la classe actuelle en champ caché --}}
                                <input type="hidden" name="classe_id" value="{{ $inscription->classe_id }}">
                                <input type="text" class="form-control" disabled
                                       value="{{ $inscription->classe->cours->titre }} — {{ $inscription->classe->nom }}">
                                <div class="form-text text-warning">
                                    Cette classe a déjà commencé (ou est fermée) : l'inscription ne peut plus être déplacée.
                                </div>
                            @else
                                <select name="classe_id" id="classe_id" class="form-select @error('classe_id') is-invalid @enderror">
                                    @foreach ($classes as $classe)
                                        @php
                                            $actuelle = $classe->id === $inscription->classe_id;
                                            $pleine = ! $actuelle && $classe->inscriptions_count >= $classe->capacite_max;
                                        @endphp
                                        <option value="{{ $classe->id }}"
                                                @selected((int) old('classe_id', $inscription->classe_id) === $classe->id)
                                                @disabled($pleine)>
                                            {{ $classe->cours->titre }} — {{ $classe->nom }}
                                            (début {{ $classe->date_debut->format('d/m/Y') }},
                                            {{ $classe->inscriptions_count }}/{{ $classe->capacite_max }} inscrits{{ $pleine ? ', complète' : '' }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('classe_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                @if ($inscription->estPayee())
                                    <div class="form-text">Inscription payée : seules les classes du même cours sont proposées.</div>
                                @endif
                            @endif
                        </div>

                        <div class="mb-4">
                            <label for="date_inscription" class="form-label">Date d'inscription</label>
                            <input type="date" name="date_inscription" id="date_inscription"
                                   value="{{ old('date_inscription', \Carbon\Carbon::parse($inscription->date_inscription)->toDateString()) }}"
                                   max="{{ today()->toDateString() }}"
                                   class="form-control @error('date_inscription') is-invalid @enderror">
                            @error('date_inscription')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                        <a href="{{ route('admin.inscriptions.index') }}" class="btn btn-light">Annuler</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection