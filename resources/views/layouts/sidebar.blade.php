<!-- ========== App Menu ========== -->
<div class="app-menu navbar-menu">

    <!-- LOGO -->
    <div class="navbar-brand-box">

        <a href="{{ route('dashboard') }}" class="logo logo-dark">
            <span class="logo-sm">
                <img
                    src="{{ URL::asset('build/images/logo_neovision.png') }}"
                    alt="NEO VISION"
                    height="22"
                >
            </span>

            <span class="logo-lg">
                <img
                    src="{{ URL::asset('build/images/logo_neovision.png') }}"
                    alt="NEO VISION"
                    height="30"
                >
            </span>
        </a>

        <a href="{{ route('dashboard') }}" class="logo logo-light">
            <span class="logo-sm">
                <img
                    src="{{ URL::asset('build/images/logo_neovision.png') }}"
                    alt="NEO VISION"
                    height="22"
                >
            </span>

            <span class="logo-lg">
                <img
                    src="{{ URL::asset('build/images/logo_neovision.png') }}"
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


                    {{-- ================================
                         UTILISATEURS
                    ================================= --}}

                    <li class="nav-item">
                        <a
                            class="nav-link menu-link"
                            href="{{ route('admin.users.index') }}"
                        >
                            <i class="ri-user-line"></i>
                            <span>Utilisateurs</span>
                        </a>
                    </li>


                    {{-- ================================
                         FORMATIONS
                    ================================= --}}

                    <li class="nav-item">

                        <a
                            class="nav-link menu-link"
                            href="#sidebarFormations"
                            data-bs-toggle="collapse"
                            role="button"
                            aria-expanded="false"
                            aria-controls="sidebarFormations"
                        >
                            <i class="ri-book-open-line"></i>
                            <span>Formations</span>
                        </a>

                        <div
                            class="collapse menu-dropdown"
                            id="sidebarFormations"
                        >

                            <ul class="nav nav-sm flex-column">

                                {{-- Catalogue --}}
                                <li class="nav-item">
                                    <a
                                        href="{{ route('admin.cours.index') }}"
                                        class="nav-link"
                                    >
                                        Catalogue de formations
                                    </a>
                                </li>

                                {{-- Création --}}
                                <li class="nav-item">
                                    <a
                                        href="{{ route('admin.cours.create') }}"
                                        class="nav-link"
                                    >
                                        Créer une formation
                                    </a>
                                </li>

                                {{-- Contenu pédagogique --}}
                                <li class="nav-item">
                                    <span
                                        class="nav-link text-muted"
                                        style="cursor: not-allowed;"
                                        title="Accessible depuis une formation"
                                    >
                                        Contenu pédagogique
                                    </span>
                                </li>

                            </ul>

                        </div>

                    </li>


                    {{-- ================================
                         CLASSES
                    ================================= --}}

                    <li class="nav-item">

                        <a
                            class="nav-link menu-link"
                            href="#sidebarClasses"
                            data-bs-toggle="collapse"
                            role="button"
                            aria-expanded="false"
                            aria-controls="sidebarClasses"
                        >
                            <i class="ri-group-line"></i>
                            <span>Classes</span>
                        </a>

                        <div
                            class="collapse menu-dropdown"
                            id="sidebarClasses"
                        >

                            <ul class="nav nav-sm flex-column">

                                <li class="nav-item">
                                    <span
                                        class="nav-link text-muted"
                                        style="cursor: not-allowed;"
                                    >
                                        Toutes les classes
                                    </span>
                                </li>

                                <li class="nav-item">
                                    <span
                                        class="nav-link text-muted"
                                        style="cursor: not-allowed;"
                                    >
                                        Créer une classe
                                    </span>
                                </li>

                            </ul>

                        </div>

                    </li>


                    {{-- ================================
                         QUIZ
                    ================================= --}}

                    <li class="nav-item">
                    <a class="nav-link menu-link" href="{{ route('admin.questions.index') }}">
                        <i class="ri-question-line"></i> <span>Banque de questions</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="{{ route('admin.quiz.index') }}">
                        <i class="ri-file-list-3-line"></i> <span>Suivi des quiz</span>
                    </a>
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
                            <span>Mes formations</span>
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

<!-- Vertical Overlay -->
<div class="vertical-overlay"></div>