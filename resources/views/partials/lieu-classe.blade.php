{{--
    Lieu d'une classe : le nom du lieu est un lien Google Maps (localisation + itinéraire),
    avec la photo du lieu si elle existe.
    Variables : $classe (obligatoire), $image (bool, défaut false : affiche la photo en miniature)
--}}
@php($avecImage = ($image ?? false) && $classe->lieu_image_url)

<a href="{{ $classe->lieu_url }}" target="_blank" rel="noopener noreferrer"
   class="d-inline-flex align-items-center gap-2 text-reset text-decoration-none"
   title="Voir sur Google Maps et suivre l'itinéraire">
    @if ($avecImage)
        <img src="{{ $classe->lieu_image_url }}" alt="Photo du lieu : {{ $classe->lieu }}"
             class="avatar-sm rounded object-fit-cover flex-shrink-0">
    @else
        <i class="ri-map-pin-line text-danger"></i>
    @endif
    <span class="text-decoration-underline">{{ $classe->lieu }}</span>
    <i class="ri-external-link-line text-muted fs-12"></i>
</a>