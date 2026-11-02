@extends('layouts.master')

@section('title', 'Modifier la session')

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('admin.sessions.show', $session) }}">{{ $session->nom }}</a>
        @endslot
        @slot('title')
            Modifier la session
        @endslot
    @endcomponent

    <form action="{{ route('admin.sessions.update', $session) }}" method="POST" novalidate>
        @csrf
        @method('PUT')
        <div class="row justify-content-center">
            <div class="col-xl-9">
                @include('sessions._form')

                <div class="d-flex justify-content-end gap-2 mb-4">
                    <a href="{{ route('admin.sessions.show', $session) }}" class="btn btn-light">Annuler</a>
                    <button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i> Enregistrer</button>
                </div>
            </div>
        </div>
    </form>
@endsection