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
@endphp

<div class="nv-home">

{{-- ==================== NAVIGATION ==================== --}}
<nav class="navbar navbar-expand-lg sticky-top nv-navbar">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/') }}">
            <img src="{{ asset('images/logo_neovision.png') }}" alt="NEO VISION" height="34">
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nvMenu"
                aria-controls="nvMenu" aria-expanded="false" aria-label="Ouvrir le menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="nvMenu">
            <ul class="navbar-nav nav nav-pills mx-lg-4 gap-lg-1">
                <li class="nav-item"><a class="nav-link active" href="#accueil">Accueil</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('cours.catalogue') }}">Formations</a></li>
                <li class="nav-item"><a class="nav-link" href="#calendrier">Classes</a></li>
                <li class="nav-item"><a class="nav-link" href="#pourquoi">À propos</a></li>
            </ul>

            {{-- Connexion / inscription --}}
            <div class="d-flex align-items-center gap-2 ms-lg-auto pb-3 pb-lg-0">
                <span class="nv-user-icon"><i class="ri-user-3-fill"></i></span>
                <div class="lh-sm">
                    <a href="{{ route('login') }}" class="fw-semibold d-block nv-user-link">Connexion</a>
                    <small><a href="{{ route('register') }}" class="text-muted">ou Inscription</a></small>
                </div>
            </div>
        </div>
    </div>
</nav>

{{-- ==================== BANNIÈRE (HERO) ==================== --}}
<header class="nv-hero" id="accueil">
    {{-- Image de fond + voile bleu pour que le texte reste lisible --}}
    <img src="{{ asset('build/images/auth-one-bg.jpg') }}" alt="" class="nv-hero-bg">
    <div class="nv-hero-voile"></div>

    <div class="container nv-hero-contenu">
        <h1 class="fw-bold mb-2">
            Développez vos compétences, construisez votre avenir
        </h1>
        <hr class="nv-underline">

        <p class="mb-3 col-lg-7 px-0">
            NEO VISION vous accompagne dans votre montée en compétences avec des formations modernes,
            pratiques et adaptées à vos objectifs.
        </p>

        {{-- Chiffres clés (réels), en pastilles compactes --}}
        <div class="d-flex flex-wrap gap-2">
            @foreach([
                ['ri-book-open-line',   $nbCours,     'formations'],
                ['ri-folder-open-line', $nbDomaines,  'domaines'],
                ['ri-calendar-2-line',  $nbClasses,   'classes'],
                ['ri-article-line',     $nbChapitres, 'chapitres'],
            ] as [$icone, $valeur, $libelle])
                <span class="nv-hero-stat">
                    <i class="{{ $icone }}"></i>
                    <strong>{{ number_format($valeur, 0, ',', ' ') }}</strong> {{ $libelle }}
                </span>
            @endforeach
        </div>
    </div>
</header>

