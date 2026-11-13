{{--
    Numéro Airtel Money à débiter (à inclure dans le formulaire de paiement en ligne).
    Pré-rempli avec le téléphone du profil s'il s'agit d'un numéro gabonais.
    La demande de paiement arrive sur ce téléphone : le client la valide avec son code PIN.
--}}
@php
    // Téléphone du profil au format international (+24174123456) -> format local (074123456).
    // Proposé seulement s'il s'agit d'un numéro Airtel (7 après l'indicatif).
    $telephoneProfil = (string) auth()->user()?->particulier?->telephone;
    $numeroPropose = preg_match('/^\+2417\d{7}$/', $telephoneProfil) ? '0' . substr($telephoneProfil, 4) : '';
@endphp

<div class="mb-3">
    <label for="numero_airtel" class="form-label">Numéro Airtel Money <span class="text-danger">*</span></label>
    <div class="input-group">
        <span class="input-group-text"><i class="ri-smartphone-line"></i></span>
        <input type="tel" name="numero_airtel" id="numero_airtel" inputmode="numeric" autocomplete="tel-national"
               class="form-control @error('numero_airtel') is-invalid @enderror"
               value="{{ old('numero_airtel', $numeroPropose) }}" placeholder="074 12 34 56" required>
    </div>
    @error('numero_airtel')
        <div class="text-danger fs-13 mt-1">{{ $message }}</div>
    @enderror
    <div class="form-text">Vous recevrez sur ce téléphone une demande de paiement à valider avec votre code PIN.</div>
</div>
