@extends('layouts.master')

@section('title')
    Modifier une question
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('admin.questions.index') }}">Banque de questions</a>
        @endslot
        @slot('title')
            Modifier une question
        @endslot
    @endcomponent

    <div class="card">
        <div class="card-body">
            {{-- Avertissement si la question a déjà été posée dans des tentatives --}}
            @if ($question->reponses_count > 0)
                <div class="alert alert-warning">
                    Cette question a déjà été posée dans <strong>{{ $question->reponses_count }}</strong> tentative(s) de quiz.
                    Modifier la bonne réponse ou supprimer une option change la correction affichée
                    pour ces tentatives (les notes déjà calculées ne sont pas recalculées).
                </div>
            @endif

            <form action="{{ route('admin.questions.update', $question) }}" method="POST">
                @include('questions.form')
            </form>
        </div>
    </div>
@endsection