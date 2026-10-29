@extends('layouts.master')

@section('title', 'Ajouter des membres')

@section('content')

@component('components.breadcrumb')
    @slot('li_1')
        <a href="{{ route('admin.entreprises.show', $entreprise) }}">{{ $entreprise->raison_sociale }}</a>
    @endslot
    @slot('title')
        Ajouter des membres
    @endslot
@endcomponent

<div class="row justify-content-center">
    <div class="col-xl-9">

        {{-- ===== 1. Particuliers déjà inscrits : recherche + sélection ===== --}}
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <span class="badge bg-primary me-2">1</span>Ajouter des utilisateurs déjà inscrits
                </h5>
            </div>
            <div class="card-body">
                @error('user_ids')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror

                <div class="row g-3">
                    <div class="col-lg-7">
                        <div class="input-group mb-3">
                            <span class="input-group-text bg-transparent"><i class="ri-search-line text-primary"></i></span>
                            <input type="text" id="recherche" class="form-control" autocomplete="off"
                                   placeholder="Nom ou adresse e-mail (2 caractères minimum)…"
                                   aria-label="Rechercher un particulier">
                        </div>

                        <div id="message-recherche" class="text-muted small mb-2"></div>
                        <div id="resultats" class="list-group"></div>
                    </div>

                    <div class="col-lg-5">
                        <h6 class="mb-2">Sélection (<span id="compteur">0</span>)</h6>

                        <form method="POST" action="{{ route('admin.entreprises.membres.store', $entreprise) }}">
                            @csrf
                            <div id="champs-ids"></div>
                            <ul id="selection" class="list-group mb-3"></ul>

                            <button type="submit" id="bouton-ajout" class="btn btn-primary w-100" disabled>
                                <i class="ri-user-add-line me-1"></i> Ajouter les utilisateurs
                            </button>
                        </form>
                    </div>
                </div>

                <p class="text-muted fs-13 mt-3 mb-0">
                    <i class="ri-information-line me-1"></i>
                    Un particulier n'est membre que d'une seule entreprise : ajouter un utilisateur déjà membre
                    d'une autre entreprise l'en retire.
                </p>
            </div>
        </div>

        {{-- ===== 2. Nouveau particulier ===== --}}
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <span class="badge bg-primary me-2">2</span>Ou créer un nouveau particulier
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.entreprises.membres.store-nouveau', $entreprise) }}" novalidate>
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Nom complet <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label">Adresse e-mail <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label for="telephone" class="form-label">Téléphone <span class="text-danger">*</span></label>
                            <input type="tel" id="telephone" data-champ-telephone="telephone" placeholder="+24177123456"
                                   class="form-control @error('telephone') is-invalid @enderror"
                                   value="{{ old('telephone') }}" required>
                            <div class="form-text">Format international, avec l'indicatif du pays.</div>
                            @error('telephone')<div class="text-danger fs-13 mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label for="date_de_naissance" class="form-label">Date de naissance <span class="text-muted">(facultative)</span></label>
                            <input type="date" name="date_de_naissance" id="date_de_naissance"
                                   class="form-control @error('date_de_naissance') is-invalid @enderror"
                                   value="{{ old('date_de_naissance') }}">
                            @error('date_de_naissance')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="alert alert-info d-flex align-items-start gap-2 mt-3">
                        <i class="ri-key-2-line fs-4"></i>
                        <div>
                            Le particulier sera automatiquement membre de <strong>{{ $entreprise->raison_sociale }}</strong>.
                            Son adresse e-mail est considérée comme vérifiée (aucun code à saisir) et son mot de passe est attribué
                            automatiquement : <code class="fs-14">{{ \App\Models\User::MOT_DE_PASSE_PAR_DEFAUT }}</code>.
                            Il devra le remplacer lors de sa première connexion.
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-user-add-line me-1"></i> Créer et ajouter comme membre
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="d-flex justify-content-end mb-4">
            <a href="{{ route('admin.entreprises.show', $entreprise) }}" class="btn btn-light">Retour à l'entreprise</a>
        </div>
    </div>
</div>

@endsection

@section('css')
    @include('partials.css-telephone')
@endsection

