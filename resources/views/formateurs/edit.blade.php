@extends('layouts.master')

@section('title', 'Modifier un formateur')

@section('content')
    {{-- Fil d'Ariane --}}
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('admin.formateurs.index') }}">Gestion des formateurs</a>
        @endslot
        @slot('title')
            Modifier {{ $formateur->name }}
        @endslot
    @endcomponent

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.formateurs.update', $formateur) }}" method="POST">
                @csrf
                @method('PUT') {{-- La route update est déclarée en PUT --}}

                {{-- Champs communs, pré-remplis grâce à $formateur --}}
                @include('formateurs.form')

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                    <a href="{{ route('admin.formateurs.show', $formateur) }}" class="btn btn-light">Annuler</a>
                </div>
            </form>
        </div>
    </div>
@endsection