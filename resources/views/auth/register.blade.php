<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!--
            Rôle : détermine si l'inscrit est un "particulier" ou une "entreprise".
            Ce choix pilote :
              1) quels champs supplémentaires afficher/valider juste en dessous (JS + validation serveur)
              2) quelle ligne créer en base (table particuliers ou entreprises)
              3) quel role_id est assigné à l'utilisateur (obligatoire en base, cf. migration users)
        -->
        <div class="mt-4">
            <x-input-label for="role" :value="__('Rôle')" />
            <select id="role" name="role" required
                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"
                    onchange="toggleRoleFields()">
                {{-- old('role', 'particulier') : pré-sélectionne "particulier" par défaut au premier
                     chargement, mais réaffiche le choix précédent de l'utilisateur si le formulaire
                     est réaffiché après une erreur de validation --}}
                <option value="particulier" @selected(old('role', 'particulier') == 'particulier')>Particulier</option>
                <option value="entreprise" @selected(old('role') == 'entreprise')>Entreprise</option>
            </select>
            <x-input-error :messages="$errors->get('role')" class="mt-2" />
        </div>

        <!--
            Bloc de champs "particulier" : visible par défaut (rôle par défaut du select).
            Alimente ensuite Particulier::create() côté contrôleur.
        -->
        <div id="particulier-fields">
            <div class="mt-4">
                <x-input-label for="telephone" :value="__('Téléphone')" />
                <x-text-input id="telephone" class="block mt-1 w-full" type="text" name="telephone" :value="old('telephone')" autocomplete="tel" />
                <x-input-error :messages="$errors->get('telephone')" class="mt-2" />
            </div>
            <div class="mt-4">
                <x-input-label for="date_de_naissance" :value="__('Date de naissance')" />
                <x-text-input id="date_de_naissance" class="block mt-1 w-full" type="date" name="date_de_naissance" :value="old('date_de_naissance')" />
                <x-input-error :messages="$errors->get('date_de_naissance')" class="mt-2" />
            </div>
        </div>

        <!--
            Bloc de champs "entreprise" : caché par défaut (class="hidden", utilitaire Tailwind).
            Alimente ensuite Entreprise::create() côté contrôleur.
        -->
        <div id="entreprise-fields" class="hidden">
            <div class="mt-4">
                <x-input-label for="raison_sociale" :value="__('Raison sociale')" />
                <x-text-input id="raison_sociale" class="block mt-1 w-full" type="text" name="raison_sociale" :value="old('raison_sociale')" />
                <x-input-error :messages="$errors->get('raison_sociale')" class="mt-2" />
            </div>
            <div class="mt-4">
                <x-input-label for="adresse" :value="__('Adresse')" />
                <x-text-input id="adresse" class="block mt-1 w-full" type="text" name="adresse" :value="old('adresse')" />
                <x-input-error :messages="$errors->get('adresse')" class="mt-2" />
            </div>
            <div class="mt-4">
                <x-input-label for="contact_principal" :value="__('Contact principal')" />
                <x-text-input id="contact_principal" class="block mt-1 w-full" type="text" name="contact_principal" :value="old('contact_principal')" />
                <x-input-error :messages="$errors->get('contact_principal')" class="mt-2" />
            </div>
            <div class="mt-4">
                <x-input-label for="secteur_activite" :value="__('Secteur d\'activité')" />
                <x-text-input id="secteur_activite" class="block mt-1 w-full" type="text" name="secteur_activite" :value="old('secteur_activite')" />
                <x-input-error :messages="$errors->get('secteur_activite')" class="mt-2" />
            </div>
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>
            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>

    <script>
        // Affiche uniquement le bloc de champs correspondant au rôle sélectionné,
        // masque l'autre. Pas d'Alpine.js utilisé ici : présent dans package.json
        // mais jamais démarré (pas d'Alpine.start() dans resources/js/app.js),
        // donc on reste en JS natif pour ne rien casser.
        function toggleRoleFields() {
            const role = document.getElementById('role').value;
            document.getElementById('particulier-fields').classList.toggle('hidden', role !== 'particulier');
            document.getElementById('entreprise-fields').classList.toggle('hidden', role !== 'entreprise');
        }

        // Appliquer l'état correct dès le chargement de la page (utile si le
        // navigateur restaure une ancienne sélection du <select> après un refresh)
        document.addEventListener('DOMContentLoaded', toggleRoleFields);
    </script>
</x-guest-layout>