{{-- Invité : layout sans sidebar. Connecté : layout complet. --}}
@extends(Auth::check() ? 'layouts.master' : 'layouts.master-without-nav')

@section('title')
    Catalogue des cours
@endsection

@section('content')
    <style>
        .course-card {
            overflow: hidden;
            border: 1px solid #eef0f6;
            border-radius: 0.9rem;
            background: #ffffff;
            box-shadow: 0 0.3rem 1rem rgba(31, 45, 61, 0.05);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .course-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 0.6rem 1.4rem rgba(31, 45, 61, 0.1);
        }
        .course-accent-bar {
            height: 4px;
            width: 100%;
        }
        .course-card .card-body {
            padding: 1rem 1.1rem 1.1rem;
        }
        .course-icon-chip {
            width: 2.1rem;
            height: 2.1rem;
            border-radius: 0.6rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
        }
        .course-title {
            font-size: 0.98rem;
            font-weight: 700;
            color: #1f2d3d;
            line-height: 1.3;
        }
        .course-description {
            color: #6a7488;
            font-size: 0.78rem;
            line-height: 1.45;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 2.2em;
        }
        .course-badges .badge {
            font-weight: 500;
            font-size: 0.68rem;
        }
        .course-price-tag {
            font-size: 0.95rem;
        }
        .course-card .btn-outline-primary {
            border-radius: 0.5rem;
            padding: 0.4rem 0.8rem;
            font-weight: 600;
            font-size: 0.8rem;
        }
    </style>

    {{-- Invité : le layout sans sidebar n'ajoute aucune marge autour du contenu.
         On l'enveloppe dans un conteneur, comme la page de détail d'un cours. --}}
    @guest
        <div class="container py-4">
    @endguest

    @auth
        @component('components.breadcrumb')
            @slot('li_1')
                Cours
            @endslot
            @slot('title')
                Catalogue des cours
            @endslot
        @endcomponent
    @else
        {{-- Le fil d'Ariane de Velzon est prévu pour la zone de contenu avec sidebar :
             pour un invité, un simple titre suffit, avec un accès à la connexion. --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">Catalogue des cours</h4>
            <a href="{{ route('login') }}" class="btn btn-primary btn-sm">Se connecter</a>
        </div>
    @endauth

    {{-- La recherche filtre les informations publiques des cours côté serveur. --}}
    <form method="GET" action="{{ route('cours.catalogue') }}" class="mb-4">
        <div class="input-group">
            <span class="input-group-text">
                <i class="ri-search-line"></i>
            </span>
            <input
                type="search"
                name="search"
                value="{{ $search }}"
                class="form-control"
                placeholder="Rechercher un cours, une catégorie..."
                aria-label="Rechercher un cours"
            >
            <button type="submit" class="btn btn-primary">
                Rechercher
            </button>
            @if($search !== '')
                <a href="{{ route('cours.catalogue') }}" class="btn btn-outline-secondary">
                    Réinitialiser
                </a>
            @endif
        </div>
    </form>

    @if($search !== '')
        <p class="text-muted mb-3">
            {{ $cours->count() }} résultat(s) pour « {{ $search }} »
        </p>
    @endif

    @php
        // Un particulier ne doit pas voir le tarif entreprise, et inversement.
        // Visiteur anonyme (ou session périmée) -> $role vaut null grâce à ?->,
        // et on affiche le tarif particulier par défaut (offre grand public).
        $role = Auth::user()?->role?->nom;

        $palette = [
            ['#405189', '64,81,137'],
            ['#0ab39c', '10,179,156'],
            ['#f06548', '240,101,72'],
            ['#f7b84b', '247,184,75'],
            ['#299cdb', '41,156,219'],
        ];

        $categoryIcon = function ($categorie) {
            $categorie = strtolower((string) $categorie);
            return match (true) {
                str_contains($categorie, 'excel') => 'ri-file-excel-2-line',
                str_contains($categorie, 'word') => 'ri-file-word-2-line',
                str_contains($categorie, 'powerpoint') => 'ri-file-ppt-2-line',
                str_contains($categorie, 'bureautique') => 'ri-computer-line',
                str_contains($categorie, 'web') || str_contains($categorie, 'informatique') || str_contains($categorie, 'développement') => 'ri-code-s-slash-line',
                str_contains($categorie, 'compt') => 'ri-calculator-line',
                str_contains($categorie, 'gestion') => 'ri-briefcase-4-line',
                str_contains($categorie, 'anglais') || str_contains($categorie, 'langue') => 'ri-translate-2',
                default => 'ri-book-open-line',
            };
        };
    @endphp

    <div class="row">
        @forelse($cours as $unCours)
            @php
                $accent = $palette[$loop->index % count($palette)];
                [$accentHex, $accentRgb] = $accent;
            @endphp

            <div class="col-xxl-3 col-md-6 col-sm-6">
                <div class="card course-card card-animate h-100">
                    <div class="course-accent-bar" style="background: {{ $accentHex }};"></div>

                    <div class="card-body d-flex flex-column">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="course-icon-chip" style="background: rgba({{ $accentRgb }}, 0.12); color: {{ $accentHex }};">
                                <i class="{{ $categoryIcon($unCours->categorie ?? null) }}"></i>
                            </span>
                            @if(!empty($unCours->categorie))
                                <span class="badge rounded-pill" style="background: rgba({{ $accentRgb }}, 0.1); color: {{ $accentHex }}; font-size: 0.66rem;">
                                    {{ $unCours->categorie }}
                                </span>
                            @endif
                        </div>

                        <h5 class="course-title mb-1">{{ $unCours->titre }}</h5>
                        <p class="course-description mb-2">
                            {{ $unCours->description ?: 'Description à venir pour cette formation.' }}
                        </p>

                        <div class="mt-auto">
                            <div class="d-flex flex-wrap gap-1 mb-2 course-badges">
                                <span class="badge bg-light text-secondary">
                                    <i class="ri-calendar-2-line me-1"></i>{{ $unCours->classes_count }} classe(s)
                                </span>
                                <span class="badge bg-light text-secondary">
                                    <i class="ri-question-line me-1"></i>{{ $unCours->quizzes_count }} quiz
                                </span>
                            </div>

                            <div class="course-price-tag fw-bold text-primary mb-2">
                                @if($role === 'entreprise')
                                    {{ number_format($unCours->prix_entreprise, 0, ',', ' ') }} fcfa
                                @else
                                    {{ number_format($unCours->prix_particulier, 0, ',', ' ') }} fcfa
                                @endif
                            </div>

                            <a href="{{ route('cours.show', $unCours) }}" class="btn btn-outline-primary btn-sm w-100 d-flex align-items-center justify-content-center gap-1">
                                Voir le détail <i class="ri-arrow-right-line"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="ri-inbox-line text-muted mb-3" style="font-size: 48px;"></i>
                    <p class="text-muted">Aucun cours disponible pour le moment.</p>
                </div>
            </div>
        @endforelse
    </div>

    @guest
        </div>
    @endguest
@endsection