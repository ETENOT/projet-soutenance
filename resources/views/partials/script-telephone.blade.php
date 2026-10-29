{{--
    Active le sélecteur de pays (intl-tel-input) sur chaque <input type="tel" data-champ-telephone="nom_du_champ">.
    Le numéro complet, au format international (+24177123456), est envoyé dans un champ caché portant ce nom.
    À inclure dans @section('script') ; le CSS (partials.css-telephone) va dans @section('css').
--}}
<script src="{{ URL::asset('build/libs/intl-tel-input/js/intlTelInputWithUtils.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('input[data-champ-telephone]').forEach(function (input) {
            const nom = input.dataset.champTelephone;

            const champ = window.intlTelInput(input, {
                initialCountry: 'ga',
                countryOrder: ['ga', 'fr'],
                countryNameLocale: 'fr',
                separateDialCode: true,
                strictMode: true,
                hiddenInputs: () => ({ phone: nom }),
            });

            // Valeur déjà connue (modification, ou retour après une erreur de validation)
            if (input.value) {
                champ.setNumber(input.value);
            }

            function verifier() {
                input.setCustomValidity(
                    input.value.trim() !== '' && champ.isValidNumber() === false
                        ? 'Numéro invalide pour le pays sélectionné.'
                        : ''
                );
            }

            input.addEventListener('input', verifier);
            input.addEventListener('countrychange', verifier);
            verifier();
        });
    });
</script>