@extends('layouts.master')

@section('title')
    Galerie
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Communauté
        @endslot
        @slot('title')
            Galerie
        @endslot
    @endcomponent

    {{-- Onglets : Mes forums | Galerie (page actuelle) --}}
    <ul class="nav nav-tabs nav-tabs-custom mb-3">
        <li class="nav-item"><a class="nav-link" href="{{ route('forum.index') }}"><i class="ri-chat-3-line align-bottom me-1"></i>Mes forums</a></li>
        <li class="nav-item"><a class="nav-link active" href="{{ route('forum.galerie') }}"><i class="ri-gallery-line align-bottom me-1"></i>Galerie</a></li>
    </ul>

    {{-- Filtre par forum (classe) : seulement les forums qui contiennent des photos --}}
    @if ($classes->isNotEmpty())
        <div class="d-flex flex-wrap gap-2 mb-3">
            <a href="{{ route('forum.galerie') }}"
               class="btn btn-sm {{ $classeFiltre ? 'btn-soft-primary' : 'btn-primary' }}">
                Tous les forums
            </a>
            @foreach ($classes as $classe)
                <a href="{{ route('forum.galerie', ['classe' => $classe->id]) }}"
                   class="btn btn-sm {{ $classeFiltre === $classe->id ? 'btn-primary' : 'btn-soft-primary' }}">
                    {{ $classe->cours?->titre }} · {{ $classe->nom }}
                </a>
            @endforeach
        </div>
    @endif

    <div class="row g-3">
        @forelse ($photos as $message)
            <div class="col-xl-2 col-lg-3 col-md-4 col-6">
                <div class="card mb-0 h-100">
                    {{-- Clic = agrandissement dans la fenêtre modale (script en bas de page).
                         La photo passe par ForumController::photo : jamais d'URL publique. --}}
                    @php $urlPhoto = route('forum.photo', [$message->classe_id, $message->id]); @endphp
                    <a href="{{ $urlPhoto }}" class="js-photo"
                       data-src="{{ $urlPhoto }}" data-video="{{ $message->est_video ? '1' : '0' }}"
                       data-legende="{{ $message->classe->cours?->titre }} · {{ $message->classe->nom }} · {{ $message->user->name }} · {{ $message->created_at->format('d/m/Y H:i') }}">
                        @if ($message->est_video)
                            {{-- Vidéo : 1re image comme vignette + pastille « lecture » --}}
                            <div class="position-relative">
                                <video src="{{ $urlPhoto }}#t=0.1" preload="metadata" muted
                                       class="card-img-top nv-gallery-square pe-none"></video>
                                <i class="ri-play-circle-fill text-white fs-1 position-absolute top-50 start-50 translate-middle nv-text-shadow"
                                  ></i>
                            </div>
                        @else
                            <img src="{{ $urlPhoto }}" loading="lazy"
                                 alt="Photo du forum {{ $message->classe->cours?->titre }} — {{ $message->classe->nom }}"
                                 class="card-img-top nv-gallery-square">
                        @endif
                    </a>
                    <div class="card-body p-2">
                        <p class="mb-0 fs-12 text-truncate fw-medium">{{ $message->classe->cours?->titre }} · {{ $message->classe->nom }}</p>
                        <p class="mb-0 fs-12 text-muted text-truncate">
                            {{ $message->user->name }} · {{ $message->created_at->format('d/m/Y') }}
                        </p>
                        {{-- Retour au forum de la classe --}}
                        <a href="{{ route('forum.show', $message->classe_id) }}" class="fs-12">Voir dans le forum</a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center text-muted py-5">
                        <i class="ri-gallery-line fs-1 d-block mb-2"></i>
                        Aucune photo ni vidéo pour l'instant. Les médias partagés dans vos forums apparaissent ici.
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination (24 photos par page, le filtre est conservé via withQueryString) --}}
    <div class="mt-3">
        {{ $photos->links() }}
    </div>

    {{-- Fenêtre modale d'agrandissement (Bootstrap 5, déjà chargé par le layout) --}}
    <div class="modal fade" id="modalPhoto" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-body text-center p-2">
                    <img src="" alt="" id="modalPhotoImg" class="img-fluid rounded">
                    <video src="" id="modalVideo" class="img-fluid rounded d-none" controls></video>
                    <p class="text-muted fs-13 mt-2 mb-0" id="modalPhotoLegende"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modalEl = document.getElementById('modalPhoto');
            const modal = new bootstrap.Modal(modalEl);

            // À la fermeture : on arrête la vidéo (sinon le son continue)
            modalEl.addEventListener('hidden.bs.modal', function () {
                const video = document.getElementById('modalVideo');
                video.pause();
                video.removeAttribute('src');
                video.load();
            });

            // Au clic sur une vignette : on remplit la modale puis on l'ouvre (sans quitter la page)
            document.querySelectorAll('.js-photo').forEach(function (lien) {
                lien.addEventListener('click', function (e) {
                    e.preventDefault();
                    const estVideo = lien.dataset.video === '1';
                    const img = document.getElementById('modalPhotoImg');
                    const video = document.getElementById('modalVideo');

                    // On affiche soit l'image, soit le lecteur vidéo
                    img.classList.toggle('d-none', estVideo);
                    video.classList.toggle('d-none', !estVideo);
                    img.src = estVideo ? '' : lien.dataset.src;
                    video.src = estVideo ? lien.dataset.src : '';

                    document.getElementById('modalPhotoLegende').textContent = lien.dataset.legende;
                    modal.show();
                });
            });
        });
    </script>
@endsection