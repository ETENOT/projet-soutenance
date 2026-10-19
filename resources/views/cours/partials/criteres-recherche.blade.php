{{-- Colonne "Plus de critères" de la liste admin des formations (incluse par partials/barre-recherche) --}}
<select name="categorie" class="form-select form-select-sm mb-2" aria-label="Filtrer par catégorie"
        onchange="this.form.submit()">
    <option value="">Toutes les catégories</option>
    @foreach ($toutesLesCategories as $categorie)
        <option value="{{ $categorie }}" @selected(request('categorie') === $categorie)>{{ $categorie }}</option>
    @endforeach
</select>

<div class="input-group input-group-sm mb-2">
    <span class="input-group-text">Classes</span>
    <input type="number" name="classes_min" min="0" value="{{ request('classes_min') }}"
           class="form-control" placeholder="min" aria-label="Nombre de classes minimum">
    <input type="number" name="classes_max" min="0" value="{{ request('classes_max') }}"
           class="form-control" placeholder="max" aria-label="Nombre de classes maximum">
</div>

<div class="input-group input-group-sm mb-2">
    <span class="input-group-text">Prix particulier</span>
    <input type="number" name="prix_min" min="0" value="{{ request('prix_min') }}"
           class="form-control" placeholder="min" aria-label="Prix particulier minimum">
    <input type="number" name="prix_max" min="0" value="{{ request('prix_max') }}"
           class="form-control" placeholder="max" aria-label="Prix particulier maximum">
</div>