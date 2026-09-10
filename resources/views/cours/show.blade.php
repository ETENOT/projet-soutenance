@extends('layouts.master-without-nav')

@section('title')
    {{ $cours->titre }}
@endsection

@section('content')
    <div class="container py-5" style="max-width: 700px;">
        <a href="{{ route('cours.catalogue') }}" class="text-muted text-decoration-none mb-3 d-inline-block">
            <i data-feather="arrow-left" class="me-1" style="width: 16px; height: 16px;"></i>Retour au catalogue
        </a>

        <div class="card">
            <div class="card-body">
                <h2 class="mb-4">{{ $cours->titre }}</h2>

                <div class="row mb-4">
                    <div class="col-6">
                        <p class="text-muted mb-1">Tarif particulier</p>
                        <h4>{{ number_format($cours->prix_particulier, 2) }} €</h4>
                    </div>
                    <div class="col-6">
                        <p class="text-muted mb-1">Tarif entreprise</p>
                        <h4>{{ number_format($cours->prix_entreprise, 2) }} €</h4>
                    </div>
                </div>

                <a href="{{ route('login') }}" class="btn btn-primary btn-lg w-100">
                    Se connecter pour s'inscrire
                </a>
            </div>
        </div>
    </div>

    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
