{{--
    Affiche une note sur 5 sous forme d'étoiles (demi-étoiles comprises).
    Paramètres : $note (nombre, 0 à 5), $taille (classe de taille optionnelle, ex. "fs-5")
--}}
@php $note = (float) ($note ?? 0); @endphp

<span class="text-warning {{ $taille ?? '' }}" aria-label="Note : {{ number_format($note, 1, ',', '') }} sur 5">
    @for($i = 1; $i <= 5; $i++)
        <i class="{{ $note >= $i ? 'ri-star-fill' : ($note >= $i - 0.5 ? 'ri-star-half-fill' : 'ri-star-line') }}"></i>
    @endfor
</span>