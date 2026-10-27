@extends('layouts.master-without-nav')

@section('title', 'Vérification de l’adresse e-mail')

@section('content')
    <div class="auth-page-wrapper auth-page-brand pt-5">
        <a href="{{ url('/') }}"
           class="btn btn-light position-fixed top-0 start-0 m-3 m-sm-4 z-3 auth-return-home">
            <i class="ri-arrow-left-line align-middle me-1"></i>
            Retour à l'accueil
        </a>

        <div class="auth-one-bg-position auth-one-bg">
            <div class="bg-overlay"></div>
            <div class="shape">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 120" aria-hidden="true">
                    <path d="M 0,36 C 144,53.6 432,123.2 720,124 C 1008,124.8 1296,56.8 1440,40L1440 140L0 140z"></path>
                </svg>
            </div>
        </div>

        <div class="auth-page-content">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="text-center mt-sm-5 mb-4 text-white-50">
                            <a href="{{ url('/') }}" class="d-inline-block auth-logo">
                                <img src="{{ asset('images/logo_neovision.png') }}" alt="Néovision" height="55">
                            </a>
                            <p class="mt-3 fs-15 fw-medium">Voir, Faire et Réaliser Différemment</p>
                        </div>
                    </div>
                </div>

                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-6 col-xl-5">
                        <div class="card mt-4">
                            <div class="card-body p-4">
                                <div class="text-center mt-2 mb-4">
                                    <h5 class="text-primary">Vérifiez votre adresse e-mail</h5>
                                    <p class="text-muted mb-0">
                                        Avant de continuer, vérifiez votre adresse e-mail en cliquant sur le lien que nous vous avons envoyé.
                                        Si vous n’avez pas reçu le message, vous pouvez en demander un nouveau.
                                    </p>
                                </div>

                                @if (session('status') === 'verification-link-sent')
                                    <div class="alert alert-success" role="status">
                                        Un nouveau lien de vérification a été envoyé à l’adresse indiquée lors de votre inscription.
                                    </div>
                                @endif

                                <div class="d-flex flex-column gap-3">
                                    <form method="POST" action="{{ route('verification.send') }}">
                                        @csrf
                                        <button type="submit" class="btn btn-success w-100">
                                            Renvoyer le lien de vérification
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="btn btn-soft-secondary w-100">
                                            Se déconnecter
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <footer class="footer">
            <div class="container">
                <div class="text-center">
                    <p class="mb-0 text-muted">&copy; {{ date('Y') }} NEO-VISION</p>
                </div>
            </div>
        </footer>
    </div>
@endsection