{{-- ==================== APERÇU DE LA PLATEFORME (tuiles-images qui chevauchent la bannière) ==================== --}}
<section class="nv-apercu">
    <div class="container">
        <div class="row g-3">
            @foreach(['blog/img-3.jpg', 'blog/img-5.jpg', 'blog/img-6.jpg', 'blog/img-08.jpg'] as $image)
                <div class="col-6 col-lg-3">
                    <div class="nv-tuile">
                        <img src="{{ asset('build/images/' . $image) }}" alt="">
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ==================== FORMATIONS LES MIEUX NOTÉES ==================== --}}
<section class="py-4" id="formations">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-2 mb-3">
            <div>
                <hr class="nv-bar">
                <h2 class="fw-bold mb-1">Nos formations les mieux notées</h2>
                <p class="text-muted small mb-0">Les formations préférées de nos stagiaires, d'après leurs avis.</p>
            </div>
            <a href="{{ route('cours.catalogue') }}" class="fw-semibold small text-nowrap">
                Voir toutes les formations <i class="ri-arrow-right-line ms-1"></i>
            </a>
        </div>

        <div class="row g-3">
            @forelse($coursPopulaires as $c)
                <div class="col-lg-4 col-md-6">
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

                        {{-- Bandeau fin : image du cours ou icône --}}
                        @if(!empty($c->image))
                            <div class="ratio ratio-21x9">
                                <img src="{{ asset('storage/' . $c->image) }}" alt="{{ $c->titre }}" class="object-fit-cover">
                            </div>
                        @else
                            <div class="ratio ratio-21x9 bg-primary-subtle">
                                <div class="d-flex align-items-center justify-content-center">
                                    <span class="avatar-title rounded-3 bg-white text-primary fs-2 shadow-sm nv-cours-icone">
                                        <i class="{{ $icon }}"></i>
                                    </span>
                                </div>
                            </div>
                        @endif

                        <div class="card-body p-3 d-flex flex-column">
                            @if(!empty($c->categorie))
                                <span class="badge bg-primary-subtle text-primary text-uppercase align-self-start mb-2">
                                    {{ $c->categorie }}
                                </span>
                            @endif

                            <h6 class="fw-bold mb-1">{{ $c->titre }}</h6>

                            {{-- Note moyenne des avis --}}
                            <div class="d-flex align-items-center gap-1 small mb-2">
                                @include('cours.partials.etoiles', ['note' => $c->avis_avg_note])
                                <span class="fw-semibold">{{ number_format($c->avis_avg_note, 1, ',', '') }}</span>
                                <span class="text-muted">({{ $c->avis_count }} avis)</span>
                            </div>

                            <p class="text-muted small mb-0">
                                {{ \Illuminate\Support\Str::limit($c->description ?: 'Description à venir pour cette formation.', 80) }}
                            </p>

                            <div class="d-flex gap-3 text-muted small border-top py-2 mt-2">
                                <span><i class="ri-book-open-line me-1"></i>{{ $c->chapitres_count }} chapitre(s)</span>
                                <span><i class="ri-calendar-2-line me-1"></i>{{ $c->classes_count }} classe(s)</span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center gap-2 mt-auto">
                                <div class="lh-sm">
                                    <small class="text-muted d-block">À partir de</small>
                                    <span class="fw-bold text-danger">
                                        {{ number_format($c->prix_particulier, 0, ',', ' ') }} FCFA
                                    </span>
                                </div>
                                <a href="{{ route('cours.show', $c) }}" class="btn btn-primary btn-sm fw-semibold text-nowrap">
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

{{-- ==================== CALENDRIER DES CLASSES ==================== --}}
<section class="pb-4" id="calendrier">
    <div class="container">

        <div class="text-center mb-3">
            <hr class="nv-bar mx-auto">
            <h2 class="fw-bold mb-1">Les prochaines classes</h2>
            <p class="text-muted small mb-0 mx-auto nv-texte-centre">
                Repérez en un coup d'œil les sessions de formation : chaque classe est marquée le jour où elle
                <strong>commence</strong> et le jour où elle <strong>se termine</strong>. Cliquez sur une classe pour
                découvrir la formation et vous inscrire.
            </p>
        </div>

        <div class="row justify-content-center">
            <div class="col-xl-8 col-lg-10">
                @include('partials.calendrier-classes', [
                    'evenements' => $evenementsCalendrier,
                    'titre' => 'Calendrier des classes',
                    'legende' => [
                        ['nv-cal-a-venir', 'Classe à venir (pas encore commencée)'],
                        ['nv-cal-en-cours', 'Classe en cours (déjà démarrée)'],
                    ],
                    'aide' => 'Chaque classe apparaît deux fois : « Début » le premier jour et « Fin » le dernier. La période entre les deux est surlignée en couleur claire.',
                ])
            </div>
        </div>
    </div>
</section>

