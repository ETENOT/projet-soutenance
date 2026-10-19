@extends('layouts.master')

@section('title')
    Forum — {{ $classe->nom }}
@endsection

@section('css')
    {{-- Zoom sur les photos (déjà fourni avec le template) --}}
    <link rel="stylesheet" href="{{ URL::asset('build/libs/glightbox/css/glightbox.min.css') }}">
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('forum.index') }}">Forums</a>
        @endslot
        @slot('title')
            {{ $classe->nom }}
        @endslot
    @endcomponent

    <div class="row">

        {{-- ====================== MEMBRES (liste automatique) ====================== --}}
        <div class="col-lg-3 order-2 order-lg-1">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">Membres ({{ $stagiaires->count() + ($classe->formateur ? 1 : 0) }})</h6>
                    <p class="text-muted fs-12 mb-0">Les stagiaires dont l'inscription est payée sont ajoutés automatiquement.</p>
                </div>
                <div class="card-body" style="max-height: 420px; overflow-y: auto;">
                    {{-- Le formateur en premier : c'est lui qui anime et modère le forum --}}
                    @if ($classe->formateur)
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar-xs me-2">
                                <span class="avatar-title rounded-circle bg-warning-subtle text-warning">
                                    {{ mb_strtoupper(mb_substr($classe->formateur->name, 0, 1)) }}
                                </span>
                            </div>
                            <div>
                                <div class="fw-medium fs-13">{{ $classe->formateur->name }}</div>
                                <span class="badge bg-warning-subtle text-warning">Formateur</span>
                            </div>
                        </div>
                    @endif

                    @foreach ($stagiaires as $stagiaire)
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar-xs me-2">
                                <span class="avatar-title rounded-circle bg-primary-subtle text-primary">
                                    {{ mb_strtoupper(mb_substr($stagiaire->name, 0, 1)) }}
                                </span>
                            </div>
                            <div class="fw-medium fs-13">{{ $stagiaire->name }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ====================== CONVERSATION ====================== --}}
        <div class="col-lg-9 order-1 order-lg-2">
            <div class="card">

                <div class="card-header d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="card-title mb-0">{{ $classe->nom }}</h5>
                        <p class="text-muted fs-12 mb-0">{{ $classe->cours->titre ?? '' }}</p>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        @if ($estModerateur)
                            <span class="badge bg-warning-subtle text-warning">Vous modérez ce forum</span>
                        @endif
                        {{-- Ouvre le panneau « Galerie » (photos de CE forum), sans quitter la conversation --}}
                        <button type="button" class="btn btn-sm btn-soft-primary" id="boutonGalerie"
                                data-bs-toggle="offcanvas" data-bs-target="#panneauGalerie" aria-controls="panneauGalerie">
                            <i class="ri-gallery-line align-bottom me-1"></i>Galerie
                        </button>
                    </div>
                </div>

                {{-- Zone des messages : classes du template Velzon (chat-conversation / chat-list / ctext-wrap) --}}
                <div class="chat-conversation p-3" id="zoneMessages" style="overflow-y: auto; min-height: 340px;">
                    <ul class="list-unstyled chat-conversation-list" id="listeMessages"></ul>
                    <p class="text-center text-muted my-4" id="messageVide" style="display:none">
                        Aucun message pour l'instant. Lancez la discussion et partagez vos photos et vidéos de la formation !
                    </p>
                </div>

                {{-- Zone de saisie --}}
                <div class="card-footer">
                    @if ($lectureSeule)
                        <div class="alert alert-secondary mb-0">
                            La classe est fermée : le forum est en lecture seule.
                        </div>
                    @else
                        {{-- Bandeau "réponse à ..." --}}
                        <div class="alert alert-info py-2 d-none align-items-center justify-content-between" id="barreReponse">
                            <div class="text-truncate">
                                <i class="ri-reply-line me-1"></i>Réponse à <strong id="repAuteur"></strong> :
                                <span id="repExtrait" class="text-muted"></span>
                            </div>
                            <button type="button" class="btn-close" id="annulerReponse" aria-label="Annuler"></button>
                        </div>

                        {{-- Bandeau "fichier sélectionné" (photo : aperçu ; vidéo : icône) --}}
                        <div class="d-none align-items-center mb-2" id="barrePhoto">
                            <img id="apercuPhoto" alt="" class="rounded me-2" style="height:48px;width:48px;object-fit:cover">
                            <i id="iconeVideo" class="ri-movie-line fs-24 text-primary me-2 d-none"></i>
                            <span class="text-muted fs-12 me-2" id="nomPhoto"></span>
                            <button type="button" class="btn btn-sm btn-soft-danger" id="retirerPhoto">Retirer</button>
                        </div>

                        <div class="text-danger fs-12 mb-1 d-none" id="erreurForum"></div>

                        <div class="d-flex align-items-end gap-2">
                            {{-- accept="image/*,video/*" : sur téléphone, propose aussi l'appareil photo / la caméra --}}
                            <input type="file" id="champPhoto" accept="image/*,video/*" class="d-none">
                            <button type="button" class="btn btn-soft-primary" id="boutonPhoto" title="Ajouter une photo ou une vidéo">
                                <i class="ri-image-add-line fs-18"></i>
                            </button>

                            <textarea id="champTexte" class="form-control" rows="1" maxlength="2000"
                                      placeholder="Écrire un message… (Entrée pour envoyer, Maj+Entrée pour aller à la ligne)"></textarea>

                            <button type="button" class="btn btn-success" id="boutonEnvoyer" title="Envoyer">
                                <i class="ri-send-plane-2-fill fs-18"></i>
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ====================== GALERIE DE CE FORUM (panneau latéral) ====================== --}}
    <div class="offcanvas offcanvas-end" tabindex="-1" id="panneauGalerie" aria-labelledby="titreGalerie" style="width: min(520px, 100vw);">
        <div class="offcanvas-header border-bottom">
            <div>
                <h5 class="offcanvas-title mb-0" id="titreGalerie">Galerie — {{ $classe->nom }}</h5>
                <p class="text-muted fs-12 mb-0" id="galerieCompteur"></p>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Fermer"></button>
        </div>
        <div class="offcanvas-body">
            <div class="text-danger fs-13 mb-2 d-none" id="galerieErreur"></div>

            <p class="text-center text-muted my-4 d-none" id="galerieVide">
                Aucune photo ni vidéo dans ce forum pour l'instant.
            </p>

            {{-- Grille de vignettes (remplie en Ajax à l'ouverture du panneau) --}}
            <div class="row g-2" id="grilleGalerie"></div>

            {{-- Lot suivant (24 médias de plus) --}}
            <div class="text-center mt-3">
                <button type="button" class="btn btn-soft-primary d-none" id="galeriePlus">Charger plus</button>
            </div>
        </div>
        <div class="offcanvas-footer border-top p-3 text-center">
            {{-- Mêmes médias, mais en pleine page et avec le filtre sur ce forum --}}
            <a href="{{ route('forum.galerie', ['classe' => $classe->id]) }}" class="fs-13">
                Ouvrir dans la page Galerie
            </a>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ URL::asset('build/libs/glightbox/js/glightbox.min.js') }}"></script>

    <script>
        // ====================================================================
        // Forum de discussion : chargement, rafraîchissement automatique,
        // envoi (texte / photo / réponse) et suppression des messages.
        // On n'utilise PAS chat.init.js du template : c'est une démo avec de faux
        // utilisateurs enregistrés dans le navigateur, sans lien avec la base de données.
        // ====================================================================
        (function () {
            const cfg = {
                urlMessages: @json(route('forum.messages', $classe)),
                urlBase: @json(url('forum/' . $classe->id . '/messages')),   // + /{id} pour supprimer
                csrf: @json(csrf_token()),
                lectureSeule: @json($lectureSeule),
                intervalle: 4000,   // rafraîchissement toutes les 4 secondes
            };

            const zone = document.getElementById('zoneMessages');
            const liste = document.getElementById('listeMessages');
            const vide = document.getElementById('messageVide');

            let dernierId = 0;      // plus grand id affiché (pour ne demander que les nouveaux messages)
            let premierId = 0;      // plus petit id affiché (pour détecter les suppressions)
            let reponseA = null;    // message auquel on est en train de répondre
            let photo = null;       // photo choisie, pas encore envoyée
            let envoiEnCours = false;
            let lightbox = GLightbox({ selector: '.glightbox' });

            // ------------------------------------------------------------
            // Construction d'un message (textContent partout : aucun HTML
            // venant d'un utilisateur n'est interprété -> pas de faille XSS)
            // ------------------------------------------------------------
            function el(tag, classe, texte) {
                const e = document.createElement(tag);
                if (classe) e.className = classe;
                if (texte !== undefined && texte !== null) e.textContent = texte;
                return e;
            }

            function construire(m) {
                const li = el('li', 'chat-list ' + (m.mien ? 'right' : 'left'));
                li.dataset.id = m.id;

                const conv = el('div', 'conversation-list');

                // Pastille avec l'initiale pour les messages des autres
                if (!m.mien) {
                    const av = el('div', 'chat-avatar');
                    const pastille = el('span', 'avatar-xs d-inline-block');
                    pastille.appendChild(el('span', 'avatar-title rounded-circle ' +
                        (m.est_formateur ? 'bg-warning-subtle text-warning' : 'bg-primary-subtle text-primary'),
                        m.auteur.charAt(0).toUpperCase()));
                    av.appendChild(pastille);
                    conv.appendChild(av);
                }

                const contenu = el('div', 'user-chat-content');
                const enveloppe = el('div', 'ctext-wrap');
                const bulle = el('div', 'ctext-wrap-content');

                // Nom de l'auteur (+ pastille Formateur)
                if (!m.mien) {
                    const nom = el('h6', 'fs-12 mb-1 text-primary', m.auteur);
                    if (m.est_formateur) nom.appendChild(el('span', 'badge bg-warning-subtle text-warning ms-1', 'Formateur'));
                    bulle.appendChild(nom);
                }

                // Citation du message auquel on répond
                if (m.parent) {
                    const cit = el('div', 'rounded p-2 mb-2 bg-light text-body fs-12');
                    cit.style.cursor = 'pointer';
                    cit.appendChild(el('strong', '', m.parent.auteur));
                    cit.appendChild(el('div', 'text-muted', m.parent.extrait));
                    cit.addEventListener('click', function () {
                        const cible = liste.querySelector('li[data-id="' + m.parent.id + '"]');
                        if (cible) cible.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    });
                    bulle.appendChild(cit);
                }

                // Vidéo : lecteur intégré (le plein écran est fourni par le navigateur)
                if (m.photo_url && m.est_video) {
                    const video = el('video', 'rounded mb-1 d-block');
                    video.src = m.photo_url;
                    video.controls = true;
                    video.preload = 'metadata';   // ne télécharge que la durée / la 1re image, pas toute la vidéo
                    video.style.maxWidth = '260px';
                    video.style.width = '100%';
                    bulle.appendChild(video);
                }

                // Photo (cliquable pour agrandir)
                if (m.photo_url && !m.est_video) {
                    const lien = el('a', 'glightbox d-block mb-1');
                    lien.href = m.photo_url;
                    const img = el('img', 'rounded img-fluid');
                    img.src = m.photo_url;
                    img.alt = 'Photo partagée par ' + m.auteur;
                    img.loading = 'lazy';
                    img.style.maxWidth = '260px';
                    lien.appendChild(img);
                    bulle.appendChild(lien);
                }

                if (m.contenu) {
                    const p = el('p', 'mb-0 ctext-content', m.contenu);
                    p.style.whiteSpace = 'pre-wrap';   // respecte les retours à la ligne
                    p.style.wordBreak = 'break-word';
                    bulle.appendChild(p);
                }

                enveloppe.appendChild(bulle);

                // Menu "..." : Répondre / Supprimer
                const menu = el('div', 'dropdown align-self-start message-box-drop');
                const decl = el('a', '');
                decl.href = '#';
                decl.setAttribute('role', 'button');
                decl.setAttribute('data-bs-toggle', 'dropdown');
                decl.appendChild(el('i', 'ri-more-2-fill'));
                decl.addEventListener('click', e => e.preventDefault());
                const items = el('div', 'dropdown-menu');

                if (!cfg.lectureSeule) {
                    const rep = el('a', 'dropdown-item');
                    rep.href = '#';
                    rep.appendChild(el('i', 'ri-reply-line me-2 text-muted align-bottom'));
                    rep.appendChild(document.createTextNode('Répondre'));
                    rep.addEventListener('click', function (e) { e.preventDefault(); preparerReponse(m); });
                    items.appendChild(rep);
                }
                if (m.peut_supprimer) {
                    const sup = el('a', 'dropdown-item');
                    sup.href = '#';
                    sup.appendChild(el('i', 'ri-delete-bin-5-line me-2 text-muted align-bottom'));
                    sup.appendChild(document.createTextNode('Supprimer'));
                    sup.addEventListener('click', function (e) { e.preventDefault(); supprimer(m.id); });
                    items.appendChild(sup);
                }
                if (items.children.length) {
                    menu.appendChild(decl);
                    menu.appendChild(items);
                    enveloppe.appendChild(menu);
                }

                contenu.appendChild(enveloppe);

                const nomLigne = el('div', 'conversation-name');
                nomLigne.appendChild(el('small', 'text-muted time', m.date));
                contenu.appendChild(nomLigne);

                conv.appendChild(contenu);
                li.appendChild(conv);
                return li;
            }

            // Ajoute un message en bas de la liste (ignore ceux déjà affichés)
            function ajouter(m, forcerDefilement) {
                if (liste.querySelector('li[data-id="' + m.id + '"]')) return;

                // On ne fait défiler vers le bas que si l'utilisateur y était déjà (sinon il lit plus haut)
                const etaitEnBas = zone.scrollHeight - zone.scrollTop - zone.clientHeight < 80;

                liste.appendChild(construire(m));
                vide.style.display = 'none';
                if (!premierId || m.id < premierId) premierId = m.id;
                if (m.id > dernierId) dernierId = m.id;
                lightbox.reload();

                if (etaitEnBas || forcerDefilement || m.mien) zone.scrollTop = zone.scrollHeight;
            }

            // ------------------------------------------------------------
            // Chargement initial et rafraîchissement automatique
            // ------------------------------------------------------------
            async function charger() {
                const r = await fetch(cfg.urlMessages, { headers: { 'Accept': 'application/json' } });
                if (!r.ok) return;
                const data = await r.json();
                data.messages.forEach(m => ajouter(m, true));
                if (!data.messages.length) vide.style.display = 'block';
            }

            async function rafraichir() {
                if (document.hidden) return;   // onglet en arrière-plan : on ne sollicite pas le serveur
                try {
                    const params = new URLSearchParams({ apres: dernierId, depuis: premierId });
                    const r = await fetch(cfg.urlMessages + '?' + params, { headers: { 'Accept': 'application/json' } });
                    if (!r.ok) return;
                    const data = await r.json();
                    data.messages.forEach(m => ajouter(m));

                    // Retire les messages supprimés par quelqu'un d'autre
                    if (data.ids_existants) {
                        const existants = new Set(data.ids_existants);
                        liste.querySelectorAll('li[data-id]').forEach(function (li) {
                            const id = Number(li.dataset.id);
                            if (id >= premierId && !existants.has(id)) li.remove();
                        });
                        if (!liste.children.length) vide.style.display = 'block';
                    }
                } catch (e) { /* réseau coupé : on réessaiera au prochain tour */ }
            }

            // ------------------------------------------------------------
            // Suppression
            // ------------------------------------------------------------
            async function supprimer(id) {
                if (!confirm('Supprimer ce message ?')) return;
                const r = await fetch(cfg.urlBase + '/' + id, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': cfg.csrf, 'Accept': 'application/json' },
                });
                if (r.ok) {
                    const li = liste.querySelector('li[data-id="' + id + '"]');
                    if (li) li.remove();
                    if (!liste.children.length) vide.style.display = 'block';
                } else {
                    alert('Suppression impossible.');
                }
            }

            // ------------------------------------------------------------
            // Envoi (désactivé si la classe est fermée)
            // ------------------------------------------------------------
            if (!cfg.lectureSeule) {
                const champTexte = document.getElementById('champTexte');
                const champPhoto = document.getElementById('champPhoto');
                const boutonEnvoyer = document.getElementById('boutonEnvoyer');
                const barreReponse = document.getElementById('barreReponse');
                const barrePhoto = document.getElementById('barrePhoto');
                const erreur = document.getElementById('erreurForum');

                function afficherErreur(texte) {
                    erreur.textContent = texte || '';
                    erreur.classList.toggle('d-none', !texte);
                }

                // Réponse à un message
                window.preparerReponse = function (m) {
                    reponseA = m;
                    document.getElementById('repAuteur').textContent = m.auteur;
                    document.getElementById('repExtrait').textContent = m.contenu ? m.contenu.slice(0, 80) : (m.est_video ? '🎬 Vidéo' : '📷 Photo');
                    barreReponse.classList.remove('d-none');
                    barreReponse.classList.add('d-flex');
                    champTexte.focus();
                };
                function annulerReponse() {
                    reponseA = null;
                    barreReponse.classList.add('d-none');
                    barreReponse.classList.remove('d-flex');
                }
                document.getElementById('annulerReponse').addEventListener('click', annulerReponse);

                // Réduit la photo (1600 px max, JPEG) avant l'envoi : les photos de téléphone font souvent 5 à 10 Mo
                async function reduire(fichier) {
                    if (!fichier.type.startsWith('image/') || fichier.type === 'image/gif') return fichier;
                    try {
                        const bmp = await createImageBitmap(fichier, { imageOrientation: 'from-image' });
                        const ratio = Math.min(1, 1600 / Math.max(bmp.width, bmp.height));
                        const canvas = document.createElement('canvas');
                        canvas.width = Math.round(bmp.width * ratio);
                        canvas.height = Math.round(bmp.height * ratio);
                        canvas.getContext('2d').drawImage(bmp, 0, 0, canvas.width, canvas.height);
                        const blob = await new Promise(res => canvas.toBlob(res, 'image/jpeg', 0.85));
                        return blob ? new File([blob], fichier.name.replace(/\.\w+$/, '') + '.jpg', { type: 'image/jpeg' }) : fichier;
                    } catch (e) {
                        return fichier;   // navigateur trop ancien : on envoie l'original
                    }
                }

                document.getElementById('boutonPhoto').addEventListener('click', () => champPhoto.click());

                champPhoto.addEventListener('change', async function () {
                    if (!champPhoto.files.length) return;
                    afficherErreur('');
                    const choisi = champPhoto.files[0];
                    const estVideo = choisi.type.startsWith('video/');

                    // Vidéo : 50 Mo maximum (même limite que le serveur), on prévient avant d'envoyer pour rien
                    if (estVideo && choisi.size > 50 * 1024 * 1024) {
                        afficherErreur('La vidéo dépasse 50 Mo.');
                        champPhoto.value = '';
                        return;
                    }

                    // Une vidéo part telle quelle ; seule une photo est réduite
                    photo = estVideo ? choisi : await reduire(choisi);
                    const apercu = document.getElementById('apercuPhoto');
                    apercu.classList.toggle('d-none', estVideo);
                    document.getElementById('iconeVideo').classList.toggle('d-none', !estVideo);
                    if (!estVideo) apercu.src = URL.createObjectURL(photo);
                    document.getElementById('nomPhoto').textContent = photo.name;
                    barrePhoto.classList.remove('d-none');
                    barrePhoto.classList.add('d-flex');
                });

                document.getElementById('retirerPhoto').addEventListener('click', function () {
                    photo = null;
                    champPhoto.value = '';
                    document.getElementById('apercuPhoto').classList.remove('d-none');
                    document.getElementById('iconeVideo').classList.add('d-none');
                    barrePhoto.classList.add('d-none');
                    barrePhoto.classList.remove('d-flex');
                });

                async function envoyer() {
                    if (envoiEnCours) return;
                    const texte = champTexte.value.trim();
                    if (!texte && !photo) return;

                    envoiEnCours = true;
                    boutonEnvoyer.disabled = true;
                    afficherErreur('');

                    const fd = new FormData();
                    fd.append('contenu', texte);
                    if (photo) fd.append('photo', photo);
                    if (reponseA) fd.append('parent_id', reponseA.id);

                    try {
                        const r = await fetch(cfg.urlMessages, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': cfg.csrf, 'Accept': 'application/json' },
                            body: fd,
                        });
                        const data = await r.json().catch(() => ({}));

                        if (!r.ok) {
                            // Message d'erreur du serveur (validation, classe fermée, trop d'envois…)
                            const premiere = data.errors ? Object.values(data.errors)[0][0] : null;
                            afficherErreur(premiere || data.message || 'Envoi impossible.');
                            return;
                        }

                        ajouter(data, true);
                        champTexte.value = '';
                        champTexte.style.height = '';
                        annulerReponse();
                        document.getElementById('retirerPhoto').click();
                    } catch (e) {
                        afficherErreur('Connexion impossible. Réessayez.');
                    } finally {
                        envoiEnCours = false;
                        boutonEnvoyer.disabled = false;
                    }
                }

                boutonEnvoyer.addEventListener('click', envoyer);

                // Entrée = envoyer ; Maj+Entrée = nouvelle ligne
                champTexte.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        envoyer();
                    }
                });

                // Le champ grandit avec le texte (6 lignes max)
                champTexte.addEventListener('input', function () {
                    champTexte.style.height = 'auto';
                    champTexte.style.height = Math.min(champTexte.scrollHeight, 150) + 'px';
                });
            }

            charger();
            setInterval(rafraichir, cfg.intervalle);
        })();
    </script>

    <script>
        // ====================================================================
        // Galerie du forum : le panneau se remplit à chaque ouverture (les photos et vidéos
        // envoyées ou supprimées depuis la dernière fois sont donc à jour),
        // par lots de 24 ; un clic sur une vignette l'agrandit / la lit (GLightbox).
        // ====================================================================
        (function () {
            const urlGalerie = @json(route('forum.galerie-classe', $classe));

            const panneau = document.getElementById('panneauGalerie');
            const grille = document.getElementById('grilleGalerie');
            const vide = document.getElementById('galerieVide');
            const compteur = document.getElementById('galerieCompteur');
            const boutonPlus = document.getElementById('galeriePlus');
            const erreur = document.getElementById('galerieErreur');

            let suivant = null;        // id à partir duquel charger le lot suivant (null = plus rien à charger)
            let chargement = false;    // évite deux chargements en même temps
            // Les liens de la galerie ont leur propre classe : le zoom de la conversation n'est pas touché.
            // Avec data-gallery, on peut passer d'une photo à l'autre dans l'agrandissement.
            const lightboxGalerie = GLightbox({ selector: '.glightbox-galerie' });

            // Textes toujours posés avec textContent / setAttribute : aucun HTML venant d'un utilisateur n'est interprété
            function ajouterVignette(m) {
                const col = document.createElement('div');
                col.className = 'col-4';

                const lien = document.createElement('a');
                lien.className = 'glightbox-galerie d-block';
                lien.href = m.url;
                lien.setAttribute('data-gallery', 'galerie-forum');
                lien.setAttribute('data-description', m.auteur + ' · ' + m.date);

                // Vidéo : GLightbox doit savoir que c'est une vidéo hébergée chez nous (l'URL n'a pas d'extension .mp4)
                if (m.est_video) {
                    lien.setAttribute('data-type', 'video');
                    lien.setAttribute('data-source', 'local');
                    lien.style.position = 'relative';

                    // Vignette = 1re image de la vidéo (#t=0.1 évite l'image noire du tout début)
                    const apercu = document.createElement('video');
                    apercu.src = m.url + '#t=0.1';
                    apercu.preload = 'metadata';
                    apercu.muted = true;
                    apercu.className = 'rounded w-100';
                    apercu.style.aspectRatio = '1 / 1';
                    apercu.style.objectFit = 'cover';
                    apercu.style.pointerEvents = 'none';   // le clic va au lien, pas au lecteur
                    lien.appendChild(apercu);

                    // Pastille « lecture » pour distinguer une vidéo d'une photo
                    const play = document.createElement('i');
                    play.className = 'ri-play-circle-fill text-white fs-1';
                    play.style.cssText = 'position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-shadow:0 0 6px rgba(0,0,0,.6);pointer-events:none';
                    lien.appendChild(play);
                } else {
                    const img = document.createElement('img');
                    img.src = m.url;
                    img.alt = 'Photo partagée par ' + m.auteur;
                    img.loading = 'lazy';
                    img.className = 'rounded w-100';
                    img.style.aspectRatio = '1 / 1';
                    img.style.objectFit = 'cover';
                    lien.appendChild(img);
                }

                col.appendChild(lien);
                grille.appendChild(col);
            }

            // Charge un lot : le premier (avant = null) ou le suivant (avant = id de la dernière photo affichée)
            async function charger(avant) {
                if (chargement) return;
                chargement = true;
                boutonPlus.disabled = true;
                erreur.classList.add('d-none');

                try {
                    const url = avant ? urlGalerie + '?' + new URLSearchParams({ avant: avant }) : urlGalerie;
                    const r = await fetch(url, { headers: { 'Accept': 'application/json' } });
                    if (!r.ok) throw new Error('http ' + r.status);
                    const data = await r.json();

                    data.medias.forEach(ajouterVignette);
                    lightboxGalerie.reload();   // prend en compte les nouvelles vignettes

                    suivant = data.suivant;
                    compteur.textContent = data.total + (data.total > 1 ? ' médias (photos et vidéos)' : ' média');
                    vide.classList.toggle('d-none', data.total > 0);
                    boutonPlus.classList.toggle('d-none', suivant === null);
                } catch (e) {
                    erreur.textContent = 'Impossible de charger la galerie. Réessayez.';
                    erreur.classList.remove('d-none');
                } finally {
                    chargement = false;
                    boutonPlus.disabled = false;
                }
            }

            // À chaque ouverture : on repart de zéro avec les photos les plus récentes
            panneau.addEventListener('show.bs.offcanvas', function () {
                grille.replaceChildren();
                suivant = null;
                boutonPlus.classList.add('d-none');
                compteur.textContent = '';
                vide.classList.add('d-none');
                charger(null);
            });

            boutonPlus.addEventListener('click', function () {
                if (suivant !== null) charger(suivant);
            });
        })();
    </script>
@endsection