<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Feuille de présence — {{ $classe->cours->titre }} — {{ $classe->nom }}</title>

    {{-- Document autonome pour dompdf (comme le reçu de paiement) : CSS ici, mise en page en
         tableaux (dompdf ne gère ni flexbox ni grid), police DejaVu Sans pour les accents. --}}
    <style>
        @page { margin: 10mm 10mm 14mm 10mm; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #111; margin: 0; }
        h1 { font-size: 16px; margin: 0 0 2px 0; }
        h2 { font-size: 12px; margin: 14px 0 6px 0; }
        .muted { color: #666; }
        .entete td { vertical-align: middle; }
        .logo { width: 110px; }
        table.grille { width: 100%; border-collapse: collapse; }
        table.grille th, table.grille td { border: 1px solid #bbb; padding: 3px 4px; }
        table.grille th { background: #eef0f6; font-weight: bold; }
        .c { text-align: center; }
        /* Couleurs des cases : vert = présent, rouge = absent, gris = non comptabilisé */
        .present { background: #d4f4e4; color: #0a7a46; font-weight: bold; }
        .absent  { background: #fbe0e0; color: #c0392b; font-weight: bold; }
        .exclu   { background: #f2f2f2; color: #999; }
        .ok  { color: #0a7a46; font-weight: bold; }
        .nok { color: #b7791f; font-weight: bold; }
        /* Un bloc de jours n'est jamais coupé entre deux pages */
        .bloc { page-break-inside: avoid; margin-bottom: 8px; }
        /* Pied de page répété sur chaque page (position fixed dans dompdf) */
        .pied { position: fixed; bottom: -9mm; left: 0; right: 0; font-size: 8px; color: #888; }
    </style>
</head>
<body>
@php
    // dompdf lit le logo sur le disque
    $logo = public_path('images/logo_neovision.png');

    // Beaucoup de jours ne tiennent pas sur une largeur A4 : on coupe la grille en blocs de 16 jours
    // (la colonne « Membre » est répétée dans chaque bloc).
    $blocs = $jours->chunk(16);
@endphp

    <div class="pied">
        NEO VISION — Feuille de présence générée le {{ now()->format('d/m/Y à H:i') }} pour {{ $entreprise->raison_sociale }}
    </div>

    {{-- En-tête : logo + informations de la classe --}}
    <table class="entete" style="width: 100%; margin-bottom: 8px;">
        <tr>
            <td style="width: 130px;"><img class="logo" src="{{ $logo }}" alt="NEO VISION"></td>
            <td>
                <h1>Feuille de présence</h1>
                <div><strong>{{ $classe->cours->titre }}</strong> — classe {{ $classe->nom }}
                    @if ($classe->session) — session {{ $classe->session->nom }} @endif
                </div>
                <div class="muted">
                    Du {{ $classe->date_debut->format('d/m/Y') }} au {{ $classe->date_fin->format('d/m/Y') }}
                    · {{ $classe->lieu }}
                    @if ($classe->formateur) · Formateur : {{ $classe->formateur->name }} @endif
                </div>
                <div class="muted">
                    Entreprise : {{ $entreprise->raison_sociale }}
                    · Classe fermée le {{ $classe->fermee_le->format('d/m/Y') }}
                    · {{ $nbJoursComptes }} jour(s) comptabilisé(s)
                </div>
            </td>
        </tr>
    </table>

    {{-- Récapitulatif --}}
    <h2>Récapitulatif</h2>
    <table class="grille">
        <thead>
            <tr>
                <th style="text-align: left;">Membre</th>
                <th style="text-align: left;">E-mail</th>
                <th class="c">Jours présents</th>
                <th class="c">Jours absents</th>
                <th class="c">Taux de présence</th>
                <th class="c">Attestation (seuil {{ $seuil }} %)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($lignes as $ligne)
                <tr>
                    <td>{{ $ligne['membre']->name }}</td>
                    <td>{{ $ligne['membre']->email }}</td>
                    <td class="c">{{ $ligne['presents'] }}</td>
                    <td class="c">{{ $ligne['absents'] }}</td>
                    <td class="c">{{ rtrim(rtrim(number_format($ligne['taux'], 2, ',', ''), '0'), ',') }} %</td>
                    <td class="c {{ $ligne['attestation'] ? 'ok' : 'nok' }}">{{ $ligne['attestation'] ? 'Délivrée' : 'Non délivrée' }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="c muted">Aucun membre inscrit par l'entreprise.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- Grille jour par jour, en blocs de 16 jours --}}
    <h2>Présences jour par jour</h2>
    <div class="muted" style="margin-bottom: 4px;">P = présent · A = absent · — = jour non comptabilisé</div>

    @foreach ($blocs as $bloc)
        <div class="bloc">
            <table class="grille">
                <thead>
                    <tr>
                        <th style="text-align: left; width: 150px;">Membre</th>
                        @foreach ($bloc as $jour)
                            <th class="c">{{ $jour->translatedFormat('D') }}<br>{{ $jour->format('d/m') }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($lignes as $ligne)
                        <tr>
                            <td>{{ $ligne['membre']->name }}</td>
                            @foreach ($bloc as $jour)
                                {{-- Statut du membre ce jour-là ; la classe CSS du même nom colore la case --}}
                                @php $statut = $ligne['statuts'][$jour->toDateString()]; @endphp
                                <td class="c {{ $statut }}">{{ ['present' => 'P', 'absent' => 'A', 'exclu' => '—'][$statut] }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach
</body>
</html>