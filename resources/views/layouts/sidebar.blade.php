<!-- ========== App Menu ========== -->
<div class="app-menu navbar-menu">

    <!-- LOGO -->
    <div class="navbar-brand-box">

        <a href="{{ route('dashboard') }}" class="logo logo-dark">
            <span class="logo-sm">
                <img
                    src="{{ asset('images/logo-nv-transparent.png') }}"
                    alt="NEO VISION"
                    height="26"
                >
            </span>

            <span class="logo-lg">
                <img
                    src="{{ asset('images/logo-nv-transparent.png') }}"
                    alt="NEO VISION"
                    height="42"
                >
            </span>
        </a>

        <a href="{{ route('dashboard') }}" class="logo logo-light">
            <span class="logo-sm">
                <img
                    src="{{ asset('images/logo-nv-transparent.png') }}"
                    alt="NEO VISION"
                    height="26"
                >
            </span>

            <span class="logo-lg">
                <img
                    src="{{ asset('images/logo-nv-transparent.png') }}"
                    alt="NEO VISION"
                    height="42"
                >
            </span>
        </a>

        <button
            type="button"
            class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover"
            id="vertical-hover"
        >
            <i class="ri-record-circle-line"></i>
        </button>

    </div>

    <!-- SIDEBAR -->
    <div id="scrollbar">

        <div class="container-fluid">

            <div id="two-column-menu"></div>

            <ul class="navbar-nav" id="navbar-nav">

                {{-- Nombre de forums contenant de nouveaux messages (affiché en badge sur "Forums") --}}
                @php
                    $forumsNonLus = Auth::check() ? \App\Models\MessageForum::nombreForumsNonLus(Auth::user()) : 0;
                @endphp

                @if (Auth::user()?->role?->nom === 'admin')

                    {{-- ================================
                         TABLEAU DE BORD
                    ================================= --}}

                    <li class="nav-item">
                        <a
                            class="nav-link menu-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                            href="{{ route('dashboard') }}"
                        >
                            <i class="ri-dashboard-2-line"></i>
                            <span>Tableau de bord</span>
                        </a>
                    </li>

                    {{-- ================================
                         UTILISATEURS
                    ================================= --}}

                    <li class="nav-item">
                        <a
                            class="nav-link menu-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
                            href="{{ route('admin.users.index') }}"
                        >
                            <i class="ri-user-line"></i>
                            <span>Gestion des utilisateurs</span>
                        </a>
                    </li>

                    {{-- ================================
                         ENTREPRISES
                    ================================= --}}
                    <li class="nav-item">
                        <a class="nav-link menu-link {{ request()->routeIs('admin.entreprises.*') ? 'active' : '' }}"
                        href="{{ route('admin.entreprises.index') }}">
                            <i class="ri-building-2-line"></i>
                            <span>Gestion des entreprises</span>
                        </a>
                    </li>

                     {{-- ================================
                         FORMATEURS (CRUD, affectations, avis anonymes)
                    ================================= --}}

                    <li class="nav-item">
                        <a class="nav-link menu-link {{ request()->routeIs('admin.formateurs.*') ? 'active' : '' }}"
                        href="{{ route('admin.formateurs.index') }}">
                            <i class="ri-user-star-line"></i>
                            <span>Gestion des formateurs</span>
                        </a>
                    </li>


                    {{-- ================================
                         COURS
                    ================================= --}}

                    <li class="nav-item">

                        <a
                            class="nav-link menu-link {{ request()->routeIs('admin.cours.*') ? 'active' : '' }}"
                            href="{{ route('admin.cours.index') }}"
                        >
                            <i class="ri-book-open-line"></i>
                            <span>Gestion des cours</span>
                        </a>

                    </li>

                    <li class="nav-item">
                        <a class="nav-link menu-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}"
                           href="{{ route('admin.categories.index') }}">
                            <i class="ri-price-tag-3-line"></i>
                            <span>Catégories</span>
                        </a>
                    </li>

                    {{-- ================================
                         CLASSES
                    ================================= --}}

                    <li class="nav-item">
                        <a
                            class="nav-link menu-link {{ request()->routeIs('admin.classes.*') ? 'active' : '' }}"
                            href="{{ route('admin.classes.index') }}"
                        >
                            <i class="ri-group-line"></i>
                            <span>Gestion des Classes</span>
                        </a>
                    </li>

                    {{-- ================================
                        INSCRIPTIONS
                    ================================= --}}

                    <li class="nav-item">
                        <a
                            class="nav-link menu-link {{ request()->routeIs('admin.inscriptions.*') ? 'active' : '' }}"
                            href="{{ route('admin.inscriptions.index') }}"
                        >
                            <i class="ri-user-add-line"></i>
                            <span>Gestion des inscriptions</span>
                        </a>
                    </li>

                    {{-- ================================
                        ATTESTATIONS (toutes celles délivrées, les plus récentes d'abord)
                    ================================= --}}
                    <li class="nav-item">
                        <a class="nav-link menu-link {{ request()->routeIs('admin.attestations.*') ? 'active' : '' }}"
                        href="{{ route('admin.attestations.index') }}">
                            <i class="ri-award-line"></i>
                            <span>Attestations</span>
                        </a>
                    </li>

                    {{-- ================================
                         QUIZ
                    ================================= --}}

                    {{-- "Suivi des quiz" exclut "reglages" : les deux routes commencent par admin.quiz. --}}
                    @php
                        $quizReglages = request()->routeIs('admin.quiz.reglages');
                        $quizSuivi = request()->routeIs('admin.quiz.*') && ! $quizReglages;
                        $quizBanque = request()->routeIs('admin.questions.*');
                        $quizOuvert = $quizSuivi || $quizReglages || $quizBanque;
                    @endphp

                    <li class="nav-item">

                        <a
                            class="nav-link menu-link {{ $quizOuvert ? 'active' : '' }}"
                            href="#sidebarQuiz"
                            data-bs-toggle="collapse"
                            role="button"
                            aria-expanded="{{ $quizOuvert ? 'true' : 'false' }}"
                            aria-controls="sidebarQuiz"
                        >
                            <i class="ri-question-line"></i>
                            <span>Gestion des Quiz</span>
                        </a>
                        <div
                                class="collapse menu-dropdown {{ $quizOuvert ? 'show' : '' }}"
                                id="sidebarQuiz"
                            >
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item">
                                    <a class="nav-link menu-link {{ $quizBanque ? 'active' : '' }}" href="{{ route('admin.questions.index') }}">
                                        <span>Banque de questions</span>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link menu-link {{ $quizSuivi ? 'active' : '' }}" href="{{ route('admin.quiz.index') }}">
                                        <span>Suivi des quiz</span>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link menu-link {{ $quizReglages ? 'active' : '' }}" href="{{ route('admin.quiz.reglages') }}">
                                        <span>Réglages des notations</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    {{-- ================================
                         ÉVALUATIONS (formateurs + cours)
                    ================================= --}}

                    <li class="nav-item">
                        <a class="nav-link menu-link {{ request()->routeIs('admin.evaluations.*') ? 'active' : '' }}" href="{{ route('admin.evaluations.index') }}">
                            <i class="ri-star-smile-line"></i>
                            <span>Évaluations</span>
                        </a>
                    </li>

                    {{-- ================================
                         FORUMS
                    ================================= --}}

                    <li class="nav-item">
                        <a class="nav-link menu-link {{ request()->routeIs('forum.*') ? 'active' : '' }}" href="{{ route('forum.index') }}">
                            <i class="ri-chat-3-line"></i>
                            <span>Forums</span>
                            {{-- Badge rouge : caché quand il n'y a rien de nouveau ; mis à jour par le script en bas --}}
                            <span class="badge badge-pill bg-danger js-forum-badge {{ $forumsNonLus > 0 ? '' : 'd-none' }}">{{ $forumsNonLus }}</span>
                        </a>
                    </li>

                @elseif (Auth::user()?->role?->nom === 'formateur')

                    {{-- ================================
                         ESPACE FORMATEUR
                    ================================= --}}

                    <li class="menu-title">
                        <span>ESPACE FORMATEUR</span>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link menu-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                            <i class="ri-dashboard-2-line"></i>
                            <span>Tableau de bord</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link menu-link {{ request()->routeIs('classes.formateur.*') ? 'active' : '' }}" href="{{ route('classes.formateur.index') }}">
                            <i class="ri-group-line"></i>
                            <span>Mes classes</span>
                        </a>
                    </li>
                    <!-- Résultat et correction des quiz:la page liste mes classes, puis chaque classe mène à ses résultats -->
                    <li class="nav-item">
                        <a class="nav-link menu-link {{ request()->routeIs('quiz.formateur.*') ? 'active' : '' }}" href="{{ route('quiz.formateur.classes') }}">
                            <i class="ri-questionnaire-line"></i>
                            <span>Quiz des stagiaires</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link menu-link {{ request()->routeIs('avis_formateurs.formateur.*') ? 'active' : '' }}" href="{{ route('avis_formateurs.formateur.index') }}">
                            <i class="ri-user-star-line"></i>
                            <span>Avis sur moi</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link menu-link {{ request()->routeIs('forum.*') ? 'active' : '' }}" href="{{ route('forum.index') }}">
                            <i class="ri-chat-3-line"></i>
                            <span>Forums</span>
                            {{-- Badge rouge : caché quand il n'y a rien de nouveau ; mis à jour par le script en bas --}}
                            <span class="badge badge-pill bg-danger js-forum-badge {{ $forumsNonLus > 0 ? '' : 'd-none' }}">{{ $forumsNonLus }}</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link menu-link {{ request()->is('pages-profile') ? 'active' : '' }}" href="{{ route('index', ['any' => 'pages-profile']) }}">
                            <i class="ri-user-line"></i>
                            <span>Mon profil</span>
                        </a>
                    </li>
                @else

                    {{-- ================================
                         ESPACE UTILISATEUR
                    ================================= --}}

                    <li class="menu-title">
                        <span>MENU</span>
                    </li>

                    <li class="nav-item">
                        <a
                            class="nav-link menu-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                            href="{{ route('dashboard') }}"
                        >
                            <i class="ri-dashboard-2-line"></i>
                            <span>Tableau de bord</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a
                            class="nav-link menu-link {{ request()->routeIs('cours.catalogue') ? 'active' : '' }}"
                            href="{{ route('cours.catalogue') }}"
                        >
                            <i class="ri-book-open-line"></i>
                            <span>Catalogue des cours</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a
                            class="nav-link menu-link {{ request()->routeIs('cours.mes') ? 'active' : '' }}"
                            href="{{ route('cours.mes') }}"
                        >
                            <i class="ri-graduation-cap-line"></i>
                            <span>Mes classes</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link menu-link {{ request()->routeIs('forum.*') ? 'active' : '' }}" href="{{ route('forum.index') }}">
                            <i class="ri-chat-3-line"></i>
                            <span>Forums</span>
                            {{-- Badge rouge : caché quand il n'y a rien de nouveau ; mis à jour par le script en bas --}}
                            <span class="badge badge-pill bg-danger js-forum-badge {{ $forumsNonLus > 0 ? '' : 'd-none' }}">{{ $forumsNonLus }}</span>
                        </a>
                    </li>

                    {{-- ENTREPRISE : consulter ses membres (gérés par l'administrateur) --}}
                    @if (Auth::user()?->role?->nom === 'entreprise')
                        <li class="nav-item">
                            <a class="nav-link menu-link {{ request()->routeIs('entreprise.membres.*') ? 'active' : '' }}"
                            href="{{ route('entreprise.membres.index') }}">
                                <i class="ri-team-line"></i>
                                <span>Membres entreprises</span>
                            </a>
                        </li>
                    @endif

                    {{-- PARTICULIER : voir l'entreprise dont il est membre --}}
                    @if (Auth::user()?->role?->nom === 'particulier')
                        <li class="nav-item">
                            <a class="nav-link menu-link {{ request()->routeIs('particulier.entreprise.*') ? 'active' : '' }}"
                            href="{{ route('particulier.entreprise.index') }}">
                                <i class="ri-building-line"></i>
                                <span>Entreprise</span>
                            </a>
                        </li>
                    @endif


                    {{-- ENTREPRISE : consulter ses membres (gérés par l'administrateur) --}}
                    @if (Auth::user()?->role?->nom === 'entreprise')
                        <li class="nav-item">
                            <a class="nav-link menu-link {{ request()->routeIs('entreprise.membres.*') ? 'active' : '' }}"
                            href="{{ route('entreprise.membres.index') }}">
                                <i class="ri-team-line"></i>
                                <span>Membres entreprises</span>
                            </a>
                        </li>
                    @endif

                    {{-- PARTICULIER : voir l'entreprise dont il est membre --}}
                    @if (Auth::user()?->role?->nom === 'particulier')
                        <li class="nav-item">
                            <a class="nav-link menu-link {{ request()->routeIs('particulier.entreprise.*') ? 'active' : '' }}"
                            href="{{ route('particulier.entreprise.index') }}">
                                <i class="ri-building-line"></i>
                                <span>Entreprise</span>
                            </a>
                        </li>
                    @endif

                    <li class="nav-item">
                        <a
                            class="nav-link menu-link {{ request()->is('pages-profile') ? 'active' : '' }}"
                            href="{{ route('index', ['any' => 'pages-profile']) }}"
                        >
                            <i class="ri-user-line"></i>
                            <span>Mon profil</span>
                        </a>
                    </li>

                @endif

            </ul>

        </div>

    </div>

    <div class="sidebar-background"></div>

</div>

{{-- Met à jour le badge "Forums" toutes les 30 secondes, sans recharger la page --}}
@if(auth()->check() && auth()->user()->role === 'formateur')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const badges = document.querySelectorAll('.js-forum-badge');
        if (!badges.length) return;

        let timer = null;

        async function majBadge() {
            try {
                const r = await fetch(@json(route('forum.non-lus')), { headers: { 'Accept': 'application/json' } });
                if (r.status === 403 || r.status === 401) { clearInterval(timer); return; }
                if (!r.ok) return;
                const { nombre } = await r.json();
                badges.forEach(b => {
                    b.textContent = nombre;
                    b.classList.toggle('d-none', nombre === 0);
                });
            } catch (e) { /* réseau coupé : on réessaiera au prochain tour */ }
        }

        timer = setInterval(majBadge, 30000);
    });
</script>
@endif

<!-- Vertical Overlay -->
<div class="vertical-overlay"></div>