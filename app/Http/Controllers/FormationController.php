<?php

namespace App\Http\Controllers;

use App\Models\Formation;
use App\Models\Entreprise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FormationController extends Controller
{
    /**
     * Liste toutes les formations
     */
    public function index()
    {
        $formations = Formation::with(['entreprise', 'city'])->latest()->get();

        return response()->json($formations);
    }

    public function getFormationsByEntreprise()
    {
        // 1) On récupère l’utilisateur connecté
        $user = Auth::user();
    
        // 2) Vérifier que cet utilisateur possède une entreprise
        if (!$user->entreprise) {
            return response()->json([
                'message' => 'Aucune entreprise associée à cet utilisateur.'
            ], 404);
        }
    
        // 3) Récupérer l’ID de l’entreprise
        $entrepriseId = $user->entreprise->id;
    
        // 4) Récupérer uniquement les formations liées à cette entreprise
        $formations = Formation::with(['entreprise', 'city'])
            ->where('entreprise_id', $entrepriseId)
            ->latest()
            ->get();
    
        return response()->json($formations);
    }
    /**
     * Créer une formation
     */
    public function store(Request $request)
    {
        try {
            // Validation
            $request->validate([
                'title' => 'required|string',
                'description' => 'required|string',
                'resume' => 'nullable|string',
                'programme' => 'nullable|string',
                'sector' => 'required|string',
                'city_id' => 'required|exists:cities,id',
                'price' => 'required|numeric',
                'duree' => 'required|string',
                'end_date' => 'required|date',
                'image' => 'required|file|max:4096',
            ]);
    
            $user = Auth::user();
    
            if (!$user->entreprise) {
                return response()->json(['message' => "Vous n'êtes pas une entreprise"], 403);
            }
    
            // Upload image
            $imagePath = null;
            $imageUrl = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('formations', 'public');
                $imageUrl = asset('storage/' . $imagePath);
            }
    
            // Création formation
            $formation = Formation::create([
                'entreprise_id' => $user->entreprise->id,
                'title' => $request->title,
                'description' => $request->description,
                'resume' => $request->resume,
                'programme' => $request->programme,
                'sector' => $request->sector,
                'city_id' => $request->city_id,
                'price' => $request->price,
                'duree' => $request->duree,
                'end_date' => $request->end_date,
                'image' => $imagePath,
            ]);
    
            return response()->json([
                "message" => "Formation créée avec succès",
                "formation" => [
                    'id' => $formation->id,
                    'title' => $formation->title,
                    'description' => $formation->description,
                    'resume' => $formation->resume,
                    'programme' => $formation->programme,
                    'sector' => $formation->sector,
                    'city_id' => $formation->city_id,
                    'price' => $formation->price,
                    'duree' => $formation->duree,
                    'end_date' => $formation->end_date,
                    'image_couverture_url' => $imageUrl,
                ]
            ], 201);
    
        } catch (\Exception $e) {
            return response()->json([
                'message' => "Erreur lors de la création",
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    


    /**
     * Afficher une formation + incrémenter les vues
     */
    public function show($id)
    {
        $formation = Formation::with(['entreprise', 'city'])->findOrFail($id);

        // Incrémenter les vues
        $formation->increment('views');

        return response()->json($formation);
    }

    /**
     * Modifier une formation
     */
    public function update(Request $request, $id)
    {
        $formation = Formation::findOrFail($id);

        $user = Auth::user();

        if (!$user->entreprise || $user->entreprise->id !== $formation->entreprise_id) {
            return response()->json(["message" => "Non autorisé"], 403);
        }

        $formation->update($request->all());

        return response()->json([
            "message" => "Formation mise à jour",
            "formation" => $formation
        ]);
    }


    /**
     * Supprimer une formation
     */
    public function destroy($id)
    {
        $formation = Formation::findOrFail($id);

        $user = Auth::user();

        if (!$user->entreprise || $user->entreprise->id !== $formation->entreprise_id) {
            return response()->json(["message" => "Non autorisé"], 403);
        }

        $formation->delete();

        return response()->json(["message" => "Formation supprimée"]);
    }

    public function stats()
    {
        $user = Auth::user();
        $entreprise = $user->entreprise;
    
        if (!$entreprise) {
            return response()->json([
                'active_formations' => 0,
                'total_formations' => 0,
                'total_demands' => 0 // ou supprimer ce champ si inutile
            ]);
        }
    
        $total_formations = Formation::where('entreprise_id', $entreprise->id)->count();
        $active_formations = $entreprise->status === 'validated' ? $total_formations : 0;

                    
    

        $total_demands = 4;

        return response()->json([
            'active_formations' => $active_formations,
            'total_formations' => $total_formations,
            'total_demands' => $total_demands // si pas de table demandes
        ]);
    }
    
}
