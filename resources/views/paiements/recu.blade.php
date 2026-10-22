<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Reçu {{ $paiement->numero_recu }}</title>

    {{-- Document autonome (il n'étend pas layouts.master) : le CSS est ici car dompdf
         ne charge pas les feuilles compilées de Velzon. Mise en page en tableaux (dompdf
         ne gère ni flexbox ni grid). Police DejaVu Sans pour les accents.
         Format : petit reçu A6 paysage, sobre (noir et blanc, le logo est la seule couleur). --}}
    <style>
        @page { size: 148mm 105mm; margin: 5mm; }
        * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #111; margin: 0; background: #fff; }
        table { width: 100%; border-collapse: collapse; }
        td { vertical-align: middle; }

        .barre { width: 520px; margin: 18px auto 0; text-align: right; }
        .btn { display: inline-block; padding: 7px 14px; border-radius: 4px; font-size: 13px; text-decoration: none; border: 0; cursor: pointer; color: #fff; background: #374151; }

        .page { box-sizing: border-box; width: 520px; margin: 12px auto 32px; background: #fff; padding: 8px 10px; box-shadow: 0 1px 4px rgba(0,0,0,.2); }
        .pdf .page { width: 100%; margin: 0; padding: 0; box-shadow: none; }

        .logo { width: 82px; }
        .titre { text-align: right; }
        .titre .nom { font-size: 13px; font-weight: bold; letter-spacing: 1px; }
        .titre .petit { font-size: 9px; color: #444; margin-top: 2px; }

        .ligne { border-top: 1px solid #111; margin: 5px 0 3px; }

        .infos td { padding: 3px 0; border-bottom: 1px dotted #999; font-size: 10px; }
        .infos .lib { width: 118px; color: #555; font-size: 9px; text-transform: uppercase; letter-spacing: .5px; }
        .infos .val { font-weight: bold; }

        .total { border: 1.5px solid #111; margin-top: 6px; }
        .total td { padding: 5px 10px; }
        .total .lib { font-size: 9px; text-transform: uppercase; letter-spacing: .5px; }
        .total .montant { text-align: right; font-size: 16px; font-weight: bold; }

        .bas { margin-top: 6px; }
        .paye { display: inline-block; border: 1.5px solid #111; padding: 2px 10px; font-size: 11px; font-weight: bold; letter-spacing: 2px; }
        .paye-date { font-size: 9px; color: #444; margin-top: 3px; }
        .signature { height: 22px; border-bottom: 1px solid #111; }
        .sig-lib { margin-top: 3px; font-size: 8px; text-transform: uppercase; letter-spacing: .5px; color: #555; }

        .pied { margin-top: 5px; font-size: 8px; color: #555; text-align: center; }

        @media print {
            body { background: #fff; }
            .barre { display: none; }
            .page { width: 100%; margin: 0; padding: 0; box-shadow: none; }
        }
    </style>
</head>
<body class="{{ $pdf ? 'pdf' : '' }}">

{{-- Boutons visibles à l'écran uniquement (jamais dans le PDF ni à l'impression) --}}
@unless ($pdf)
    <div class="barre">
        <button type="button" class="btn" onclick="window.print()">Imprimer</button>
        <a class="btn" href="{{ route('paiements.recu.telecharger', $paiement) }}">Télécharger le PDF</a>
        <a class="btn" href="javascript:history.back()">Retour</a>
    </div>
@endunless

@php
    $inscription = $paiement->inscription;
    $classe = $inscription->classe;
    $cours = $classe->cours;
    $stagiaire = $inscription->user;

    // dompdf lit le logo sur le disque ; le navigateur le lit par son URL.
    $logo = $pdf ? public_path('images/logo_neovision.png') : asset('images/logo_neovision.png');
@endphp

<div class="page">
    <table>
        <tr>
            <td><img class="logo" src="{{ $logo }}" alt="NEO VISION"></td>
            <td class="titre">
                <div class="nom">REÇU DE PAIEMENT</div>
                <div class="petit">N° {{ $paiement->numero_recu }} &nbsp;·&nbsp; {{ $paiement->created_at->format('d/m/Y à H:i') }}</div>
            </td>
        </tr>
    </table>

    <div class="ligne"></div>

    <table class="infos">
        <tr>
            <td class="lib">Reçu de</td>
            <td class="val">{{ $stagiaire->name }}</td>
        </tr>
        <tr>
            <td class="lib">Formation</td>
            <td class="val">{{ $cours->titre }}</td>
        </tr>
        <tr>
            <td class="lib">Classe</td>
            <td class="val">{{ $classe->nom }}</td>
        </tr>
        <tr>
            <td class="lib">Période</td>
            <td class="val">du {{ $classe->date_debut->format('d/m/Y') }} au {{ $classe->date_fin->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="lib">Paiement</td>
            <td class="val">
                {{ $paiement->libelle_mode }}
                @if ($paiement->reference)
                    · Réf. {{ $paiement->reference }}
                @endif
            </td>
        </tr>
    </table>

    <table class="total">
        <tr>
            <td class="lib">Montant payé</td>
            <td class="montant">{{ number_format($paiement->montant, 0, ',', ' ') }} fcfa</td>
        </tr>
    </table>

    <table class="bas">
        <tr>
            <td style="width: 38%">
                <span class="paye">PAYÉ</span>
                <div class="paye-date">le {{ $paiement->created_at->format('d/m/Y') }}</div>
            </td>
            <td style="width: 62%; vertical-align: top">
                <div class="signature"></div>
                <div class="sig-lib">Cachet et signature</div>
            </td>
        </tr>
    </table>

    <div class="pied">NEO VISION · Ce reçu atteste le règlement des frais de formation ci-dessus.</div>
</div>

</body>
</html>