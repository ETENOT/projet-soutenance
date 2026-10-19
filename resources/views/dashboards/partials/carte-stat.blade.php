{{--
    Carte de statistique commune à tous les tableaux de bord.
    h-100 : toutes les cartes d'une même rangée ont la même hauteur.

    Variables :
      $titre        libellé de la carte
      $icone        nom de l'icône feather
      $couleur      info | primary | warning | danger | success | secondary
      $lien         (facultatif) la carte entière devient un lien
      $valeur       (facultatif) nombre affiché avec l'animation de compteur
      $suffixe      (facultatif) petit texte à côté du nombre
      $texte        (facultatif) texte affiché à la place d'un nombre
      $texteClasse  (facultatif) classe de couleur du texte
      $detail       (facultatif) petite ligne sous la valeur
      $detailClasse (facultatif) classe de couleur de la petite ligne
      $largeur      (facultatif) classes de colonne, par défaut 3 cartes par ligne
--}}
@php
    $largeur = $largeur ?? 'col-xl-4 col-md-6';
    $texteClasse = $texteClasse ?? '';
    $detailClasse = $detailClasse ?? 'text-muted';
@endphp

<div class="{{ $largeur }} mb-4">
    <div class="card card-animate h-100 mb-0">
        @if (!empty($lien))
            <a href="{{ $lien }}" class="d-block h-100 text-reset">
        @endif

        <div class="card-body d-flex justify-content-between align-items-start h-100">
            <div class="me-2">
                <p class="fw-medium text-muted mb-0">{{ $titre }}</p>

                @if (isset($valeur))
                    <h2 class="mt-4 mb-1 ff-secondary fw-semibold">
                        <span class="counter-value" data-target="{{ $valeur }}">0</span>
                        @if (!empty($suffixe))
                            <span class="fs-14 text-muted">{{ $suffixe }}</span>
                        @endif
                    </h2>
                @else
                    <h2 class="mt-4 mb-1 ff-secondary fw-semibold fs-20 {{ $texteClasse }}">{{ $texte ?? '—' }}</h2>
                @endif

                @if (!empty($detail))
                    <p class="mb-0 fs-13 {{ $detailClasse }}">{{ $detail }}</p>
                @endif
            </div>

            <div class="avatar-sm flex-shrink-0">
                <span class="avatar-title bg-{{ $couleur }}-subtle rounded-circle fs-2">
                    <i data-feather="{{ $icone }}" class="text-{{ $couleur }}"></i>
                </span>
            </div>
        </div>

        @if (!empty($lien))
            </a>
        @endif
    </div>
</div>