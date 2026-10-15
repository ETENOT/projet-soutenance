{{--
    Une ligne de notation : libellé + 5 étoiles cliquables (même principe que les avis de cours).
    Variables : $champ (nom du champ), $libelle, $valeur (note actuelle ou vide)
    Le script de la page (evaluations/create) gère le survol et le clic sur ".js-etoile".
--}}
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 border-bottom py-2 js-note">
    <div>
        {{ $libelle }}
        @error($champ)
            <div class="text-danger small">{{ $message }}</div>
        @enderror
        <div class="text-danger small d-none js-erreur">Choisissez une note de 1 à 5 étoiles.</div>
    </div>

    <div>
        <input type="hidden" name="{{ $champ }}" value="{{ $valeur }}" class="js-valeur">

        <div class="d-inline-flex gap-1" role="group" aria-label="{{ $libelle }} : note de 1 à 5 étoiles">
            @for ($i = 1; $i <= 5; $i++)
                <button type="button"
                        class="btn btn-link p-0 text-warning fs-3 lh-1 text-decoration-none js-etoile"
                        data-note="{{ $i }}"
                        aria-label="{{ $i }} étoile{{ $i > 1 ? 's' : '' }}">
                    <i class="ri-star-line"></i>
                </button>
            @endfor
        </div>
    </div>
</div>