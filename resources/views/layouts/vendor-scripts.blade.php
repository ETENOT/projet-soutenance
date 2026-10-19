<script src="{{ URL::asset('build/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/simplebar/simplebar.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/node-waves/waves.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/feather-icons/feather.min.js') }}"></script>
<script src="{{ URL::asset('build/js/pages/plugins/lord-icon-2.1.0.js') }}"></script>
<script src="{{ URL::asset('build/js/plugins.js') }}"></script>

{{-- app.js pilote le mode sombre/clair, le plein écran, le bouton qui réduit le menu latéral
     et le défilement (SimpleBar) du menu : il doit donc être chargé sur TOUTES les pages.
     Certaines pages (tableaux de bord, profil) l'ajoutent déjà dans leur @section('script') :
     le charger deux fois ferait traiter chaque clic deux fois (le bouton s'annulerait lui-même).
     On vérifie donc d'abord que la page ne l'a pas déjà fourni. --}}
@php
    // Contenu de la section 'script' de la page en cours
    $scriptsDeLaPage = $__env->yieldContent('script');
@endphp
@unless (str_contains($scriptsDeLaPage, 'build/js/app.js'))
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endunless
@yield('script')
@yield('script-bottom')
