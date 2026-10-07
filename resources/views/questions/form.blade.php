{{-- Formulaire commun de création / modification d'une question (QCM) --}}
@php
    // Valeurs de départ : anciennes valeurs (après erreur de validation) > question existante > 2 lignes vides
    $optionsInit = old('options');
    if ($optionsInit === null) {
        if (isset($question)) {
            $optionsInit = $question->options->map(fn ($o) => ['id' => $o->id, 'libelle' => $o->libelle])->all();
            $correcteInit = $question->options->search(fn ($o) => $o->est_correct);
        } else {
            $optionsInit = [['libelle' => ''], ['libelle' => '']];
            $correcteInit = null;
        }
    } else {
        $correcteInit = old('correcte');
    }
    $coursInit = old('cours_id', $question->cours_id ?? $coursChoisi ?? null);
    // Prochain index libre pour les options ajoutées en JavaScript
    $prochaineCle = count($optionsInit) ? max(array_keys($optionsInit)) + 1 : 0;
@endphp

@csrf
@if (isset($question))
    @method('PUT')
@endif

<div class="mb-3">
    <label for="cours_id" class="form-label">Cours</label>
    <select name="cours_id" id="cours_id" class="form-select @error('cours_id') is-invalid @enderror" required>
        <option value="">— Choisir un cours —</option>
        @foreach ($cours as $c)
            <option value="{{ $c->id }}" @selected((string) $coursInit === (string) $c->id)>{{ $c->titre }}</option>
        @endforeach
    </select>
    @error('cours_id')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="enonce" class="form-label">Énoncé de la question</label>
    <textarea name="enonce" id="enonce" rows="3" maxlength="2000"
              class="form-control @error('enonce') is-invalid @enderror" required>{{ old('enonce', $question->enonce ?? '') }}</textarea>
    @error('enonce')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label class="form-label">Options de réponse <span class="text-muted">(cochez la bonne réponse)</span></label>

    <div id="options-container">
        @foreach ($optionsInit as $cle => $option)
            <div class="input-group mb-2 option-row">
                {{-- Bouton radio : une seule bonne réponse possible (valeur = clé de l'option) --}}
                <div class="input-group-text">
                    <input class="form-check-input mt-0" type="radio" name="correcte" value="{{ $cle }}"
                           title="Bonne réponse" @checked((string) $correcteInit === (string) $cle) required>
                </div>
                {{-- id caché : permet au contrôleur de retrouver l'option existante à la modification --}}
                <input type="hidden" name="options[{{ $cle }}][id]" value="{{ $option['id'] ?? '' }}">
                <input type="text" name="options[{{ $cle }}][libelle]" class="form-control" maxlength="255"
                       placeholder="Texte de l'option" value="{{ $option['libelle'] ?? '' }}" required>
                <button type="button" class="btn btn-soft-danger btn-supprimer-option" title="Retirer cette option">
                    <i class="ri-delete-bin-line"></i>
                </button>
            </div>
        @endforeach
    </div>

    <button type="button" id="btn-ajouter-option" class="btn btn-sm btn-soft-primary">
        <i class="ri-add-line me-1"></i>Ajouter une option
    </button>
    <small class="text-muted ms-2">Entre 2 et 6 options, une seule bonne réponse.</small>

    @error('options')
        <div class="text-danger small mt-2">{{ $message }}</div>
    @enderror
    @error('correcte')
        <div class="text-danger small mt-2">{{ $message }}</div>
    @enderror
    {{-- Erreurs propres à une option (ex: texte vide) : clés "options.0.libelle", etc. --}}
    @foreach (collect($errors->keys())->filter(fn ($k) => str_starts_with($k, 'options.'))->map(fn ($k) => $errors->first($k))->unique() as $message)
        <div class="text-danger small mt-2">{{ $message }}</div>
    @endforeach
</div>

{{-- Modèle d'une ligne d'option, cloné par le JavaScript (__CLE__ est remplacé par un nouvel index) --}}
<template id="option-template">
    <div class="input-group mb-2 option-row">
        <div class="input-group-text">
            <input class="form-check-input mt-0" type="radio" name="correcte" value="__CLE__" title="Bonne réponse" required>
        </div>
        <input type="hidden" name="options[__CLE__][id]" value="">
        <input type="text" name="options[__CLE__][libelle]" class="form-control" maxlength="255"
               placeholder="Texte de l'option" required>
        <button type="button" class="btn btn-soft-danger btn-supprimer-option" title="Retirer cette option">
            <i class="ri-delete-bin-line"></i>
        </button>
    </div>
</template>

<div class="d-flex gap-2">
    <button type="submit" class="btn btn-primary">{{ isset($question) ? 'Enregistrer les modifications' : 'Ajouter la question' }}</button>
    <a href="{{ route('admin.questions.index') }}" class="btn btn-light">Annuler</a>
</div>

@section('script')
<script>
    (function () {
        const MIN = 2, MAX = 6;
        const conteneur = document.getElementById('options-container');
        const modele = document.getElementById('option-template').innerHTML;
        const boutonAjout = document.getElementById('btn-ajouter-option');
        let prochaineCle = {{ $prochaineCle }};

        // Active/désactive les boutons selon le nombre d'options (min 2, max 6)
        function actualiser() {
            const lignes = conteneur.querySelectorAll('.option-row');
            boutonAjout.disabled = lignes.length >= MAX;
            lignes.forEach(l => l.querySelector('.btn-supprimer-option').disabled = lignes.length <= MIN);
        }

        boutonAjout.addEventListener('click', function () {
            conteneur.insertAdjacentHTML('beforeend', modele.replaceAll('__CLE__', prochaineCle++));
            actualiser();
        });

        // Délégation d'événement : fonctionne aussi pour les lignes ajoutées dynamiquement
        conteneur.addEventListener('click', function (e) {
            const bouton = e.target.closest('.btn-supprimer-option');
            if (bouton && !bouton.disabled) {
                bouton.closest('.option-row').remove();
                actualiser();
            }
        });

        actualiser();
    })();
</script>
@endsection