@extends('layouts.master')

@section('title')
    Modifier le cours
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('admin.cours.index') }}">Gestion des cours</a>
        @endslot
        @slot('title')
            Modifier le cours
        @endslot
    @endcomponent

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.cours.update', $cours) }}" method="POST">
                @csrf
                @method('PUT')

                                <div class="mb-3">
                    <label for="titre" class="form-label">Titre du cours</label>
                    <input type="text" name="titre" id="titre" class="form-control @error('titre') is-invalid @enderror"
                           value="{{ old('titre', $cours->titre) }}">
                    @error('titre')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- categorie/description : requis par la validation mais pas encore de champ dédié
                     dans ce formulaire — on préserve la valeur actuelle pour ne pas bloquer
                     l'enregistrement (sujet à part, pas traité ici). --}}
                <input type="hidden" name="categorie" value="{{ old('categorie', $cours->categorie) }}">
                <input type="hidden" name="description" value="{{ old('description', $cours->description) }}">

                <div class="mb-3">
                    <label for="programme" class="form-label">Plan détaillé du cours (visible publiquement, même sans être inscrit)</label>
                                        <textarea name="programme"
                            id="programme"
                            rows="10"
                            class="form-control @error('programme') is-invalid @enderror"
                            placeholder="## Module 1 - Découverte de l'interface&#10;- Présentation des outils&#10;- Navigation dans le logiciel&#10;&#10;## Module 2 - Mise en forme&#10;- Styles de texte&#10;- Mise en page">{{ old('programme', $cours->programme ?? null) }}</textarea>
                    <div class="form-text">
                        Une ligne commençant par <code>## </code> = un grand point (module). Les lignes
                        <code>- </code> juste après = ses sous-points. Voir l'exemple pré-rempli ci-dessus.
                    </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="prix_particulier" class="form-label">Prix particulier (fcfa)</label>
                        <input type="number" step="0.01" name="prix_particulier" id="prix_particulier"
                               class="form-control @error('prix_particulier') is-invalid @enderror"
                               value="{{ old('prix_particulier', $cours->prix_particulier) }}">
                        @error('prix_particulier')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="prix_entreprise" class="form-label">Prix entreprise (fcfa)</label>
                        <input type="number" step="0.01" name="prix_entreprise" id="prix_entreprise"
                               class="form-control @error('prix_entreprise') is-invalid @enderror"
                               value="{{ old('prix_entreprise', $cours->prix_entreprise) }}">
                        @error('prix_entreprise')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                    <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">Documents du cours</h5>

                {{-- Liste des documents déjà en ligne --}}
                @if($cours->resources->isEmpty())
                    <p class="text-muted">Aucun document pour l'instant.</p>
                @else
                    <ul class="list-group mb-3">
                        @foreach($cours->resources as $resource)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>
                                    <i class="{{ $resource->estVideo() ? 'ri-video-line' : 'ri-file-text-line' }} text-muted me-1"></i>
                                    {{ $resource->titre }}
                                    <span class="text-muted small">
                                        @if($resource->estVideo())
                                            (Vidéo)
                                        @else
                                            ({{ strtoupper($resource->extension) }} — {{ $resource->taille_lisible }})
                                        @endif
                                    </span>
                                </span>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('cours.resources.voir', [$cours, $resource]) }}" target="_blank" class="btn btn-sm btn-outline-primary">Voir</a>
                                    @if(!$resource->estVideo())
                                        <a href="{{ route('cours.resources.download', [$cours, $resource]) }}" class="btn btn-sm btn-outline-secondary">Télécharger</a>
                                    @endif
                                    <form action="{{ route('admin.cours.resources.destroy', [$cours, $resource]) }}" method="POST"
                                        onsubmit="return confirm('Supprimer ce document ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Supprimer</button>
                                    </form>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif

                {{-- Formulaire d'ajout : soit un fichier, soit un lien vidéo --}}
                <form action="{{ route('admin.cours.resources.store', $cours) }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label d-block">Type de contenu</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="type" id="type_fichier" value="fichier" checked
                                onchange="document.getElementById('bloc_fichier').classList.remove('d-none'); document.getElementById('bloc_video').classList.add('d-none');">
                            <label class="form-check-label" for="type_fichier">Fichier (PDF, Word, Excel, PowerPoint)</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="type" id="type_video" value="video"
                                onchange="document.getElementById('bloc_video').classList.remove('d-none'); document.getElementById('bloc_fichier').classList.add('d-none');">
                            <label class="form-check-label" for="type_video">Lien vidéo (YouTube, Vimeo...)</label>
                        </div>
                        @error('type')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row g-2 align-items-end">
                        <div class="col-md-5">
                            <label for="titre_document" class="form-label">Titre</label>
                            <input type="text" name="titre" id="titre_document"
                                class="form-control @error('titre') is-invalid @enderror" value="{{ old('titre') }}">
                            @error('titre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-5" id="bloc_fichier">
                            <label for="fichier" class="form-label">Fichier (20 Mo max)</label>
                            <input type="file" name="fichier" id="fichier"
                                class="form-control @error('fichier') is-invalid @enderror">
                            @error('fichier')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-5 d-none" id="bloc_video">
                            <label for="url" class="form-label">Lien de la vidéo</label>
                            <input type="url" name="url" id="url" placeholder="https://..."
                                class="form-control @error('url') is-invalid @enderror" value="{{ old('url') }}">
                            @error('url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Ajouter</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

                <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                <a href="{{ route('admin.cours.index') }}" class="btn btn-light">Annuler</a>
            </form>
        </div>
    </div>
@endsection
