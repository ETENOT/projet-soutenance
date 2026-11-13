{{--
    Fenêtre « Modifier la somme versée » (administrateur).
    S'ouvre avec un bouton portant :
      data-bs-toggle="modal" data-bs-target="#modalMontantVerse"
      data-action="{{ route('paiements.update', $paiement) }}"
      data-nom="Nom du stagiaire"
      data-montant="somme déjà versée" data-total="prix total"
    Somme = total  -> le paiement devient « Payé » (attestation délivrée si la classe est fermée).
--}}
<div class="modal fade" id="modalMontantVerse" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="formMontantVerse" method="POST" action="#">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Modifier la somme versée</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">Paiement de <strong id="montantVerseNom"></strong></p>

                    <label for="champMontantVerse" class="form-label">Somme versée</label>
                    <div class="input-group">
                        <input type="number" min="1" step="1" name="montant" id="champMontantVerse" class="form-control" required>
                        <span class="input-group-text">fcfa</span>
                    </div>
                    <div class="form-text">
                        Prix de la formation : <strong id="montantVerseTotal"></strong> fcfa.
                        <span id="montantVerseReste"></span>
                    </div>

                    {{-- Raccourci : tout est réglé --}}
                    <button type="button" class="btn btn-sm btn-soft-success mt-2" id="montantVerseSolder">
                        <i class="ri-checkbox-circle-line me-1"></i>Le stagiaire a tout payé
                    </button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const fenetre = document.getElementById('modalMontantVerse');
    const champ = document.getElementById('champMontantVerse');
    const reste = document.getElementById('montantVerseReste');
    const fcfa = n => Number(n).toLocaleString('fr-FR', { maximumFractionDigits: 0 });
    let total = 0;

    // Recalcule le reste à payer pendant la saisie
    function majReste() {
        const verse = parseFloat(champ.value) || 0;
        const r = total - verse;
        reste.textContent = r > 0
            ? 'Reste à payer : ' + fcfa(r) + ' fcfa (paiement partiel).'
            : (r === 0 ? 'Reste à payer : 0 fcfa (le paiement deviendra « Payé »).' : 'La somme dépasse le prix.');
        reste.className = r < 0 ? 'text-danger' : '';
    }

    // Ouverture : on remplit la fenêtre avec les données du bouton cliqué
    fenetre.addEventListener('show.bs.modal', function (e) {
        const d = e.relatedTarget.dataset;
        total = parseFloat(d.total) || 0;
        document.getElementById('formMontantVerse').action = d.action;
        document.getElementById('montantVerseNom').textContent = d.nom;
        document.getElementById('montantVerseTotal').textContent = fcfa(total);
        champ.max = total;
        champ.value = Math.round(parseFloat(d.montant) || 0);
        majReste();
    });

    champ.addEventListener('input', majReste);
    document.getElementById('montantVerseSolder').addEventListener('click', function () {
        champ.value = total;
        majReste();
    });
});
</script>
