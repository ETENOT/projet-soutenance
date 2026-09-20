@extends(Auth::check() ? 'layouts.master' : 'layouts.master-without-nav')

@section('title')
    {{ $cours->titre }}
@endsection

@section('content')
    {{-- Styles locaux du détail de cours : hero, tarif et sessions. --}}
    <style>
        .cours-hero {
            border-radius: 1rem;
            padding: 2rem 2.25rem;
            background: linear-gradient(135deg, #344b8e 0%, #6979ae 100%);
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .cours-hero::after {
            content: '';
            position: absolute;
            right: -4rem;
            top: -6rem;
            width: 14rem;
            height: 14rem;
            border-radius: 50%;
            border: 1px solid rgba(255, 255, 255, 0.15);
            box-shadow: 0 0 0 1.5rem rgba(255, 255, 255, 0.05), 0 0 0 3rem rgba(255, 255, 255, 0.03);
        }

        .cours-hero-icon {
            width: 4rem;
            height: 4rem;
            flex-shrink: 0;
            border-radius: 1rem;
            background: rgba(255, 255, 255, 0.16);
            border: 1px solid rgba(255, 255, 255, 0.24);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: #fff;
        }

        .cours-hero h2, .cours-hero p, .cours-hero .badge-category {
            color: #ffffff !important;
        }

        .cours-hero .badge-category {
            background: rgba(255, 255, 255, 0.18);
            border: 1px solid rgba(255, 255, 255, 0.28);
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: .04em;
            padding: .3rem .7rem;
            border-radius: 2rem;
        }

        .price-panel {
            background: #fff;
            border-radius: 0.9rem;
            box-shadow: 0 0.45rem 1.5rem rgba(31, 45, 61, 0.06);
            border: 1px solid #eef0f6;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .price-panel-icon {
            width: 2.75rem;
            height: 2.75rem;
            border-radius: 0.7rem;
            background: rgba(var(--vz-primary-rgb), 0.1);
            color: var(--vz-primary);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            flex-shrink: 0;
        }

        .session-item, .quiz-item {
            border: 1px solid #eef0f6;
            border-radius: 0.75rem;
            padding: 0.9rem 1rem;
            margin-bottom: 0.6rem;
            transition: box-shadow .15s ease;
        }
        .session-item:hover {
            box-shadow: 0 0.3rem 0.8rem rgba(31, 45, 61, 0.06);
        }
    </style>

    <div class="container py-4" style="max-width: 960px;">
        <a href="{{ route('cours.catalogue') }}" class="text-muted text-decoration-none mb-3 d-inline-flex align-items-center">
            <i class="ri-arrow-left-line me-1"></i>Retour au catalogue
        </a>

        @php
            // Le ?-> évite un plantage si la session est périmée (utilisateur supprimé).
            $role = Auth::user()?->role?->nom;
        @endphp

        <div class="cours-hero mb-4">
            <span class="cours-hero-icon">
                <i class="ri-book-open-line"></i>
            </span>
            <div>
                @if(!empty($cours->categorie))
                    <span class="badge-category mb-2 d-inline-block">{{ $cours->categorie }}</span>
                @endif
                <h2 class="fw-bold mb-1">{{ $cours->titre }}</h2>
                @if(!empty($cours->description))
                    <p class="mb-0" style="opacity: .9; max-width: 640px;">{{ $cours->description }}</p>
                @endif
            </div>
        </div>

        {{-- Le tarif affiché dépend du rôle de l'utilisateur connecté. --}}
        <div class="price-panel p-3 mb-4">
            <span class="price-panel-icon"><i class="ri-price-tag-3-line"></i></span>
            @if($role === 'entreprise')
                <div>
                    <span class="text-muted small d-block">Tarif entreprise</span>
                    <span class="fs-4 fw-bold text-primary">{{ number_format($cours->prix_entreprise, 0, ',', ' ') }} fcfa</span>
                </div>
            @elseif($role === 'particulier')
                <div>
                    <span class="text-muted small d-block">Tarif particulier</span>
                    <span class="fs-4 fw-bold text-primary">{{ number_format($cours->prix_particulier, 0, ',', ' ') }} fcfa</span>
                </div>
            @else
                {{-- Visiteur ou admin : pas encore de rôle tranché, on montre les deux --}}
                <div class="row w-100">
                    <div class="col-6">
                        <span class="text-muted small d-block">Tarif particulier</span>
                        <span class="fs-4 fw-bold text-primary">{{ number_format($cours->prix_particulier, 0, ',', ' ') }} fcfa</span>
                    </div>
                    <div class="col-6">
                        <span class="text-muted small d-block">Tarif entreprise</span>
                        <span class="fs-4 fw-bold text-primary">{{ number_format($cours->prix_entreprise, 0, ',', ' ') }} fcfa</span>
                    </div>
                </div>
            @endif
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <h5 class="mb-3"><i class="ri-calendar-2-line text-primary me-1"></i>Classes disponibles</h5>

                @forelse($cours->classes as $classe)
                    <div class="session-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="fw-medium">{{ $classe->nom }}</div>
                                <small class="text-muted d-block">
                                    Du {{ $classe->date_debut->format('d/m/Y') }}
                                    au {{ $classe->date_fin->format('d/m/Y') }}
                                </small>
                                <span class="badge bg-light text-muted mt-1" style="font-size: 0.7rem;">
                                    {{ $classe->inscriptions_count }}/{{ $classe->capacite_max }} places
                                </span>
                            </div>

                            <div class="text-end">
                                @auth
                                    @if($mesInscriptions->contains($classe->id))
                                        <span class="badge bg-success-subtle text-success d-block mb-1">Inscrit</span>
                                        <form action="{{ route('classes.inscription.destroy', $classe) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-link text-danger p-0">Se désinscrire</button>
                                        </form>
                                    @elseif($classe->inscriptions_count >= $classe->capacite_max)
                                        <span class="badge bg-danger-subtle text-danger">Complet</span>
                                    @else
                                        <form action="{{ route('classes.inscription.store', $classe) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-primary">S'inscrire</button>
                                        </form>
                                    @endif
                                @else
                                    <a href="{{ route('login') }}" class="btn btn-sm btn-outline-primary">Se connecter</a>
                                @endauth
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-muted mb-0">Aucune classe disponible pour le moment.</p>
                @endforelse
            </div>

            <div class="col-md-6">
                <h5 class="mb-3"><i class="ri-question-line text-primary me-1"></i>Quiz du cours</h5>

                @forelse($cours->quizzes as $quiz)
                    <div class="quiz-item">
                        <div class="fw-medium">Quiz du {{ \Carbon\Carbon::parse($quiz->date)->format('d/m/Y') }}</div>
                        <small class="text-muted">De {{ $quiz->heure_debut }} à {{ $quiz->heure_fin }}</small>
                    </div>
                @empty
                    <p class="text-muted mb-0">Aucun quiz configuré pour le moment.</p>
                @endforelse
            </div>
        </div>
    </div>

    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection