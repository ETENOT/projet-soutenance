{{--
    Champs communs à la création et à la modification d'une session.
    Variables : $session (absent à la création), $entreprises, $entrepriseId (entreprise présélectionnée)
--}}
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0"><span class="badge bg-primary me-2">1</span>Informations de la session</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label for="nom" class="form-label">Nom de la session <span class="text-danger">*</span></label>
                <input type="text" name="nom" id="nom" class="form-control @error('nom') is-invalid @enderror"
                       value="{{ old('nom', $session->nom ?? '') }}" required>
                @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label for="entreprise_id" class="form-label">Entreprise <span class="text-danger">*</span></label>
                <select name="entreprise_id" id="entreprise_id" class="form-select @error('entreprise_id') is-invalid @enderror" required>
                    <option value="">— Choisir une entreprise —</option>
                    @foreach ($entreprises as $entreprise)
                        <option value="{{ $entreprise->id }}" @selected((int) old('entreprise_id', $entrepriseId) === $entreprise->id)>
                            {{ $entreprise->raison_sociale }}
                        </option>
                    @endforeach
                </select>
                @error('entreprise_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="form-text">Une session est toujours rattachée à une seule entreprise.</div>
            </div>

            <div class="col-12">
                <label for="description" class="form-label">Description <span class="text-muted">(facultatif)</span></label>
                <textarea name="description" id="description" rows="3"
                          class="form-control @error('description') is-invalid @enderror">{{ old('description', $session->description ?? '') }}</textarea>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <p class="text-muted small mt-3 mb-0">
            <i class="ri-information-line me-1"></i>
            Les dates de début et de fin ne se saisissent pas : elles sont déduites des classes de la session
            (début de la première classe, fin de la dernière) et se mettent à jour automatiquement.
        </p>
    </div>
</div>