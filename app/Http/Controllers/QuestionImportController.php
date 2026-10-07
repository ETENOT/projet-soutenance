<?php

namespace App\Http\Controllers;

use App\Models\Cours;
use App\Services\QuestionCsvImporter;
use Illuminate\Http\Request;

// Import en masse de questions depuis un fichier CSV (réservé à l'administrateur)
class QuestionImportController extends Controller
{
    // Nombre maximum d'erreurs affichées (le fichier complet est tout de même vérifié)
    private const MAX_ERREURS_AFFICHEES = 50;

    // Le service est injecté automatiquement par Laravel (pas besoin de "new")
    public function __construct(private QuestionCsvImporter $importer)
    {
    }

    public function form()
    {
        return view('questions.import', [
            'cours' => Cours::orderBy('titre')->get(['id', 'titre']),
        ]);
    }

    public function traiter(Request $request)
    {
        $request->validate([
            'fichier'  => ['required', 'file', 'extensions:csv,txt', 'max:2048'],
            'cours_id' => ['nullable', 'exists:cours,id'],
        ], [
            'fichier.required'   => 'Choisissez un fichier CSV.',
            'fichier.extensions' => 'Le fichier doit être au format .csv.',
            'fichier.max'        => 'Le fichier ne doit pas dépasser 2 Mo.',
        ]);

        $resultat = $this->importer->importer(
            $request->file('fichier')->getRealPath(),
            $request->filled('cours_id') ? (int) $request->input('cours_id') : null
        );

        // Au moins une erreur : rien n'a été enregistré, on réaffiche le formulaire avec la liste
        if ($resultat['erreurs']) {
            return back()
                ->withInput($request->except('fichier'))
                ->with('error', 'Import annulé : aucune question n\'a été enregistrée.')
                ->with('import_erreurs', array_slice($resultat['erreurs'], 0, self::MAX_ERREURS_AFFICHEES))
                ->with('import_total_erreurs', count($resultat['erreurs']));
        }

        $message = $resultat['crees'] . ' question(s) importée(s)';
        if ($resultat['ignores'] > 0) {
            $message .= ', ' . $resultat['ignores'] . ' doublon(s) ignoré(s)';
        }

        return redirect()->route('admin.questions.index')->with('success', $message . '.');
    }

    // Fichier modèle à remplir (séparateur ; + BOM UTF-8 pour s'ouvrir correctement dans Excel en français)
    public function modele()
    {
        $titre = Cours::orderBy('titre')->value('titre') ?? 'Microsoft Word';

        return response()->streamDownload(function () use ($titre) {
            $sortie = fopen('php://output', 'w');
            fwrite($sortie, "\xEF\xBB\xBF");

            $lignes = [
                ['cours', 'question', 'option_1', 'option_2', 'option_3', 'option_4', 'bonne_reponse'],
                [$titre, 'Quelle est la capitale du Gabon ?', 'Libreville', 'Port-Gentil', 'Franceville', 'Oyem', '1'],
                [$titre, 'Combien font 2 + 2 ?', '3', '4', '5', '', 'B'],
            ];
            foreach ($lignes as $ligne) {
                fputcsv($sortie, $ligne, ';', '"', '');
            }
            fclose($sortie);
        }, 'modele_import_questions.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}