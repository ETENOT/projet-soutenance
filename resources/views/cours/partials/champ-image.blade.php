{{--
    Champ « Image de la formation » des formulaires de création et de modification d'un cours.
    Paramètre : $cours (facultatif, présent seulement à la modification : sert à montrer l'image actuelle).
    L'image choisie est affichée tout de suite en aperçu (script en bas de ce fichier).
--}}
@php
    $imageActuelle = isset($cours) && $cours->image ? asset('storage/' . $cours->image) : null;
@endphp

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="ri-image-line me-1 text-primary"></i>
            Image de la formation
        </h5>
    </div>

    <div class="card-body">
        {{-- Aperçu : même forme que l'emplacement image des cartes du catalogue --}}
        <div class="ratio ratio-21x9 bg-primary-subtle rounded mb-3">
            <img id="apercu-image" src="{{ $imageActuelle }}" alt="Aperçu de l'image"
                 class="object-fit-contain p-2 {{ $imageActuelle ? '' : 'd-none' }}">
            <div id="apercu-vide"
                 class="d-flex align-items-center justify-content-center text-muted {{ $imageActuelle ? 'd-none' : '' }}">
                <span><i class="ri-image-add-line me-1"></i>Aucune image</span>
            </div>
        </div>

        <input type="file" name="image" id="image" accept="image/png,image/jpeg,image/webp,image/gif"
               class="form-control @error('image') is-invalid @enderror">
        @error('image')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">
            Logo ou photo qui représente la formation (par exemple le logo de Word).
            JPG, PNG, WebP ou GIF, 2 Mo maximum. Un logo sur fond transparent donne le meilleur rendu.
        </div>

        @if ($imageActuelle)
            <div class="form-check mt-3">
                <input class="form-check-input" type="checkbox" name="supprimer_image" value="1" id="supprimer_image">
                <label class="form-check-label" for="supprimer_image">Supprimer l'image actuelle</label>
            </div>
        @endif
    </div>
</div>

<script>
    // Aperçu immédiat de l'image choisie, avant l'enregistrement
    document.addEventListener('DOMContentLoaded', function () {
        const champ = document.getElementById('image');
        const apercu = document.getElementById('apercu-image');
        const vide = document.getElementById('apercu-vide');

        champ.addEventListener('change', function () {
            const fichier = champ.files[0];
            if (!fichier) { return; }

            apercu.src = URL.createObjectURL(fichier);
            apercu.classList.remove('d-none');
            vide.classList.add('d-none');
        });
    });
</script>