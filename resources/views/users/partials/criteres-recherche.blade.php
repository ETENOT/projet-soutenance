{{-- Colonne "Plus de critères" de la liste admin des utilisateurs (incluse par partials/barre-recherche) --}}
<div class="input-group input-group-sm mb-2">
    <span class="input-group-text">Inscrit du</span>
    <input type="date" name="date_du" value="{{ request('date_du') }}" class="form-control"
           aria-label="Date d'inscription : début de la période">
    <span class="input-group-text">au</span>
    <input type="date" name="date_au" value="{{ request('date_au') }}" class="form-control"
           aria-label="Date d'inscription : fin de la période">
</div>

<div class="input-group input-group-sm mb-2">
    <span class="input-group-text">Inscriptions</span>
    <input type="number" name="inscriptions_min" min="0" value="{{ request('inscriptions_min') }}"
           class="form-control" placeholder="min" aria-label="Nombre d'inscriptions minimum">
    <input type="number" name="inscriptions_max" min="0" value="{{ request('inscriptions_max') }}"
           class="form-control" placeholder="max" aria-label="Nombre d'inscriptions maximum">
</div>