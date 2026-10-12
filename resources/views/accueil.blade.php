@extends('layouts.master-without-nav')

@section('title', 'Accueil')

{{-- ScrollSpy Bootstrap : l'onglet du menu suit la section visible --}}
@section('body')
<body data-bs-spy="scroll" data-bs-target="#nvMenu" data-bs-root-margin="-90px 0px -55%"
      data-bs-smooth-scroll="true" tabindex="0">
@endsection

@section('content')
@php
    $coursPopulaires->loadCount(['classes', 'chapitres']);
    $nbClasses   = \App\Models\Classe::count();
    $nbChapitres = \App\Models\Chapitre::count();
@endphp

<div class="nv-home">

{{-- ==================== NAVIGATION ==================== --}}
<nav class="navbar navbar-expand-lg sticky-top nv-navbar">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/') }}">
            <img src="{{ asset('images/logo_neovision.png') }}" alt="NEO VISION" height="40">
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nvMenu"
                aria-controls="nvMenu" aria-expanded="false" aria-label="Ouvrir le menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="nvMenu">
            <ul class="navbar-nav nav nav-pills mx-lg-4 gap-lg-1">
                <li class="nav-item"><a class="nav-link active" href="#accueil">Accueil</a></li>
                <li class="nav-item"><a class="nav-link" href="#formations">Formations</a></li>
                <li class="nav-item"><a class="nav-link" href="#pourquoi">À propos</a></li>
            </ul>

            {{-- Recherche compacte dans l'en-tête --}}
            <form action="{{ route('cours.catalogue') }}" method="GET" class="nv-nav-search ms-lg-auto my-3 my-lg-0">
                <i class="ri-search-line"></i>
                <input type="search" name="search" class="form-control" placeholder="Rechercher une formation..."
                       aria-label="Rechercher une formation">
            </form>

            {{-- Connexion / inscription --}}
            <div class="d-flex align-items-center gap-2 ms-lg-4 pb-3 pb-lg-0">
                <span class="nv-user-icon"><i class="ri-user-3-fill"></i></span>
                <div class="lh-sm">
                    <a href="{{ route('login') }}" class="fw-semibold d-block nv-user-link">Connexion</a>
                    <small><a href="{{ route('register') }}" class="text-muted">ou Inscription</a></small>
                </div>
            </div>
        </div>
    </div>
</nav>

{{-- ==================== HERO ==================== --}}
<header class="nv-hero" id="accueil">
    <div class="container">
        <div class="row align-items-center g-4 g-lg-5">

            <div class="col-lg-6">
                <span class="badge rounded-pill px-3 py-2 mb-4 nv-hero-badge">
                    <i class="ri-graduation-cap-fill me-1"></i> Centre de formation professionnelle
                </span>

                <h1 class="fw-bold mb-3">
                    Développez vos compétences,<br>
                    <span class="text-primary">construisez</span> <span class="text-danger">votre avenir</span>
                </h1>
                <hr class="nv-underline">

                <p class="fs-6 text-muted mb-4 col-xl-9 px-0">
                    NEO VISION vous accompagne dans votre montée en compétences avec des formations modernes,
                    pratiques et adaptées à vos objectifs.
                </p>

                <form action="{{ route('cours.catalogue') }}" method="GET" class="input-group nv-search mb-3">
                    <span class="input-group-text"><i class="ri-search-line"></i></span>
                    <input type="search" name="search" class="form-control" value="{{ request('search') }}"
                           placeholder="Rechercher une formation, un cours, une compétence..."
                           aria-label="Rechercher une formation">
                    <button class="btn btn-primary btn-sm px-3" type="submit">Rechercher</button>
                </form>

                @if($categories->isNotEmpty())
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <small class="fw-semibold me-1">Domaines :</small>
                        @foreach($categories->take(4) as $cat)
                            <a href="{{ route('cours.catalogue', ['search' => $cat]) }}"
                               class="badge rounded-pill px-3 py-2 text-decoration-none nv-domain-badge">{{ $cat }}</a>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Visuel : photo + phrase d'accroche --}}
            <div class="col-lg-6 d-none d-lg-block">
                <div class="nv-hero-visual text-center">
                    <p class="nv-tagline mb-0">Votre potentiel<br>n'a pas de limite</p>
                    <img src="{{ asset('build/images/auth-one-bg.jpg') }}"
                    alt="NEO VISION" class="rounded-4 shadow">
                </div>
            </div>
        </div>
    </div>
</header>

{{-- ==================== CHIFFRES (réels) ==================== --}}
<section class="nv-stats">
    <div class="container">
        <div class="card mb-0">
            <div class="card-body p-3 p-lg-4">
                <div class="row g-3">
                    @foreach([
                        ['ri-book-open-line',   'primary', $nbCours,             'Formations disponibles',  'Des parcours adaptés à vos objectifs'],
                        ['ri-folder-open-line', 'danger',  $categories->count(), 'Domaines de formation',   'Explorez de nouvelles opportunités'],
                        ['ri-calendar-2-line',  'primary', $nbClasses,           'Classes programmées',     'Choisissez la session qui vous convient'],
                        ['ri-article-line',     'danger',  $nbChapitres,         'Chapitres pédagogiques',  'Des contenus structurés pour progresser'],
                    ] as [$icone, $couleur, $valeur, $libelle, $sous])
                        <div class="col-6 col-lg-3 nv-stat-item">
                            <div class="d-flex align-items-center gap-3 h-100">
                                <span class="nv-icon-circle bg-{{ $couleur }}"><i class="{{ $icone }}"></i></span>
                                <div>
                                    <h4 class="mb-0 fw-bold">{{ number_format($valeur, 0, ',', ' ') }}</h4>
                                    <div class="fw-semibold small">{{ $libelle }}</div>
                                    <small class="text-muted d-none d-md-block">{{ $sous }}</small>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ==================== FORMATIONS ==================== --}}
<section class="py-5" id="formations">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-3">
            <div>
                <hr class="nv-bar">
                <h2 class="fw-bold mb-1">Nos formations</h2>
                <p class="text-muted mb-0 col-lg-8 px-0">
                    Découvrez nos catégories de formations et trouvez celle qui correspond à vos ambitions.
                </p>
            </div>
            <a href="{{ route('cours.catalogue') }}" class="fw-semibold text-nowrap">
                Voir toutes les formations <i class="ri-arrow-right-line ms-1"></i>
            </a>
        </div>

        @if($categories->isNotEmpty())
            <div class="d-flex flex-wrap gap-2 mb-4">
                <a href="{{ route('cours.catalogue') }}" class="btn btn-sm btn-primary rounded-pill px-3">Toutes</a>
                @foreach($categories->take(6) as $cat)
                    <a href="{{ route('cours.catalogue', ['search' => $cat]) }}"
                       class="btn btn-sm rounded-pill px-3 nv-pill">{{ $cat }}</a>
                @endforeach
            </div>
        @endif

        <div class="row g-4">
            @forelse($coursPopulaires as $c)
                <div class="col-xl-4 col-md-6">
                    @php
                        $icones = ['ri-computer-line', 'ri-code-s-slash-line', 'ri-briefcase-4-line', 'ri-calculator-line', 'ri-book-open-line'];
                        $icon = $icones[$loop->index % count($icones)];
                        $categorie = strtolower((string) $c->categorie);

                        if (str_contains($categorie, 'excel')) {
                            $icon = 'ri-file-excel-2-line';
                        } elseif (str_contains($categorie, 'word')) {
                            $icon = 'ri-file-word-2-line';
                        } elseif (str_contains($categorie, 'powerpoint')) {
                            $icon = 'ri-file-ppt-2-line';
                        } elseif (str_contains($categorie, 'web') || str_contains($categorie, 'développement') || str_contains($categorie, 'informatique')) {
                            $icon = 'ri-code-s-slash-line';
                        } elseif (str_contains($categorie, 'gestion')) {
                            $icon = 'ri-briefcase-4-line';
                        }
                    @endphp

                    <div class="card card-animate h-100 overflow-hidden border shadow-sm mb-0">

                        @if(!empty($c->image))
                            <div class="ratio ratio-16x9">
                                <img src="{{ asset('storage/' . $c->image) }}" alt="{{ $c->titre }}" class="object-fit-cover">
                            </div>
                        @else
                            <div class="ratio ratio-16x9 bg-primary-subtle">
                                <div class="d-flex align-items-center justify-content-center">
                                    <span class="avatar-lg">
                                        <span class="avatar-title rounded-3 bg-white text-primary fs-1 shadow-sm">
                                            <i class="{{ $icon }}"></i>
                                        </span>
                                    </span>
                                </div>
                            </div>
                        @endif

                        <div class="card-body d-flex flex-column">
                            @if(!empty($c->categorie))
                                <span class="badge bg-primary-subtle text-primary text-uppercase align-self-start mb-2">
                                    {{ $c->categorie }}
                                </span>
                            @endif

                            <h5 class="fw-bold mb-2">{{ $c->titre }}</h5>

                            {{-- Note moyenne des avis --}}
                            <div class="d-flex align-items-center gap-1 small mb-2">
                                @include('cours.partials.etoiles', ['note' => $c->avis_avg_note])
                                <span class="fw-semibold">{{ number_format($c->avis_avg_note, 1, ',', '') }}</span>
                                <span class="text-muted">({{ $c->avis_count }} avis)</span>
                            </div>

                            <p class="text-muted small mb-0">
                                {{ \Illuminate\Support\Str::limit($c->description ?: 'Description à venir pour cette formation.', 100) }}
                            </p>

                            <div class="d-flex gap-4 text-muted small border-top border-bottom py-2 my-3">
                                <span><i class="ri-book-open-line me-1"></i>{{ $c->chapitres_count }} chapitre(s)</span>
                                <span><i class="ri-calendar-2-line me-1"></i>{{ $c->classes_count }} classe(s)</span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center gap-2 mt-auto">
                                <div>
                                    <small class="text-muted d-block">À partir de</small>
                                    <span class="fs-5 fw-bold text-danger">
                                        {{ number_format($c->prix_particulier, 0, ',', ' ') }} FCFA
                                    </span>
                                </div>
                                <a href="{{ route('cours.show', $c) }}" class="btn btn-primary fw-semibold text-nowrap">
                                    Découvrir <i class="ri-arrow-right-line ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-light border text-center mb-0">
                        <i class="ri-star-line fs-2 text-primary d-block mb-2"></i>
                        Les formations les mieux notées apparaîtront ici dès les premiers avis.
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</section>

{{-- ==================== POURQUOI CHOISIR NEO VISION ==================== --}}
<section class="pb-5" id="pourquoi">
    <div class="container">
        <div class="nv-why rounded-4 p-4 p-lg-5">
            <div class="row align-items-center g-4">

                <div class="col-lg-2 d-none d-lg-block">
                    <div class="nv-why-art"><i class="ri-graduation-cap-fill"></i></div>
                </div>

                <div class="col-lg-6">
                    <hr class="nv-bar">
                    <h2 class="fw-bold mb-1">Pourquoi choisir <span class="text-primary">NEO VISION</span> ?</h2>
                    <p class="text-muted mb-4">
                        Une plateforme pensée pour votre réussite, que vous soyez particulier ou entreprise.
                    </p>

                    <div class="row g-3">
                        @foreach([
                            ['ri-tools-line',         'danger',  'Formations pratiques',    'Des contenus concrets, à appliquer tout de suite.'],
                            ['ri-user-star-line',     'primary', 'Formateurs accrédités',   'Des intervenants qualifiés pour chaque domaine.'],
                            ['ri-questionnaire-line', 'danger',  'Quiz et suivi',           'Testez votre niveau et suivez votre progression.'],
                            ['ri-building-line',      'primary', 'En centre ou en entreprise', 'Un tarif particulier ou entreprise adapté.'],
                        ] as [$icone, $couleur, $titre, $texte])
                            <div class="col-6 col-md-3 nv-why-item">
                                <span class="nv-icon-circle nv-icon-soft text-{{ $couleur }} bg-{{ $couleur }}-subtle mb-2">
                                    <i class="{{ $icone }}"></i>
                                </span>
                                <div class="fw-semibold small mb-1">{{ $titre }}</div>
                                <small class="text-muted">{{ $texte }}</small>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="nv-cta-card rounded-4 p-4 text-white">
                        <i class="ri-graduation-cap-fill fs-1"></i>
                        <h4 class="fw-bold text-white mt-2">Prêt à booster votre carrière ?</h4>
                        <p class="small opacity-75">Rejoignez NEO VISION dès aujourd'hui et accédez à des formations de qualité.</p>
                        <a href="{{ route('register') }}" class="nv-cta-btn">
                            <span>Créer un compte</span>
                            <span class="nv-arrow-btn"><i class="ri-arrow-right-line"></i></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ==================== PIED DE PAGE ==================== --}}
<footer class="nv-footer py-4">
    <div class="container d-flex flex-column flex-md-row justify-content-between gap-3">
        <div>
            <img src="{{ asset('images/logo_neovision.png') }}" alt="NEO VISION" height="34" class="mb-2 d-block">
            <small class="text-muted">&copy; {{ date('Y') }} NEO VISION. Tous droits réservés.</small>
        </div>
        <div class="d-flex flex-wrap gap-3 align-items-center">
            <a href="{{ route('cours.catalogue') }}">Formations</a>
            <a href="#pourquoi">À propos</a>
            <a href="{{ route('login') }}">Connexion</a>
            <a href="{{ route('register') }}">Inscription</a>
        </div>
    </div>
</footer>

</div>{{-- /.nv-home --}}
@endsection