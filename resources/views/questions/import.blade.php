@extends('layouts.master')

@section('title')
    Importer des questions
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('admin.questions.index') }}">Banque de questions</a>
        @endslot
        @slot('title')
            Importer des questions (CSV)
        @endslot
    @endcomponent

    {{-- Erreurs de l'import : le fichier est refusé en entier tant qu'une ligne est invalide --}}
    @if (session('import_erreurs'))
        <div class="alert alert-danger">
            <strong>{{ session('import_total_erreurs') }} erreur(s) dans le fichier</strong> — corrigez-les puis réimportez :
            <ul class="mb-0 mt-2">
                @foreach (session('import_erreurs') as $erreur)
                    <li>{{ $erreur }}</li>
                @endforeach
            </ul>
            @if (session('import_total_erreurs') > count(session('import_erreurs')))
                <div class="mt-2 text-muted">… et {{ session('import_total_erreurs') - count(session('import_erreurs')) }} autre(s).</div>
            @endif
        </div>
    @endif

    <div class="row">
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Fichier à importer</h5></div>
                <div class="card-body">
                    {{-- enctype obligatoire pour envoyer un fichier --}}
                    <form action="{{ route('admin.questions.import.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label for="fichier" class="form-label">Fichier CSV (2 Mo max)</label>
                            <input type="file" name="fichier" id="fichier" accept=".csv,.txt"
                                   class="form-control @error('fichier') is-invalid @enderror" required>
                            @error('fichier')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="cours_id" class="form-label">Cours par défaut <span class="text-muted">(optionnel)</span></label>
                            <select name="cours_id" id="cours_id" class="form-select @error('cours_id') is-invalid @enderror">
                                <option value="">— Aucun (utiliser la colonne « cours ») —</option>
                                @foreach ($cours as $c)
                                    <option value="{{ $c->id }}" @selected((string) old('cours_id') === (string) $c->id)>{{ $c->titre }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Utilisé pour les lignes dont la colonne « cours » est vide ou absente.</small>
                            @error('cours_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success"><i class="ri-file-upload-line me-1"></i>Importer</button>
                            <a href="{{ route('admin.questions.import.modele') }}" class="btn btn-soft-primary">
                                <i class="ri-download-2-line me-1"></i>Télécharger le modèle
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Format du fichier</h5></div>
                <div class="card-body">
                    <p>La première ligne contient les en-têtes. Une ligne = une question.</p>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>cours</th><th>question</th><th>option_1</th><th>option_2</th><th>option_3</th><th>option_4</th><th>bonne_reponse</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Microsoft Word</td><td>Quel logiciel crée des documents ?</td><td>Word</td><td>Excel</td><td></td><td>PowerPoint</td><td>1</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <ul class="mb-0">
                        <li><strong>question</strong>, <strong>option_1</strong>, <strong>option_2</strong> et <strong>bonne_reponse</strong> sont obligatoires ; jusqu'à <strong>option_6</strong>.</li>
                        <li><strong>cours</strong> : titre exact du cours (ou son identifiant). Facultatif si vous choisissez un cours par défaut.</li>
                        <li><strong>bonne_reponse</strong> : le numéro de l'option (<code>1</code>, <code>2</code>…), sa lettre (<code>A</code>, <code>B</code>…) ou son texte exact.</li>
                        <li>Les options vides sont ignorées (au moins 2 options renseignées).</li>
                        <li>Séparateur <code>;</code>, <code>,</code> ou tabulation détecté automatiquement ; encodage UTF-8 ou Excel (Windows-1252) accepté.</li>
                        <li>Une question déjà présente dans le même cours (même énoncé) est <strong>ignorée</strong>, pas dupliquée.</li>
                        <li>Si une ligne est invalide, <strong>rien n'est importé</strong> : les erreurs sont listées avec leur numéro de ligne.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection