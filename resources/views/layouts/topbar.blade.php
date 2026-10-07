<header id="page-topbar">
    <div class="layout-width">
        <div class="navbar-header">

            {{-- Bouton ouverture / fermeture de la sidebar --}}
            <div class="d-flex align-items-center">
                <button
                    type="button"
                    class="btn btn-sm px-3 fs-16 header-item vertical-menu-btn topnav-hamburger"
                    id="topnav-hamburger-icon"
                >
                    <span class="hamburger-icon">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </button>
            </div>

            {{-- Partie droite --}}
            <div class="d-flex align-items-center">

                {{-- Plein écran --}}
                <div class="ms-1 header-item d-none d-sm-flex">
                    <button
                        type="button"
                        class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle"
                        data-toggle="fullscreen"
                    >
                        <i class="bx bx-fullscreen fs-22"></i>
                    </button>
                </div>

                {{-- Mode clair / sombre --}}
                <div class="ms-1 header-item d-none d-sm-flex">
                    <button
                        type="button"
                        class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle light-dark-mode"
                    >
                        <i class="bx bx-moon fs-22"></i>
                    </button>
                </div>

                {{-- Notifications --}}
                @auth
                    <div class="dropdown topbar-head-dropdown ms-1 header-item">

                        <button
                            type="button"
                            class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle"
                            id="page-header-notifications-dropdown"
                            data-bs-toggle="dropdown"
                            data-bs-auto-close="outside"
                            aria-haspopup="true"
                            aria-expanded="false"
                        >
                            <i class="bx bx-bell fs-22"></i>

                            @if(isset($notificationsCount) && $notificationsCount > 0)
                                <span class="position-absolute topbar-badge fs-10 translate-middle badge rounded-pill bg-danger">
                                    {{ $notificationsCount }}
                                    <span class="visually-hidden">
                                        notifications non lues
                                    </span>
                                </span>
                            @endif
                        </button>

                        <div
                            class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0"
                            aria-labelledby="page-header-notifications-dropdown"
                        >

                            <div class="dropdown-head bg-primary bg-pattern rounded-top">
                                <div class="p-3">

                                    <div class="row align-items-center">
                                        <div class="col">
                                            <h6 class="m-0 fs-16 fw-semibold text-white">
                                                Notifications
                                            </h6>
                                        </div>

                                        @if(isset($notificationsCount) && $notificationsCount > 0)
                                            <div class="col-auto dropdown-tabs">
                                                <span class="badge bg-light-subtle text-body fs-13">
                                                    {{ $notificationsCount }} nouvelle{{ $notificationsCount > 1 ? 's' : '' }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>

                                </div>

                                <div class="px-2 pt-2">
                                    <div class="bg-white-subtle rounded-top p-2">
                                        <div class="row g-2">

                                            <div class="col">
                                                <a
                                                    href="{{ route('notifications.index') }}"
                                                    class="btn btn-light btn-sm w-100"
                                                >
                                                    Voir toutes les notifications
                                                </a>
                                            </div>

                                            @if(isset($notificationsCount) && $notificationsCount > 0)
                                                <div class="col">
                                                    <form
                                                        action="{{ route('notifications.lues') }}"
                                                        method="POST"
                                                    >
                                                        @csrf

                                                        <button
                                                            type="submit"
                                                            class="btn btn-light btn-sm w-100"
                                                        >
                                                            Tout marquer comme lu
                                                        </button>
                                                    </form>
                                                </div>
                                            @endif

                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="tab-content position-relative"
                                id="notificationItemsTabContent"
                            >
                                <div class="tab-pane fade show active py-2 ps-2">
                                    <div
                                        data-simplebar
                                        style="max-height: 300px;"
                                        class="pe-2"
                                    >

                                        @if(isset($notifications) && $notifications->count())
                                            @foreach($notifications as $notification)

                                                <a
                                                    href="{{ route('notifications.show', $notification->id) }}"
                                                    class="text-reset notification-item d-block dropdown-item"
                                                >
                                                    <div class="d-flex">

                                                        <div class="flex-shrink-0 avatar-xs me-3">
                                                            <span class="avatar-title bg-primary-subtle text-primary rounded-circle fs-16">
                                                                <i class="bx bx-bell"></i>
                                                            </span>
                                                        </div>

                                                        <div class="flex-grow-1">
                                                            <h6 class="mb-1">
                                                                {{ $notification->titre ?? 'Notification' }}
                                                            </h6>

                                                            <div class="fs-13 text-muted">
                                                                {{ $notification->message ?? '' }}
                                                            </div>

                                                            @if(isset($notification->created_at))
                                                                <p class="mb-0 fs-11 fw-medium text-uppercase text-muted">
                                                                    {{ $notification->created_at->diffForHumans() }}
                                                                </p>
                                                            @endif
                                                        </div>

                                                    </div>
                                                </a>

                                            @endforeach
                                        @else
                                            <div class="text-center py-4">
                                                <div class="avatar-md mx-auto mb-3">
                                                    <div class="avatar-title bg-light text-muted rounded-circle fs-24">
                                                        <i class="bx bx-bell-off"></i>
                                                    </div>
                                                </div>

                                                <h6 class="mb-1">
                                                    Aucune notification
                                                </h6>

                                                <p class="text-muted mb-0">
                                                    Vous n'avez aucune nouvelle notification.
                                                </p>
                                            </div>
                                        @endif

                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- Utilisateur connecté --}}
                    <div class="dropdown ms-sm-3 header-item topbar-user">

                        <button
                            type="button"
                            class="btn"
                            id="page-header-user-dropdown"
                            data-bs-toggle="dropdown"
                            aria-haspopup="true"
                            aria-expanded="false"
                        >
                            <span class="d-flex align-items-center">

                                @if(Auth::user()->avatar)
                                    <img
                                        class="rounded-circle header-profile-user"
                                        src="{{ asset('storage/' . Auth::user()->avatar) }}"
                                        alt="Avatar"
                                    >
                                @else
                                    <span
                                        class="rounded-circle header-profile-user bg-primary text-white d-flex align-items-center justify-content-center fw-semibold"
                                        style="width: 32px; height: 32px;"
                                    >
                                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                    </span>
                                @endif

                                <span class="text-start ms-xl-2">
                                    <span class="d-none d-xl-inline-block ms-1 fw-medium user-name-text">
                                        {{ Auth::user()->name }}
                                    </span>

                                    <span class="d-none d-xl-block ms-1 fs-12 user-name-sub-text">
                                        {{ Auth::user()->role->nom ?? 'Utilisateur' }}
                                    </span>
                                </span>

                                <i class="mdi mdi-chevron-down d-none d-xl-inline-block ms-1"></i>

                            </span>
                        </button>

                        <div class="dropdown-menu dropdown-menu-end">

                            {{-- Profil --}}
                            <h6 class="dropdown-header">
                                Bienvenue {{ Auth::user()->name }} !
                            </h6>

                            <a
                                class="dropdown-item"
                                href="{{ route('index', ['any' => 'pages-profile']) }}"
                            >
                                <i class="mdi mdi-account-circle text-muted fs-16 align-middle me-1"></i>
                                <span class="align-middle">
                                    Mon profil
                                </span>
                            </a>

                            {{-- Notifications --}}
                            <a
                                class="dropdown-item"
                                href="{{ route('notifications.index') }}"
                            >
                                <i class="mdi mdi-bell-outline text-muted fs-16 align-middle me-1"></i>
                                <span class="align-middle">
                                    Notifications
                                </span>

                                @if(isset($notificationsCount) && $notificationsCount > 0)
                                    <span class="badge bg-danger-subtle text-danger float-end">
                                        {{ $notificationsCount }}
                                    </span>
                                @endif
                            </a>

                            <div class="dropdown-divider"></div>

                            {{-- Déconnexion --}}
                            <form
                                method="POST"
                                action="{{ route('logout') }}"
                            >
                                @csrf

                                <button
                                    type="submit"
                                    class="dropdown-item"
                                >
                                    <i class="mdi mdi-logout text-muted fs-16 align-middle me-1"></i>
                                    <span class="align-middle">
                                        Déconnexion
                                    </span>
                                </button>
                            </form>

                        </div>
                    </div>

                @endauth

            </div>
        </div>
    </div>
</header>