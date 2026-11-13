{{--
    Choix du PARTICULIER avant la demande Airtel Money (à inclure dans le <form> de paiement en ligne) :
      - « Payer totalement »    -> type_paiement = total : Airtel Money demande le prix complet ;
      - « Payer partiellement » -> type_paiement = partiel + montant_partiel : au moins la moitié du prix.
        Une somme trop basse est refusée par le serveur avec le message
        « Vous devez payer au moins la moitié de la formation ».

    Variables : $montant (prix complet), $minimumPartiel (moitié du prix, arrondie au franc supérieur)
--}}
@php
    $typeChoisi = old('type_paiement', 'total');
    $fcfa = fn ($valeur) => number_format($valeur, 0, ',', ' ');
@endphp

<div class="js-choix-en-ligne" data-minimum="{{ $minimumPartiel }}" data-total="{{ $montant }}">
    <div class="d-flex flex-column gap-2 mb-3">
        {{-- Payer la totalité --}}
        <label class="border rounded p-3 d-flex gap-2 mb-0" for="paiement-total">
            <input class="form-check-input mt-1 js-type-en-ligne" type="radio" name="type_paiement"
                   id="paiement-total" value="total" @checked($typeChoisi !== 'partiel')>
            <span>
                <span class="fw-semibold d-block">Payer totalement</span>
                <small class="text-muted">{{ $fcfa($montant) }} fcfa — accès au cours et attestation en fin de formation.</small>
            </span>
        </label>

        {{-- Payer une partie --}}
        <label class="border rounded p-3 d-flex gap-2 mb-0" for="paiement-partiel">
            <input class="form-check-input mt-1 js-type-en-ligne" type="radio" name="type_paiement"
                   id="paiement-partiel" value="partiel" @checked($typeChoisi === 'partiel')>
            <span>
                <span class="fw-semibold d-block">Payer partiellement</span>
                <small class="text-muted">
                    Au moins {{ $fcfa($minimumPartiel) }} fcfa (la moitié de la formation). Vous accédez au cours ;
                    l'attestation de suivi ne sera délivrée qu'après le paiement de la totalité.
                </small>
            </span>
        </label>
    </div>

    {{-- Somme à payer : visible seulement pour le paiement partiel --}}
    <div class="mb-3 js-bloc-montant-en-ligne {{ $typeChoisi === 'partiel' ? '' : 'd-none' }}">
        <label for="montant-partiel" class="form-label">Somme que vous souhaitez payer</label>
        <div class="input-group">
            {{-- Pas d'attributs min/max : notre message en français s'affiche à la place de celui du navigateur --}}
            <input type="number" step="1"
                   name="montant_partiel" id="montant-partiel"
                   class="form-control js-montant-en-ligne @error('montant_partiel') is-invalid @enderror"
                   value="{{ old('montant_partiel') }}" placeholder="{{ $minimumPartiel }}"
                   @disabled($typeChoisi !== 'partiel')>
            <span class="input-group-text">fcfa</span>
        </div>
        {{-- Message d'erreur du serveur (ex. moins de la moitié du prix) --}}
        @error('montant_partiel')
            <div class="text-danger fs-13 mt-1">{{ $message }}</div>
        @enderror
        {{-- Message d'erreur immédiat (avant l'envoi) --}}
        <div class="text-danger fs-13 mt-1 d-none js-erreur-en-ligne"></div>
        <div class="form-text js-reste-en-ligne"></div>
    </div>
</div>

@once
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.js-choix-en-ligne').forEach(function (bloc) {
                const minimum = parseFloat(bloc.dataset.minimum);
                const total = parseFloat(bloc.dataset.total);
                const zone = bloc.querySelector('.js-bloc-montant-en-ligne');
                const champ = bloc.querySelector('.js-montant-en-ligne');
                const erreur = bloc.querySelector('.js-erreur-en-ligne');
                const reste = bloc.querySelector('.js-reste-en-ligne');
                const formulaire = bloc.closest('form');
                const fcfa = n => Number(n).toLocaleString('fr-FR', { maximumFractionDigits: 0 });

                // Affiche le reste à payer pendant la saisie
                function majReste() {
                    const valeur = parseFloat(champ.value);
                    reste.textContent = !isNaN(valeur) && valeur >= minimum && valeur < total
                        ? 'Reste à payer après ce paiement : ' + fcfa(total - valeur) + ' fcfa.'
                        : '';
                }

                // Payer totalement / partiellement : on montre ou cache la somme
                bloc.querySelectorAll('.js-type-en-ligne').forEach(function (radio) {
                    radio.addEventListener('change', function () {
                        const partiel = radio.value === 'partiel' && radio.checked;
                        zone.classList.toggle('d-none', !partiel);
                        champ.disabled = !partiel;   // désactivé = non envoyé
                        erreur.classList.add('d-none');
                        if (partiel) champ.focus();
                    });
                });

                champ.addEventListener('input', function () {
                    erreur.classList.add('d-none');
                    majReste();
                });

                // Vérification avant l'envoi (le serveur refait la même vérification)
                formulaire.addEventListener('submit', function (e) {
                    if (champ.disabled) return;   // paiement total : rien à vérifier
                    const valeur = parseFloat(champ.value);
                    let message = '';
                    if (isNaN(valeur)) {
                        message = 'Indiquez la somme que vous souhaitez payer.';
                    } else if (valeur < minimum) {
                        message = 'Vous devez payer au moins la moitié de la formation, soit ' + fcfa(minimum) + ' fcfa.';
                    } else if (valeur > total) {
                        message = 'La somme ne peut pas dépasser le prix de la formation (' + fcfa(total) + ' fcfa).';
                    }
                    if (message) {
                        e.preventDefault();
                        e.stopImmediatePropagation();   // empêche le blocage du bouton « Redirection… »
                        erreur.textContent = message;
                        erreur.classList.remove('d-none');
                        champ.focus();
                    }
                });

                majReste();
            });
        });
    </script>
@endonce
