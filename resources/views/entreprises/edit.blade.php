@extends('layouts.master')

@section('title', 'Modifier une entreprise')

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('admin.entreprises.index') }}">Gestion des entreprises</a>
        @endslot
        @slot('title')
            Modifier {{ $entreprise->raison_sociale }}
        @endslot
    @endcomponent

    <form action="{{ route('admin.entreprises.update', $entreprise) }}" method="POST" novalidate>
        @csrf
        @method('PUT')

        <div class="row justify-content-center">
            <div class="col-xl-9">
                @include('entreprises.form', ['entreprise' => $entreprise])

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