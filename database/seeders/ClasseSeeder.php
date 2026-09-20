<?php
 
namespace Database\Seeders;
 
use App\Models\Classe;
use App\Models\Cours;
use Illuminate\Database\Seeder;
 
class ClasseSeeder extends Seeder
{
	/**
	 * Cree des sessions disponibles pour les cours du catalogue.
	 * Ce seeder doit etre execute apres CoursSeeder.
	 */
	public function run(): void
	{
		// On recupere chaque cours pour utiliser son identifiant comme cle etrangere.
		$word = Cours::where('titre', 'Microsoft Word')->firstOrFail();
		$excel = Cours::where('titre', 'Microsoft Excel')->firstOrFail();
		$powerPoint = Cours::where('titre', 'Microsoft PowerPoint')->firstOrFail();
		$devWeb = Cours::where('titre', 'Developpement Web - HTML, CSS, JavaScript')->firstOrFail();
		$comptabilite = Cours::where('titre', 'Comptabilite generale')->firstOrFail();
		$gestionProjet = Cours::where('titre', 'Gestion de projet')->firstOrFail();
		$anglais = Cours::where('titre', 'Anglais professionnel')->firstOrFail();
		$initiation = Cours::where('titre', 'Initiation a l\'informatique')->firstOrFail();
 
		// Chaque classe represente une session avec ses propres dates et son lieu.
		Classe::create([
			'nom' => 'Microsoft Word - Session Octobre 2026',
			'capacite_max' => 10,
			'date_debut' => '2026-10-05',
			'date_fin' => '2026-10-09',
			'lieu' => 'Libreville',
			'cours_id' => $word->id,
		]);
 
		Classe::create([
			'nom' => 'Microsoft Word - Session Decembre 2026',
			'capacite_max' => 10,
			'date_debut' => '2026-12-07',
			'date_fin' => '2026-12-11',
			'lieu' => 'Libreville',
			'cours_id' => $word->id,
		]);
 
		Classe::create([
			'nom' => 'Microsoft Excel - Session Octobre 2026',
			'capacite_max' => 10,
			'date_debut' => '2026-10-12',
			'date_fin' => '2026-10-16',
			'lieu' => 'Libreville',
			'cours_id' => $excel->id,
		]);
 
		Classe::create([
			'nom' => 'Microsoft PowerPoint - Session Octobre 2026',
			'capacite_max' => 10,
			'date_debut' => '2026-10-19',
			'date_fin' => '2026-10-23',
			'lieu' => 'Libreville',
			'cours_id' => $powerPoint->id,
		]);
 
		Classe::create([
			'nom' => 'Developpement Web - Session Novembre 2026',
			'capacite_max' => 8,
			'date_debut' => '2026-11-02',
			'date_fin' => '2026-11-20',
			'lieu' => 'Libreville',
			'cours_id' => $devWeb->id,
		]);
 
		Classe::create([
			'nom' => 'Comptabilite - Session Novembre 2026',
			'capacite_max' => 10,
			'date_debut' => '2026-11-23',
			'date_fin' => '2026-12-04',
			'lieu' => 'Libreville',
			'cours_id' => $comptabilite->id,
		]);
 
		Classe::create([
			'nom' => 'Gestion de projet - Session Decembre 2026',
			'capacite_max' => 10,
			'date_debut' => '2026-12-07',
			'date_fin' => '2026-12-11',
			'lieu' => 'Libreville',
			'cours_id' => $gestionProjet->id,
		]);
 
		Classe::create([
			'nom' => 'Anglais professionnel - Session Decembre 2026',
			'capacite_max' => 10,
			'date_debut' => '2026-12-14',
			'date_fin' => '2026-12-18',
			'lieu' => 'Libreville',
			'cours_id' => $anglais->id,
		]);
 
		// Classe DEJA COMMENCEE, pour tester la carte "Cours en cours" du dashboard.
		// Les dates sont relatives a aujourd'hui : elle reste "en cours" quel que soit
		// le jour ou on lance le seed. Aucune inscription n'est creee ici : c'est le
		// particulier qui s'inscrit avec le bouton, pour tester tout le parcours.
		Classe::create([
			'nom' => 'Initiation - Session en cours',
			'capacite_max' => 10,
			'date_debut' => now()->subDays(2),
			'date_fin' => now()->addDays(14),
			'lieu' => 'Libreville',
			'cours_id' => $initiation->id,
		]);
	}
}