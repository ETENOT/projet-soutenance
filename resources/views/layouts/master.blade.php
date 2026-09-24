<!doctype html >
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-preloader="disable">

<head>
    <meta charset="utf-8" />
    <title>@yield('title') | NEO VISION</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Plateforme de gestion et de formation professionnelle NEO VISION" name="description" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ URL::asset('build/images/favicon.ico')}}">
    @include('layouts.head-css')
</head>

@section('body')
    @include('layouts.body')
@show
    <!-- Begin page -->
    <div id="layout-wrapper">
        @include('layouts.topbar')
        @include('layouts.sidebar')
        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    {{-- Messages de session (succès / erreur) : UN SEUL affichage pour toute l'application.
                         Les pages ne doivent plus avoir leur propre bloc @if(session('success')).
                         Ce sont de petites notifications flottantes, fixées sous la barre du haut
                         (top: 70px), qui ne décalent pas le contenu et se ferment toutes seules. --}}
                    @if(session('success') || session('error'))
                        <div class="toast-container position-fixed end-0 p-3" style="top: 70px; z-index: 1100;">
                            @if(session('success'))
                                <div class="toast flash-toast show align-items-center text-bg-success border-0 mb-2" role="alert" aria-live="assertive" aria-atomic="true">
                                    <div class="d-flex">
                                        <div class="toast-body">
                                            <i class="ri-checkbox-circle-line me-1"></i>{{ session('success') }}
                                        </div>
                                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Fermer"></button>
                                    </div>
                                </div>
                            @endif

                            @if(session('error'))
                                <div class="toast flash-toast show align-items-center text-bg-danger border-0 mb-2" role="alert" aria-live="assertive" aria-atomic="true">
                                    <div class="d-flex">
                                        <div class="toast-body">
                                            <i class="ri-error-warning-line me-1"></i>{{ session('error') }}
                                        </div>
                                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Fermer"></button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    @yield('content')
                </div>
                <!-- container-fluid -->
            </div>
            <!-- End Page-content -->
            @include('layouts.footer')
        </div>
        <!-- end main content-->
    </div>
    <!-- END layout-wrapper -->

        {{-- Le personnalisateur du template (bouton d'engrenage) est caché : il n'a pas sa place
         dans l'application. On le garde dans la page, en d-none, car le JavaScript de Velzon
         cherche ses éléments. --}}
    <div class="d-none">
        @include('layouts.customizer')
    </div>

    <!-- JAVASCRIPT -->
    @include('layouts.vendor-scripts')

    <script>
        // Ferme automatiquement les messages : 5 secondes pour un succès, 8 pour une erreur
        // (plus longtemps, pour laisser le temps de la lire). On simule un clic sur la croix
        // pour réutiliser la fermeture animée de Bootstrap.
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.flash-toast').forEach(function (toast) {
                var delai = toast.classList.contains('text-bg-danger') ? 8000 : 5000;

                setTimeout(function () {
                    var croix = toast.querySelector('.btn-close');
                    if (croix) croix.click();
                    // Filet de sécurité : si la fermeture animée n'a pas eu lieu, on retire le message.
                    setTimeout(function () { toast.remove(); }, 600);
                }, delai);
            });
        });
    </script>
</body>

</html>