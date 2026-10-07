<?php

namespace App\Http\Controllers;

use App\Models\Cours;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

// CRUD de la banque de questions (QCM), réservé à l'administrateur.
// Une question appartient à un cours et possède de 2 à 6 options dont UNE seule est correcte
// (le calcul du score dans QuizFinalisationService repose sur cette règle).
class QuestionController extends Controller
{
    private const MIN_OPTIONS = 2;
    private const MAX_OPTIONS = 6;

    // Liste paginée, filtrable par cours et par mot-clé dans l'énoncé
    public function index(Request $request)
    {
        $questions = Question::with(['cours:id,titre', 'options'])
            // reponses_count = nombre de fois où la question a été posée dans une tentative
            ->withCount('reponses')
            ->when($request->filled('cours_id'), fn ($q) => $q->where('cours_id', $request->input('cours_id')))
            ->when($request->filled('q'), fn ($q) => $q->where('enonce', 'like', '%' . $request->input('q') . '%'))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        // questions_count sert à repérer d'un coup d'œil les cours dont la banque est vide
        $cours = Cours::withCount('questions')->orderBy('titre')->get();

        return view('questions.index', compact('questions', 'cours'));
    }

    public function create(Request $request)
    {
        return view('questions.create', [
            'cours'       => Cours::orderBy('titre')->get(['id', 'titre']),
            'coursChoisi' => $request->input('cours_id'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->valider($request);

        // Transaction : la question et ses options sont créées ensemble ou pas du tout
        $question = DB::transaction(function () use ($data) {
            $question = Question::create([
                'enonce'   => $data['enonce'],
                'cours_id' => $data['cours_id'],
            ]);

            foreach ($data['options'] as $cle => $option) {
                $question->options()->create([
                    'libelle'     => $option['libelle'],
                    // "correcte" contient la clé de l'option cochée dans le formulaire
                    'est_correct' => (string) $cle === (string) $data['correcte'],
                ]);
            }

            return $question;
        });

        return redirect()
            ->route('admin.questions.index', ['cours_id' => $question->cours_id])
            ->with('success', 'La question a été ajoutée à la banque.');
    }

    public function edit(Question $question)
    {
        $question->loadCount('reponses')->load('options');

        return view('questions.edit', [
            'question' => $question,
            'cours'    => Cours::orderBy('titre')->get(['id', 'titre']),
        ]);
    }

    public function update(Request $request, Question $question)
    {
        $data = $this->valider($request);

        DB::transaction(function () use ($data, $question) {
            $question->update([
                'enonce'   => $data['enonce'],
                'cours_id' => $data['cours_id'],
            ]);

            $existantes = $question->options()->get()->keyBy('id');
            $idsGardes  = [];

            foreach ($data['options'] as $cle => $option) {
                $attributs = [
                    'libelle'     => $option['libelle'],
                    'est_correct' => (string) $cle === (string) $data['correcte'],
                ];

                // On met à jour l'option existante (même id) plutôt que de la recréer :
                // les réponses déjà données dans des tentatives passées continuent de pointer dessus.
                // Le test ->has() empêche aussi de toucher à l'option d'une AUTRE question.
                $id = $option['id'] ?? null;
                if ($id && $existantes->has((int) $id)) {
                    $existantes[(int) $id]->update($attributs);
                    $idsGardes[] = (int) $id;
                } else {
                    $idsGardes[] = $question->options()->create($attributs)->id;
                }
            }

            // Les options retirées du formulaire sont supprimées
            $question->options()->whereNotIn('id', $idsGardes)->delete();
        });

        return redirect()
            ->route('admin.questions.index', ['cours_id' => $question->cours_id])
            ->with('success', 'La question a été modifiée.');
    }

    public function destroy(Question $question)
    {
        // Les options et les lignes reponses_quiz liées partent en cascade (clés étrangères)
        $coursId = $question->cours_id;
        $question->delete();

        return redirect()
            ->route('admin.questions.index', ['cours_id' => $coursId])
            ->with('success', 'La question a été supprimée.');
    }

    // Règles communes à store() et update()
    private function valider(Request $request): array
    {
        $data = $request->validate([
            'cours_id'           => ['required', 'exists:cours,id'],
            'enonce'             => ['required', 'string', 'max:2000'],
            'options'            => ['required', 'array', 'min:' . self::MIN_OPTIONS, 'max:' . self::MAX_OPTIONS],
            'options.*.id'       => ['nullable', 'integer'],
            'options.*.libelle'  => ['required', 'string', 'max:255'],
            'correcte'           => ['required'],
        ], [
            'options.min'                => 'Il faut au moins ' . self::MIN_OPTIONS . ' options.',
            'options.max'                => 'Maximum ' . self::MAX_OPTIONS . ' options.',
            'options.*.libelle.required' => 'Chaque option doit avoir un texte.',
            'correcte.required'          => 'Indiquez quelle option est la bonne réponse.',
        ]);

        // La bonne réponse doit désigner une des options envoyées
        if (!array_key_exists($data['correcte'], $data['options'])) {
            throw ValidationException::withMessages(['correcte' => 'Indiquez quelle option est la bonne réponse.']);
        }

        // Pas deux options identiques (insensible à la casse)
        $libelles = array_map(fn ($o) => mb_strtolower(trim($o['libelle'])), $data['options']);
        if (count(array_unique($libelles)) !== count($libelles)) {
            throw ValidationException::withMessages(['options' => 'Deux options ont le même texte.']);
        }

        return $data;
    }
}