@section('script')
@include('partials.script-telephone')
<script>
(function () {
    const urlRecherche = @json(route('admin.entreprises.membres.rechercher', $entreprise));
    const champ = document.getElementById('recherche');
    const zoneResultats = document.getElementById('resultats');
    const message = document.getElementById('message-recherche');
    const listeSelection = document.getElementById('selection');
    const champsIds = document.getElementById('champs-ids');
    const compteur = document.getElementById('compteur');
    const boutonAjout = document.getElementById('bouton-ajout');

    // Utilisateurs sélectionnés : id -> {id, name, email, autre_entreprise}
    const selection = new Map();
    let minuteur = null;
    let derniereRequete = 0;
    let derniersResultats = [];

    // Échappe le texte avant de l'insérer dans le HTML (noms et e-mails saisis par des utilisateurs)
    function echapper(texte) {
        const div = document.createElement('div');
        div.textContent = texte;
        return div.innerHTML;
    }

    // Redessine la colonne de droite et les champs cachés envoyés au serveur
    function afficherSelection() {
        listeSelection.innerHTML = '';
        champsIds.innerHTML = '';

        selection.forEach(function (u) {
            const li = document.createElement('li');
            li.className = 'list-group-item d-flex justify-content-between align-items-center';
            li.innerHTML = '<div><div class="fw-medium">' + echapper(u.name) + '</div>'
                + '<small class="text-muted">' + echapper(u.email) + '</small></div>';

            const retirer = document.createElement('button');
            retirer.type = 'button';
            retirer.className = 'btn btn-sm btn-soft-danger';
            retirer.title = 'Retirer de la sélection';
            retirer.innerHTML = '<i class="ri-close-line"></i>';
            retirer.addEventListener('click', function () {
                selection.delete(u.id);
                afficherSelection();
                afficherResultats(derniersResultats);
            });
            li.appendChild(retirer);
            listeSelection.appendChild(li);

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'user_ids[]';
            input.value = u.id;
            champsIds.appendChild(input);
        });

        compteur.textContent = selection.size;
        boutonAjout.disabled = selection.size === 0;
    }

    function afficherResultats(utilisateurs) {
        derniersResultats = utilisateurs;
        zoneResultats.innerHTML = '';
        message.textContent = utilisateurs.length ? '' : 'Aucun particulier trouvé.';

        utilisateurs.forEach(function (u) {
            const ligne = document.createElement('div');
            ligne.className = 'list-group-item d-flex justify-content-between align-items-center gap-2';

            let detail = '<small class="text-muted">' + echapper(u.email) + '</small>';
            if (u.autre_entreprise) {
                detail += '<div class="fs-12 text-warning"><i class="ri-alert-line me-1"></i>Membre de « '
                    + echapper(u.autre_entreprise) + ' » : il la quittera</div>';
            }
            ligne.innerHTML = '<div><div class="fw-medium">' + echapper(u.name) + '</div>' + detail + '</div>';

            const bouton = document.createElement('button');
            bouton.type = 'button';

            if (u.deja) {
                // Déjà membre de cette entreprise : rien à ajouter
                bouton.className = 'btn btn-sm btn-light disabled';
                bouton.textContent = 'Déjà membre';
            } else if (selection.has(u.id)) {
                bouton.className = 'btn btn-sm btn-success disabled';
                bouton.innerHTML = '<i class="ri-check-line me-1"></i> Sélectionné';
            } else {
                bouton.className = 'btn btn-sm btn-soft-primary';
                bouton.innerHTML = '<i class="ri-add-line me-1"></i> Sélectionner';
                bouton.addEventListener('click', function () {
                    selection.set(u.id, u);
                    afficherSelection();
                    afficherResultats(derniersResultats);
                });
            }

            ligne.appendChild(bouton);
            zoneResultats.appendChild(ligne);
        });
    }

    // Recherche avec un léger délai (on attend que la frappe s'arrête)
    champ.addEventListener('input', function () {
        clearTimeout(minuteur);
        const terme = champ.value.trim();

        if (terme.length < 2) {
            zoneResultats.innerHTML = '';
            derniersResultats = [];
            message.textContent = terme.length ? 'Saisissez au moins 2 caractères.' : '';
            return;
        }

        minuteur = setTimeout(function () {
            const numero = ++derniereRequete;
            fetch(urlRecherche + '?q=' + encodeURIComponent(terme), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(function (r) {
                    if (!r.ok) { throw new Error('http ' + r.status); }
                    return r.json();
                })
                .then(function (donnees) {
                    // On ignore une réponse arrivée après une recherche plus récente
                    if (numero === derniereRequete) { afficherResultats(donnees); }
                })
                .catch(function () {
                    message.textContent = 'La recherche a échoué. Réessayez dans un instant.';
                });
        }, 300);
    });

    afficherSelection();
})();
</script>
@endsection