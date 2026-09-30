<?php

namespace Database\Seeders;

use App\Models\Cours;
use App\Models\CoursResource;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class CoursResourceSeeder extends Seeder
{
    /**
     * Ajoute un document (PDF de test) et un lien vidéo à chaque cours,
     * pour tester l'affichage côté utilisateur sans tout uploader à la main.
     * Doit tourner après CoursSeeder.
     */
    public function run(): void
    {
        Cours::all()->each(function (Cours $cours) {
            $this->ajouterFichierTest($cours);
            $this->ajouterVideoTest($cours);
        });
    }

    private function ajouterFichierTest(Cours $cours): void
    {
        $chemin = 'cours_documents/' . $cours->id . '/support-' . $cours->id . '.pdf';

        Storage::disk('public')->put($chemin, $this->pdfDeTest($cours->titre));

        CoursResource::create([
            'cours_id' => $cours->id,
            'type' => 'fichier',
            'titre' => 'Support de cours — ' . $cours->titre,
            'chemin' => $chemin,
            'extension' => 'pdf',
            'taille' => Storage::disk('public')->size($chemin),
        ]);
    }

    private function ajouterVideoTest(Cours $cours): void
    {
        CoursResource::create([
            'cours_id' => $cours->id,
            'type' => 'video',
            'titre' => 'Vidéo de présentation — ' . $cours->titre,
            'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ]);
    }

    /**
     * PDF minimal valide (une page, un titre) — juste assez pour tester
     * l'ouverture/le téléchargement. Ce n'est PAS un vrai support de cours,
     * juste un fichier de test généré à la volée.
     */
    private function pdfDeTest(string $titre): string
    {
        $texte = 'Support de cours : ' . str_replace(['(', ')'], '', $titre);
        $contenuFlux = "BT /F1 14 Tf 20 100 Td ({$texte}) Tj ET";
        $longueur = strlen($contenuFlux);

        return <<<PDF
%PDF-1.1
1 0 obj
<< /Type /Catalog /Pages 2 0 R >>
endobj
2 0 obj
<< /Type /Pages /Kids [3 0 R] /Count 1 >>
endobj
3 0 obj
<< /Type /Page /Parent 2 0 R /Resources << /Font << /F1 4 0 R >> >> /MediaBox [0 0 300 144] /Contents 5 0 R >>
endobj
4 0 obj
<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>
endobj
5 0 obj
<< /Length {$longueur} >>
stream
{$contenuFlux}
endstream
endobj
trailer
<< /Root 1 0 R /Size 6 >>
%%EOF
PDF;
    }
}