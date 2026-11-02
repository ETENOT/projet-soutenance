@extends('layouts.master')

@section('title', 'Créer une session')

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('admin.sessions.index') }}">Gestion des sessions</a>
        @endslot
        @slot('title')
            Créer une session
        @endslot
    @endcomponent

    <form action="{{ route('admin.sessions.store') }}" method="POST" novalidate>
        @csrf
        <div class="row justify-content-center">
            <div class="col-xl-9">
                @include('sessions._form')

                <div class="d-flex justify-content-end gap-2 mb-4">
                    <a href="{{ route('admin.sessions.index') }}" class="btn btn-light">Annuler</a>
                    <button type="submit" class="btn btn-primary"><i class="ri-calendar-schedule-line me-1"></i> Créer la session</button>
                </div>
            </div>
        </div>
    </form>
@endsection