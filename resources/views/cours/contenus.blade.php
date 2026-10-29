@extends('layouts.master')

@section('title', 'Chapitres et ressources')

@section('content')

{{-- ===== Fil d'Ariane ===== --}}
@component('components.breadcrumb')
    @slot('li_1')
        <a href="{{ route('admin.cours.index') }}">Cours</a>
    @endslot
    @slot('title')
        {{ $cours->titre }}
    @endslot
@endcomponent

@php
    // Retour d'une validation qui a échoué : "_form" dit quel formulaire rouvrir (chapitre ou ressource),
    // "_mode" (ajout ou modification) dit dans quel mode pour le chapitre
    $formAvecErreur = $errors->any() ? old('_form') : null;
    $modeAvecErreur = $formAvecErreur === 'chapitre' ? old('_mode') : null;

    $iconesRessource = ['video' => 'ri-video-line', 'lien' => 'ri-links-line', 'fichier' => 'ri-file-text-line'];
    $libellesRessource = ['video' => 'Vidéo', 'lien' => 'Lien', 'fichier' => 'Document'];
@endphp

<div class="container-fluid pb-4">

    {{-- ===== Erreurs d'un formulaire (la fenêtre concernée se rouvre aussi) ===== --}}
    @if ($errors->any() && ! $formAvecErreur)
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $erreur)
                    <li>{{ $erreur }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ===== Onglets : une seule liste visible à la fois, chacune avec sa pagination ===== --}}
    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item" role="presentation">
            <button type="button" class="nav-link @if ($onglet !== 'ressources') active @endif" id="ongletChapitres"
                    data-bs-toggle="tab" data-bs-target="#panneauChapitres" role="tab"
                    data-onglet="chapitres">
                Chapitres <span class="badge bg-primary-subtle text-primary ms-1">{{ $chapitres->total() }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button type="button" class="nav-link @if ($onglet === 'ressources') active @endif" id="ongletRessources"
                    data-bs-toggle="tab" data-bs-target="#panneauRessources" role="tab"
                    data-onglet="ressources">
                Ressources <span class="badge bg-primary-subtle text-primary ms-1">{{ $resources->total() }}</span>
            </button>
        </li>
    </ul>

    <div class="tab-content">

        {{-- ===================== CHAPITRES ===================== --}}
        <div class="tab-pane fade @if ($onglet !== 'ressources') show active @endif" id="panneauChapitres" role="tabpanel">
            <div class="d-flex justify-content-start mb-3">
                <button type="button" class="btn btn-primary fw-semibold"
                        data-bs-toggle="modal" data-bs-target="#modalChapitre" data-mode="ajout">
                    <i class="ri-add-line me-1"></i>Créer un chapitre
                </button>
            </div>

        <div class="card border shadow-sm">
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0 table-contenus">
                    <thead>
                        <tr class="table-light">
                            <th>N°</th>
                            <th>Titre</th>
                            <th>Description</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($chapitres as $chapitre)
                            <tr>
                                <td>{{ $chapitre->ordre }}</td>
                                <td class="fw-semibold">{{ $chapitre->titre }}</td>
                                <td class="text-muted">{{ \Illuminate\Support\Str::limit((string) $chapitre->contenu, 90) ?: '—' }}</td>
                                <td class="text-end contenus-actions">
                                    <div class="d-flex justify-content-end gap-1">
                                        {{-- Modifier : les valeurs actuelles voyagent dans les data-* du bouton --}}
                                        <button type="button" class="btn btn-sm btn-soft-secondary" title="Modifier le chapitre"
                                                data-bs-toggle="modal" data-bs-target="#modalChapitre"
                                                data-mode="modif"
                                                data-action="{{ route('admin.cours.chapitres.update', [$cours, $chapitre]) }}"
                                                data-titre="{{ $chapitre->titre }}"
                                                data-contenu="{{ $chapitre->contenu }}"
                                                data-ordre="{{ $chapitre->ordre }}">
                                            <i class="ri-edit-line"></i>
                                        </button>

                                        {{-- Supprimer : demande confirmation dans une fenêtre --}}
                                        <button type="button" class="btn btn-sm btn-soft-danger" title="Supprimer le chapitre"
                                                data-bs-toggle="modal" data-bs-target="#modalSuppression"
                                                data-action="{{ route('admin.cours.chapitres.destroy', [$cours, $chapitre]) }}"
                                                data-objet="le chapitre"
                                                data-nom="{{ $chapitre->titre }}">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-5">
                                    <i class="ri-list-check-2 display-5 d-block mb-2"></i>
                                    Aucun chapitre pour ce cours.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
                @if ($chapitres->total() > 0)
                <div class="card-footer bg-transparent d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                    <small class="text-muted">
                        Affichage de {{ $chapitres->firstItem() }} à {{ $chapitres->lastItem() }}
                        sur {{ $chapitres->total() }} résultat(s)
                    </small>
                    {{ $chapitres->links() }}
                </div>
            @endif
    </div>
        </div>

        {{-- ===================== RESSOURCES ===================== --}}
        <div class="tab-pane fade @if ($onglet === 'ressources') show active @endif" id="panneauRessources" role="tabpanel">
            <div class="d-flex justify-content-start mb-3">
                <button type="button" class="btn btn-primary fw-semibold"
                        data-bs-toggle="modal" data-bs-target="#modalRessource">
                    <i class="ri-add-line me-1"></i>Ajouter une ressource
                </button>
            </div>

        <div class="card border shadow-sm">
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0 table-contenus">
                    <thead>
                        <tr class="table-light">
                            <th>N°</th>
                            <th>Titre</th>
                            <th>Type</th>
                            <th>Détails</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($resources as $resource)
                            <tr>
                                <td>{{ $resources->firstItem() + $loop->index }}</td>
                                <td class="fw-semibold">{{ $resource->titre }}</td>
                                <td>
                                    <i class="{{ $iconesRessource[$resource->type] ?? 'ri-file-text-line' }} text-primary me-1"></i>{{ $libellesRessource[$resource->type] ?? ucfirst($resource->type) }}
                                </td>
                                <td class="text-muted">
                                    @if ($resource->extension){{ strtoupper($resource->extension) }}@endif
                                    @if ($resource->taille_lisible) · {{ $resource->taille_lisible }}@endif
                                    @if (! $resource->chemin && $resource->url) Adresse externe @endif
                                </td>
                                <td class="text-end contenus-actions">
                                    <div class="d-flex justify-content-end gap-1">
                                        <a href="{{ route('cours.resources.voir', [$cours, $resource]) }}" target="_blank" rel="noopener"
                                           class="btn btn-sm btn-soft-primary" title="Consulter">
                                            <i class="ri-eye-line"></i>
                                        </a>
                                        @if ($resource->chemin)
                                            <a href="{{ route('cours.resources.download', [$cours, $resource]) }}"
                                               class="btn btn-sm btn-soft-success" title="Télécharger">
                                                <i class="ri-download-line"></i>
                                            </a>
                                        @endif
                                        <button type="button" class="btn btn-sm btn-soft-danger" title="Supprimer la ressource"
                                                data-bs-toggle="modal" data-bs-target="#modalSuppression"
                                                data-action="{{ route('admin.cours.resources.destroy', [$cours, $resource]) }}"
                                                data-objet="la ressource"
                                                data-nom="{{ $resource->titre }}">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">
                                    <i class="ri-folder-open-line display-5 d-block mb-2"></i>
                                    Aucune ressource pour ce cours.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
                @if ($resources->total() > 0)
                <div class="card-footer bg-transparent d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                    <small class="text-muted">
                        Affichage de {{ $resources->firstItem() }} à {{ $resources->lastItem() }}
                        sur {{ $resources->total() }} résultat(s)
                    </small>
                    {{ $resources->links() }}
                </div>
            @endif
    </div>
        </div>
    </div>
</div>

{{-- ===== Fenêtre de confirmation de suppression (chapitre ou ressource) ===== --}}
<div class="modal fade" id="modalSuppression" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="formSuppression" method="POST" action="#">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title" id="suppressionTitre">Supprimer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Voulez-vous vraiment supprimer <span id="suppressionObjet"></span> <strong id="suppressionNom"></strong> ?</p>
                    <p class="text-muted mb-0">Cette action est définitive.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">Supprimer</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ===== Fenêtre « Créer / Modifier un chapitre » ===== --}}
<div class="modal fade" id="modalChapitre" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formChapitre" method="POST" action="{{ route('admin.cours.chapitres.store', $cours) }}">
                @csrf
                {{-- Champs techniques : _method = PUT seulement en modification ; _form, _mode et _action
                     permettent de rouvrir la bonne fenêtre si la validation échoue --}}
                <input type="hidden" name="_method" value="PUT" id="champMethode" disabled>
                <input type="hidden" name="_form" value="chapitre">
                <input type="hidden" name="_mode" id="champMode" value="{{ $modeAvecErreur }}">
                <input type="hidden" name="_action" id="champAction" value="{{ $formAvecErreur === 'chapitre' ? old('_action') : '' }}">

                <div class="modal-header">
                    <h5 class="modal-title" id="chapitreTitre">Créer un chapitre</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>

                <div class="modal-body">
                    @if ($formAvecErreur === 'chapitre')
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $erreur)
                                    <li>{{ $erreur }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label for="champTitre" class="form-label">Titre du chapitre</label>
                        <input type="text" name="titre" id="champTitre" class="form-control"
                               value="{{ $formAvecErreur === 'chapitre' ? old('titre') : '' }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="champOrdre" class="form-label">Numéro d'ordre</label>
                        <input type="number" name="ordre" id="champOrdre" min="1" class="form-control"
                               value="{{ $formAvecErreur === 'chapitre' ? old('ordre') : '' }}">
                        <div class="form-text" id="aideOrdre">Laissez vide pour placer le chapitre à la fin.</div>
                    </div>

                    <div class="mb-0">
                        <label for="champContenu" class="form-label">Contenu / description (facultatif)</label>
                        <textarea name="contenu" id="champContenu" rows="4" class="form-control">{{ $formAvecErreur === 'chapitre' ? old('contenu') : '' }}</textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ===== Fenêtre « Ajouter une ressource » ===== --}}
<div class="modal fade" id="modalRessource" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formRessource" method="POST" action="{{ route('admin.cours.resources.store', $cours) }}"
                  enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_form" value="ressource">

                <div class="modal-header">
                    <h5 class="modal-title">Ajouter une ressource</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>

                <div class="modal-body">
                    @if ($formAvecErreur === 'ressource')
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $erreur)
                                    <li>{{ $erreur }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label for="resourceTitre" class="form-label">Titre</label>
                        <input type="text" id="resourceTitre" name="titre" class="form-control"
                               placeholder="Ex. Support du cours" value="{{ $formAvecErreur === 'ressource' ? old('titre') : '' }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="resourceType" class="form-label">Type</label>
                        <select name="type" id="resourceType" class="form-select" required>
                            <option value="fichier" @selected(old('type', 'fichier') === 'fichier')>Document</option>
                            <option value="video" @selected(old('type') === 'video')>Vidéo</option>
                            <option value="lien" @selected(old('type') === 'lien')>Lien</option>
                        </select>
                    </div>

                    <div class="mb-3 d-none" id="videoSourceField">
                        <label class="form-label">Source de la vidéo</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" id="videoUpload" name="video_source"
                                   value="upload" @checked(old('video_source', 'upload') === 'upload')>
                            <label class="form-check-label" for="videoUpload">Importer une vidéo</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" id="videoUrl" name="video_source"
                                   value="url" @checked(old('video_source') === 'url')>
                            <label class="form-check-label" for="videoUrl">Utiliser une adresse (URL)</label>
                        </div>
                    </div>

                    <div class="mb-3 d-none" id="fileField">
                        <label for="resourceFile" id="fileLabel" class="form-label">Document</label>
                        <input type="file" id="resourceFile" name="fichier" class="form-control">
                        <div class="form-text" id="fileHelp"></div>
                    </div>

                    <div class="mb-0 d-none" id="urlField">
                        <label for="resourceUrl" class="form-label">Adresse (URL)</label>
                        <input type="url" id="resourceUrl" name="url" class="form-control"
                               placeholder="https://" value="{{ $formAvecErreur === 'ressource' ? old('url') : '' }}">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const champ = id => document.getElementById(id);

    // ---------- Onglets : on retient le dernier ouvert (utile après un ajout, qui recharge la page) ----------
    const ongletDansAdresse = @json($onglet);
    document.querySelectorAll('[data-onglet]').forEach(function (bouton) {
        bouton.addEventListener('shown.bs.tab', function () {
            try { sessionStorage.setItem('contenusOnglet', bouton.dataset.onglet); } catch (e) {}
        });
    });
    if (!ongletDansAdresse) {
        let dernier = null;
        try { dernier = sessionStorage.getItem('contenusOnglet'); } catch (e) {}
        if (dernier === 'ressources') {
            bootstrap.Tab.getOrCreateInstance(champ('ongletRessources')).show();
        }
    }

    // ---------- Chapitre : créer / modifier ----------
    const urlAjout = @json(route('admin.cours.chapitres.store', $cours));
    const modeAvecErreur = @json($modeAvecErreur);
    const modalChapitre = champ('modalChapitre');
    const formChapitre = champ('formChapitre');

    // Adapte la fenêtre au mode : "ajout" (POST) ou "modif" (PUT)
    function preparerChapitre(mode) {
        const modif = mode === 'modif';
        champ('chapitreTitre').textContent = modif ? 'Modifier le chapitre' : 'Créer un chapitre';
        champ('champMode').value = mode;
        champ('champMethode').disabled = !modif;
        // À la modification le numéro d'ordre est obligatoire (il est déjà rempli)
        champ('champOrdre').required = modif;
        champ('aideOrdre').classList.toggle('d-none', modif);
    }

    modalChapitre.addEventListener('show.bs.modal', function (e) {
        const bouton = e.relatedTarget;
        if (!bouton) return;

        const d = bouton.dataset;
        preparerChapitre(d.mode);

        if (d.mode === 'modif') {
            champ('champAction').value = d.action;
            formChapitre.action = d.action;
            champ('champTitre').value = d.titre;
            champ('champOrdre').value = d.ordre;
            champ('champContenu').value = d.contenu || '';
        } else {
            champ('champAction').value = urlAjout;
            formChapitre.action = urlAjout;
            champ('champTitre').value = '';
            champ('champOrdre').value = '';
            champ('champContenu').value = '';
        }
    });

    // Validation refusée sur le chapitre : on rouvre la fenêtre avec ce qui avait été saisi
    if (modeAvecErreur) {
        preparerChapitre(modeAvecErreur);
        formChapitre.action = champ('champAction').value || urlAjout;
        bootstrap.Modal.getOrCreateInstance(modalChapitre).show();
    }

    // ---------- Suppression (chapitre ou ressource) ----------
    champ('modalSuppression').addEventListener('show.bs.modal', function (e) {
        const d = e.relatedTarget.dataset;
        champ('suppressionTitre').textContent = 'Supprimer ' + d.objet;
        champ('suppressionObjet').textContent = d.objet;
        champ('suppressionNom').textContent = d.nom;
        champ('formSuppression').action = d.action;
    });

    // ---------- Ressource : champs selon le type ----------
    const modalRessource = champ('modalRessource');
    const resourceType = champ('resourceType');
    const fileField = champ('fileField');
    const videoSourceField = champ('videoSourceField');
    const urlField = champ('urlField');
    const resourceFile = champ('resourceFile');
    const resourceUrl = champ('resourceUrl');
    const videoUpload = champ('videoUpload');

    // Montre le champ utile (fichier ou adresse) et désactive l'autre pour qu'il ne soit pas envoyé
    function majRessource() {
        const type = resourceType.value;
        const fichier = type === 'fichier' || (type === 'video' && videoUpload.checked);
        const adresse = type === 'lien' || (type === 'video' && !videoUpload.checked);

        videoSourceField.classList.toggle('d-none', type !== 'video');
        fileField.classList.toggle('d-none', !fichier);
        urlField.classList.toggle('d-none', !adresse);

        resourceFile.disabled = !fichier;
        resourceFile.required = fichier;
        resourceUrl.disabled = !adresse;
        resourceUrl.required = adresse;

        champ('fileLabel').textContent = type === 'video' ? 'Fichier vidéo' : 'Document';
        champ('fileHelp').textContent = type === 'video'
            ? 'MP4, WebM, OGG, MOV, AVI ou MKV'
            : 'PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, JPG ou PNG';
    }

    resourceType.addEventListener('change', majRessource);
    document.querySelectorAll('input[name="video_source"]').forEach(r => r.addEventListener('change', majRessource));

    // Ouverture par le bouton : formulaire remis à zéro (pas en cas de retour d'erreur, géré plus bas)
    modalRessource.addEventListener('show.bs.modal', function (e) {
        if (!e.relatedTarget) return;
        champ('formRessource').reset();
        resourceType.value = 'fichier';
        videoUpload.checked = true;
        majRessource();
    });

    majRessource();

    @if ($formAvecErreur === 'ressource')
        bootstrap.Modal.getOrCreateInstance(modalRessource).show();
    @endif
});
</script>
@endsection