{{-- ==================== POURQUOI CHOISIR NEO VISION ==================== --}}
<section class="pb-4" id="pourquoi">
    <div class="container">
        <div class="nv-why rounded-4 p-4">
            <div class="row align-items-center g-4">

                <div class="col-lg-8">
                    <hr class="nv-bar">
                    <h2 class="fw-bold mb-1">Pourquoi choisir <span class="text-primary">NEO VISION</span> ?</h2>
                    <p class="text-muted small mb-3">
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
                    <div class="nv-cta-card rounded-4 p-3 p-lg-4 text-white">
                        <i class="ri-graduation-cap-fill fs-2"></i>
                        <h5 class="fw-bold text-white mt-2">Prêt à booster votre carrière ?</h5>
                        <p class="small opacity-75">Rejoignez NEO VISION dès aujourd'hui et accédez à des formations de qualité.</p>
                        <a href="{{ route('register') }}" class="nv-cta-btn">
                            <span>Créer un compte</span>
                            <span class="nv-arrow-btn"><i class="ri-arrow-right-line"></i></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== Parcours du particulier : 4 étapes + le quiz, facultatif et répétable ===== --}}
        <div class="nv-parcours rounded-4 p-4 mt-3">
            <hr class="nv-bar">
            <h2 class="fw-bold mb-1">Votre parcours <span class="text-primary">particulier</span></h2>
            <p class="text-muted small mb-4">De la découverte du catalogue à l'accès à votre formation, en 4 étapes simples.</p>

            <div class="row g-4">
                @foreach([
                    ['ri-search-eye-line',     'Visiter le catalogue',     'Parcourez toutes nos formations.'],
                    ['ri-book-open-line',      'Choisir un cours',         'Comparez les contenus, les avis et les prix.'],
                    ['ri-calendar-check-line', "S'inscrire à une classe",  'Choisissez la session qui vous convient.'],
                    ['ri-folder-open-line',    'Accéder aux contenus',     'Chapitres et ressources de votre formation.'],
                ] as [$icone, $titre, $texte])
                    <div class="col-6 col-md-4 col-lg">
                        <div class="nv-etape">
                            <span class="nv-etape-numero">{{ $loop->iteration }}</span>
                            <span class="nv-icon-circle bg-primary mb-2"><i class="{{ $icone }}"></i></span>
                            <div class="fw-semibold small mb-1">{{ $titre }}</div>
                            <small class="text-muted">{{ $texte }}</small>
                        </div>
                    </div>
                @endforeach

                {{-- Étape 5 : le quiz, en pointillés pour montrer qu'elle est facultative et répétable --}}
                <div class="col-6 col-md-4 col-lg">
                    <div class="nv-etape nv-etape-facultative">
                        <span class="nv-etape-numero">5</span>
                        <span class="nv-etape-boucle" title="À refaire autant de fois que vous le souhaitez">
                            <i class="ri-repeat-2-line"></i>
                        </span>
                        <span class="nv-icon-circle bg-danger mb-2"><i class="ri-questionnaire-line"></i></span>
                        <div class="fw-semibold small mb-1">Passer le quiz</div>
                        <small class="text-muted d-block mb-2">Testez vos connaissances.</small>
                        <span class="badge rounded-pill bg-danger-subtle text-danger">Facultatif</span>
                    </div>
                </div>
            </div>

            <p class="small text-muted text-center mt-3 mb-0">
                <i class="ri-repeat-2-line text-danger me-1"></i>
                L'étape 5 est facultative : vous pouvez la passer, la sauter ou la recommencer à tout moment de votre formation.
            </p>
        </div>
    </div>
</section>

{{-- ==================== PIED DE PAGE ==================== --}}
<footer class="nv-footer py-3">
    <div class="container d-flex flex-column flex-md-row justify-content-between gap-3">
        <div>
            <img src="{{ asset('images/logo_neovision.png') }}" alt="NEO VISION" height="28" class="mb-2 d-block">
            <small class="text-muted">&copy; {{ date('Y') }} NEO VISION. Tous droits réservés.</small>
        </div>
        <div class="d-flex flex-wrap gap-3 align-items-center small">
            <a href="{{ route('cours.catalogue') }}">Formations</a>
            <a href="#pourquoi">À propos</a>
            <a href="{{ route('login') }}">Connexion</a>
            <a href="{{ route('register') }}">Inscription</a>
        </div>
    </div>
</footer>

</div>{{-- /.nv-home --}}
@endsection