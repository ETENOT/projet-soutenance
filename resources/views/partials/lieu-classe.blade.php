{{--
    Lieu d'une classe, affiché sous la forme « Lieu : nom du lieu ».
    Le nom du lieu est un lien Google Maps (localisation + itinéraire).
    Variables :
      $classe  (obligatoire)
      $image   (bool, défaut false) : affiche la photo du lieu en miniature à la place de l'icône
      $libelle (bool, défaut true)  : affiche « Lieu : » devant le nom
--}}
@php
    $avecImage = ($image ?? false) && $classe->lieu_image_url;
    $avecLibelle = $libelle ?? true;
@endphp

<span class="d-inline-flex align-items-center gap-2">
    @if ($avecImage)
        <a href="{{ $classe->lieu_url }}" target="_blank" rel="noopener noreferrer" class="flex-shrink-0"
           title="Voir sur Google Maps et suivre l'itinéraire">
            <img src="{{ $classe->lieu_image_url }}" alt="Photo du lieu : {{ $classe->lieu }}"
                 class="avatar-sm rounded object-fit-cover">
        </a>
    @else
        <i class="ri-map-pin-line text-danger"></i>
    @endif

    @if ($avecLibelle)
        <span class="text-muted">Lieu :</span>
    @endif

    <a href="{{ $classe->lieu_url }}" target="_blank" rel="noopener noreferrer"
       class="text-reset text-decoration-underline d-inline-flex align-items-center gap-1"
       title="Voir sur Google Maps et suivre l'itinéraire">
        {{ $classe->lieu }}
        <i class="ri-external-link-line text-muted fs-12"></i>
    </a>
</span>