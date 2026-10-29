{{--
    Champs communs aux formulaires de création et de modification d'une entreprise.
    Variables : $entreprise (null à la création)
--}}
@php $creation = ! isset($entreprise); @endphp

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0"><span class="badge bg-primary me-2">1</span>Informations de l'entreprise</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label for="raison_sociale" class="form-label">Raison sociale <span class="text-danger">*</span></label>
                <input type="text" name="raison_sociale" id="raison_sociale"
                       class="form-control @error('raison_sociale') is-invalid @enderror"
                       value="{{ old('raison_sociale', $entreprise->raison_sociale ?? '') }}" required>
                @error('raison_sociale')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label for="secteur_activite" class="form-label">Secteur d'activité <span class="text-muted">(facultatif)</span></label>
                <input type="text" name="secteur_activite" id="secteur_activite"
                       class="form-control @error('secteur_activite') is-invalid @enderror"
                       value="{{ old('secteur_activite', $entreprise->secteur_activite ?? '') }}">
                @error('secteur_activite')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label for="adresse" class="form-label">Siège social <span class="text-muted">(facultatif)</span></label>
                <input type="text" name="adresse" id="adresse"
                       class="form-control @error('adresse') is-invalid @enderror"
                       value="{{ old('adresse', $entreprise->adresse ?? '') }}" required>
                @error('adresse')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label for="contact_principal" class="form-label">Contact principal (téléphone) <span class="text-danger">*</span></label>
                {{-- Pas d'attribut name : le numéro international complet part dans un champ caché (voir partials.script-telephone) --}}
                <input type="tel" id="contact_principal" data-champ-telephone="contact_principal" placeholder="77123456"
                       class="form-control @error('contact_principal') is-invalid @enderror"
                       value="{{ old('contact_principal', $entreprise->contact_principal ?? '') }}" required>
                <div class="form-text">Format international, avec l'indicatif du pays.</div>
                @error('contact_principal')<div class="text-danger fs-13 mt-1">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>
</div>

{{-- Le compte de connexion n'est demandé qu'à la création (ensuite : gestion des utilisateurs) --}}
@if ($creation)
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0"><span class="badge bg-primary me-2">2</span>Compte de connexion de l'entreprise</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="name" class="form-label">Nom du responsable du compte <span class="text-danger">*</span></label>
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
            </div>

            <div class="alert alert-info d-flex align-items-start gap-2 mt-3 mb-0">
                <i class="ri-key-2-line fs-4"></i>
                <div>
                    Le mot de passe est attribué automatiquement :
                    <code class="fs-14">{{ \App\Models\User::MOT_DE_PASSE_PAR_DEFAUT }}</code>.
                    Le responsable devra le remplacer lors de sa première connexion. Son adresse e-mail est considérée comme vérifiée.
                </div>
            </div>
        </div>
    </div>
@endif