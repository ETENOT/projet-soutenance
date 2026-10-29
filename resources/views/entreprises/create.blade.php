@extends('layouts.master')

@section('title', 'Ajouter une entreprise')

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('admin.entreprises.index') }}">Gestion des entreprises</a>
        @endslot
        @slot('title')
            Ajouter une entreprise
        @endslot
    @endcomponent

    <form action="{{ route('admin.entreprises.store') }}" method="POST" novalidate>
        @csrf

        <div class="row justify-content-center">
            <div class="col-xl-9">
                @include('entreprises.form')

                <div class="d-flex justify-content-end gap-2 mb-4">
                    <a href="{{ route('admin.entreprises.index') }}" class="btn btn-light">Annuler</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-building-line me-1"></i> Ajouter l'entreprise
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