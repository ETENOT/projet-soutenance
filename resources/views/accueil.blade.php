@extends('layouts.master-without-nav')

@section('title')
    Accueil
@endsection

@section('content')
    <div class="container-fluid px-0" style="background: linear-gradient(135deg, #f4faff 0%, #edf8ff 100%); color: #0b1f2d;">
        <div class="row g-0 min-vh-100">

            {{-- Colonne gauche : message + actions, aligné à gauche --}}
            <div class="col-lg-6 d-flex flex-column justify-content-between p-5" style="background: rgba(255,255,255,0.35);">
                <div>
                    <img src="{{ URL::asset('build/images/logo_neovision.png') }}" alt="Néo-Vision" height="36">
                </div>

                <div style="max-width: 480px;">
                    <h1 class="fw-semibold mb-3" style="font-size: 2.75rem; line-height: 1.15; color: #0b1f2d;">
                        Des formations, du premier module à l'attestation.
                    </h1>
                    <p class="fs-16 mb-4" style="color: #365066;">
                        Formation Néovision accompagne particuliers et entreprises dans la réservation
                        et le suivi de leurs sessions de formation professionnelle.
                    </p>

                    <div class="d-flex align-items-center gap-4 mb-3">
                        <a href="{{ route('cours.catalogue') }}" class="btn btn-lg px-4" style="background: #0f9bd6; border-color: #0f9bd6; color: white; box-shadow: 0 10px 24px rgba(15, 155, 214, 0.2);">
                            Voir le programme des cours
                        </a>
                        <a href="{{ route('login') }}" class="fw-medium text-decoration-none" style="color: #0b1f2d;">
                            Se connecter
                        </a>
                    </div>

                    <p class="fs-14 mb-0" style="color: #365066;">
                        Pas encore de compte ?
                        <a href="{{ route('register') }}" class="text-decoration-underline" style="color: #e53935;">Créer un compte</a>
                    </p>
                </div>

                <div>
                    <p class="fs-13 mb-0" style="color: #365066;">
                        Besoin d'aide ? Contact service client — bientôt disponible
                    </p>
                </div>
            </div>

            {{-- Colonne droite : le vrai parcours du produit, pas une illustration décorative --}}
            <div class="col-lg-6 d-flex align-items-center" style="background: linear-gradient(180deg, rgba(15,155,214,0.04) 0%, rgba(11,31,45,0.02) 100%);">
                <div class="p-5 w-100" style="max-width: 420px; margin: 0 auto;">
                    <div class="d-flex mb-5">
                        <div class="d-flex flex-column align-items-center me-4">
                            <div class="d-flex align-items-center justify-content-center rounded-circle text-white fw-semibold" style="width: 36px; height: 36px; flex-shrink: 0; background: #0f9bd6;">1</div>
                            <div style="width: 2px; flex-grow: 1; min-height: 40px; background: rgba(15, 155, 214, 0.35);"></div>
                        </div>
                        <div class="pt-1">
                            <h6 class="fw-semibold mb-1" style="color: #0b1f2d;">Cours</h6>
                            <p class="fs-14 mb-0" style="color: #365066;">Choisissez une formation dans le programme, avec un tarif particulier ou entreprise.</p>
                        </div>
                    </div>

                    <div class="d-flex mb-5">
                        <div class="d-flex flex-column align-items-center me-4">
                            <div class="d-flex align-items-center justify-content-center rounded-circle text-white fw-semibold" style="width: 36px; height: 36px; flex-shrink: 0; background: #0f9bd6;">2</div>
                            <div style="width: 2px; flex-grow: 1; min-height: 40px; background: rgba(15, 155, 214, 0.35);"></div>
                        </div>
                        <div class="pt-1">
                            <h6 class="fw-semibold mb-1" style="color: #0b1f2d;">Classe</h6>
                            <p class="fs-14 mb-0" style="color: #365066;">Réservez votre session, avec date de début, date de fin et places limitées.</p>
                        </div>
                    </div>

                    <div class="d-flex mb-5">
                        <div class="d-flex flex-column align-items-center me-4">
                            <div class="d-flex align-items-center justify-content-center rounded-circle text-white fw-semibold" style="width: 36px; height: 36px; flex-shrink: 0; background: #0f9bd6;">3</div>
                            <div style="width: 2px; flex-grow: 1; min-height: 40px; background: rgba(15, 155, 214, 0.35);"></div>
                        </div>
                        <div class="pt-1">
                            <h6 class="fw-semibold mb-1" style="color: #0b1f2d;">Quiz</h6>
                            <p class="fs-14 mb-0" style="color: #365066;">Validez vos acquis à l'issue de la formation.</p>
                        </div>
                    </div>

                    <div class="d-flex">
                        <div class="d-flex flex-column align-items-center me-4">
                            <div class="d-flex align-items-center justify-content-center rounded-circle text-white fw-semibold" style="width: 36px; height: 36px; flex-shrink: 0; background: #e53935;">4</div>
                        </div>
                        <div class="pt-1">
                            <h6 class="fw-semibold mb-1" style="color: #0b1f2d;">Validation</h6>
                            <p class="fs-14 mb-0" style="color: #365066;">Suivez votre progression et vos résultats depuis votre tableau de bord.</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection