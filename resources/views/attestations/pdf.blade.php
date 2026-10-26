{{--
    resources/views/attestations/pdf.blade.php
    Gabarit PDF de l'attestation (A4 paysage), calqué sur le modèle NéoVision (Attestation_ADEMBA.pptx).

    dompdf ne comprend que le CSS 2.1 (pas de flex ni de grid) : tout est positionné en absolu, en points (pt).
    Polices du modèle : Poppins (texte), Bauhaus 93 (nom), Times New Roman gras (titre).
    Les fichiers .ttf sont dans public/fonts/attestation/ (voir @font-face ci-dessous).

    Variables : $attestation (App\Models\Attestation) et $img (images en base64).
--}}
@php
    $dossierPolices = public_path('fonts/attestation');
    $police = fn (string $f) => str_replace('\\', '/', $dossierPolices . '/' . $f);
    $aBauhaus = is_file($dossierPolices . '/BAUHS93.TTF');

    // Le modèle utilise 36 pt ; les noms très longs réduisent la taille pour tenir sur une ligne
    $n = mb_strlen($attestation->beneficiaire);
    $tailleNom = $n > 34 ? 22 : ($n > 26 ? 29 : 36);
    // ---- Nom du formateur : retour à la ligne avant la signature ----
    // Bord gauche de l'image de la signature (pt). Même valeur que « left » de la balise <img> plus bas.
    $signatureGauche = 297;
    // Centre horizontal du bloc « Formateur » (left 59,5 pt + largeur 273,6 pt / 2)
    $centreFormateur = 59.5 + 273.6 / 2;
    // Limite droite du nom (pt). L'encre de la signature commence à x = 302,8 pt à hauteur de la
    // 1re ligne du nom (351 pt pour la 2e) : on s'arrête à 297 pt, 6 pt avant. Le nom est centré,
    // donc largeur = 2 x (limite - centre). Plus long, il passe à la ligne : les derniers mots
    // descendent et chaque ligne reste centrée.
    $limiteNomDroite = 297;
    $largeurNomFormateur = round(2 * ($limiteNomDroite - $centreFormateur), 1);
    
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Attestation {{ $attestation->numero }}</title>
    <style>
        @font-face { font-family: 'Poppins'; font-weight: normal; font-style: normal; src: url('{{ $police('Poppins-Regular.ttf') }}') format('truetype'); }
        @font-face { font-family: 'Poppins'; font-weight: bold;   font-style: normal; src: url('{{ $police('Poppins-Bold.ttf') }}') format('truetype'); }
        @font-face { font-family: 'Calibri'; font-weight: bold; font-style: normal; src: url('{{ $police('Carlito-Bold.ttf') }}') format('truetype'); }
        @font-face { font-family: 'PoppinsMedium';   font-weight: bold; font-style: normal; src: url('{{ $police('Poppins-Medium.ttf') }}') format('truetype'); }
        @if ($aBauhaus)
        @font-face { font-family: 'Bauhaus93'; font-weight: normal; font-style: normal; src: url('{{ $police('BAUHS93.TTF') }}') format('truetype'); }
        @endif

        @page { margin: 0; }
        html, body { margin: 0; padding: 0; }
        body { font-family: 'Poppins', DejaVu Sans, sans-serif; color: #000; }

        /* 841 x 594 pt (1 pt de moins que la page pour éviter une 2e page blanche) */
        .page { position: absolute; left: 0; top: 0; width: 842pt; height: 596pt; overflow: hidden; }
        .abs  { position: absolute; }
        .centre { left: 0; width: 841pt; text-align: center; white-space: nowrap; }

        /* Titre : Times New Roman gras 42,67 pt, espacement 2,38 pt, bleu #1800AD */
        .titre   { top: 127.3pt; font-family: Times-Roman, serif; font-weight: bold; font-size: 42.67pt;
                   letter-spacing: 2.86pt; color: #1800ad; line-height: 59.7pt; }
        /* « DE FORMATION » : Poppins Medium 18,18 pt, espacement 5,12 pt, blanc, sur le bandeau bleu du fond */
        .bandeau { top: 182.2pt; font-family: 'PoppinsMedium', 'Poppins', sans-serif; font-weight: bold; font-size: 18.18pt;
                   letter-spacing: 5.12pt; color: #fff; line-height: 25.45pt; }
        .delivre { top: 234.2pt; font-size: 15pt; line-height: 21pt; }
        /* Nom : Bauhaus 93 36 pt, espacement 1,03 pt */
        .nom     { left: 12.8pt; top: 255.3pt; font-size: 36pt; letter-spacing: 1.03pt; color: #000; line-height: 60pt;
                   font-family: {!! $aBauhaus ? "'Bauhaus93', " : '' !!}'Poppins', sans-serif; {!! $aBauhaus ? '' : 'font-weight: bold;' !!} }
        /* Corps : Poppins 15 pt, interligne exact 25,2 pt */
        .texte   { left: 32.5pt; top: 353.9pt; width: 761.6pt; text-align: center; font-size: 15pt; line-height: 15.27pt; }
        .texte p { margin: 0; }
        .formateur { left: 59.5pt; top: 488.9pt; width: 273.6pt; text-align: center; }
        .formateur .lib { font-weight: bold; font-size: 15pt; line-height: 21pt; }
        .formateur .nom-formateur { margin-top: -4.2pt; font-family: 'Calibri', 'Poppins', sans-serif; font-weight: bold; font-size: 16pt; line-height: 19.06pt; }
        .numero  { left: 0; top: 583pt; width: 842pt; font-size: 7.5pt; color: #666; line-height: 9pt; }
    </style>
</head>
<body>
<div class="page">

    <img class="abs" src="{{ $img['fond'] }}" style="left:0; top:0; width:842pt; height:596pt;">
    <img class="abs" src="{{ $img['logo'] }}" style="left:32.5pt; top:43.8pt; width:195.4pt;">
    <img class="abs" src="{{ $img['medaille'] }}" style="left:658.8pt; top:141.4pt; width:102.9pt;">

    <div class="abs centre titre">ATTESTATION</div>
    <div class="abs centre bandeau">DE FORMATION</div>
    <div class="abs centre delivre">délivrée à :</div>

    <div class="abs centre nom" style="font-size: {{ $tailleNom }}pt;">{{ mb_strtoupper($attestation->beneficiaire) }}</div>

    <div class="abs texte">
        <p>
            pour avoir suivi avec succès la formation {{ $attestation->intitule }},
            du <b>{{ $attestation->date_debut->format('d/m/Y') }}</b>
            au <b>{{ $attestation->date_fin->format('d/m/Y') }}</b>@if ($attestation->lieu) à {{ $attestation->lieu }}@endif.
        </p>
        @if ($attestation->contenu)
            <p>Contenu : {{ $attestation->contenu }}.</p>
        @endif
    </div>

    @if ($attestation->formateur)
        <div class="abs formateur">
            <div class="lib">Formateur :</div>
            <div class="nom-formateur" style="width: {{ $largeurNomFormateur }}pt; margin-left: auto; margin-right: auto;">{{ mb_strtoupper($attestation->formateur) }}</div>
        </div>
    @endif

    <img style="position:absolute; left:{{ $signatureGauche }}pt; top:479.8pt; width:235.9pt;" src="{{ $img['signature'] }}">

<div class="abs centre numero">
        Attestation n° {{ $attestation->numero }} — délivrée le {{ ($attestation->created_at ?? now())->format('d/m/Y') }}
    </div>
</div>
</body>
</html>
