<!doctype html >
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-preloader="disable">

<head>
    <meta charset="utf-8" />
    <title>@yield('title') | NEO VISION</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Plateforme de gestion et de formation professionnelle NEO VISION" name="description" />
    <!-- App favicon -->
    <link rel="icon" type="image/x-icon" href="{{ URL::asset('images/favicon.ico') }}">
    <link rel="icon" type="image/png" href="{{ URL::asset('images/favicon.png') }}">
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
                    @php
                        // Codes « status » de Laravel -> texte français
                        $textesStatus = [
                            'profile-updated' => 'Profil mis à jour.',
                            'password-updated' => 'Mot de passe modifié.',
                            'email-verified' => 'Adresse e-mail vérifiée.',
                            'verification-link-sent' => 'Un nouveau lien de vérification a été envoyé à votre adresse e-mail.',
                            'verification-code-sent' => 'Un nouveau code de vérification a été envoyé.',
                        ];

                        $succes = array_filter([
                            session('success'),
                            session('status') ? ($textesStatus[session('status')] ?? __(session('status'))) : null,
                            session('message') && session('alert-class') === 'alert-success' ? session('message') : null,
                        ]);

                        $erreurs = array_filter(array_merge(
                            [session('error')],
                            session('message') && session('alert-class') !== 'alert-success' ? [session('message')] : [],
                            $errors->all(),
                            $errors->getBag('updatePassword')->all(),
                            $errors->getBag('userDeletion')->all()
                        ));
                    @endphp

                    {{-- Notifications flottantes : UN SEUL affichage pour toute l'application (succès, informations, erreurs).
                         Le conteneur existe toujours : le JavaScript (window.notifier) y ajoute aussi des messages. --}}
                    <div id="conteneur-notifications" class="toast-container position-fixed end-0 p-3" style="top: 70px; z-index: 1100;">
                        @foreach ($succes as $texte)
                            <div class="toast flash-toast show align-items-center text-bg-success border-0 mb-2" role="alert" aria-live="assertive" aria-atomic="true">
                                <div class="d-flex">
                                    <div class="toast-body"><i class="ri-checkbox-circle-line me-1"></i>{{ $texte }}</div>
                                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Fermer"></button>
                                </div>
                            </div>
                        @endforeach

                        @if (session('info'))
                            <div class="toast flash-toast show align-items-center text-bg-info border-0 mb-2" role="alert" aria-live="assertive" aria-atomic="true">
                                <div class="d-flex">
                                    <div class="toast-body"><i class="ri-information-line me-1"></i>{{ session('info') }}</div>
                                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Fermer"></button>
                                </div>
                            </div>
                        @endif

                        @if (count($erreurs))
                            <div class="toast flash-toast show align-items-center text-bg-danger border-0 mb-2" role="alert" aria-live="assertive" aria-atomic="true">
                                <div class="d-flex">
                                    <div class="toast-body">
                                        <i class="ri-error-warning-line me-1"></i>
                                        @foreach ($erreurs as $erreur){{ $erreur }}@if (! $loop->last)<br>@endif @endforeach
                                    </div>
                                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Fermer"></button>
                                </div>
                            </div>
                        @endif
                    </div>

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
    document.addEventListener('DOMContentLoaded', function () {

        // Affiche une notification flottante depuis le JavaScript : notifier('Message', 'success' | 'danger' | 'info')
        window.notifier = function (message, type) {
            type = type || 'success';
            const icones = { success: 'ri-checkbox-circle-line', danger: 'ri-error-warning-line', info: 'ri-information-line' };

            const toast = document.createElement('div');
            toast.className = 'toast flash-toast show align-items-center border-0 mb-2 text-bg-' + type;
            toast.setAttribute('role', 'alert');

            const ligne = document.createElement('div');
            ligne.className = 'd-flex';
            const corps = document.createElement('div');
            corps.className = 'toast-body';
            const icone = document.createElement('i');
            icone.className = (icones[type] || icones.info) + ' me-1';
            corps.append(icone, document.createTextNode(message));   // texte inséré tel quel : pas de HTML injecté
            const croix = document.createElement('button');
            croix.type = 'button';
            croix.className = 'btn-close btn-close-white me-2 m-auto';
            croix.setAttribute('aria-label', 'Fermer');
            croix.addEventListener('click', () => toast.remove());
            ligne.append(corps, croix);
            toast.appendChild(ligne);

            document.getElementById('conteneur-notifications').appendChild(toast);
            setTimeout(() => toast.remove(), type === 'danger' ? 8000 : 5000);
        };

        // Ferme automatiquement les messages : 5 secondes pour un succès, 8 pour une erreur
        // (plus longtemps, pour laisser le temps de la lire). On simule un clic sur la croix
        // pour réutiliser la fermeture animée de Bootstrap.
        document.querySelectorAll('.flash-toast').forEach(function (toast) {
            var delai = toast.classList.contains('text-bg-danger') ? 8000 : 5000;

            setTimeout(function () {
                var croix = toast.querySelector('.btn-close');
                if (croix) croix.click();
                // Filet de sécurité : si la fermeture animée n'a pas eu lieu, on retire le message.
                setTimeout(function () { toast.remove(); }, 600);
            }, delai);
        });

        // Listes d'administration : le texte complet d'une cellule coupée (« … ») s'affiche au survol
        document.querySelectorAll('.table-liste td').forEach(function (td) {
            if (td.scrollWidth > td.clientWidth && !td.title) {
                td.title = td.textContent.trim();
            }
        });
    });
</script>

    
</body>

</html>