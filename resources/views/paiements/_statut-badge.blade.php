{{--
    Badge du statut de paiement d'une inscription : Payé / Paiement partiel / En attente.
    Pour un paiement partiel, le reste à payer est affiché sous le badge (sauf $sansReste = true).

    Variables : $inscription (avec sa relation "paiement" chargée), $sansReste (facultatif)
--}}
@php $statutPaiement = $inscription->statutPaiement(); @endphp

@if ($statutPaiement === 'paye')
    <span class="badge bg-success-subtle text-success">Payé</span>
@elseif ($statutPaiement === 'partiel')
    <span class="badge bg-info-subtle text-info">Paiement partiel</span>
    @unless ($sansReste ?? false)
        <small class="d-block text-muted text-nowrap">
            Reste : {{ number_format($inscription->paiement->reste_a_payer, 0, ',', ' ') }} fcfa
        </small>
    @endunless
@else
    <span class="badge bg-warning-subtle text-warning">En attente</span>
@endif
