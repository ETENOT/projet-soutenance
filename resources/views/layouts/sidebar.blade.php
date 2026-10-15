<!-- ========== App Menu ========== -->
<div class="app-menu navbar-menu">

    <!-- LOGO -->
    <div class="navbar-brand-box">

        <a href="{{ route('dashboard') }}" class="logo logo-dark">
            <span class="logo-sm">
                <img
                    src="{{ ('images/logo_neovision_white.JPG') }}"
                    alt="NEO VISION"
                    height="22"
                >
            </span>

            <span class="logo-lg">
                <img
                    src="{{ ('images/logo_neovision_white.JPG') }}"
                    alt="NEO VISION"
                    height="30"
                >
            </span>
        </a>

        <a href="{{ route('dashboard') }}" class="logo logo-light">
            <span class="logo-sm">
                <img
                    src="{{ ('build/images/logo_neovision_white.JPG') }}"
                    alt="NEO VISION"
                    height="22"
                >
            </span>

            <span class="logo-lg">
                <img
                    src="{{ ('images/logo_neovision_white.JPG') }}"
                    alt="NEO VISION"
                    height="30"
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
                            class="nav-link menu-link"
                            href="{{ route('dashboard') }}"
                        >
                            <i class="ri-dashboard-2-line"></i>
                            <span>Tableau de bord</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link menu-link" href="{{ route('forum.index') }}">
                            <i class="ri-chat-3-line"></i>
                            <span>Forums</span>
                            {{-- Badge rouge : caché quand il n'y a rien de nouveau ; mis à jour par le script en bas --}}
                            <span class="badge badge-pill bg-danger js-forum-badge {{ $forumsNonLus > 0 ? '' : 'd-none' }}">{{ $forumsNonLus }}</span>
                        </a>
                    </li>

                    {{-- ================================
                         UTILISATEURS
                    ================================= --}}

                    <li class="nav-item">
                        <a
                            class="nav-link menu-link"
                            href="{{ route('admin.users.index') }}"
                        >
                            <i class="ri-user-line"></i>
                            <span>Gestion des utilisateurs</span>
                        </a>
                    </li>



                    {{-- ================================
                         FORMATIONS
                    ================================= --}}

                    <li class="nav-item">

                        <a
                            class="nav-link menu-link"
                            href="{{ route('admin.cours.index') }}"
                        >
                            <i class="ri-book-open-line"></i>
                            <span>Gestion des Formations</span>
                        </a>

                    </li>


                    {{-- ================================
                         CLASSES
                    ================================= --}}

                    <li class="nav-item">

                        <a
                            class="nav-link menu-link"
                            href="{{ route('admin.classes.index') }}"
                        >
                            <i class="ri-group-line"></i>
                            <span>Gestion des Classes</span>
                        </a>

                    </li>  

                                        {{-- ================================
                         AVIS SUR LES FORMATEURS
                    ================================= --}}

                                        {{-- ================================
                         ÉVALUATIONS (formateurs + formations)
                    ================================= --}}

                    <li class="nav-item">
                        <a class="nav-link menu-link" href="{{ route('admin.evaluations.index') }}">
                            <i class="ri-star-smile-line"></i>
                            <span>Évaluations</span>
                        </a>
                    </li>


                    {{-- ================================
                         QUIZ
                    ================================= --}}

                    <li class="nav-item">

                        <a
                            class="nav-link menu-link"
                            href="#sidebarQuiz"
                            data-bs-toggle="collapse"
                            role="button"
                            aria-expanded="false"
                            aria-controls="sidebarQuiz"
                        >
                            <i class="ri-question-line"></i>
                            <span>Gestion des Quiz</span>
                        </a>
                        <div
                                class="collapse menu-dropdown"
                                id="sidebarQuiz"
                            >
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item">
                                    <a class="nav-link menu-link" href="{{ route('admin.questions.index') }}">
                                        <span>Banque de questions</span>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link menu-link" href="{{ route('admin.quiz.index') }}">
                                        <span>Suivi des quiz</span>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link menu-link" href="{{ route('admin.quiz.reglages') }}">
                                        <span>Réglages des notations</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    {{-- ================================
                         FINANCES
                    ================================= --}}

                    <li class="nav-item">

                        <a
                            class="nav-link menu-link"
                            href="#"
                            style="pointer-events: none; opacity: 0.6;"
                        >
                            <i class="ri-file-list-3-line"></i>
                            <span>Inscriptions</span>
                        </a>

                    </li>

                    <li class="nav-item">

                        <a
                            class="nav-link menu-link"
                            href="#"
                            style="pointer-events: none; opacity: 0.6;"
                        >
                            <i class="ri-bank-card-line"></i>
                            <span>Paiements</span>
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
                        <a class="nav-link menu-link" href="{{ route('dashboard') }}">
                            <i class="ri-dashboard-2-line"></i>
                            <span>Tableau de bord</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link menu-link" href="{{ route('classes.formateur.index') }}">
                            <i class="ri-group-line"></i>
                            <span>Mes classes</span>
                        </a>
                    </li>
                    //Résultat et correction des quiz:la page liste mes classes, puis chaque classe mène à ses résultats 
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="{{ route('quiz.formateur.classes') }}">
                            <i class="ri-questionnaire-line"></i>
                            <span>Quiz des stagiaires</span>
                        </a>
                    </li>

                                        <li class="nav-item">
                        <a class="nav-link menu-link" href="{{ route('avis_formateurs.formateur.index') }}">
                            <i class="ri-user-star-line"></i>
                            <span>Avis sur moi</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link menu-link" href="{{ route('forum.index') }}">
                            <i class="ri-chat-3-line"></i>
                            <span>Forums</span>
                            {{-- Badge rouge : caché quand il n'y a rien de nouveau ; mis à jour par le script en bas --}}
                            <span class="badge badge-pill bg-danger js-forum-badge {{ $forumsNonLus > 0 ? '' : 'd-none' }}">{{ $forumsNonLus }}</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link menu-link" href="{{ route('index', ['any' => 'pages-profile']) }}">
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
                            class="nav-link menu-link"
                            href="{{ route('dashboard') }}"
                        >
                            <i class="ri-dashboard-2-line"></i>
                            <span>Tableau de bord</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a
                            class="nav-link menu-link"
                            href="{{ route('cours.catalogue') }}"
                        >
                            <i class="ri-book-open-line"></i>
                            <span>Catalogue des cours</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a
                            class="nav-link menu-link"
                            href="{{ route('cours.mes') }}"
                        >
                            <i class="ri-graduation-cap-line"></i>
                            <span>Mes classes</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link menu-link" href="{{ route('forum.index') }}">
                            <i class="ri-chat-3-line"></i>
                            <span>Forums</span>
                            {{-- Badge rouge : caché quand il n'y a rien de nouveau ; mis à jour par le script en bas --}}
                            <span class="badge badge-pill bg-danger js-forum-badge {{ $forumsNonLus > 0 ? '' : 'd-none' }}">{{ $forumsNonLus }}</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a
                            class="nav-link menu-link"
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
@auth
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const badges = document.querySelectorAll('.js-forum-badge');
        if (!badges.length) return;

        async function majBadge() {
            try {
                const r = await fetch(@json(route('forum.non-lus')), { headers: { 'Accept': 'application/json' } });
                if (!r.ok) return;
                const { nombre } = await r.json();
                // On affiche le nombre, et on cache le badge quand il vaut 0
                badges.forEach(b => {
                    b.textContent = nombre;
                    b.classList.toggle('d-none', nombre === 0);
                });
            } catch (e) { /* réseau coupé : on réessaiera au prochain tour */ }
        }

        setInterval(majBadge, 30000);
    });
</script>
@endauth

<!-- Vertical Overlay -->
<div class="vertical-overlay"></div>