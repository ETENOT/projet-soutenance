{{--
    Choix du mode de paiement par l'ADMINISTRATEUR (à inclure dans un <form>) :
      - « Paiement reçu »    -> type_paiement = complet : reste à payer = 0 ;
      - « Paiement partiel » -> type_paiement = partiel + montant_partiel (somme versée).
    La somme partielle s'applique à TOUS les particuliers sélectionnés ; elle reste modifiable
    ensuite, inscrit par inscrit (bouton « Modifier la somme versée »).

    Variables :
      $prefixe : préfixe unique des id (plusieurs inclusions possibles sur une même page)
      $prix    : (facultatif) prix de la formation affiché comme repère, en fcfa
--}}
@php
    $prefixe = $prefixe ?? 'choix';
    // Après une erreur de validation, on remet le choix et la somme saisis
    $typeChoisi = old('type_paiement', 'complet');
@endphp

<div class="js-choix-paiement">
    <div class="d-flex flex-column gap-2">
        {{-- Paiement reçu en totalité --}}
        <label class="border rounded p-3 d-flex gap-2 mb-0" for="{{ $prefixe }}-complet">
            <input class="form-check-input mt-1 js-type-paiement" type="radio" name="type_paiement"
                   id="{{ $prefixe }}-complet" value="complet" @checked($typeChoisi !== 'partiel')>
            <span>
                <span class="fw-semibold d-block"><i class="ri-checkbox-circle-line text-success me-1"></i>Paiement reçu</span>
                <small class="text-muted">La totalité de la formation est payée : reste à payer 0 fcfa.</small>
            </span>
        </label>

        {{-- Paiement partiel : la somme versée devient obligatoire --}}
        <label class="border rounded p-3 d-flex gap-2 mb-0" for="{{ $prefixe }}-partiel">
            <input class="form-check-input mt-1 js-type-paiement" type="radio" name="type_paiement"
                   id="{{ $prefixe }}-partiel" value="partiel" @checked($typeChoisi === 'partiel')>
            <span>
                <span class="fw-semibold d-block"><i class="ri-pie-chart-line text-warning me-1"></i>Paiement partiel</span>
                <small class="text-muted">
                    Accès au cours comme un paiement reçu, mais pas d'attestation tant que la totalité n'est pas réglée.
                </small>
            </span>
        </label>
    </div>

    {{-- Somme versée : visible uniquement pour le paiement partiel --}}
    <div class="mt-3 js-bloc-montant {{ $typeChoisi === 'partiel' ? '' : 'd-none' }}">
        <label for="{{ $prefixe }}-montant" class="form-label">
            Somme versée par chaque particulier sélectionné
        </label>
        <div class="input-group">
            <input type="number" min="1" step="1" name="montant_partiel" id="{{ $prefixe }}-montant"
                   class="form-control js-montant-partiel @error('montant_partiel') is-invalid @enderror"
                   value="{{ old('montant_partiel') }}" placeholder="Ex. 50000"
                   @disabled($typeChoisi !== 'partiel') @required($typeChoisi === 'partiel')>
            <span class="input-group-text">fcfa</span>
        </div>
        @error('montant_partiel')
            <div class="text-danger fs-13 mt-1">{{ $message }}</div>
        @enderror
        <div class="form-text">
            @isset($prix)
                Prix de la formation : <strong class="js-prix">{{ number_format($prix, 0, ',', ' ') }}</strong> fcfa.
            @endisset
            La somme doit être inférieure au prix. Vous pourrez la modifier ensuite pour chaque inscrit.
        </div>
    </div>
</div>

{{-- Script chargé une seule fois, même si le partiel est inclus plusieurs fois --}}
@once
    <script>
        // Affiche / masque la somme versée selon le choix « Paiement reçu » / « Paiement partiel ».
        // Délégation d'événement : fonctionne pour toutes les inclusions de la page.
        document.addEventListener('change', function (e) {
            if (!e.target.classList.contains('js-type-paiement')) return;

            const bloc = e.target.closest('.js-choix-paiement');
            const partiel = e.target.value === 'partiel';
            const zone = bloc.querySelector('.js-bloc-montant');
            const champ = bloc.querySelector('.js-montant-partiel');

            zone.classList.toggle('d-none', !partiel);
            champ.disabled = !partiel;   // champ désactivé = non envoyé au serveur
            champ.required = partiel;
            if (partiel) champ.focus();
        });
    </script>
@endonce
