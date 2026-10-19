{{--
    Sélecteur de "jours non comptabilisés" dans le taux de présence.
    Paramètres :
      $joursExclus  : tableau de dates Y-m-d déjà exclues
      $debutId / $finId : ids des champs date de début/fin du formulaire (formulaires admin, dates modifiables)
      $min / $max   : bornes fixes Y-m-d (page du formateur, dates non modifiables) — utilisés si pas d'ids
      $verrouille   : true = lecture seule (classe fermée)
    Chaque jour choisi devient un <input type="hidden" name="jours_exclus[]"> envoyé avec le formulaire.
--}}
@php
    // old() : si la validation a échoué, on remet la liste que l'utilisateur venait de saisir
    $valeursJoursExclus = collect(old('jours_exclus', $joursExclus ?? []))->filter()->unique()->sort()->values();
    $verrouille = $verrouille ?? false;
@endphp

<div id="jours-exclus-bloc">
    <label class="form-label">Jours non comptabilisés dans le taux de présence <span class="text-muted">(facultatif)</span></label>

    @unless ($verrouille)
        <div class="input-group mb-2">
            <input type="date" id="jour-exclu-saisie" class="form-control"
                   @isset($min) min="{{ $min }}" @endisset
                   @isset($max) max="{{ $max }}" @endisset>
            <button type="button" id="jour-exclu-ajouter" class="btn btn-outline-primary">
                <i class="ri-add-line me-1"></i>Ajouter ce jour
            </button>
        </div>
    @endunless

    {{-- La liste est dessinée par le script ci-dessous --}}
    <div id="jours-exclus-liste" class="d-flex flex-wrap gap-2"></div>
    <div id="jours-exclus-message" class="text-danger fs-12 mt-1"></div>

    <div class="form-text">
        Jour férié, séance annulée, formateur absent… Ces jours ne comptent ni comme présence ni comme absence.
        @unless ($verrouille) Modifiable à tout moment, jusqu'à la fermeture de la classe. @endunless
    </div>

    @error('jours_exclus')<div class="text-danger fs-12 mt-1">{{ $message }}</div>@enderror
    @error('jours_exclus.*')<div class="text-danger fs-12 mt-1">{{ $message }}</div>@enderror
</div>

<script>
    (function () {
        // Jours actuellement exclus (format YYYY-MM-DD)
        let jours = @json($valeursJoursExclus);
        const verrouille = @json($verrouille);
        const liste = document.getElementById('jours-exclus-liste');
        const message = document.getElementById('jours-exclus-message');
        const saisie = document.getElementById('jour-exclu-saisie');

        // Bornes de la période : lues dans les champs date du formulaire si on les a, sinon valeurs fixes
        function bornes() {
            const debutChamp = document.getElementById(@json($debutId ?? ''));
            const finChamp = document.getElementById(@json($finId ?? ''));
            return {
                min: debutChamp ? debutChamp.value : @json($min ?? ''),
                max: finChamp ? finChamp.value : @json($max ?? ''),
            };
        }

        // "2026-10-07" -> "mer. 07/10/2026"
        function libelle(iso) {
            const [a, m, j] = iso.split('-').map(Number);
            return new Date(a, m - 1, j).toLocaleDateString('fr-FR', {
                weekday: 'short', day: '2-digit', month: '2-digit', year: 'numeric',
            });
        }

        // Ajoute la date choisie dans le champ à la liste.
        // Retourne false si la date est refusée (dimanche, hors période) : le message explique pourquoi.
        function ajouterSaisie() {
            message.textContent = '';
            const iso = saisie.value;
            if (!iso) { return true; }   // rien à ajouter

            const b = bornes();
            const [a, m, j] = iso.split('-').map(Number);
            const jourSemaine = new Date(a, m - 1, j).getDay(); // 0 = dimanche (le samedi est un jour de cours)

            if (jourSemaine === 0) {
                message.textContent = 'Ce jour tombe un dimanche : il n\'est de toute façon pas compté.';
                return false;
            }
            if ((b.min && iso < b.min) || (b.max && iso > b.max)) {
                message.textContent = 'Ce jour est en dehors de la période de la classe.';
                return false;
            }

            if (!jours.includes(iso)) {
                jours.push(iso);
                dessiner();
            }
            saisie.value = '';
            return true;
        }

        if (saisie) {
            document.getElementById('jour-exclu-ajouter').addEventListener('click', ajouterSaisie);

            // Date choisie mais pas "ajoutée" : on l'ajoute automatiquement à l'enregistrement
            // (sinon elle serait perdue en silence). Si elle est refusée, on bloque l'envoi.
            if (saisie.form) {
                saisie.form.addEventListener('submit', function (e) {
                    if (!ajouterSaisie()) { e.preventDefault(); }
                });
            }
        }

        // Redessine les badges + les champs cachés envoyés au serveur
        function dessiner() {
            jours.sort();
            liste.innerHTML = '';
            jours.forEach(function (iso) {
                const badge = document.createElement('span');
                badge.className = 'badge bg-warning-subtle text-warning fs-12 d-inline-flex align-items-center';
                badge.textContent = libelle(iso);

                const cache = document.createElement('input');
                cache.type = 'hidden';
                cache.name = 'jours_exclus[]';
                cache.value = iso;
                badge.appendChild(cache);

                if (!verrouille) {
                    const retirer = document.createElement('button');
                    retirer.type = 'button';
                    retirer.className = 'btn-close ms-2';
                    retirer.style.fontSize = '0.6rem';
                    retirer.setAttribute('aria-label', 'Retirer ce jour');
                    retirer.addEventListener('click', function () {
                        jours = jours.filter(function (j) { return j !== iso; });
                        dessiner();
                    });
                    badge.appendChild(retirer);
                }
                liste.appendChild(badge);
            });
        }

        if (saisie) {
            document.getElementById('jour-exclu-ajouter').addEventListener('click', function () {
                message.textContent = '';
                const iso = saisie.value;
                if (!iso) { return; }

                const b = bornes();
                const [a, m, j] = iso.split('-').map(Number);
                const jourSemaine = new Date(a, m - 1, j).getDay(); // 0 = dimanche (le samedi est un jour de cours)

                if (jourSemaine === 0) {
                    message.textContent = 'Ce jour tombe un dimanche : il n\'est de toute façon pas compté.';
                } else if ((b.min && iso < b.min) || (b.max && iso > b.max)) {
                    message.textContent = 'Ce jour est en dehors de la période de la classe.';
                } else if (!jours.includes(iso)) {
                    jours.push(iso);
                    dessiner();
                }
                saisie.value = '';
            });
        }

        dessiner();
    })();
</script>