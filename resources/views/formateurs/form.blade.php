{{--
    Champs communs aux formulaires de création et de modification d'un formateur.
    Variables : $formateur (null à la création)
--}}
@php $creation = ! isset($formateur); @endphp

{{-- Nom --}}
<div class="mb-3">
    <label for="name" class="form-label">Nom complet</label>
    <input type="text" name="name" id="name"
           class="form-control @error('name') is-invalid @enderror"
           value="{{ old('name', $formateur->name ?? '') }}" required>
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

{{-- E-mail --}}
<div class="mb-3">
    <label for="email" class="form-label">Adresse e-mail</label>
    <input type="email" name="email" id="email"
           class="form-control @error('email') is-invalid @enderror"
           value="{{ old('email', $formateur->email ?? '') }}" required>
    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

{{-- Mot de passe : obligatoire à la création, facultatif à la modification --}}
<div class="row">
        {{-- Création : mot de passe attribué automatiquement. Modification : changement facultatif --}}
    @if ($creation)
        <div class="alert alert-info d-flex align-items-start gap-2 mb-3">
            <i class="ri-key-2-line fs-4"></i>
            <div>
                Le mot de passe est attribué automatiquement :
                <code class="fs-14">{{ \App\Models\User::MOT_DE_PASSE_PAR_DEFAUT }}</code>.
                Le formateur devra le remplacer lors de sa première connexion.
            </div>
        </div>
    @else
        <div class="col-md-6 mb-3">
            <label for="password" class="form-label">
                Mot de passe @unless($creation)<small class="text-muted">(laisser vide pour ne pas le changer)</small>@endunless
            </label>
            <input type="password" name="password" id="password" autocomplete="new-password"
                class="form-control @error('password') is-invalid @enderror"
                @required($creation)>
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-6 mb-3">
            <label for="password_confirmation" class="form-label">Confirmer le mot de passe</label>
            <input type="password" name="password_confirmation" id="password_confirmation" autocomplete="new-password"
                class="form-control" @required($creation)>
        </div>
    @endif
</div>