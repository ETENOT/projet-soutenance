@extends('layouts.auth-split')

@section('title', 'Vérification de l\'adresse e-mail')

@section('titre_visuel', 'Un dernier contrôle')
@section('texte_visuel', 'Un e-mail de vérification vous attend dans votre boîte de réception.')

@section('formulaire')

    <div class="nv-env" id="enveloppe" aria-hidden="true">
        <span class="nv-env-corps">
            <span class="nv-env-lettre"><i class="ri-mail-check-line"></i></span>
            <span class="nv-env-poche"></span>
            <span class="nv-env-rabat"></span>
        </span>
    </div>
    <script>
        // L'enveloppe s'ouvre une demi-seconde après l'affichage de la page
        document.addEventListener('DOMContentLoaded', function () {
            setTimeout(function () { document.getElementById('enveloppe').classList.add('est-ouverte'); }, 500);
        });
    </script>

    <div class="text-center mb-4">
        <h2 class="fw-bold mb-2">Vérifiez votre adresse e-mail</h2>
        <p class="text-muted mb-0">
            Avant de continuer, cliquez sur le lien que nous vous avons envoyé.
            Si vous n'avez pas reçu le message, vous pouvez en demander un nouveau.
        </p>
    </div>

    @if (session('status') === 'verification-link-sent')
        <div class="alert alert-success" role="status">
            Un nouveau lien de vérification a été envoyé à l'adresse indiquée lors de votre inscription.
        </div>
    @endif

    <div class="d-flex flex-column gap-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn btn-primary w-100">Renvoyer le lien de vérification</button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-soft-secondary w-100">Se déconnecter</button>
        </form>
    </div>

@endsection