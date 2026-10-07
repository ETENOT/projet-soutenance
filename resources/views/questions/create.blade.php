@extends('layouts.master')

@section('title')
    Ajouter une question
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('admin.questions.index') }}">Banque de questions</a>
        @endslot
        @slot('title')
            Ajouter une question
        @endslot
    @endcomponent

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.questions.store') }}" method="POST">
                @include('questions.form')
            </form>
        </div>
    </div>
@endsection