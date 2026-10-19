@extends('layouts.master')

@section('title', 'Ajouter un formateur')

@section('content')
    {{-- Fil d'Ariane --}}
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('admin.formateurs.index') }}">Gestion des formateurs</a>
        @endslot
        @slot('title')
            Ajouter un formateur
        @endslot
    @endcomponent

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.formateurs.store') }}" method="POST">
                @csrf

                {{-- Champs communs (nom, e-mail, mot de passe) --}}
                @include('formateurs.form')

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Créer le formateur</button>
                    <a href="{{ route('admin.formateurs.index') }}" class="btn btn-light">Annuler</a>
                </div>
            </form>
        </div>
    </div>
@endsection