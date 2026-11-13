{{-- Gabarit des pages de connexion : image + message à gauche, formulaire à droite.
     Les pages qui l'utilisent remplissent :
       @section('titre_visuel') / @section('texte_visuel') : message sur l'image (facultatifs)
       @section('formulaire')                              : le formulaire (obligatoire)
     L'image de fond se change à UN seul endroit : la ligne <img class="nv-auth-bg"> ci-dessous. --}}
@extends('layouts.master-without-nav')

@section('content')
<div class="nv-auth">

    {{-- ===== Côté image (caché sur téléphone et tablette) ===== --}}
    <aside class="nv-auth-visuel">
        <img src="{{ asset('images/images projets/bat-NV.jpeg') }}" alt="" class="nv-auth-bg">
        <div class="nv-auth-voile"></div>

        <div class="nv-auth-visuel-contenu">
            <a href="{{ url('/') }}">
                <img src="{{ asset('images/logo-nv-transparent.png') }}" alt="NEO VISION" height="48">
            </a>

            <div>
                <hr class="nv-underline">
                <h1 class="fw-bold text-white mb-2">
                    @yield('titre_visuel', 'Développez vos compétences, construisez votre avenir')
                </h1>
                <p class="mb-4 opacity-75">
                    @yield('texte_visuel', 'Voir, Faire et Réaliser Différemment.')
                </p>

                <ul class="nv-auth-points list-unstyled mb-0">
                    @foreach ([
                        ['ri-book-open-line',    'Des cours et des quiz pour progresser à votre rythme'],
                        ['ri-group-line',        'Des classes en centre ou dans votre entreprise'],
                        ['ri-award-line',        'Une attestation à la fin de votre formation'],
                    ] as [$icone, $texte])
                        <li><span><i class="{{ $icone }}"></i></span>{{ $texte }}</li>
                    @endforeach
                </ul>
            </div>

            <small class="opacity-75">&copy; {{ date('Y') }} NEO VISION</small>
        </div>
    </aside>

    {{-- ===== Côté formulaire ===== --}}
    <main class="nv-auth-formulaire">
        <div class="nv-auth-haut">
            <a href="{{ url('/') }}" class="btn btn-light btn-sm">
                <i class="ri-arrow-left-line align-middle me-1"></i>Retour à l'accueil
            </a>
        </div>

        <div class="nv-auth-boite">
            {{-- Logo seulement sur téléphone / tablette (sur grand écran, il est sur l'image) --}}
            <a href="{{ url('/') }}" class="d-lg-none d-block text-center mb-4">
                <img src="{{ asset('images/logo_neovision.png') }}" alt="NEO VISION" height="44">
            </a>

            @yield('formulaire')
        </div>
    </main>
</div>
@endsection