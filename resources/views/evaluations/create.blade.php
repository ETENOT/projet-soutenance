@extends('layouts.master')

@section('title', 'Évaluer la formation')

@section('content')

@component('components.breadcrumb')
    @slot('li_1')
        <a href="{{ route('cours.mes') }}">Mes classes</a>
    @endslot
    @slot('title')
        Évaluer la formation
    @endslot
@endcomponent

<div class="container-fluid pb-4">
    <form action="{{ route('evaluations.store', $classe) }}" method="POST" id="formEvaluation" novalidate>
        @csrf

        {{-- ===== En-tête (rempli automatiquement) ===== --}}
        <div class="card border shadow-sm mb-3">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-2">
                    <i class="ri-star-smile-line text-danger me-2"></i>Évaluation de la formation
                </h5>
                <p class="text-muted">
                    Votre avis est important : il nous aide à améliorer la qualité de nos formations.
                    Notez chaque critère de 1 à 5 étoiles, tout de suite après la formation.
                </p>
                <div class="row g-2">
                    <div class="col-md-6"><strong>Intitulé :</strong> {{ $classe->cours->titre }}</div>
                    <div class="col-md-6"><strong>Classe :</strong> {{ $classe->nom }}</div>
                    <div class="col-md-6"><strong>Formateur :</strong> {{ $classe->formateur?->name ?? 'Non renseigné' }}</div>
                    <div class="col-md-6">
                        <strong>Dates :</strong>
                        du {{ $classe->date_debut->format('d/m/Y') }} au {{ $classe->date_fin->format('d/m/Y') }}
                    </div>
                </div>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <div class="fw-semibold mb-1">Merci de compléter l'évaluation :</div>
                <ul class="mb-0">
                    @foreach ($errors->all() as $erreur)
                        <li>{{ $erreur }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ===== Le formateur (seulement si un formateur est affecté à la classe) ===== --}}
        @if ($classe->formateur)
            <div class="card border shadow-sm mb-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Le formateur</h5>
                </div>
                <div class="card-body">
                    @foreach (\App\Models\AvisFormateur::CRITERES as $colonne => $libelle)
                        @include('evaluations.partials.saisie', [
                            'champ' => $colonne,
                            'libelle' => $libelle,
                            'valeur' => old($colonne, $avisFormateur?->{$colonne}),
                        ])
                    @endforeach

                    <div class="mt-3">
                        <label for="commentaire_formateur" class="form-label fw-medium">
                            Commentaire sur le formateur <span class="text-muted fw-normal">(facultatif)</span>
                        </label>
                        <textarea id="commentaire_formateur" name="commentaire_formateur" rows="3" maxlength="1000"
                                  class="form-control @error('commentaire_formateur') is-invalid @enderror"
                                  placeholder="Ce qui était bien, ce qui pourrait être amélioré...">{{ old('commentaire_formateur', $avisFormateur?->commentaire) }}</textarea>
                        @error('commentaire_formateur')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Le formateur lira votre commentaire, mais ne verra pas votre nom.</div>
                    </div>
                </div>
            </div>
        @endif

        {{-- ===== Environnement et moyens pédagogiques, puis La formation ===== --}}
        @foreach (\App\Models\EvaluationFormation::CRITERES as $section => $criteres)
            <div class="card border shadow-sm mb-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ $section }}</h5>
                </div>
                <div class="card-body">
                    @foreach ($criteres as $colonne => $libelle)
                        @include('evaluations.partials.saisie', [
                            'champ' => $colonne,
                            'libelle' => $libelle,
                            'valeur' => old($colonne, $evaluation?->{$colonne}),
                        ])
                    @endforeach

                    {{-- La satisfaction globale conclut le bloc « La formation » --}}
                    @if ($section === 'La formation')
                        @include('evaluations.partials.saisie', [
                            'champ' => 'note_globale',
                            'libelle' => 'Satisfaction globale',
                            'valeur' => old('note_globale', $avisCours?->note),
                        ])
                        <div class="form-text mt-2">
                            Cette note est aussi votre avis sur la formation, visible dans l'onglet « Avis » du cours.
                        </div>
                    @endif
                </div>
            </div>
        @endforeach

        {{-- ===== Verso de la fiche : remarques libres (facultatives, lues par l'administration) ===== --}}
        <div class="card border shadow-sm mb-3">
            <div class="card-header">
                <h5 class="card-title mb-0">Vos remarques <span class="text-muted fs-13 fw-normal">(facultatif)</span></h5>
            </div>
            <div class="card-body">
                @foreach (\App\Models\EvaluationFormation::TEXTES as $champ => $titre)
                    <div class="mb-3">
                        <label for="{{ $champ }}" class="form-label fw-medium">{{ $titre }}</label>
                        <textarea id="{{ $champ }}" name="{{ $champ }}" rows="3" maxlength="2000"
                                  class="form-control @error($champ) is-invalid @enderror">{{ old($champ, $evaluation?->{$champ}) }}</textarea>
                        @error($champ)
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                @endforeach
                <div class="form-text">Ces remarques sont lues par l'administration de NEO VISION.</div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('cours.mes') }}" class="btn btn-light">Annuler</a>
            <button type="submit" class="btn btn-danger fw-semibold">
                <i class="ri-send-plane-line me-1"></i>{{ $evaluation ? 'Mettre à jour mon évaluation' : 'Envoyer mon évaluation' }}
            </button>
        </div>
    </form>
</div>

@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const lignes = document.querySelectorAll('.js-note');

    // Remplit les étoiles d'une ligne jusqu'à la note donnée
    function afficher(ligne, note) {
        ligne.querySelectorAll('.js-etoile').forEach(function (bouton) {
            const remplie = Number(bouton.dataset.note) <= note;
            const icone = bouton.querySelector('i');
            icone.classList.toggle('ri-star-fill', remplie);
            icone.classList.toggle('ri-star-line', !remplie);
        });
    }

    lignes.forEach(function (ligne) {
        const champ = ligne.querySelector('.js-valeur');
        const erreur = ligne.querySelector('.js-erreur');

        afficher(ligne, Number(champ.value) || 0);

        ligne.querySelectorAll('.js-etoile').forEach(function (bouton) {
            // Survol : aperçu de la note
            bouton.addEventListener('mouseenter', function () {
                afficher(ligne, Number(bouton.dataset.note));
            });

            // Clic : on retient la note
            bouton.addEventListener('click', function () {
                champ.value = bouton.dataset.note;
                afficher(ligne, Number(champ.value));
                erreur.classList.add('d-none');
            });
        });

        // On quitte la ligne : retour à la note choisie
        ligne.addEventListener('mouseleave', function () {
            afficher(ligne, Number(champ.value) || 0);
        });
    });

    // Empêche l'envoi tant qu'une note manque, et montre la première ligne à compléter
    document.getElementById('formEvaluation').addEventListener('submit', function (event) {
        let premiere = null;

        lignes.forEach(function (ligne) {
            const vide = !ligne.querySelector('.js-valeur').value;
            ligne.querySelector('.js-erreur').classList.toggle('d-none', !vide);
            if (vide && !premiere) {
                premiere = ligne;
            }
        });

        if (premiere) {
            event.preventDefault();
            premiere.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
});
</script>
@endsection