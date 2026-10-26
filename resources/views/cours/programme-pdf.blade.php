<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Programme - {{ $cours->titre }}</title>
    <style>
        @page { margin: 22mm 18mm; }
        body { color: #273449; font-family: DejaVu Sans, sans-serif; font-size: 12px; line-height: 1.6; }
        .entete { border-bottom: 2px solid #c8202f; margin-bottom: 26px; padding-bottom: 16px; }
        .logo { height: 52px; }
        h1 { color: #172b4d; font-size: 25px; margin: 18px 0 8px; }
        h2 { border-bottom: 1px solid #d9dee7; color: #172b4d; font-size: 17px; margin: 26px 0 12px; padding-bottom: 6px; }
        h3 { color: #c8202f; font-size: 14px; margin: 0 0 8px; }
        .meta { color: #65738a; font-size: 11px; }
        .description { margin-top: 22px; }
        .chapitre { page-break-inside: avoid; margin: 0 0 18px; }
        .prix { border-collapse: collapse; margin-top: 8px; width: 100%; }
        .prix td { border: 1px solid #d9dee7; padding: 9px 11px; }
        .prix td:last-child { font-weight: bold; text-align: right; }
        .pied { border-top: 1px solid #d9dee7; color: #65738a; font-size: 9px; margin-top: 30px; padding-top: 10px; text-align: center; }
    </style>
</head>
<body>
    <div class="entete">
        <img class="logo" src="{{ $logo }}" alt="NEO VISION">
        <h1>{{ $cours->titre }}</h1>
        <div class="meta">{{ $cours->categorie }} · Niveau : {{ $cours->niveau ?: 'Non précisé' }}</div>
    </div>

    <h2>Présentation</h2>
    <div class="description">{!! nl2br(e($cours->description)) !!}</div>

    <h2>Programme</h2>
    @forelse ($cours->chapitres as $chapitre)
        <section class="chapitre">
            <h3>{{ $loop->iteration }}. {{ $chapitre->titre }}</h3>
            <div>{!! nl2br(e($chapitre->contenu)) !!}</div>
        </section>
    @empty
        <p>Le programme de cette formation n'est pas encore disponible.</p>
    @endforelse

    <h2>Tarifs</h2>
    <table class="prix">
        <tr>
            <td>Particulier</td>
            <td>{{ number_format($cours->prix_particulier, 0, ',', ' ') }} FCFA</td>
        </tr>
        <tr>
            <td>Entreprise</td>
            <td>{{ number_format($cours->prix_entreprise, 0, ',', ' ') }} FCFA</td>
        </tr>
    </table>

    <div class="pied">NEO VISION · Programme de formation</div>
</body>
</html>
