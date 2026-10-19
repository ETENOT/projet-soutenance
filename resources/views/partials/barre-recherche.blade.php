{{--
    Barre de recherche façon Odoo, réutilisable par les listes d'administration :
    étiquettes de filtres actifs dans la barre + menu à 3 colonnes (Filtres | Regrouper par | Plus de critères).
    Les filtres vivent dans l'URL (GET) : ils s'appliquent à toutes les pages de la liste.

    Variables :
      $action        URL de la liste (ex. route('admin.cours.index'))
      $placeholder   texte d'aide dans la zone de saisie
      $etiquettes    filtres actifs : tableau de [texte, [paramètres à retirer], icône]
      $filtres       cases du menu "Filtres" : tableau de blocs (séparés par un trait),
                     chaque bloc = tableau de ['nom', 'valeur', 'libelle', 'coche']
      $groupes       regroupements possibles : ['clé' => 'Libellé']  (facultatif)
      $groupe        regroupement actif (clé ou null)
      $vueCriteres   vue Blade de la 3e colonne "Plus de critères" (selects, nombres, dates...)
--}}
@php
    // URL de la liste sans certains paramètres (sert au "×" de chaque étiquette)
    $sans = function (array $cles) use ($action) {
        $parametres = request()->except(array_merge($cles, ['page']));
        return $parametres ? $action . '?' . http_build_query($parametres) : $action;
    };
    $groupes = $groupes ?? [];
    $groupe = $groupe ?? null;
@endphp

<form method="GET" action="{{ $action }}" class="mb-3">
    <div class="input-group dropdown">

        {{-- Zone de recherche : étiquettes actives + saisie libre --}}
        <div class="form-control h-auto d-flex flex-wrap align-items-center gap-1 py-1">
            <i class="ri-search-line text-primary me-1"></i>

            @foreach ($etiquettes as [$texte, $retirer, $icone])
                <span class="badge bg-primary-subtle text-primary d-inline-flex align-items-center gap-1 fs-12 py-2">
                    <i class="{{ $icone }}"></i>{{ $texte }}
                    <a href="{{ $sans($retirer) }}" class="text-primary ms-1" title="Retirer ce filtre"
                       aria-label="Retirer le filtre {{ $texte }}">
                        <i class="ri-close-line"></i>
                    </a>
                </span>
            @endforeach

            <input type="text" name="q" value="{{ request('q') }}"
                   class="form-control border-0 shadow-none flex-grow-1 w-auto p-1"
                   placeholder="{{ $placeholder }}" aria-label="{{ $placeholder }}">
        </div>

        <button type="button" class="btn btn-outline-secondary dropdown-toggle"
                data-bs-toggle="dropdown" data-bs-auto-close="outside" data-bs-display="static"
                aria-expanded="false" title="Filtres et regroupements">
            <i class="ri-equalizer-line"></i>
        </button>
        {{-- Sur téléphone, la touche Entrée du clavier suffit : on garde la place pour les étiquettes --}}
        <button type="submit" class="btn btn-primary d-none d-sm-inline-block">Rechercher</button>

        {{-- Menu déroulant : Filtres | Regrouper par | Plus de critères --}}
        <div class="dropdown-menu w-100 p-3 shadow">
            <div class="row g-4">

                {{-- Colonne 1 : filtres rapides (cocher = appliquer tout de suite) --}}
                <div class="col-md-4">
                    <h6 class="fw-semibold mb-2"><i class="ri-filter-3-line text-primary me-1"></i>Filtres</h6>

                    @foreach ($filtres as $bloc)
                        @if (! $loop->first)
                            <hr class="my-2">
                        @endif
                        @foreach ($bloc as $filtre)
                            @php
                                $idFiltre = 'filtre-' . md5($filtre['nom'] . $filtre['valeur']);
                            @endphp
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="checkbox" name="{{ $filtre['nom'] }}"
                                       value="{{ $filtre['valeur'] }}" id="{{ $idFiltre }}"
                                       @checked($filtre['coche']) onchange="this.form.submit()">
                                <label class="form-check-label" for="{{ $idFiltre }}">{{ $filtre['libelle'] }}</label>
                            </div>
                        @endforeach
                    @endforeach
                </div>

                {{-- Colonne 2 : regrouper par (un seul choix à la fois) --}}
                <div class="col-md-4">
                    @if ($groupes)
                        <h6 class="fw-semibold mb-2"><i class="ri-stack-line text-primary me-1"></i>Regrouper par</h6>

                        <div class="form-check mb-1">
                            <input class="form-check-input" type="radio" name="groupe" value="" id="groupe-aucun"
                                   @checked(! $groupe) onchange="this.form.submit()">
                            <label class="form-check-label" for="groupe-aucun">Aucun regroupement</label>
                        </div>
                        @foreach ($groupes as $valeur => $libelle)
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="radio" name="groupe" value="{{ $valeur }}"
                                       id="groupe-{{ $valeur }}" @checked($groupe === $valeur) onchange="this.form.submit()">
                                <label class="form-check-label" for="groupe-{{ $valeur }}">{{ $libelle }}</label>
                            </div>
                        @endforeach
                    @endif
                </div>

                {{-- Colonne 3 : critères précis, propres à chaque liste --}}
                <div class="col-md-4">
                    <h6 class="fw-semibold mb-2"><i class="ri-list-settings-line text-primary me-1"></i>Plus de critères</h6>

                    @include($vueCriteres)

                    <button type="submit" class="btn btn-sm btn-primary w-100">Appliquer</button>
                </div>
            </div>

            @if ($etiquettes || request()->filled('q'))
                <div class="text-end mt-3">
                    <a href="{{ $action }}" class="btn btn-sm btn-outline-secondary">
                        <i class="ri-refresh-line me-1"></i> Tout réinitialiser
                    </a>
                </div>
            @endif
        </div>
    </div>
</form>