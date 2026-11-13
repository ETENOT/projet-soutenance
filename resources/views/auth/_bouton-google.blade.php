{{--
    Bouton « Continuer avec Google » + séparateur « ou ».
    Inclus sur les pages de connexion et d'inscription.
    Le bouton mène à GoogleController@redirect, qui envoie vers la page de connexion de Google.
--}}
<div class="mt-4">
    {{-- Séparateur « ou » entre le formulaire et le bouton Google --}}
    <div class="d-flex align-items-center gap-2 mb-3">
        <hr class="flex-grow-1 my-0">
        <span class="text-muted fs-13">{{ __('ou') }}</span>
        <hr class="flex-grow-1 my-0">
    </div>

    <a href="{{ route('google.redirect') }}"
       class="btn btn-light border w-100 d-flex align-items-center justify-content-center gap-2">
        {{-- Icône de la police Remix Icon déjà chargée par le template --}}
        <i class="ri-google-fill fs-18 text-danger"></i>
        <span class="fw-medium">{{ __('Continuer avec Google') }}</span>
    </a>
</div>
