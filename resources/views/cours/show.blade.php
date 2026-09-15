@extends(Auth::check() ? 'layouts.master' : 'layouts.master-without-nav')

@section('title')
    {{ $cours->titre }}
@endsection

@section('content')
    <div class="container py-5" style="max-width: 900px;">
        <a href="{{ route('cours.catalogue') }}" class="text-muted text-decoration-none mb-3 d-inline-block">
            <i data-feather="arrow-left" class="me-1" style="width: 16px; height: 16px;"></i>Retour au catalogue
        </a>

        <div class="card">
            <div class="card-body">
                <h2 class="mb-4">{{ $cours->titre }}</h2>

                <div class="row mb-4">
                    <div class="col-6">
                        <p class="text-muted mb-1">Tarif particulier</p>
                        <h4>{{ number_format($cours->prix_particulier, 2) }} fcfa</h4>
                    </div>
                    <div class="col-6">
                        <p class="text-muted mb-1">Tarif entreprise</p>
                        <h4>{{ number_format($cours->prix_entreprise, 2) }} fcfa</h4>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <h5 class="mb-3">
                                <i data-feather="calendar" class="text-primary me-1"></i>
                                Classes disponibles
                            </h5>

                            @forelse($cours->classes as $classe)
                                <div class="border-bottom py-2">
                                    <div class="fw-medium">{{ $classe->nom }}</div>
                                    <small class="text-muted">
                                        Du {{ $classe->date_debut->format('d/m/Y') }}
                                        au {{ $classe->date_fin->format('d/m/Y') }}
                                    </small>
                                </div>
                            @empty
                                <p class="text-muted mb-0">Aucune classe disponible pour le moment.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <h5 class="mb-3">
                                <i data-feather="help-circle" class="text-primary me-1"></i>
                                Quiz du cours
                            </h5>

                            @forelse($cours->quizzes as $quiz)
                                <div class="border-bottom py-2">
                                    <div class="fw-medium">Quiz du {{ \Carbon\Carbon::parse($quiz->date)->format('d/m/Y') }}</div>
                                    <small class="text-muted">
                                        De {{ $quiz->heure_debut }} à {{ $quiz->heure_fin }}
                                    </small>
                                </div>
                            @empty
                                <p class="text-muted mb-0">Aucun quiz configuré pour le moment.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Un visiteur doit se connecter avant d'accéder au parcours d'inscription. --}}
                @auth
                    <a href="{{ route('cours.mes') }}" class="btn btn-primary btn-lg">
                        Voir mes cours
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary btn-lg">
                        Se connecter pour s'inscrire
                    </a>
                @endauth
            </div>
        </div>
    </div>

    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
